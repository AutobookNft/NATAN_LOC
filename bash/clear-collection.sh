#!/usr/bin/env bash
# Clear contents of a specific users_files/collections_N directory safely
# Posizionamento: root del progetto (deployato su server in /home/forge/default)
# Usage:
#   ./clear-collection.sh [N] [--base PATH] [-n|--dry-run] [-y]
# Examples:
#   ./clear-collection.sh 3
#   ./clear-collection.sh --base /home/forge/default/storage/app/public/users_files 4
#   ./clear-collection.sh 4 -n     # dry-run
#   ./clear-collection.sh 2 -y     # no prompt

set -Eeuo pipefail
shopt -s nullglob

# --- defaults ---
DRY_RUN=false
ASSUME_YES=false
BASE_CANDIDATES=()

# Detect sensible bases relative to CWD
if [[ -d "./users_files" ]]; then
  BASE_CANDIDATES+=("$(realpath ./users_files)")
fi
if [[ -d "storage/app/public/users_files" ]]; then
  BASE_CANDIDATES+=("$(realpath storage/app/public/users_files)")
fi
# Common Forge path
if [[ -d "/home/forge/default/storage/app/public/users_files" ]]; then
  BASE_CANDIDATES+=("/home/forge/default/storage/app/public/users_files")
fi

BASE=""

usage() {
  cat <<EOF
Clear contents of users_files/collections_N without removing the folder itself.

Options:
  N                 Numero della collection (obbligatorio se non interattivo)
  --base PATH       Percorso base a users_files (default: autodetect)
  -n, --dry-run     Mostra cosa verrebbe eliminato senza cancellare
  -y                Salta la conferma interattiva
  -h, --help        Mostra questo aiuto

Esempi:
  $0 3
  $0 --base /home/forge/default/storage/app/public/users_files 4
  $0 2 -n   # solo dry-run
EOF
}

is_number() { [[ "$1" =~ ^[0-9]+$ ]]; }

# --- parse args ---
COLL_NUM=""
while (( "$#" )); do
  case "${1:-}" in
    -h|--help)
      usage; exit 0 ;;
    -n|--dry-run)
      DRY_RUN=true; shift ;;
    -y)
      ASSUME_YES=true; shift ;;
    --base)
      BASE="${2:-}"; shift 2 ;;
    --*)
      echo "Errore: opzione sconosciuta: $1" >&2; usage; exit 2 ;;
    *)
      if [[ -z "$COLL_NUM" ]]; then
        COLL_NUM="$1"; shift
      else
        echo "Errore: argomento inaspettato: $1" >&2; usage; exit 2
      fi
      ;;
  esac
done

# Ask interactively if not provided
if [[ -z "$COLL_NUM" ]]; then
  read -rp "Inserisci il numero della collection (es. 3 per collections_3): " COLL_NUM
fi

if ! is_number "$COLL_NUM"; then
  echo "Errore: il valore indicato non è un numero: '$COLL_NUM'" >&2; exit 2
fi

# Resolve base
if [[ -z "$BASE" ]]; then
  for cand in "${BASE_CANDIDATES[@]}"; do
    if [[ -d "$cand" ]]; then BASE="$cand"; break; fi
  done
fi

if [[ -z "$BASE" ]]; then
  echo "Errore: impossibile determinare la base 'users_files'. Specifica --base PATH." >&2
  exit 3
fi

if [[ ! -d "$BASE" ]]; then
  echo "Errore: base non trovata: $BASE" >&2; exit 3
fi

TARGET="$BASE/collections_${COLL_NUM}"

# Safety checks
if [[ ! "$TARGET" =~ /users_files/collections_[0-9]+$ ]]; then
  echo "Errore di sicurezza: path target inatteso: $TARGET" >&2; exit 4
fi

if [[ ! -d "$TARGET" ]]; then
  echo "La directory non esiste: $TARGET" >&2; exit 0
fi

# Show summary
echo "Base:   $BASE"
echo "Target: $TARGET"

# List what would be removed
echo "Contenuto attuale (primi livelli):"
find "$TARGET" -mindepth 1 -maxdepth 1 -printf "%p\n" | sed 's/^/  - /'

if [[ "$DRY_RUN" == true ]]; then
  echo "Dry-run attivo: nessun file verrà eliminato."
  exit 0
fi

if [[ "$ASSUME_YES" != true ]]; then
  read -rp "Confermi la pulizia di '$TARGET'? Scrivi esattamente: collections_${COLL_NUM} per procedere: " CONF
  if [[ "$CONF" != "collections_${COLL_NUM}" ]]; then
    echo "Conferma non valida. Operazione annullata."; exit 1
  fi
fi

# Perform deletion safely (keeps the folder itself)
echo "Pulizia in corso..."
find "$TARGET" -mindepth 1 -delete

echo "Fatto. Verifica contenuto residuo:"
ls -la "$TARGET" || true
