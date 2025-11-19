#!/bin/bash
# Script per monitorare lo scraping in tempo reale

clear
echo "╔════════════════════════════════════════════════════════════╗"
echo "║     MONITOR SCRAPING COMUNI TOSCANA - TEMPO REALE         ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# 1. Verifica se il processo è attivo
echo "🔍 STATO PROCESSO:"
if ps aux | grep -v grep | grep "scrape_all_toscana.py" > /dev/null; then
    PID=$(ps aux | grep -v grep | grep "scrape_all_toscana.py" | awk '{print $2}')
    echo "   ✅ ATTIVO - PID: $PID"
    
    # Mostra da quanto tempo gira
    ELAPSED=$(ps -p $PID -o etime= | tr -d ' ')
    echo "   ⏱️  Tempo esecuzione: $ELAPSED"
else
    echo "   ❌ NON ATTIVO"
    echo ""
    echo "🚀 Per avviarlo:"
    echo "   cd /home/fabio/dev/NATAN_LOC/python_ai_service/app/scrapers"
    echo "   python3 scrape_all_toscana.py > output.log 2>&1 &"
    exit 1
fi

echo ""
echo "────────────────────────────────────────────────────────────"
echo ""

# 2. Statistiche dal file JSON (se esiste)
if [ -f "/home/fabio/dev/NATAN_LOC/python_ai_service/app/scrapers/toscana_scraping_results.json" ]; then
    echo "📊 STATISTICHE PROGRESSIVE:"
    
    TOTALE=$(cat /home/fabio/dev/NATAN_LOC/python_ai_service/app/scrapers/toscana_scraping_results.json | jq -r '.stats.totale // 0')
    FUNZIONANTI=$(cat /home/fabio/dev/NATAN_LOC/python_ai_service/app/scrapers/toscana_scraping_results.json | jq -r '.stats.funzionanti // 0')
    CLOUDFLARE=$(cat /home/fabio/dev/NATAN_LOC/python_ai_service/app/scrapers/toscana_scraping_results.json | jq -r '.stats.cloudflare // 0')
    DA_CONTATTARE=$(cat /home/fabio/dev/NATAN_LOC/python_ai_service/app/scrapers/toscana_scraping_results.json | jq -r '.stats.albo_non_trovato // 0')
    ATTI=$(cat /home/fabio/dev/NATAN_LOC/python_ai_service/app/scrapers/toscana_scraping_results.json | jq -r '.stats.atti_totali // 0')
    
    echo "   Comuni testati: $TOTALE / 273"
    echo "   ✅ Funzionanti: $FUNZIONANTI"
    echo "   🔒 Cloudflare: $CLOUDFLARE"
    echo "   ⚠️  Da contattare: $DA_CONTATTARE"
    echo "   📄 Atti totali: $ATTI"
    
    if [ "$TOTALE" -gt 0 ]; then
        PERCENTUALE=$((TOTALE * 100 / 273))
        echo ""
        echo "   Progresso: [$PERCENTUALE%] $TOTALE/273"
        
        # Barra progresso
        BAR_LENGTH=50
        FILLED=$((PERCENTUALE * BAR_LENGTH / 100))
        EMPTY=$((BAR_LENGTH - FILLED))
        
        printf "   ["
        printf "█%.0s" $(seq 1 $FILLED)
        printf "░%.0s" $(seq 1 $EMPTY)
        printf "]\n"
    fi
else
    echo "📊 STATISTICHE:"
    echo "   ⏳ In attesa del primo salvataggio..."
    echo "   (Risultati salvati ogni 10 comuni)"
fi

echo ""
echo "────────────────────────────────────────────────────────────"
echo ""

# 3. Ultimi comuni testati
if [ -f "/home/fabio/dev/NATAN_LOC/python_ai_service/app/scrapers/toscana_scraping_results.json" ]; then
    echo "📋 ULTIMI 5 COMUNI TESTATI:"
    cat /home/fabio/dev/NATAN_LOC/python_ai_service/app/scrapers/toscana_scraping_results.json | jq -r '.comuni[-5:] | .[] | "   • \(.comune) (\(.abitanti) ab.) → \(if .funzionante then "✅ " + .metodo + " - " + (.atti_recuperabili | tostring) + " atti" else "❌ " + (.tag // "ERROR") end)"'
fi

echo ""
echo "────────────────────────────────────────────────────────────"
echo ""
echo "💡 COMANDI UTILI:"
echo "   • Monitora live:    watch -n 5 ./monitor_scraping.sh"
echo "   • Vedi risultati:   cat toscana_scraping_results.json | jq"
echo "   • Ferma processo:   kill $PID"
echo ""
echo "Aggiornamento: $(date '+%H:%M:%S')"
