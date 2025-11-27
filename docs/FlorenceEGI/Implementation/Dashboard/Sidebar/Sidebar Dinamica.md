Certamente, Fabio. Ecco un documento Markdown che descrive il sistema della sidebar, pensato per futuri sviluppatori che potrebbero lavorare su FlorenceEGI.

---
# Documentazione Sistema Sidebar Dinamica e Contestuale di FlorenceEGI

**(Versione 1.0 - 21 Maggio 2025)**

## Abstract

Il sistema di sidebar di FlorenceEGI è progettato per fornire una navigazione utente dinamica, contestuale e basata sui permessi. Utilizza una combinazione di classi PHP per la definizione della struttura del menu, un repository per la gestione centralizzata delle icone (con supporto per SVG colorati e caching), e una vista Blade per il rendering finale. La sidebar si adatta al contesto dell'applicazione (es. "dashboard", "collections") mostrando solo i gruppi di menu e gli item pertinenti, e rispettando i permessi dell'utente autenticato per la visualizzazione di ciascun elemento. Le icone sono definite centralmente e possono essere personalizzate con colori intrinseci.

## 1. Flusso Generale di Funzionamento

1.  **Richiesta Pagina:** L'utente naviga verso una pagina che include la sidebar.
2.  **Determinazione Contesto:** Il controller responsabile della pagina determina il contesto corrente dell'applicazione (es. `'dashboard'`).
3.  **Recupero Struttura Menu:** Il controller invoca `App\Services\Menu\ContextMenus::getMenusForContext($context)` per ottenere un array di oggetti `App\Services\Menu\MenuGroup`.
    *   Ogni `MenuGroup` contiene un nome (tradotto), una chiave per l'icona, e un array di oggetti `App\Services\Menu\MenuItem`.
    *   Ogni `MenuItem` contiene una chiave di traduzione per il nome (tradotto nel costruttore), una rotta Laravel, una chiave per l'icona, un permesso opzionale e un array opzionale di `MenuItem` figli.
4.  **Arricchimento Icone (Pre-Rendering):** Prima di passare i dati alla vista, il controller (o un service/view composer dedicato) itera sulla struttura dei menu ottenuta. Per ogni chiave icona presente in `MenuGroup` e `MenuItem`:
    *   Chiama `App\Repositories\IconRepository` (solitamente `getDefaultIcon($iconKey)` o `getIcon($iconKey, $style, $class)`) per recuperare l'HTML SVG completo e processato dell'icona. Le definizioni SVG, con colori intrinseci, sono memorizzate nel database (popolate da `config/icons.php` via `IconSeeder`).
    *   La chiave icona nell'array/oggetto del menu viene sostituita con l'HTML SVG renderizzato.
5.  **Passaggio Dati alla Vista:** L'array dei menu arricchito (ora con HTML SVG per le icone) e il titolo del contesto vengono passati alla vista Blade `resources/views/components/sidebar.blade.php` (o il percorso effettivo della tua vista sidebar).
6.  **Rendering Vista:**
    *   `sidebar.blade.php` itera sulla struttura dei menu.
    *   Utilizza `Gate::allows($permission)` per verificare se l'utente ha i permessi per visualizzare un gruppo di menu o un item.
    *   Renderizza l'icona usando `{!! $menu['icon'] !!}` o `{!! $item['icon'] !!}` (poiché ora contengono HTML SVG).
    *   Gestisce la visualizzazione di gruppi collassabili (`<details>`) per menu con sottomenu e link diretti per menu senza sottomenu.
    *   Applica stili Tailwind CSS e DaisyUI per l'aspetto, inclusa l'evidenziazione dell'item/gruppo attivo.

## 2. Componenti Chiave del Sistema

### 2.1. PHP - Logica e Definizione dei Menu

*   **`app/Services/Menu/MenuItem.php`**:
    *   Classe base per ogni singola voce di menu.
    *   Proprietà: `$translationKey`, `$name` (tradotto), `$route`, `$icon` (chiave), `$permission`, `$children`.
*   **`app/Services/Menu/MenuGroup.php`**:
    *   Raggruppa `MenuItem`s.
    *   Proprietà: `$name` (tradotto), `$icon` (chiave), `$items` (array di `MenuItem`).
*   **`app/Services/Menu/Items/*.php`** (es. `NewCollectionMenu.php`, `StatisticsMenu.php`):
    *   Classi concrete che estendono `MenuItem`.
    *   Definiscono i parametri specifici (chiave traduzione, rotta, icona, permesso) per ogni voce di menu.
*   **`app/Services/Menu/ContextMenus.php`**:
    *   Classe "factory" principale.
    *   Metodo statico `getMenusForContext(string $context): array` che costruisce e restituisce l'array di `MenuGroup` appropriato per il contesto fornito. Qui viene definita la logica di quali menu appaiono in quali sezioni.
*   **`app/Services/Menu/MenuConditionEvaluator.php`**:
    *   Contiene la logica (`shouldDisplay(MenuItem $menuItem): bool`) per verificare se un `MenuItem` dovrebbe essere visualizzato in base ai permessi dell'utente. (Nota: la vista `sidebar.blade.php` fornita usa `Gate::allows()` direttamente, quindi l'integrazione di questo evaluator deve essere chiara o potrebbe essere un helper per logiche più complesse).
*   **`app/Repositories/IconRepository.php`**:
    *   Gestisce il recupero, il processamento (sostituzione `%class%`) e il caching delle icone SVG.
    *   Interagisce con il modello `App\Models\Icon` per recuperare le definizioni SVG dal database.
*   **`app/Models/Icon.php`**:
    *   Modello Eloquent per la tabella `icons` del database.
*   **`config/icons.php`**:
    *   File di configurazione centrale che definisce tutte le icone dell'applicazione.
    *   Contiene gli array con `name`, `type`, `class` (default, senza opacità), e `html` (la stringa SVG grezza con placeholder `%class%` e colori intrinseci).
*   **`database/seeders/IconSeeder.php`**:
    *   Legge `config/icons.php` e popola/aggiorna la tabella `icons` nel database.

### 2.2. Blade - Vista di Rendering

*   **`resources/views/components/sidebar.blade.php`** (o percorso effettivo):
    *   Responsabile del rendering HTML della sidebar.
    *   Utilizza direttive Blade (`@foreach`, `@if`, `Gate::allows()`) per iterare sulla struttura del menu e visualizzare gli elementi.
    *   Stampa direttamente l'HTML SVG delle icone ( `{!! $menu['icon'] !!}` ).
    *   Utilizza Tailwind CSS e classi DaisyUI per lo styling.
    *   Include una logica `@php` (o dovrebbe riceverla dal controller) per determinare e stilizzare l'item/gruppo di menu attivo.

## 3. Gestione delle Icone

*   Le icone sono definite come stringhe SVG nel file `config/icons.php`.
*   **Cruciale:** Per icone colorate, gli attributi `fill` o `stroke` all'interno degli elementi SVG (`<path>`, `<circle>`, etc.) devono essere impostati con valori di colore esadecimali specifici (es. `fill="#60A5FA"`). Il tag `<svg>` principale non dovrebbe avere un `fill="currentColor"` globale se si vogliono colori intrinseci.
*   La classe di default per le icone in `config/icons.php` dovrebbe specificare solo le dimensioni (es. `w-5 h-5`) e non l'opacità, per permettere ai colori SVG di essere pienamente visibili. L'opacità per stati (attivo/inattivo/hover) viene gestita dalle classi CSS nella vista Blade.
*   `IconSeeder` trasferisce queste definizioni nel database.
*   `IconRepository` le recupera, applica la classe CSS (tramite sostituzione di `%class%`) e le mette in cache.
*   La vista `sidebar.blade.php` stampa l'HTML SVG fornito dopo l'arricchimento.

## 4. Aggiungere/Modificare Voci di Menu

### 4.1. Aggiungere un Nuovo Item di Menu Semplice:

1.  **Creare la Classe Item:** In `app/Services/Menu/Items/`, creare una nuova classe PHP che estende `App\Services\Menu\MenuItem`.
    ```php
    // Esempio: app/Services/Menu/Items/MyNewFeatureMenu.php
    namespace App\Services\Menu\Items;
    use App\Services\Menu\MenuItem;

    class MyNewFeatureMenu extends MenuItem
    {
        public function __construct()
        {
            parent::__construct(
                'menu.my_new_feature', // Chiave per traduzione (in resources/lang/.../menu.php)
                'route.name.for.feature', // Nome della rotta Laravel
                'icon_key_for_feature',   // Chiave dell'icona (definita in config/icons.php)
                'permission_to_view_feature' // Permesso Laravel (opzionale)
            );
        }
    }
    ```
2.  **Definire Traduzione:** Aggiungere la chiave `'menu.my_new_feature'` ai file di lingua.
3.  **Definire Icona (se nuova):** Aggiungere la definizione per `'icon_key_for_feature'` in `config/icons.php` (con SVG colorato) e rieseguire `IconSeeder` e pulire la cache.
4.  **Aggiungere a `ContextMenus.php`:**
    *   Modificare `app/Services/Menu/ContextMenus.php`.
    *   Nel metodo `getMenusForContext()`, all'interno dello `switch` per il contesto desiderato, istanziare il nuovo item e aggiungerlo a un `MenuGroup` esistente o a uno nuovo.
    ```php
    // Esempio in ContextMenus.php
    use App\Services\Menu\Items\MyNewFeatureMenu;
    // ...
    case 'dashboard':
        // ...
        $someGroup = new MenuGroup(__('menu.some_group'), 'group_icon_key', [
            new ExistingItemMenu(),
            new MyNewFeatureMenu(), // Aggiunto qui
        ]);
        $menus[] = $someGroup;
        // ...
    break;
    ```

### 4.2. Aggiungere un Nuovo Gruppo di Menu con Sottomenu:

1.  Creare le classi per ogni `MenuItem` del sottomenu come sopra.
2.  In `ContextMenus.php`, creare una nuova istanza di `MenuGroup`, passandogli un array dei nuovi `MenuItem`s.
    ```php
    // Esempio in ContextMenus.php
    use App\Services\Menu\Items\SubItemOneMenu;
    use App\Services\Menu\Items\SubItemTwoMenu;
    // ...
    case 'some_context':
        $newAwesomeGroup = new MenuGroup(
            __('menu.new_awesome_group'), // Chiave traduzione per il nome del gruppo
            'awesome_group_icon_key',     // Chiave icona per il gruppo
            [                             // Array di MenuItem figli
                new SubItemOneMenu(),
                new SubItemTwoMenu(),
            ]
        );
        // Opzionale: se il gruppo stesso richiede un permesso per essere visualizzato,
        // dovrai estendere MenuGroup o aggiungere logica qui.
        // Attualmente i permessi sono a livello di MenuItem.
        $menus[] = $newAwesomeGroup;
    break;
    ```

## 5. Test da Integrare

Per garantire la robustezza e la corretta funzionalità del sistema sidebar, i seguenti test dovrebbero essere implementati:

### 5.1. Test Unitari

*   **`MenuItemTest.php`**:
    *   Testare che il costruttore di `MenuItem` imposti correttamente tutte le proprietà (nome tradotto, rotta, chiave icona, permesso, figli).
    *   Testare `hasChildren()`.
*   **`MenuGroupTest.php`**:
    *   Testare che il costruttore di `MenuGroup` imposti nome, chiave icona e items.
    *   Testare `addItem()`.
    *   Testare `hasVisibleItems()` (potrebbe richiedere di mockare `MenuItem::isVisible` o permessi).
*   **`ContextMenusTest.php`**:
    *   Per ogni contesto definito in `getMenusForContext()`:
        *   Verificare che restituisca un array.
        *   Verificare che gli elementi dell'array siano istanze di `MenuGroup`.
        *   Verificare che i gruppi e gli item attesi per quel contesto siano presenti (controllando nomi o classi).
*   **`IconRepositoryTest.php`**:
    *   **Test `getIcon()`**:
        *   Con icona esistente: mockare `Icon::where(...)->first()` per restituire un oggetto icona fittizio. Verificare che l'HTML SVG sia processato correttamente (sostituzione `%class%`).
        *   Con icona non esistente: verificare che restituisca l'SVG di fallback o `null`.
        *   Testare il funzionamento della cache (mockare `Cache::remember()`, `Cache::forget()`, `Cache::tags()`).
        *   Testare con e senza `$customClass`.
    *   **Test `getDefaultIcon()`**:
        *   Mockare `Auth::user()` con e senza `icon_style` per testare la selezione dello stile.
        *   Verificare che chiami `getIcon()` con lo stile corretto.
*   **`IconSeederTest.php`**:
    *   Mockare `config()` per fornire un array di icone di test.
    *   Mockare `Icon::updateOrCreate()` e verificare che venga chiamato con i dati corretti per ogni icona di test.

### 5.2. Test di Integrazione / Funzionali (Feature Tests)

*   **Sidebar Rendering Test (per ogni contesto principale)**:
    *   Creare utenti con permessi diversi.
    *   Per ogni utente e contesto:
        *   Effettuare una richiesta HTTP a una rotta che renderizza la sidebar per quel contesto.
        *   Verificare che la risposta HTTP sia 200.
        *   **Verificare che nella risposta renderizzata siano presenti i nomi dei menu e dei gruppi che l'utente *dovrebbe* vedere.**
        *   **Verificare che nella risposta renderizzata NON siano presenti i nomi dei menu e dei gruppi che l'utente *NON dovrebbe* vedere.**
        *   Verificare che le icone (come stringhe SVG o parti di esse) siano presenti per i menu visibili.
        *   Verificare che i link (`href`) puntino alle route corrette.
*   **Test dello Stato Attivo**:
    *   Navigare verso la rotta di un `MenuItem` specifico.
    *   Verificare che l'elemento `<a>` (o `summary`) corrispondente nella sidebar abbia le classi CSS che indicano lo stato attivo (es. `bg-primary`).
*   **Test Icone Mancanti**:
    *   Simulare una chiave icona non valida in un `MenuItem`.
    *   Verificare che venga renderizzata l'icona di fallback o che non ci siano errori.

### 5.3. Test di Visual Regression (Opzionale ma Utile)

*   Utilizzare strumenti come Percy o BackstopJS per catturare screenshot della sidebar in vari stati e contesti.
*   Ad ogni modifica dello stile, confrontare i nuovi screenshot con quelli di riferimento per individuare regressioni visive involontarie.

## 6. Considerazioni Future e Miglioramenti

*   **Permessi a Livello di Gruppo:** Attualmente i permessi sembrano essere gestiti a livello di `MenuItem` e controllati nella vista per i gruppi. Si potrebbe formalizzare un attributo `$permission` anche in `MenuGroup` e farlo rispettare da `ContextMenus` o dalla vista.
*   **Caching della Struttura del Menu:** Se la generazione della struttura del menu diventa complessa, si potrebbe considerare di cachare l'output di `ContextMenus::getMenusForContext()` (invalidando la cache quando cambiano i permessi o le definizioni dei menu).
*   **Helper per Colori Icone:** Se la logica per assegnare colori specifici a icone specifiche (non tramite `fill="currentColor"`, ma colori diversi per diverse icone) dovesse diventare complessa, si potrebbe centralizzare in `IconRepository` o un helper invece di averla sparsa nella vista o nel config. (Questo punto è stato superato dalla decisione di usare SVG con colori intrinseci).
*   **Testabilità dello Stato Attivo:** La logica per determinare lo stato attivo dovrebbe essere facilmente testabile, magari incapsulata in un servizio o helper che può essere mockato/verificato.

---

Spero questo documento sia utile, Fabio. È pensato per essere una guida chiara e concisa per chiunque debba comprendere o estendere il sistema della sidebar.