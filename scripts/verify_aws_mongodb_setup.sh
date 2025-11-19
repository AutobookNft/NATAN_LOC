#!/bin/bash

# ========================================
# 🔍 AWS MongoDB Setup Verification Script
# ========================================
# Verifica tutte le informazioni AWS necessarie per configurare MongoDB
#
# @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
# @version 1.0.0
# @date 2025-01-28
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

# Output file
OUTPUT_FILE="aws_mongodb_setup_info_$(date +%Y%m%d_%H%M%S).json"
SUMMARY_FILE="aws_mongodb_setup_summary_$(date +%Y%m%d_%H%M%S).md"

echo -e "${BLUE}🔍 Verifica Configurazione AWS per MongoDB Setup${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════${NC}\n"

# Check AWS CLI
if ! command -v aws &> /dev/null; then
    echo -e "${RED}❌ AWS CLI non installato!${NC}"
    echo -e "${CYAN}💡 Installa con: sudo apt install awscli${NC}"
    exit 1
fi

# Check AWS credentials
if ! aws sts get-caller-identity &> /dev/null; then
    echo -e "${RED}❌ AWS credentials non configurate!${NC}"
    echo -e "${CYAN}💡 Configura con: aws configure${NC}"
    exit 1
fi

echo -e "${GREEN}✅ AWS CLI configurato correttamente${NC}\n"

# Get current AWS account info
echo -e "${PURPLE}📋 AWS Account Information${NC}"
echo -e "${PURPLE}═══════════════════════════${NC}"
AWS_ACCOUNT=$(aws sts get-caller-identity --query 'Account' --output text)
AWS_USER=$(aws sts get-caller-identity --query 'Arn' --output text)
AWS_REGION=$(aws configure get region || echo "eu-south-1")

echo -e "${CYAN}Account ID:${NC} $AWS_ACCOUNT"
echo -e "${CYAN}User/Role:${NC} $AWS_USER"
echo -e "${CYAN}Default Region:${NC} $AWS_REGION"
echo ""

# 1. VPC Information
echo -e "${PURPLE}📡 VPC Information${NC}"
echo -e "${PURPLE}═══════════════════${NC}"
VPC_INFO=$(aws ec2 describe-vpcs --query 'Vpcs[*].[VpcId,CidrBlock,State]' --output json)
echo "$VPC_INFO" | jq -r '.[] | "VPC ID: \(.[0]) | CIDR: \(.[1]) | State: \(.[2])"' || echo "$VPC_INFO"
echo ""

# 2. EC2 Instances (cerca per IP pubblico 13.48.57.194)
echo -e "${PURPLE}💻 EC2 Instances (Laravel Forge)${NC}"
echo -e "${PURPLE}═══════════════════════════════════${NC}"
EC2_INSTANCES=$(aws ec2 describe-instances --query 'Reservations[*].Instances[*].[InstanceId,PublicIpAddress,PrivateIpAddress,InstanceType,State.Name,VpcId,SubnetId]' --output json)

# Cerca istanza con IP pubblico 13.48.57.194
TARGET_IP="13.48.57.194"
FOUND_INSTANCE=$(echo "$EC2_INSTANCES" | jq -r ".[] | select(.[1] == \"$TARGET_IP\") | .")

if [ -n "$FOUND_INSTANCE" ]; then
    echo -e "${GREEN}✅ Trovata istanza EC2 con IP pubblico $TARGET_IP${NC}"
    echo "$FOUND_INSTANCE" | jq -r '.[] | "Instance ID: \(.[0]) | Public IP: \(.[1]) | Private IP: \(.[2]) | Type: \(.[3]) | State: \(.[4]) | VPC: \(.[5]) | Subnet: \(.[6])"'
else
    echo -e "${YELLOW}⚠️ Istanza con IP $TARGET_IP non trovata${NC}"
    echo -e "${CYAN}Lista tutte le istanze:${NC}"
    echo "$EC2_INSTANCES" | jq -r '.[] | "Instance ID: \(.[0]) | Public IP: \(.[1]) | Private IP: \(.[2]) | Type: \(.[3]) | State: \(.[4])"'
fi
echo ""

# 3. Security Groups
echo -e "${PURPLE}🔒 Security Groups${NC}"
echo -e "${PURPLE}═══════════════════${NC}"
SG_INFO=$(aws ec2 describe-security-groups --query 'SecurityGroups[*].[GroupId,GroupName,Description,VpcId]' --output json)
echo "$SG_INFO" | jq -r '.[] | "SG ID: \(.[0]) | Name: \(.[1]) | VPC: \(.[3])"' || echo "$SG_INFO"
echo ""

# 4. Subnets
echo -e "${PURPLE}🌐 Subnets${NC}"
echo -e "${PURPLE}═══════════════════${NC}"
SUBNET_INFO=$(aws ec2 describe-subnets --query 'Subnets[*].[SubnetId,VpcId,CidrBlock,AvailabilityZone]' --output json)
echo "$SUBNET_INFO" | jq -r '.[] | "Subnet ID: \(.[0]) | VPC: \(.[1]) | CIDR: \(.[2]) | AZ: \(.[3])"' || echo "$SUBNET_INFO"
echo ""

# 5. Route Tables
echo -e "${PURPLE}🗺️ Route Tables${NC}"
echo -e "${PURPLE}═══════════════════${NC}"
RT_INFO=$(aws ec2 describe-route-tables --query 'RouteTables[*].[RouteTableId,VpcId]' --output json)
echo "$RT_INFO" | jq -r '.[] | "Route Table ID: \(.[0]) | VPC: \(.[1])"' || echo "$RT_INFO"
echo ""

# 6. Internet Gateways
echo -e "${PURPLE}🌍 Internet Gateways${NC}"
echo -e "${PURPLE}═══════════════════════${NC}"
IGW_INFO=$(aws ec2 describe-internet-gateways --query 'InternetGateways[*].[InternetGatewayId,Attachments[0].VpcId,State]' --output json)
echo "$IGW_INFO" | jq -r '.[] | "IGW ID: \(.[0]) | VPC: \(.[1]) | State: \(.[2])"' || echo "$IGW_INFO"
echo ""

# Generate JSON output
echo -e "${BLUE}📝 Generazione file output...${NC}"
cat > "$OUTPUT_FILE" << EOF
{
  "timestamp": "$(date -Iseconds)",
  "aws_account": "$AWS_ACCOUNT",
  "aws_user": "$AWS_USER",
  "default_region": "$AWS_REGION",
  "vpcs": $VPC_INFO,
  "ec2_instances": $EC2_INSTANCES,
  "security_groups": $SG_INFO,
  "subnets": $SUBNET_INFO,
  "route_tables": $RT_INFO,
  "internet_gateways": $IGW_INFO
}
EOF

# Generate Markdown summary
cat > "$SUMMARY_FILE" << EOF
# AWS MongoDB Setup - Informazioni Verificate

**Data**: $(date '+%Y-%m-%d %H:%M:%S')
**AWS Account**: $AWS_ACCOUNT
**Region**: $AWS_REGION

## 📡 VPC Information

$(echo "$VPC_INFO" | jq -r '.[] | "- **VPC ID**: `\(.[0])` | **CIDR**: `\(.[1])` | **State**: `\(.[2])`"')

## 💻 EC2 Instances (Laravel Forge)

$(echo "$EC2_INSTANCES" | jq -r '.[] | "- **Instance ID**: `\(.[0])` | **Public IP**: `\(.[1])` | **Private IP**: `\(.[2])` | **Type**: `\(.[3])` | **State**: `\(.[4])` | **VPC**: `\(.[5])`"')

## 🔒 Security Groups

$(echo "$SG_INFO" | jq -r '.[] | "- **SG ID**: `\(.[0])` | **Name**: `\(.[1])` | **VPC**: `\(.[3])`"')

## 🌐 Subnets

$(echo "$SUBNET_INFO" | jq -r '.[] | "- **Subnet ID**: `\(.[0])` | **VPC**: `\(.[1])` | **CIDR**: `\(.[2])` | **AZ**: `\(.[3])`"')

## 📊 File Completi

- JSON completo: \`$OUTPUT_FILE\`
- Questo summary: \`$SUMMARY_FILE\`
EOF

echo -e "${GREEN}✅ File generati:${NC}"
echo -e "  - ${CYAN}$OUTPUT_FILE${NC} (JSON completo)"
echo -e "  - ${CYAN}$SUMMARY_FILE${NC} (Summary Markdown)"
echo ""

echo -e "${GREEN}🎉 Verifica completata!${NC}"
echo -e "${BLUE}💡 Usa questi file per completare il questionario MongoDB${NC}"


