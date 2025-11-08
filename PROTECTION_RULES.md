# 🔒 REGOLE DI PROTEZIONE CODICE - NATAN_LOC

## ⚠️ CRITICO: Sistema PA (Pubblica Amministrazione)

Questo progetto gestisce dati e software per la Pubblica Amministrazione.  
**La perdita di codice può avere conseguenze LEGALI GRAVI.**

---

## 🛡️ PROTEZIONI IMPLEMENTATE

### 1. Pre-Commit Hook

**Blocca automaticamente:**
- ❌ Commit che rimuovono **> 100 righe** per singolo file
- ❌ Commit che rimuovono **> 50%** del contenuto di un file
- ❌ Commit che rimuovono **> 500 righe** totali

**Warning:**
- ⚠️ Commit che rimuovono **> 50 righe** per file

### 2. Pre-Push Hook

**Blocca automaticamente:**
- ❌ Push di commit che rimuovono **> 500 righe** totali

---

## 🚨 COSA FARE SE IL COMMIT È BLOCCATO

### Se è un ERRORE:
```bash
# 1. Verifica cosa hai modificato
git diff --cached

# 2. Verifica statistiche
git diff --cached --stat

# 3. Ripristina file modificati per errore
git restore --staged <file>

# 4. Verifica che non ci siano file sovrascritti
git status
```

### Se è INTENZIONALE (rarissimo):
```bash
# SOLO se sei ASSOLUTAMENTE SICURO
git commit --no-verify
```

**⚠️ ATTENZIONE:** Usa `--no-verify` SOLO se:
1. Hai verificato TUTTI i file modificati
2. Hai confermato che non hai sovrascritto file con versioni vecchie
3. Hai verificato che non hai fatto reset accidentali
4. Hai verificato che non hai perso codice funzionante

---

## 📋 CHECKLIST PRIMA DI COMMIT

```
[ ] Ho verificato git diff --cached?
[ ] Ho verificato git diff --cached --stat?
[ ] Non sto sovrascrivendo file con versioni vecchie?
[ ] Non ho fatto reset/restore accidentali?
[ ] Il codice che rimuovo è veramente obsoleto?
[ ] Ho fatto backup del codice critico?
```

---

## 🔍 VERIFICA STATO PROTEZIONI

```bash
# Verifica hook installati
./scripts/protect_code.sh

# Test pre-commit hook
git commit --allow-empty -m "test"

# Verifica modifiche prima di commit
git diff --cached --stat
```

---

## ⚠️ REGOLE FONDAMENTALI

1. **MAI fare `git restore` senza verificare la versione**
2. **MAI fare `git checkout` su file senza verificare**
3. **MAI sovrascrivere file con versioni vecchie**
4. **SEMPRE verificare `git diff` prima di commit**
5. **SEMPRE verificare `git diff --stat` per statistiche**

---

## 🚨 SE HAI FATTO UN ERRORE

### Ripristina da commit precedente:
```bash
# Trova commit con codice corretto
git log --oneline -- scripts/scrape_firenze_deliberazioni.py

# Ripristina file da commit specifico
git show <commit>:scripts/scrape_firenze_deliberazioni.py > scripts/scrape_firenze_deliberazioni.py

# Verifica e commit
git diff scripts/scrape_firenze_deliberazioni.py
git add scripts/scrape_firenze_deliberazioni.py
git commit -m "[FIX] Ripristino codice perso"
```

---

## 📞 SUPPORTO

Se hai dubbi o problemi:
1. **FERMATI** - non fare commit
2. Verifica con `git diff --cached`
3. Chiedi conferma prima di procedere

---

**Versione:** 1.0.0  
**Data:** 2025-11-05  
**Criticità:** ALTA - Sistema PA

