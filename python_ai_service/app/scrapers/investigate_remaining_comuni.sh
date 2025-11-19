#!/bin/bash
# Script autonomo per investigare i 5 comuni rimanenti
# Esegui una volta: bash investigate_remaining_comuni.sh
# Risultati salvati in: investigation_results.json

OUTPUT_FILE="investigation_results.json"

echo "🔍 Investigazione automatica comuni rimanenti..."
echo "Risultati in: $OUTPUT_FILE"
echo ""

cat > "$OUTPUT_FILE" << 'EOF'
{
  "timestamp": "2025-11-13",
  "comuni_investigated": []
}
EOF

# Funzione test comune
test_comune() {
    local nome=$1
    local url=$2
    local abitanti=$3
    
    echo "========================================="
    echo "🏛️  $nome ($abitanti abitanti)"
    echo "========================================="
    
    # 1. Cerca link albo
    echo "1️⃣  Cerco link albo..."
    albo_links=$(curl -sL "$url" 2>/dev/null | grep -i -o 'href="[^"]*albo[^"]*"' | head -5)
    
    if [ -z "$albo_links" ]; then
        echo "   ❌ Nessun link 'albo' trovato"
        albo_url="NOT_FOUND"
    else
        echo "   ✅ Link trovati:"
        echo "$albo_links" | while read link; do
            echo "      $link"
        done
        # Prendi primo link
        albo_url=$(echo "$albo_links" | head -1 | sed 's/href="//;s/"//')
        # Se relativo, rendi assoluto
        if [[ ! "$albo_url" =~ ^http ]]; then
            albo_url="${url}${albo_url}"
        fi
    fi
    
    # 2. Cerca amministrazione trasparente
    echo ""
    echo "2️⃣  Cerco amministrazione trasparente..."
    trasp_links=$(curl -sL "$url" 2>/dev/null | grep -i -o 'href="[^"]*trasparent[^"]*"' | head -3)
    
    if [ -z "$trasp_links" ]; then
        echo "   ❌ Nessun link trasparenza"
    else
        echo "   ✅ Link trovati:"
        echo "$trasp_links" | while read link; do
            echo "      $link"
        done
    fi
    
    # 3. Test API comuni
    echo ""
    echo "3️⃣  Testo API REST..."
    api_endpoints=("/api/albo" "/rest/albo" "/api/atti" "/api/pubblicazioni" "/trasparenza-atti-cat/searchAtti")
    
    api_found=false
    for endpoint in "${api_endpoints[@]}"; do
        api_url="${url}${endpoint}"
        response=$(curl -s -o /dev/null -w "%{http_code}" "$api_url" 2>/dev/null)
        
        if [ "$response" = "200" ]; then
            echo "   ✅ $endpoint → HTTP 200"
            # Verifica se JSON
            content=$(curl -s "$api_url" 2>/dev/null | head -c 100)
            if [[ "$content" =~ ^\{.*\} ]] || [[ "$content" =~ ^\[.*\] ]]; then
                echo "      📦 Risposta JSON!"
                api_found=true
                break
            else
                echo "      ⚠️  Risposta HTML (non API vera)"
            fi
        fi
    done
    
    if [ "$api_found" = false ]; then
        echo "   ❌ Nessuna API REST trovata"
    fi
    
    # 4. Detecta piattaforma
    echo ""
    echo "4️⃣  Detection piattaforma..."
    homepage=$(curl -sL "$url" 2>/dev/null)
    
    platform="UNKNOWN"
    if echo "$homepage" | grep -q "wp-content\|wp-includes"; then
        platform="WordPress"
        echo "   🔵 WordPress"
    elif echo "$homepage" | grep -q "Drupal\|drupal"; then
        platform="Drupal"
        echo "   🟠 Drupal"
    elif echo "$homepage" | grep -q "joomla"; then
        platform="Joomla"
        echo "   🔴 Joomla"
    elif echo "$homepage" | grep -q "trasparenza"; then
        platform="TrasparenzaVM"
        echo "   🟣 TrasparenzaVM (possibile)"
    else
        echo "   ⚪ Piattaforma sconosciuta"
    fi
    
    # 5. Se albo trovato, analizza struttura
    if [ "$albo_url" != "NOT_FOUND" ]; then
        echo ""
        echo "5️⃣  Analizzo pagina albo: $albo_url"
        
        albo_page=$(curl -sL "$albo_url" 2>/dev/null)
        
        # Form POST?
        if echo "$albo_page" | grep -q '<form.*method="post"'; then
            echo "   📝 FORM POST trovato"
            form_action=$(echo "$albo_page" | grep -o 'action="[^"]*"' | head -1)
            echo "      $form_action"
        fi
        
        # Iframe?
        if echo "$albo_page" | grep -q '<iframe'; then
            echo "   🖼️  IFRAME trovato"
            iframe_src=$(echo "$albo_page" | grep -o '<iframe[^>]*src="[^"]*"' | head -1)
            echo "      $iframe_src"
        fi
        
        # Tabelle?
        table_count=$(echo "$albo_page" | grep -c '<table')
        echo "   📊 Tabelle HTML: $table_count"
        
        # Link delibere/determine
        atti_count=$(echo "$albo_page" | grep -i -c 'delibera\|determinazione')
        echo "   📄 Keyword atti: $atti_count occorrenze"
    fi
    
    echo ""
    echo "✅ $nome completato"
    echo ""
}

# Test comuni in ordine di popolazione
test_comune "Prato" "https://www.comune.prato.it" "195640"
test_comune "Pisa" "https://www.comune.pisa.it" "89158"
test_comune "Lucca" "https://www.comune.lucca.it" "88824"
test_comune "Massa" "https://www.comune.massa.ms.it" "66294"
test_comune "Bagno a Ripoli" "https://www.comune.bagno-a-ripoli.fi.it" "25095"

echo "========================================="
echo "✅ INVESTIGAZIONE COMPLETATA"
echo "========================================="
echo ""
echo "Risultati salvati in: $OUTPUT_FILE"
echo ""
echo "Prossimi step:"
echo "1. Leggi investigation_results.txt per dettagli"
echo "2. Per ogni comune identificato come scrapable:"
echo "   - Se HTML statico → implementa scraper httpx"
echo "   - Se API REST → implementa scraper API"
echo "   - Se Form POST → testa con curl, poi implementa"
echo "   - Se JavaScript → TAG 'REQUIRES_BROWSER_AUTOMATION'"
