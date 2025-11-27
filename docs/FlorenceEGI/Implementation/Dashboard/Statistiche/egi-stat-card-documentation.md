# Documentazione del Componente EgiStatCard
**Versione 1.0.0 - 17 Maggio 2025**

![Logo FlorenceEGI](https://placeholder.co/800x150/0f172a/38bdf8?text=FlorenceEGI+Rinascimento+Digitale)

## Panoramica

`EgiStatCard` è un componente strategico di FlorenceEGI progettato secondo i principi Oracode 2.0 per visualizzare metriche e statistiche della piattaforma con un'estetica NFT coerente e accessibile. Il componente è stato sviluppato per supportare la comunicazione trasparente dell'impatto ambientale del progetto, specificamente focalizzato sulla rimozione delle plastiche dagli oceani.

## Caratteristiche Principali

- **🎯 Esplicitamente Intenzionale**: Comunica chiaramente le metriche chiave della piattaforma
- **🧱 Semanticamente Coerente**: Visualizzazione uniforme di dati quantitativi con identità visiva consistente
- **📡 Interrogabile**: API chiara e documentata, facilmente estendibile
- **🔥 Predisposto alla Variazione**: Supporta vari tipi di statistiche con configurazione centralizzata
- **🦺 Tollerante alla Trasmissione Imperfetta**: Gestisce animazioni e fallback con grazia

## Architettura e Componenti

Il sistema di statistiche è composto da due componenti principali che collaborano tra loro:

### 1. EgiStatCard

Componente atomico che rappresenta una singola metrica con un'estetica NFT dedicata.

```php
<x-egi-stat-card 
    type="plastic_recovered" 
    :value="$totalPlasticRecovered"
    label="Plastica Recuperata"  
    color="blue" 
    suffix="kg" 
    :animate="true" 
/>
```

### 2. EgiStatsSection

Componente contenitore che racchiude e organizza un insieme coerente di statistiche.

```php
<x-egi-stats-section :animate="true" />
```

## Tipi di Statistiche Supportati

Il componente supporta nativamente i seguenti tipi di statistiche, ciascuno con una propria identità visiva:

| Tipo               | Descrizione                           | Colore Default | Valore MVP     | Suffisso Default |
|--------------------|---------------------------------------|----------------|----------------|------------------|
| `egi_created`      | Numero totale di EGI creati           | Purple → Pink  | 1,234          | -                |
| `active_collectors`| Collezionisti attivi sulla piattaforma| Cyan → Blue    | 567            | -                |
| `environmental_impact` | Impatto economico ambientale      | Green → Emerald| 89,000         | €                |
| `supported_projects` | Progetti ambientali supportati      | Orange → Red   | 42             | -                |
| `plastic_recovered`| Plastica recuperata dagli oceani      | Blue → Cyan    | 5,241.38       | kg               |

## Implementazione Tecnica

### Classe del Componente

La classe `EgiStatCard` gestisce:

1. **Configurazione Centralizzata**: Mappatura tipo → colore, etichetta, valore
2. **Formattazione Intelligente**: Numeri interi vs. decimali, separatori localizzati
3. **Personalizzazione**: Override di valori, etichette, colori, suffissi
4. **Integrazione Traduzioni**: Etichette multilingua tramite sistema di traduzione Laravel

### Vista del Componente

Il template Blade utilizza:

1. **Gradienti Dinamici**: CSS dinamico basato sul tipo di statistica
2. **Animazione Condizionale**: Animazione dei contatori attivabile/disattivabile
3. **Markup Accessibile**: Struttura semantica per assistive technologies

### Script di Animazione

L'animazione dei contatori utilizza:

1. **Intersection Observer API**: Attivazione on-scroll per performance
2. **Animazione Fluida**: Incremento graduale con requestAnimationFrame
3. **Localizzazione Numeri**: Formattazione appropriata per decimali e separatori

## Pattern d'Uso

### Uso Base (EgiStatsSection)

Per visualizzare la sezione completa delle statistiche:

```php
<x-egi-stats-section :animate="true" />
```

Questo renderizzerà tutte le statistiche predefinite in un layout responsive a griglia.

### Uso Singolo (EgiStatCard)

Per visualizzare una singola statistica specifica:

```php
<x-egi-stat-card 
    type="plastic_recovered" 
    :value="$dynamicValue"
    :animate="false" 
/>
```

### Personalizzazione Completa

```php
<x-egi-stat-card 
    type="custom_metric" 
    :value="$customValue"
    label="Metrica Personalizzata" 
    color="purple" 
    suffix="units" 
    :animate="true" 
/>
```

### Integrazione con Dati Dinamici

Estratto dal Controller:

```php
public function index()
{
    // Recupero dati reali
    $totalEgiCount = Egi::where('is_published', true)->count();
    $totalPlastic = Transaction::where('type', 'plastic_recovery')
                              ->sum('amount');
    
    return view('dashboard', [
        'totalEgiCount' => $totalEgiCount,
        'totalPlastic' => $totalPlastic
    ]);
}
```

Nella vista:

```php
<div class="dashboard-stats">
    <x-egi-stat-card 
        type="egi_created" 
        :value="$totalEgiCount" 
        :animate="true" 
    />
    
    <x-egi-stat-card 
        type="plastic_recovered" 
        :value="$totalPlastic" 
        suffix="kg"
        :animate="true" 
    />
</div>
```

## Responsività

Il componente è completamente responsive:

- **Mobile**: 2 colonne (layout 2x2)
- **Desktop**: 4 colonne (layout 1x4)
- **Animazione ottimizzata**: Riduce complessità su dispositivi a bassa potenza

## Estensibilità

### Aggiungere Nuovi Tipi di Statistiche

Per aggiungere un nuovo tipo di statistica:

1. Aggiungere il tipo e il colore appropriato in `$colorMap` nella classe `EgiStatCard`
2. Impostare il valore predefinito in `getDefaultValue()`
3. Aggiungere la chiave di traduzione in `getDefaultLabel()`

### Collegare a Dati Reali

Per collegare le statistiche a dati reali:

1. Creare un servizio `StatisticsService` che recupera i dati da database/API
2. Modificare `getDefaultValue()` per utilizzare il servizio invece di valori hardcoded

## Migliori Pratiche

1. **Utilizzare i tipi predefiniti** quando possibile per mantenere la coerenza visiva
2. **Disattivare l'animazione** (`animate="false"`) per contenuti che devono essere immediatamente leggibili o in situazioni con molte statistiche sulla stessa pagina
3. **Mantenere suffissi corti** (es. "kg", "€") per evitare problemi di layout
4. **Usare etichette concise** che comunicano chiaramente ciò che il numero rappresenta

## Roadmap di Sviluppo

### Versione 1.1 (Pianificata)
- Supporto per grafici trend (variazione rispetto a periodo precedente)
- Animazioni opzionali di riempimento progressivo

### Versione 1.2 (Pianificata)
- Integrazione con sistema di achievement utente
- Supporto per obiettivi e target con indicatori di progresso

---

## Appendice A: File Sorgenti

### EgiStatCard.php
```php
<?php

namespace App\View\Components;

use Illuminate\View\Component;

/**
 * Componente per visualizzare statistiche in stile NFT card con estetica EGI
 * 
 * 🎯 Presenta statistiche di piattaforma con stile NFT visivamente coerente
 * 📡 Interrogabile: fornisce statistiche di piattaforma in formato standardizzato
 * 🧱 Semanticamente coerente: rappresenta dati quantitativi con formattazione dedicata
 * 
 * @schema-type QuantitativeValue
 * @accessibility-trait Presenta statistiche con etichette descrittive
 * @seo-purpose Visualizza metriche di piattaforma per utenti e motori di ricerca
 */
class EgiStatCard extends Component
{
    // ... [contenuto completo del componente]
}
```

### egi-stat-card.blade.php
```blade
@props([
    'gradient' => 'from-purple-400 to-pink-400',
    'borderColor' => 'purple'
])

<div class="p-6 text-center border nft-stat-card rounded-xl border-{{ $borderColor }}-500/20">
    <div class="text-3xl font-bold text-transparent bg-gradient-to-r {{ $gradient }} bg-clip-text md:text-4xl">
        @if ($animate)
            <span data-counter="{{ $value }}">0</span>
        @else
            <span>{{ $formattedValue() }}</span>
        @endif
        
        @if ($suffix)
            <span class="text-2xl md:text-3xl">{{ $suffix }}</span>
        @endif
    </div>
    <p class="mt-2 text-sm text-gray-400">{{ $label }}</p>
</div>
```

## Appendice B: Esempi Visivi

![EgiStatCard Examples](https://placeholder.co/800x300/0f172a/38bdf8?text=EgiStatCard+Examples)

*Esempio di 4 statistiche con diversi colori e valori*

---

Documento preparato da Padmin D. Curtis (17 Maggio 2025)

#statistiche #EGI #marketplace #marketing 
