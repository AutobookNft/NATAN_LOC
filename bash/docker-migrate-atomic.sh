#!/bin/bash

# ========================================
# 🔄 FLORENCE EGI - ATOMIC MIGRATIONS + SEEDING
# ========================================
# Script atomico per migrations e seeding Docker
# Gestisce automaticamente il problema Redis password
#
# @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
# @version 1.0.0 (Migrations + Seeding)
# @date 2025-07-20
# ========================================

set -euo pipefail

# ANSI Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

# Transaction variables
ORIGINAL_ENV=".env"
DOCKER_ENV=".env.docker.safe"
BACKUP_ENV=".env.backup.$(date +%Y%m%d_%H%M%S)"
TRANSACTION_ACTIVE=false

# ========================================
# 🛡️ CLEANUP FUNCTION
# ========================================
cleanup() {
    local exit_code=$?

    echo -e "\n${YELLOW}🔄 Cleanup in progress...${NC}"

    if [ "$TRANSACTION_ACTIVE" = true ]; then
        echo -e "${RED}❌ Transaction failed! Rolling back environment...${NC}"

        if [ -f "$BACKUP_ENV" ]; then
            mv "$BACKUP_ENV" "$ORIGINAL_ENV"
            echo -e "${GREEN}✅ Original .env restored${NC}"
        fi

        echo -e "${RED}💥 TRANSACTION FAILED${NC}"
    else
        echo -e "${GREEN}✅ Transaction completed successfully${NC}"

        if [ -f "$BACKUP_ENV" ]; then
            rm -f "$BACKUP_ENV"
        fi
    fi

    exit $exit_code
}

# ========================================
# 🚨 ERROR HANDLER
# ========================================
error_handler() {
    local line_number=$1
    echo -e "\n${RED}💥 ERROR on line $line_number${NC}" >&2
    cleanup
}

# ========================================
# ✅ VALIDATION
# ========================================
validate_prerequisites() {
    echo -e "${BLUE}🔍 Validating prerequisites...${NC}"

    if [ ! -f "$ORIGINAL_ENV" ]; then
        echo -e "${RED}❌ Original .env not found!${NC}" >&2
        exit 1
    fi

    if [ ! -f "$DOCKER_ENV" ]; then
        echo -e "${RED}❌ Docker .env not found!${NC}" >&2
        exit 1
    fi

    if ! docker compose ps >/dev/null 2>&1; then
        echo -e "${RED}❌ Docker containers not running!${NC}" >&2
        echo -e "${CYAN}💡 Start with: docker compose up -d${NC}" >&2
        exit 1
    fi

    if ! docker compose exec app php --version >/dev/null 2>&1; then
        echo -e "${RED}❌ App container not responding!${NC}" >&2
        exit 1
    fi

    echo -e "${GREEN}✅ Prerequisites validated${NC}"
}

# ========================================
# 🔄 MIGRATION FUNCTIONS
# ========================================
run_migration_fresh() {
    echo -e "\n${CYAN}🗄️ RUNNING: migrate:fresh${NC}"
    echo -e "${YELLOW}⚠️  This will DROP ALL TABLES and recreate them${NC}"

    if docker compose exec app php artisan migrate:fresh --force; then
        echo -e "${GREEN}✅ Migration fresh completed${NC}"
    else
        echo -e "${RED}❌ Migration fresh failed!${NC}" >&2
        exit 1
    fi
}

run_migration_refresh() {
    echo -e "\n${CYAN}🔄 RUNNING: migrate:refresh${NC}"
    echo -e "${YELLOW}⚠️  This will rollback and re-run all migrations${NC}"

    if docker compose exec app php artisan migrate:refresh --force; then
        echo -e "${GREEN}✅ Migration refresh completed${NC}"
    else
        echo -e "${RED}❌ Migration refresh failed!${NC}" >&2
        exit 1
    fi
}

run_migration_reset() {
    echo -e "\n${CYAN}🔙 RUNNING: migrate:reset + migrate${NC}"
    echo -e "${YELLOW}⚠️  This will reset and re-run all migrations${NC}"

    if docker compose exec app php artisan migrate:reset --force; then
        echo -e "${GREEN}✅ Migration reset completed${NC}"
    else
        echo -e "${RED}❌ Migration reset failed!${NC}" >&2
        exit 1
    fi

    if docker compose exec app php artisan migrate --force; then
        echo -e "${GREEN}✅ Migration completed${NC}"
    else
        echo -e "${RED}❌ Migration failed!${NC}" >&2
        exit 1
    fi
}

run_seeding() {
    echo -e "\n${CYAN}🌱 RUNNING: db:seed (atomic)${NC}"

    if docker compose exec app php artisan db:seed --force; then
        echo -e "${GREEN}✅ Seeding completed successfully${NC}"
    else
        echo -e "${RED}❌ Seeding failed!${NC}" >&2
        exit 1
    fi
}

# ========================================
# 🔄 ATOMIC STEPS
# ========================================
step_backup() {
    echo -e "\n${BLUE}📦 STEP: Creating backup...${NC}"
    cp "$ORIGINAL_ENV" "$BACKUP_ENV"
    echo -e "${GREEN}✅ Backup created${NC}"
}

step_switch_env() {
    echo -e "\n${BLUE}🔄 STEP: Switching to Docker environment...${NC}"
    cp "$DOCKER_ENV" "$ORIGINAL_ENV"
    TRANSACTION_ACTIVE=true
    echo -e "${GREEN}✅ Environment switched${NC}"
}

step_restore_env() {
    echo -e "\n${BLUE}🔄 STEP: Restoring original environment...${NC}"
    mv "$BACKUP_ENV" "$ORIGINAL_ENV"
    TRANSACTION_ACTIVE=false
    echo -e "${GREEN}✅ Environment restored${NC}"
}

# ========================================
# 🎯 MAIN FUNCTIONS
# ========================================
show_menu() {
    echo -e "${GREEN}🔄 FLORENCE EGI - ATOMIC MIGRATIONS & SEEDING${NC}"
    echo -e "${GREEN}═══════════════════════════════════════════════${NC}"
    echo -e "${CYAN}Select migration strategy:${NC}"
    echo ""
    echo -e "${YELLOW}1)${NC} migrate:fresh + seed ${BLUE}(recommended)${NC}"
    echo -e "   ${CYAN}→ Drops all tables, recreates + seeds${NC}"
    echo ""
    echo -e "${YELLOW}2)${NC} migrate:refresh + seed"
    echo -e "   ${CYAN}→ Rollback all, re-run + seeds${NC}"
    echo ""
    echo -e "${YELLOW}3)${NC} migrate:reset + migrate + seed"
    echo -e "   ${CYAN}→ Reset, migrate, then seed${NC}"
    echo ""
    echo -e "${YELLOW}4)${NC} Only seeding (atomic)"
    echo -e "   ${CYAN}→ Only run seeders${NC}"
    echo ""
    echo -e "${YELLOW}5)${NC} Cancel"
    echo ""
}

execute_choice() {
    local choice=$1

    trap 'error_handler $LINENO' ERR
    trap cleanup EXIT

    validate_prerequisites
    step_backup
    step_switch_env

    case $choice in
        1)
            run_migration_fresh
            run_seeding
            ;;
        2)
            run_migration_refresh
            run_seeding
            ;;
        3)
            run_migration_reset
            run_seeding
            ;;
        4)
            run_seeding
            ;;
        *)
            echo -e "${RED}❌ Invalid choice${NC}"
            exit 1
            ;;
    esac

    step_restore_env

    echo -e "\n${GREEN}🎉 OPERATION COMPLETED SUCCESSFULLY!${NC}"
    echo -e "${GREEN}═══════════════════════════════════${NC}"
    echo -e "${BLUE}🐳 Database updated in Docker${NC}"
    echo -e "${BLUE}🛡️ Local environment unchanged${NC}"
}

# ========================================
# 🎬 SCRIPT EXECUTION
# ========================================
main() {
    if [ $# -eq 0 ]; then
        # Interactive mode
        show_menu
        read -p "Enter your choice (1-5): " choice

        if [ "$choice" = "5" ]; then
            echo -e "${YELLOW}🚫 Operation cancelled${NC}"
            exit 0
        fi

        execute_choice "$choice"
    else
        # Command line mode
        execute_choice "$1"
    fi
}

# Run main function
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
else
    echo -e "${RED}❌ This script should be executed, not sourced!${NC}" >&2
    return 1
fi
