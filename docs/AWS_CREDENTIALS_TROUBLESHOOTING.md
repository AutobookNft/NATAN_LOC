# 🔐 Troubleshooting Credenziali AWS

**Problema**: `InvalidClientTokenId` o `AuthFailure` quando si usa AWS CLI

---

## ✅ Verifiche da Fare

### 1. Verifica Credenziali su AWS Console

1. **Accedi a AWS Console:**
   - URL: https://504606041369.signin.aws.amazon.com/console
   - Username: `egi-kms-app`
   - Password: `6f!JcH3&`

2. **Verifica IAM User:**
   - Vai su IAM → Users → `egi-kms-app`
   - Verifica che l'utente sia **attivo**
   - Verifica che non ci siano restrizioni (es: IP whitelist)

3. **Verifica Access Keys:**
   - IAM → Users → `egi-kms-app` → Security credentials
   - Verifica che la Access Key `AKIAXK7G5EUMUA4KTCHE` sia **Active**
   - Se non è attiva, crea una nuova Access Key

### 2. Verifica Permessi IAM

L'utente `egi-kms-app` deve avere almeno questi permessi:

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "ec2:Describe*",
        "vpc:Describe*",
        "sts:GetCallerIdentity"
      ],
      "Resource": "*"
    }
  ]
}
```

**Oppure** usa il policy `ReadOnlyAccess` (più permissivo ma sicuro per read-only).

### 3. Crea Nuove Access Keys (se necessario)

1. IAM → Users → `egi-kms-app` → Security credentials
2. **Delete** la vecchia Access Key (se non funziona)
3. **Create access key** → Application running outside AWS
4. Salva nuova Access Key ID e Secret Access Key

### 4. Verifica Account Status

- Verifica che l'account AWS non sia sospeso
- Verifica che non ci siano problemi di fatturazione

---

## 🔧 Soluzione Alternativa: Usare AWS Console Manualmente

Se le credenziali CLI non funzionano, puoi esportare manualmente le informazioni necessarie:

### Informazioni da Esportare:

1. **VPC Information:**
   - VPC Dashboard → VPCs
   - Copia: VPC ID, CIDR Block

2. **EC2 Instance:**
   - EC2 Dashboard → Instances
   - Cerca per IP `13.48.57.194`
   - Copia: Instance ID, Instance Type, Private IP, VPC ID, Security Groups

3. **Security Groups:**
   - EC2 Dashboard → Security Groups
   - Trova Security Group associato all'istanza
   - Copia: Security Group ID, Name

4. **Region:**
   - Guarda in alto a destra nella Console
   - Copia: Region name (es: `eu-south-1`)

---

## 📝 Template per Compilare Manualmente

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

## 🚀 Prossimi Passi

1. **Verifica credenziali** su AWS Console
2. **Crea nuove Access Keys** se necessario
3. **Verifica permessi IAM** dell'utente
4. **Riprova configurazione AWS CLI** o esporta manualmente

---

**Versione**: 1.0.0  
**Status**: TROUBLESHOOTING


