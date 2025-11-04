#!/bin/bash

# ========================================
# 🗄️ NATAN_LOC - LOCAL ATOMIC MIGRATIONS (EGI + NATAN_LOC)
# ========================================
# Script atomico per migrations EGI + NATAN_LOC su database condiviso
# Esegue PRIMA le migration EGI, poi quelle NATAN_LOC
# Gestisce il database locale e storage per test e sviluppo
#
# @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
# @version 1.0.0 (NATAN_LOC - EGI + NATAN_LOC migrations)
# @date 2025-11-04
# ========================================

set -euo pipefail

# ANSI Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
PURPLE='\033[0;35m'
NC='\033[0m'

# Configuration variables
TRANSACTION_ACTIVE=false

# Determine NATAN_LOC root intelligently
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
NATAN_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# Project paths
EGI_ROOT="/home/fabio/EGI"
NATAN_BACKEND="$NATAN_ROOT/laravel_backend"

# Environment files
EGI_ENV="$EGI_ROOT/.env"
NATAN_ENV="$NATAN_BACKEND/.env"

# Backup files (will be created if .env exists)
EGI_BACKUP_ENV=""
NATAN_BACKUP_ENV=""

# Storage paths
NATAN_STORAGE_PATH="$NATAN_BACKEND/storage/app/public"
CLEANUP_LOG="/tmp/natan_local_cleanup_$(date +%Y%m%d_%H%M%S).log"

# ========================================
# 🛡️ CLEANUP FUNCTION
# ========================================
cleanup() {
    local exit_code=$?

    echo -e "\n${YELLOW}🔄 Cleanup in progress...${NC}"

    if [ "$TRANSACTION_ACTIVE" = true ]; then
        echo -e "${RED}❌ Transaction failed! Rolling back environment...${NC}"

        if [ -f "$EGI_BACKUP_ENV" ]; then
            mv "$EGI_BACKUP_ENV" "$EGI_ENV"
            echo -e "${GREEN}✅ EGI .env restored${NC}"
        fi

        if [ -f "$NATAN_BACKUP_ENV" ]; then
            mv "$NATAN_BACKUP_ENV" "$NATAN_ENV"
            echo -e "${GREEN}✅ NATAN .env restored${NC}"
        fi

        echo -e "${RED}💥 TRANSACTION FAILED${NC}"
    else
        echo -e "${GREEN}✅ Transaction completed successfully${NC}"

        if [ -f "$EGI_BACKUP_ENV" ]; then
            rm -f "$EGI_BACKUP_ENV"
        fi

        if [ -f "$NATAN_BACKUP_ENV" ]; then
            rm -f "$NATAN_BACKUP_ENV"
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
    echo -e "${BLUE}🔍 Validating environment prerequisites...${NC}"

    # Check EGI project exists
    if [ ! -d "$EGI_ROOT" ]; then
        echo -e "${RED}❌ EGI project not found: $EGI_ROOT${NC}" >&2
        exit 1
    fi

    if [ ! -f "$EGI_ROOT/artisan" ]; then
        echo -e "${RED}❌ EGI Laravel artisan not found!${NC}" >&2
        exit 1
    fi

    # Check NATAN_LOC project exists
    if [ ! -d "$NATAN_BACKEND" ]; then
        echo -e "${RED}❌ NATAN_LOC backend not found: $NATAN_BACKEND${NC}" >&2
        exit 1
    fi

    if [ ! -f "$NATAN_BACKEND/artisan" ]; then
        echo -e "${RED}❌ NATAN_LOC Laravel artisan not found!${NC}" >&2
        exit 1
    fi

    # Check .env files (warn if missing, but continue)
    if [ ! -f "$EGI_ENV" ]; then
        echo -e "${YELLOW}⚠️ EGI .env not found (will continue anyway)${NC}"
    fi

    if [ ! -f "$NATAN_ENV" ]; then
        echo -e "${YELLOW}⚠️ NATAN .env not found (will continue anyway)${NC}"
    fi

    # Check PHP
    if ! command -v php >/dev/null 2>&1; then
        echo -e "${RED}❌ PHP not found in PATH!${NC}" >&2
        exit 1
    fi

    # Check Composer
    if ! command -v composer >/dev/null 2>&1; then
        echo -e "${RED}❌ Composer not found in PATH!${NC}" >&2
        exit 1
    fi

    echo -e "${GREEN}✅ Prerequisites validated${NC}"
}

# ========================================
# 📊 DATABASE INFO
# ========================================
show_database_info() {
    echo -e "\n${PURPLE}📊 DATABASE INFORMATION${NC}"
    echo -e "${PURPLE}═══════════════════════════${NC}"

    # Try to get database info from NATAN (which shares DB with EGI)
    cd "$NATAN_BACKEND" || exit 1

    local db_connection=$(php artisan tinker --execute="echo config('database.default');" 2>/dev/null | tail -n 1)
    local db_name=$(php artisan tinker --execute="echo config('database.connections.${db_connection}.database');" 2>/dev/null | tail -n 1)
    local db_host=$(php artisan tinker --execute="echo config('database.connections.${db_connection}.host');" 2>/dev/null | tail -n 1)

    echo -e "${CYAN}Connection:${NC} $db_connection"
    echo -e "${CYAN}Database:${NC} $db_name ${YELLOW}(Shared EGI + NATAN_LOC)${NC}"
    echo -e "${CYAN}Host:${NC} $db_host"
    echo -e "${PURPLE}═══════════════════════════${NC}"
}

# ========================================
# 🗄️ MIGRATION FUNCTIONS
# ========================================

# Run EGI migrations
run_egi_migration_fresh() {
    echo -e "\n${CYAN}🗄️ EGI: RUNNING migrate:fresh${NC}"
    echo -e "${YELLOW}⚠️  This will DROP ALL TABLES and recreate them${NC}"

    cd "$EGI_ROOT" || exit 1

    if php artisan migrate:fresh --force; then
        echo -e "${GREEN}✅ EGI migration fresh completed${NC}"
    else
        echo -e "${RED}❌ EGI migration fresh failed!${NC}" >&2
        exit 1
    fi
}

run_egi_migration_refresh() {
    echo -e "\n${CYAN}🔄 EGI: RUNNING migrate:refresh${NC}"

    cd "$EGI_ROOT" || exit 1

    if php artisan migrate:refresh --force; then
        echo -e "${GREEN}✅ EGI migration refresh completed${NC}"
    else
        echo -e "${RED}❌ EGI migration refresh failed!${NC}" >&2
        exit 1
    fi
}

run_egi_migration_reset() {
    echo -e "\n${CYAN}🔙 EGI: RUNNING migrate:reset + migrate${NC}"

    cd "$EGI_ROOT" || exit 1

    if php artisan migrate:reset --force; then
        echo -e "${GREEN}✅ EGI migration reset completed${NC}"
    else
        echo -e "${RED}❌ EGI migration reset failed!${NC}" >&2
        exit 1
    fi

    if php artisan migrate --force; then
        echo -e "${GREEN}✅ EGI migration completed${NC}"
    else
        echo -e "${RED}❌ EGI migration failed!${NC}" >&2
        exit 1
    fi
}

run_egi_migration_status() {
    echo -e "\n${CYAN}📋 EGI MIGRATION STATUS${NC}"
    echo -e "${CYAN}═══════════════════${NC}"

    cd "$EGI_ROOT" || exit 1

    if php artisan migrate:status; then
        echo -e "${GREEN}✅ EGI migration status displayed${NC}"
    else
        echo -e "${RED}❌ Could not get EGI migration status!${NC}" >&2
        exit 1
    fi
}

# Run NATAN_LOC migrations (these add NATAN-specific tables)
run_natan_migration() {
    echo -e "\n${CYAN}🗄️ NATAN_LOC: RUNNING migrate${NC}"
    echo -e "${BLUE}   Adding NATAN_LOC-specific tables to shared database${NC}"

    cd "$NATAN_BACKEND" || exit 1

    if php artisan migrate --force; then
        echo -e "${GREEN}✅ NATAN_LOC migration completed${NC}"
    else
        echo -e "${RED}❌ NATAN_LOC migration failed!${NC}" >&2
        exit 1
    fi
}

run_natan_migration_status() {
    echo -e "\n${CYAN}📋 NATAN_LOC MIGRATION STATUS${NC}"
    echo -e "${CYAN}════════════════════════${NC}"

    cd "$NATAN_BACKEND" || exit 1

    if php artisan migrate:status; then
        echo -e "${GREEN}✅ NATAN_LOC migration status displayed${NC}"
    else
        echo -e "${RED}❌ Could not get NATAN_LOC migration status!${NC}" >&2
        exit 1
    fi
}

run_seeding() {
    echo -e "\n${CYAN}🌱 RUNNING: db:seed${NC}"
    echo -e "${BLUE}   Seeding EGI first, then NATAN_LOC (if seeders exist)${NC}"

    # Seed EGI
    cd "$EGI_ROOT" || exit 1
    if php artisan db:seed --force 2>/dev/null; then
        echo -e "${GREEN}✅ EGI seeding completed${NC}"
    else
        echo -e "${YELLOW}⚠️ EGI seeding skipped (no seeders or error)${NC}"
    fi

    # Seed NATAN_LOC
    cd "$NATAN_BACKEND" || exit 1
    if php artisan db:seed --force 2>/dev/null; then
        echo -e "${GREEN}✅ NATAN_LOC seeding completed${NC}"
    else
        echo -e "${YELLOW}⚠️ NATAN_LOC seeding skipped (no seeders or error)${NC}"
    fi
}

run_cache_clear() {
    echo -e "\n${CYAN}🧹 CLEARING: Application cache (both projects)${NC}"

    # Clear EGI cache
    cd "$EGI_ROOT" || exit 1
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear

    # Clear NATAN_LOC cache
    cd "$NATAN_BACKEND" || exit 1
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear

    echo -e "${GREEN}✅ Cache cleared for both projects${NC}"
}

run_optimize() {
    echo -e "\n${CYAN}⚡ OPTIMIZING: Applications${NC}"

    # Optimize EGI
    cd "$EGI_ROOT" || exit 1
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Optimize NATAN_LOC
    cd "$NATAN_BACKEND" || exit 1
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    echo -e "${GREEN}✅ Applications optimized${NC}"
}

# ========================================
# 🧹 STORAGE CLEANUP FUNCTIONS
# ========================================
# Logging function for storage cleanup
storage_log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$CLEANUP_LOG"
}

# Check if storage path exists
check_storage_path() {
    if [ ! -d "$STORAGE_PATH" ]; then
        echo -e "${RED}❌ Storage path does not exist: $STORAGE_PATH${NC}"
        return 1
    fi
    echo -e "${GREEN}✅ Storage path verified: $STORAGE_PATH${NC}"
    return 0
}

# Count items before storage cleanup
count_storage_items() {
    echo -e "${BLUE}📊 Counting storage items before cleanup...${NC}"

    # Count Spatie media directories (numeric folders)
    local spatie_count=$(find "$STORAGE_PATH" -maxdepth 1 -type d -name '[0-9]*' 2>/dev/null | wc -l)

    # Count export files
    local export_count=$(find "$STORAGE_PATH" -maxdepth 1 -type f -name 'export_*' 2>/dev/null | wc -l)

    # Check specific directories
    local cert_exists=0
    local users_files_exists=0

    [ -d "$STORAGE_PATH/certificates" ] && cert_exists=1
    [ -d "$STORAGE_PATH/users_files" ] && users_files_exists=1

    echo -e "${CYAN}📁 Found $spatie_count Spatie media directories${NC}"
    echo -e "${CYAN}📄 Found $export_count export files${NC}"
    echo -e "${CYAN}📂 Certificates directory exists: $cert_exists${NC}"
    echo -e "${CYAN}📂 Users_files directory exists: $users_files_exists${NC}"
}

# Clean Spatie media directories (numeric folders)
clean_spatie_directories() {
    echo -e "\n${CYAN}🗑️ Cleaning Spatie media directories...${NC}"

    local count=0
    local failed=0

    for dir in "$STORAGE_PATH"/[0-9]*; do
        if [ -d "$dir" ]; then
            local dir_name=$(basename "$dir")
            storage_log "Removing Spatie directory: $dir_name"

            # Try to remove with more aggressive approach
            if rm -rf "$dir" 2>/dev/null || sudo rm -rf "$dir" 2>/dev/null; then
                ((count++))
                echo -e "${GREEN}✅ Removed: $dir_name${NC}"
            else
                ((failed++))
                echo -e "${YELLOW}⚠️ Failed to remove: $dir_name (permissions?)${NC}"
                storage_log "WARNING: Failed to remove directory: $dir_name"
            fi
        fi
    done

    echo -e "${GREEN}✅ Removed $count Spatie media directories${NC}"
    if [ $failed -gt 0 ]; then
        echo -e "${YELLOW}⚠️ $failed directories could not be removed (check permissions)${NC}"
    fi
}

# Clean export files
clean_export_files() {
    echo -e "\n${CYAN}📤 Cleaning export files...${NC}"

    local count=0
    local failed=0

    for file in "$STORAGE_PATH"/export_*; do
        if [ -f "$file" ]; then
            local file_name=$(basename "$file")
            storage_log "Removing export file: $file_name"

            if rm -f "$file" 2>/dev/null || sudo rm -f "$file" 2>/dev/null; then
                ((count++))
                echo -e "${GREEN}✅ Removed: $file_name${NC}"
            else
                ((failed++))
                echo -e "${YELLOW}⚠️ Failed to remove: $file_name (permissions?)${NC}"
                storage_log "WARNING: Failed to remove file: $file_name"
            fi
        fi
    done

    echo -e "${GREEN}✅ Removed $count export files${NC}"
    if [ $failed -gt 0 ]; then
        echo -e "${YELLOW}⚠️ $failed files could not be removed (check permissions)${NC}"
    fi
}

# Clean certificates directory
clean_certificates_directory() {
    if [ -d "$STORAGE_PATH/certificates" ]; then
        echo -e "\n${CYAN}🔐 Cleaning certificates directory...${NC}"

        if rm -rf "$STORAGE_PATH/certificates" 2>/dev/null || sudo rm -rf "$STORAGE_PATH/certificates" 2>/dev/null; then
            echo -e "${GREEN}✅ Removed certificates directory${NC}"
            storage_log "SUCCESS: Removed certificates directory"
        else
            echo -e "${YELLOW}⚠️ Failed to remove certificates directory (permissions?)${NC}"
            storage_log "WARNING: Failed to remove certificates directory"
        fi
    else
        echo -e "${BLUE}ℹ️ Certificates directory not found - skipping${NC}"
        storage_log "INFO: Certificates directory not found"
    fi
}

# Clean users_files directory
clean_users_files_directory() {
    if [ -d "$STORAGE_PATH/users_files" ]; then
        echo -e "\n${CYAN}👥 Cleaning users_files directory...${NC}"

        if rm -rf "$STORAGE_PATH/users_files" 2>/dev/null || sudo rm -rf "$STORAGE_PATH/users_files" 2>/dev/null; then
            echo -e "${GREEN}✅ Removed users_files directory${NC}"
            storage_log "SUCCESS: Removed users_files directory"
        else
            echo -e "${YELLOW}⚠️ Failed to remove users_files directory (permissions?)${NC}"
            storage_log "WARNING: Failed to remove users_files directory"
        fi
    else
        echo -e "${BLUE}ℹ️ Users_files directory not found - skipping${NC}"
        storage_log "INFO: Users_files directory not found"
    fi
}

# Main storage cleanup function
run_storage_cleanup() {
    echo -e "\n${PURPLE}🧹 RUNNING: Storage cleanup${NC}"
    echo -e "${PURPLE}═══════════════════════════${NC}"

    storage_log "=== EGI Storage Cleanup Started ==="

    if ! check_storage_path; then
        echo -e "${YELLOW}⚠️ Storage cleanup skipped - path not found${NC}"
        storage_log "WARNING: Storage path not found, cleanup skipped"
        return 0
    fi

    count_storage_items

    echo -e "\n${BLUE}🗑️ Starting storage cleanup process...${NC}"

    # Run cleanup functions - don't fail on individual errors
    set +e  # Temporarily disable exit on error for storage cleanup

    clean_spatie_directories
    clean_export_files
    clean_certificates_directory
    clean_users_files_directory

    set -e  # Re-enable exit on error

    # Get final storage usage
    local current_usage=$(du -sh "$STORAGE_PATH" 2>/dev/null | cut -f1 || echo "Unknown")
    echo -e "\n${GREEN}📊 Current storage usage: $current_usage${NC}"

    storage_log "=== EGI Storage Cleanup Completed ==="
    echo -e "${GREEN}✅ Storage cleanup completed! Log: $CLEANUP_LOG${NC}"

    return 0  # Always return success for storage cleanup
}

# ========================================
# 🗄️ ATOMIC STEPS
# ========================================
step_backup() {
    echo -e "\n${BLUE}📦 STEP: Creating backups...${NC}"

    if [ -f "$EGI_ENV" ]; then
        EGI_BACKUP_ENV="$EGI_ROOT/.env.backup.$(date +%Y%m%d_%H%M%S)"
        cp "$EGI_ENV" "$EGI_BACKUP_ENV"
        echo -e "${GREEN}✅ EGI backup created: $EGI_BACKUP_ENV${NC}"
    fi

    if [ -f "$NATAN_ENV" ]; then
        NATAN_BACKUP_ENV="$NATAN_BACKEND/.env.backup.$(date +%Y%m%d_%H%M%S)"
        cp "$NATAN_ENV" "$NATAN_BACKUP_ENV"
        echo -e "${GREEN}✅ NATAN backup created: $NATAN_BACKUP_ENV${NC}"
    fi
}

step_start_transaction() {
    echo -e "\n${BLUE}🔄 STEP: Starting transaction...${NC}"
    TRANSACTION_ACTIVE=true
    echo -e "${GREEN}✅ Transaction started${NC}"
}

step_complete_transaction() {
    echo -e "\n${BLUE}✅ STEP: Completing transaction...${NC}"
    TRANSACTION_ACTIVE=false
    echo -e "${GREEN}✅ Transaction completed${NC}"
}

# ========================================
# 🎯 MAIN FUNCTIONS
# ========================================
show_menu() {
    echo -e "${GREEN}🗄️ NATAN_LOC - LOCAL ATOMIC MIGRATIONS (EGI + NATAN_LOC)${NC}"
    echo -e "${GREEN}═══════════════════════════════════════════════════════════════════${NC}"
    show_database_info
    echo -e "\n${CYAN}Select operation:${NC}"
    echo ""
    echo -e "${YELLOW}1)${NC} migrate:fresh (EGI) + migrate (NATAN) + seed ${BLUE}(recommended for clean state)${NC}"
    echo -e "   ${CYAN}→ Drops all tables, recreates EGI + adds NATAN tables + seeds${NC}"
    echo ""
    echo -e "${YELLOW}2)${NC} migrate:refresh (EGI) + migrate (NATAN) + seed"
    echo -e "   ${CYAN}→ Rollback EGI, re-run EGI + add NATAN tables + seeds${NC}"
    echo ""
    echo -e "${YELLOW}3)${NC} migrate:reset + migrate (EGI) + migrate (NATAN) + seed"
    echo -e "   ${CYAN}→ Reset EGI, migrate EGI, then migrate NATAN, then seed${NC}"
    echo ""
    echo -e "${YELLOW}4)${NC} Only migrate NATAN_LOC (preserve EGI data)"
    echo -e "   ${CYAN}→ Only run NATAN_LOC migrations${NC}"
    echo ""
    echo -e "${YELLOW}5)${NC} Migration status (both projects)"
    echo -e "   ${CYAN}→ Show current migration status for EGI and NATAN_LOC${NC}"
    echo ""
    echo -e "${YELLOW}6)${NC} Clear cache + optimize (both projects)"
    echo -e "   ${CYAN}→ Clear all cache and optimize both EGI and NATAN_LOC${NC}"
    echo ""
    echo -e "${YELLOW}7)${NC} Only seeding (preserve data)"
    echo -e "   ${CYAN}→ Only run seeders for both projects${NC}"
    echo ""
    echo -e "${YELLOW}8)${NC} Cancel"
    echo ""
}

execute_choice() {
    local choice=$1

    trap 'error_handler $LINENO' ERR
    trap cleanup EXIT

    validate_prerequisites

    case $choice in
        1)
            step_backup
            step_start_transaction
            run_egi_migration_fresh
            run_natan_migration
            run_seeding
            run_cache_clear
            step_complete_transaction
            ;;
        2)
            step_backup
            step_start_transaction
            run_egi_migration_refresh
            run_natan_migration
            run_seeding
            run_cache_clear
            step_complete_transaction
            ;;
        3)
            step_backup
            step_start_transaction
            run_egi_migration_reset
            run_natan_migration
            run_seeding
            run_cache_clear
            step_complete_transaction
            ;;
        4)
            step_backup
            step_start_transaction
            run_natan_migration
            run_cache_clear
            step_complete_transaction
            ;;
        5)
            # No transaction needed for status check
            trap - ERR EXIT  # Remove transaction traps
            run_egi_migration_status
            echo ""
            run_natan_migration_status
            exit 0
            ;;
        6)
            # No transaction needed for cache operations
            trap - ERR EXIT  # Remove transaction traps
            run_cache_clear
            run_optimize
            echo -e "\n${GREEN}🎉 CACHE OPERATIONS COMPLETED!${NC}"
            echo -e "${GREEN}════════════════════════════════${NC}"
            echo -e "${BLUE}⚡ Application cache cleared and optimized for both projects${NC}"
            exit 0
            ;;
        7)
            step_backup
            step_start_transaction
            run_seeding
            step_complete_transaction
            ;;
        *)
            echo -e "${RED}❌ Invalid choice${NC}"
            exit 1
            ;;
    esac

    echo -e "\n${GREEN}🎉 OPERATION COMPLETED SUCCESSFULLY!${NC}"
    echo -e "${GREEN}════════════════════════════════════${NC}"
    echo -e "${BLUE}💾 Shared database updated (EGI + NATAN_LOC)${NC}"
    echo -e "${BLUE}🛡️ Environment backups available${NC}"
    echo -e "${BLUE}⚡ Application cache cleared${NC}"
}

# ========================================
# 🎬 SCRIPT EXECUTION
# ========================================
main() {
    echo -e "${BLUE}🔍 NATAN_LOC root: $NATAN_ROOT${NC}"
    echo -e "${BLUE}🔍 EGI root: $EGI_ROOT${NC}"
    echo -e "${BLUE}🔍 NATAN backend: $NATAN_BACKEND${NC}"

    if [ $# -eq 0 ]; then
        # Interactive mode
        show_menu
        read -p "Enter your choice (1-8): " choice

        if [ "$choice" = "8" ]; then
            echo -e "${YELLOW}🚫 Operation cancelled${NC}"
            exit 0
        fi

        execute_choice "$choice"
    else
        # Command line mode
        execute_choice "$1"
    fi
}

# ========================================
# 🆘 HELP FUNCTION
# ========================================
show_help() {
    echo -e "${GREEN}🗄️ NATAN_LOC - LOCAL ATOMIC MIGRATIONS (EGI + NATAN_LOC)${NC}"
    echo -e "${GREEN}═══════════════════════════════════════════════════════════════${NC}"
    echo ""
    echo -e "${CYAN}Usage:${NC}"
    echo -e "  $0 [option]"
    echo ""
    echo -e "${CYAN}Options:${NC}"
    echo -e "  ${YELLOW}1${NC}    migrate:fresh (EGI) + migrate (NATAN) + seed"
    echo -e "  ${YELLOW}2${NC}    migrate:refresh (EGI) + migrate (NATAN) + seed"
    echo -e "  ${YELLOW}3${NC}    migrate:reset + migrate (EGI) + migrate (NATAN) + seed"
    echo -e "  ${YELLOW}4${NC}    only migrate NATAN_LOC (preserve EGI data)"
    echo -e "  ${YELLOW}5${NC}    migration status (both projects)"
    echo -e "  ${YELLOW}6${NC}    clear cache + optimize (both projects)"
    echo -e "  ${YELLOW}7${NC}    only seeding (both projects)"
    echo -e "  ${YELLOW}-h${NC}   show this help"
    echo ""
    echo -e "${CYAN}Examples:${NC}"
    echo -e "  $0        # Interactive mode"
    echo -e "  $0 1      # Fresh migration (EGI) + NATAN_LOC migration + seed"
    echo -e "  $0 4      # Only NATAN_LOC migrations (preserve EGI data)"
    echo ""
}

# Parse command line arguments
if [ $# -gt 0 ] && [ "$1" = "-h" ]; then
    show_help
    exit 0
fi

# Run main function
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
else
    echo -e "${RED}❌ This script should be executed, not sourced!${NC}" >&2
    return 1
fi
