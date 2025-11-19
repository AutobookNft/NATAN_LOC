# 🔐 Setup Accesso AWS per Verifica Configurazione MongoDB

**Versione**: 1.0.0  
**Data**: 2025-01-28  
**Contesto**: FlorenceEGI - NATAN_LOC MongoDB AWS Setup

---

## 🎯 Obiettivo

Verificare informazioni AWS necessarie per configurare MongoDB:
- VPC ID e CIDR Block
- Security Groups
- EC2 Instance details
- AWS Region
- Network configuration

---

## 📋 Opzioni di Accesso

### **Opzione 1: AWS CLI (RACCOMANDATO)**

#### **Step 1: Installare AWS CLI**

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install awscli -y

# Oppure con snap
sudo snap install aws-cli

# Verifica installazione
aws --version
```

#### **Step 2: Configurare Credenziali**

**Metodo A: AWS Access Keys (IAM User)**

1. **Crea IAM User su AWS Console:**
   - Vai su AWS Console → IAM → Users → Create User
   - Nome: `natan-mongodb-setup` (o simile)
   - Permissions: `ReadOnlyAccess` (o custom policy con solo EC2, VPC read)

2. **Crea Access Keys:**
   - IAM → Users → `natan-mongodb-setup` → Security credentials
   - Create access key → Application running outside AWS
   - Salva `Access Key ID` e `Secret Access Key`

3. **Configura AWS CLI:**
   ```bash
   aws configure
   ```
   - AWS Access Key ID: `[inserisci Access Key ID]`
   - AWS Secret Access Key: `[inserisci Secret Access Key]`
   - Default region: `eu-south-1` (o la tua regione)
   - Default output format: `json`

**Metodo B: AWS SSO (se usi AWS Organizations)**

```bash
aws configure sso
# Segui le istruzioni per login SSO
```

**Metodo C: Credenziali Temporanee (Session Token)**

```bash
# Se hai già credenziali temporanee
export AWS_ACCESS_KEY_ID="your-key"
export AWS_SECRET_ACCESS_KEY="your-secret"
export AWS_SESSION_TOKEN="your-token"  # Se presente
export AWS_DEFAULT_REGION="eu-south-1"
```

#### **Step 3: Verificare Accesso**

```bash
# Test connessione
aws sts get-caller-identity

# Lista regioni disponibili
aws ec2 describe-regions --query 'Regions[*].RegionName' --output table

# Verifica EC2 instances
aws ec2 describe-instances --query 'Reservations[*].Instances[*].[InstanceId,PublicIpAddress,PrivateIpAddress,InstanceType,State.Name]' --output table
```

---

### **Opzione 2: Script di Verifica Automatica**

Posso creare uno script che verifica tutte le informazioni necessarie:

```bash
#!/bin/bash
# verify_aws_mongodb_setup.sh

echo "🔍 Verifica Configurazione AWS per MongoDB Setup"
echo "================================================"

# 1. VPC Information
echo -e "\n📡 VPC Information:"
aws ec2 describe-vpcs --query 'Vpcs[*].[VpcId,CidrBlock,State]' --output table

# 2. Security Groups
echo -e "\n🔒 Security Groups:"
aws ec2 describe-security-groups --query 'SecurityGroups[*].[GroupId,GroupName,Description]' --output table

# 3. EC2 Instances
echo -e "\n💻 EC2 Instances:"
aws ec2 describe-instances --query 'Reservations[*].Instances[*].[InstanceId,PublicIpAddress,PrivateIpAddress,InstanceType,State.Name,VpcId]' --output table

# 4. Subnets
echo -e "\n🌐 Subnets:"
aws ec2 describe-subnets --query 'Subnets[*].[SubnetId,VpcId,CidrBlock,AvailabilityZone]' --output table

# 5. Route Tables
echo -e "\n🗺️ Route Tables:"
aws ec2 describe-route-tables --query 'RouteTables[*].[RouteTableId,VpcId]' --output table
```

**Eseguire:**
```bash
chmod +x verify_aws_mongodb_setup.sh
./verify_aws_mongodb_setup.sh > aws_info.json
```

---

### **Opzione 3: Export Manuale da AWS Console**

Se preferisci non installare AWS CLI, puoi esportare manualmente:

#### **Da AWS Console:**

1. **VPC Information:**
   - VPC Dashboard → VPCs
   - Copia: VPC ID, CIDR Block

2. **EC2 Instance:**
   - EC2 Dashboard → Instances
   - Cerca per IP `13.48.57.194`
   - Copia: Instance ID, Instance Type, Private IP, VPC ID, Security Groups

3. **Security Groups:**
   - EC2 Dashboard → Security Groups
   - Trova Security Group associato all'istanza EC2
   - Copia: Security Group ID, Name

4. **Region:**
   - Guarda in alto a destra nella Console AWS
   - Copia: Region name (es: `eu-south-1`)

#### **Template per Compilare:**

```markdown
## AWS Configuration Info

### VPC
- VPC ID: `_________________`
- CIDR Block: `_________________`
- Region: `_________________`

### EC2 Instance (Laravel Forge)
- Instance ID: `_________________`
- Instance Type: `_________________`
- Public IP: `13.48.57.194` (già verificato)
- Private IP: `_________________`
- VPC ID: `_________________`

### Security Groups
- Security Group ID (Laravel): `_________________`
- Security Group Name: `_________________`
```

---

### **Opzione 4: Laravel Forge API**

Se hai accesso a Laravel Forge, posso usare l'API di Forge:

```bash
# Installare Forge CLI (se disponibile)
# Oppure usare API direttamente
```

**Forge API Endpoints utili:**
- `GET /api/v1/servers` - Lista server
- `GET /api/v1/servers/{serverId}` - Dettagli server
- `GET /api/v1/servers/{serverId}/sites` - Siti sul server

---

## 🚀 Raccomandazione

**Per velocità e completezza: Opzione 1 (AWS CLI)**

1. Installare AWS CLI
2. Configurare credenziali IAM (ReadOnlyAccess)
3. Eseguire script di verifica automatica

**Per semplicità: Opzione 3 (Export Manuale)**

1. Accedere AWS Console
2. Copiare informazioni necessarie
3. Compilare template

---

## 🔒 Sicurezza

**Best Practices:**

1. **IAM User con Least Privilege:**
   - Solo permessi `ReadOnlyAccess` o custom policy con solo:
     - `ec2:Describe*`
     - `vpc:Describe*`
     - `iam:GetUser`

2. **Access Keys Rotation:**
   - Ruotare keys ogni 90 giorni
   - Eliminare keys non usate

3. **Credenziali Temporanee:**
   - Usare AWS SSO se disponibile
   - Usare session tokens con scadenza

4. **Non committare credenziali:**
   - Usare `~/.aws/credentials` (non committare)
   - Usare environment variables per CI/CD
   - Usare AWS Secrets Manager per produzione

---

## 📝 Prossimi Passi

Una volta configurato l'accesso AWS:

1. **Eseguire script di verifica** (Opzione 2)
2. **Completare questionario** con informazioni AWS
3. **Generare guida operativa** completa per MongoDB setup

---

**Versione**: 1.0.0  
**Status**: READY


