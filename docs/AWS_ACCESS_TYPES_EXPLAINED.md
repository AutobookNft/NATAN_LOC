# 🔐 Tipi di Accesso AWS - Spiegazione

**Versione**: 1.0.0  
**Data**: 2025-01-28  
**Contesto**: FlorenceEGI - NATAN_LOC MongoDB Setup

---

## 🎯 I 3 Tipi di Accesso AWS

AWS ha **3 sistemi di autenticazione diversi** che possono creare confusione:

### **1. AWS Builder ID** (Profilo AWS)
- **URL**: https://aws.amazon.com/it/profile
- **Scopo**: Profilo personale, preferenze, offerte
- **NON** dà accesso a risorse AWS
- **NON** serve per AWS CLI o Console operativa

### **2. AWS Account Root** (Account principale)
- **Email**: fabiocherici@hotmail.com
- **Password**: Gf2aZ#fj4$%DF7a#DNery487439
- **Scopo**: Accesso completo a TUTTE le risorse AWS
- **⚠️ ATTENZIONE**: Usa solo per emergenze, non per operazioni quotidiane

### **3. IAM User** (Utente operativo)
- **URL Console**: https://504606041369.signin.aws.amazon.com/console
- **Username**: egi-kms-app
- **Password**: x,Ck+M]FK4]!99* (nuova)
- **Scopo**: Accesso limitato a risorse specifiche
- **Access Keys**: Per AWS CLI e API

---

## 🔍 Perché "Non ho permessi sufficienti"?

Se sei l'unico admin ma vedi errori di permessi, potrebbe essere:

### **Problema 1: Stai usando IAM User invece di Root**
- L'utente `egi-kms-app` potrebbe avere permessi limitati
- **Soluzione**: Accedi come **Root** per verificare/creare Access Keys

### **Problema 2: Access Keys scadute o revocate**
- Le Access Keys possono essere disabilitate
- **Soluzione**: Crea nuove Access Keys come Root

### **Problema 3: Permessi IAM non configurati**
- L'utente IAM potrebbe non avere permessi per vedere/modificare risorse
- **Soluzione**: Aggiungi policy `ReadOnlyAccess` o `AdministratorAccess` all'utente

---

## ✅ Soluzione Pratica: Export Manuale

**La soluzione più semplice** è esportare manualmente le informazioni dalla Console AWS:

### **Step 1: Accedi come Root o IAM User**

**Opzione A: Root Account (se hai accesso)**
- Vai su: https://console.aws.amazon.com/
- Login con: fabiocherici@hotmail.com / Gf2aZ#fj4$%DF7a#DNery487439

**Opzione B: IAM User (se funziona)**
- Vai su: https://504606041369.signin.aws.amazon.com/console
- Login con: egi-kms-app / x,Ck+M]FK4]!99*

### **Step 2: Esporta Informazioni Necessarie**

Una volta dentro la Console AWS, copia queste informazioni:

#### **A. EC2 Instance (Laravel Forge)**

1. Vai su **EC2 Dashboard** → **Instances**
2. Cerca istanza con IP pubblico `13.48.57.194`
3. Clicca sull'istanza e copia:
   - **Instance ID**: `i-xxxxxxxxxxxxx`
   - **Instance Type**: `t3.medium` (o simile)
   - **Private IP**: `10.x.x.x` (o simile)
   - **VPC ID**: `vpc-xxxxxxxxx`
   - **Security Groups**: `sg-xxxxxxxxx`

#### **B. VPC Information**

1. Vai su **VPC Dashboard** → **Your VPCs**
2. Trova VPC associato all'istanza (dal VPC ID sopra)
3. Copia:
   - **VPC ID**: `vpc-xxxxxxxxx`
   - **CIDR Block**: `10.0.0.0/16` (o simile)

#### **C. Security Groups**

1. Vai su **EC2 Dashboard** → **Security Groups**
2. Trova Security Group associato all'istanza EC2
3. Copia:
   - **Security Group ID**: `sg-xxxxxxxxx`
   - **Security Group Name**: `launch-wizard-x` (o simile)

#### **D. Region**

1. Guarda in **alto a destra** nella Console AWS
2. Copia: **Region** (es: `eu-south-1`, `eu-central-1`)

---

## 📝 Template da Compilare

Copia questo template e compilalo con le informazioni trovate:

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

## 🚀 Alternativa: Creare Access Keys come Root

Se riesci ad accedere come Root, puoi creare nuove Access Keys:

1. **Accedi come Root**: https://console.aws.amazon.com/
2. Vai su **IAM** → **Users** → `egi-kms-app`
3. **Security credentials** → **Create access key**
4. Salva **Access Key ID** e **Secret Access Key**
5. Usa queste nuove credenziali per AWS CLI

---

## 💡 Raccomandazione

**Per velocità**: Usa l'**export manuale** dalla Console AWS
- Più veloce
- Non richiede configurazione AWS CLI
- Basta copiare le informazioni

**Per automazione futura**: Configura AWS CLI con Access Keys valide
- Permette automazione
- Script di verifica automatica
- Utile per operazioni future

---

**Versione**: 1.0.0  
**Status**: GUIDE

