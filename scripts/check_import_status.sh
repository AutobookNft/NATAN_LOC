#!/bin/bash

echo "📊 STATUS IMPORT DOCUMENTI"
echo "=========================="
echo ""

# Check processo attivo
if ps aux | grep -q "[s]crape_firenze"; then
    echo "✅ Processo ATTIVO"
    PID=$(cat /tmp/import_pid.txt 2>/dev/null || echo "N/A")
    echo "   PID: $PID"
else
    echo "⚠️  Processo NON attivo (potrebbe essere terminato)"
fi

echo ""

# Conta documenti processati
PROCESSED=$(grep -c "✅ Documento aggiornato" /tmp/import_full_*.log 2>/dev/null || echo "0")
echo "📄 Documenti processati: $PROCESSED"

# Ultimi 3 documenti
echo ""
echo "🔄 Ultimi documenti elaborati:"
grep "✅ Documento aggiornato" /tmp/import_full_*.log 2>/dev/null | tail -3 | while read line; do
    DOC=$(echo "$line" | grep -oP 'pa_act_[^ ]+')
    CHARS=$(echo "$line" | grep -oP '\d+ → \d+')
    echo "   - $DOC ($CHARS chars)"
done

# Errori eventuali
ERRORS=$(grep -c "❌" /tmp/import_full_*.log 2>/dev/null || echo "0")
if [ "$ERRORS" -gt "0" ]; then
    echo ""
    echo "⚠️  Errori rilevati: $ERRORS"
fi

echo ""
echo "📊 Stima completamento: $((1199 - PROCESSED)) documenti rimanenti"

