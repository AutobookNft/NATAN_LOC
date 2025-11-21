# Git Hooks - NATAN_LOC

Questa directory contiene i file sorgente per i Git hooks del progetto.

## 🚀 Installazione Rapida

```bash
# Dalla root del progetto
cd /home/fabio/NATAN_LOC
bash scripts/install-git-hooks.sh
```

## 📋 Hook Disponibili

- **pre-commit** - Previene eliminazione accidentale di codice (>100 righe per file, >500 totali)
- **pre-push** - Doppia verifica prima del push (>500 righe per commit)

## 📚 Documentazione Completa

Per documentazione dettagliata, regole, troubleshooting e best practices:

```bash
cat docs/git-hooks/README.md
```

O apri: `/home/fabio/NATAN_LOC/docs/git-hooks/README.md`

## 🔧 Bypass Temporaneo

Se necessario (solo per modifiche intenzionali):

```bash
git commit --no-verify -m "[TAG] Messaggio"
git push --no-verify
```

## 📝 Formato Commit Richiesto

I commit message devono seguire il formato standard del progetto con tag appropriati.

Esempi:
```
[FEAT] Aggiunta integrazione AI con RAG
[FIX] Corretto bug nella gestione token
[REFACTOR] Ottimizzato servizio chat
[DOC] Aggiornata documentazione API
[TEST] Aggiunti test per autenticazione
[CHORE] Aggiornate dipendenze
```

## ⚠️ Note Importanti

1. Questi file sono **sorgenti versionati** - modificali qui, non in `.git/hooks/`
2. Dopo modifiche, esegui `bash scripts/install-git-hooks.sh` per applicare
3. Gli hook **non** si sincronizzano automaticamente con git pull

## 🆘 Supporto

Se gli hook causano problemi, consulta la sezione Troubleshooting nella documentazione completa.

