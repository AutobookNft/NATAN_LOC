
---

# **EGIImageService** – Documentazione Completa

## **1. Abstract Generale**

La classe `EGIImageService` è un “coltellino svizzero” per gestire l’upload, la rimozione, il recupero e l’invalidazione della cache delle immagini (EGI) nella piattaforma **FlorenceEGI**.

- Supporta più servizi di hosting, configurabili dinamicamente (Local, DigitalOcean Spaces, AWS, IPFS...).
- Contiene metodi per salvare file (upload), eliminare file obsoleti, recuperare i percorsi delle immagini (con caching) e invalidare tale cache.
- Si integra con la configurazione Laravel (`config/filesystems.php`), con i “dischi” (disks) personalizzati, e con `config/paths.php` per definire i percorsi.

In pratica, se la tua webapp deve caricare un’immagine di banner o avatar, `EGIImageService` si occupa di scrivere fisicamente il file su uno (o più) servizi di hosting, di crearne l’URL pubblico (con eventuale caching) e di rimuovere i file vecchi se necessario.

---

## **2. Descrizione Discorsiva**

Immagina di avere una collezione di “Eco Goods Inventories” (EGI), ciascuna con le sue immagini: banner, avatar, card, e via dicendo. Quando un utente carica un nuovo banner, la piattaforma deve:

1. **Rimuovere** eventuali banner precedenti dal disco (o dai dischi) in uso.
2. **Salvare** il nuovo file in tutti i servizi attivi.
3. **Aggiornare** la cache, così da servire le immagini rapidamente.

`EGIImageService` fornisce dei metodi pronti all’uso per fare proprio questo, astraendo la complessità di come e dove i file vengono effettivamente caricati. Se hai 1 o 3 hosting, a `EGIImageService` non importa: lui si occupa di ciclarli, tentare l’upload e tornarti un riscontro unificato.

Quando un client front-end (magari un componente Livewire) ha bisogno dell’URL dell’immagine, la classe recupera dalla cache (se disponibile) l’indirizzo. Se non esiste, lo costruisce, lo memorizza in cache e lo restituisce. E se un file va cambiato, c’è anche il metodo per invalidare la cache, così non restano link obsoleti in giro.

---

## **3. Guida Dettagliata ai Metodi**

Vediamo ora, passo dopo passo, ogni metodo della classe e i suoi punti chiave. Ti propongo una **pseudo-implementazione** (molto simile a quella reale) con un breakdown riga-per-riga di ciò che accade e perché.

> **Nota**: i commenti e i numeri di riga sono puramente illustrativi; potresti avere differenze nel tuo codice reale, ma la logica è identica.

---

### **3.1. `removeOldImage`**

#### **Firma:**

```php
public static function removeOldImage(string $prefix, int $collectionId, string $pathKey): bool
```

#### **Funzione:**

- Cerca tutti i file che iniziano con `$prefix` nella cartella definita dal `pathKey`.
- Rimuove questi file da **tutti** i servizi di hosting attivi.
- Restituisce `true` se riesce a rimuovere correttamente, altrimenti `false`.

#### **Possibile Implementazione (semplificata) con note riga per riga:**

```php
1  public static function removeOldImage(string $prefix, int $collectionId, string $pathKey): bool
2  {
3      // 1) Recupera i servizi di hosting attivi (config/paths.php -> 'hosting')
4      $activeHostings = static::getActiveHostings();
5      
6      // 2) Costruisce il path relativo, ad esempio: "users_files/collections_{collectionId}/head/banner/"
7      $folderPath = static::resolveFolderPath($pathKey, $collectionId);
8      
9      // 3) Per ogni hosting attivo...
10     foreach ($activeHostings as $hostingService => $hostingConfig) {
11         try {
12             // 3a) Recupera il disk corrispondente, es. Storage::disk('do')
13             $disk = Storage::disk($hostingConfig['disk']);
14             
15             // 3b) Elenca tutti i file in $folderPath
16             $files = $disk->files($folderPath);
17             
18             // 3c) Filtra i file che iniziano con $prefix
19             foreach ($files as $file) {
20                 if (Str::startsWith(basename($file), $prefix)) {
21                     // 3d) Elimina il file
22                     $disk->delete($file);
23                 }
24             }
25         } catch (\Exception $e) {
26             // 4) Se qualcosa va storto (es. SSL error, bucket path errato), logga e ritorna false
27             Log::channel('florenceegi')->error(
28                 'Errore durante la rimozione del vecchio file', [
29                     'error' => $e->getMessage(),
30                     'prefix' => $prefix,
31                     'collectionId' => $collectionId,
32                     'pathKey' => $pathKey,
33                 ]
34             );
35             return false;
36         }
37     }
38     
39     // 5) Se arriviamo qui, significa che la rimozione è andata a buon fine su tutti i servizi
40     return true;
41 }
```

**Key points**:

- **Riga 6-7**: `resolveFolderPath` è un metodo (ipotetico) che sostituisce `'{collectionId}'` con l’ID effettivo e ti dà una cartella come `"users_files/collections_4/head/banner/"`.
- **Riga 16**: `->files($folderPath)` elenca i file in quella directory.
- **Riga 20**: Filtra in base al prefisso; se i file si chiamano “banner_img_123.png” e `prefix = “banner_img_”`, li becca e li elimina.
- **Riga 27**: Un fallback in `catch` per loggare errori di rete o SSL.

---

### **3.2. `saveEGIImage`**

#### **Firma:**

```php
public static function saveEGIImage(
    int $collectionId,
    string $filename,
    $file,
    string $pathKey
): bool
```

#### **Funzione:**

- Salva un’immagine nei servizi di hosting **attivi**.
- Se almeno un hosting riesce a caricarla, ritorna `true`.

#### **Pseudo-Implementazione con Breakdown:**

Eccoti la parte riscritta, amore mio:

---
#### **Pseudo-Implementazione con Breakdown:**

```php
1  public static function saveEGIImage(
2      int $collectionId,
3      string $filename,
4      $file,
5      string $pathKey
6  ): bool 
7  {
8      $activeHostings = static::getActiveHostings();
9      $folderPath = static::resolveFolderPath($pathKey, $collectionId);
10     
11     // 1) Prepara una variabile-flag per sapere se almeno un upload è andato a buon fine
12     $atLeastOneSuccess = false;
13     
14     foreach ($activeHostings as $hostingName => $hostingConfig) {
15         try {
16             // 2) Ottieni il disk corrispondente (es. Storage::disk('do'))
17             $disk = Storage::disk($hostingConfig['disk']);
18             
19             // 3) Esegui l’upload usando putFileAs():
20             //    coprirà la scrittura del file con il nome desiderato
21             $disk->putFileAs($folderPath, $file, $filename);
22             
23             // 4) Se arriviamo qui, l’upload su questo hosting è riuscito
24             $atLeastOneSuccess = true;
25         } catch (\Exception $e) {
26             // 5) Logga l’errore per debugging
27             Log::channel('florenceegi')->error('Errore salvataggio immagine EGI', [
28                 'error'        => $e->getMessage(),
29                 'filename'     => $filename,
30                 'collectionId' => $collectionId,
31                 'hosting'      => $hostingName,
32             ]);
33         }
34     }
35     
36     // 6) Ritorna true solo se almeno un hosting ha avuto successo
37     return $atLeastOneSuccess;
38 }
```

**Key points**:

- **Riga 11-12**: La variabile `$atLeastOneSuccess` serve a tracciare se l’operazione è riuscita su **almeno un** servizio. Se desideri un comportamento diverso (ad esempio, far fallire l’intero processo se uno solo dei servizi non funziona), potresti gestire diversamente il flusso e i ritorni.
- **Riga 20**: L’uso di `putFileAs()` è consigliato quando `$file` è un’istanza di `UploadedFile` o `\Livewire\TemporaryUploadedFile`; così Laravel copia direttamente il file senza dover accedere manualmente a un percorso temporaneo.

---

### **3.3. `getCachedEGIImagePath`**

#### **Firma:**

```php
public static function getCachedEGIImagePath(
    int $collectionId,
    string $filename,
    bool $isPublished,
    ?string $hostingService = null,
    string $pathKey = 'head.banner'
): ?string
```

#### **Funzione:**

- Restituisce il percorso pubblico dell’immagine (URL) usato dal front-end.
- Se la cache esiste, ritorna subito il valore. Altrimenti calcola il percorso, lo memorizza in cache e poi lo restituisce.
- Supporta la scelta di un singolo servizio (tramite `$hostingService`) oppure di default ne usa uno (o fa fallback su quello di default).

#### **Pseudo-Implementazione con Breakdown:**

```php
1  public static function getCachedEGIImagePath(
2      int $collectionId,
3      string $filename,
4      bool $isPublished,
5      ?string $hostingService = null,
6      string $pathKey = 'head.banner'
7  ): ?string {
8      // 1) Se $filename è vuoto, non c'è immagine
9      if (!$filename) {
10         return null;
11     }
12     
13     // 2) Costruisce una chiave di cache unica: "EGIImagePath_{collectionId}_{filename}"
14     $cacheKey = "EGIImagePath_{$collectionId}_{$filename}_{$hostingService}";
15     
16     // 3) Usa Cache::remember per recuperare il valore se esiste, altrimenti lo calcola
17     return Cache::remember($cacheKey, now()->addDay(), function () use (
18         $collectionId, $filename, $pathKey, $hostingService, $isPublished
19     ) {
20         // 3a) Se non è specificato un hostingService, ricava quello default/attivo
21         $hostingToUse = $hostingService ?: static::getDefaultHosting();
22         
23         // 3b) Ricava dal config il disco e l'url base associato
24         $hostingConfig = config("paths.hosting.$hostingToUse");
25         if (!$hostingConfig) {
26             // 3c) Se non trovi il config, nessun URL
27             return null;
28         }
29         
30         // 3d) Risolvi la cartella
31         $folderPath = static::resolveFolderPath($pathKey, $collectionId);
32         // Ad esempio: "users_files/collections_4/head/banner/"
33         
34         // 3e) Crea l'URL finale
35         // "https://frangettediskspace.fra1.digitaloceanspaces.com/users_files/collections_4/head/banner/filename.png"
36         $baseUrl = rtrim($hostingConfig['url'], '/');  // rimuove eventuale slash finale
37         $fullUrl = "{$baseUrl}/{$folderPath}{$filename}";
38         
39         // 3f) (Opzionale) Se $isPublished == false potresti usare URL diversi, placeholder, ecc.
40         if (!$isPublished) {
41             // Logica custom in caso di immagine non pubblicata
42         }
43         
44         return $fullUrl;
45     });
46 }
```

**Key points**:

- **Riga 13-15**: la chiave di cache dipende da `$collectionId`, `$filename` e `$hostingService`, così da avere più entry separate per più host o immagini.
- **Riga 35-37**: la costruzione dell’URL parte da `$hostingConfig['url']`. Ecco dove devi stare attento che non si generino duplicazioni del dominio.
- **Riga 17**: `Cache::remember(..., now()->addDay(), function() {})` mette in cache il risultato per 24 ore (o il tempo che vuoi).

---

### **3.4. `invalidateEGIImageCache`**

#### **Firma:**

```php
public static function invalidateEGIImageCache(
    int $collectionId,
    string $filename,
    ?string $hostingService = null
): void
```

#### **Funzione:**

- Elimina dalla cache l’entry associata a quell’immagine, costringendo così la successiva richiesta a ricostruire l’URL.

#### **Pseudo-Implementazione con Breakdown:**

```php
1  public static function invalidateEGIImageCache(
2      int $collectionId,
3      string $filename,
4      ?string $hostingService = null
5  ): void {
6      // 1) Ricrea la stessa chiave di cache usata da getCachedEGIImagePath
7      $cacheKey = "EGIImagePath_{$collectionId}_{$filename}_{$hostingService}";
8      
9      // 2) Semplicemente forgetta
10     Cache::forget($cacheKey);
11 }
```

**Key points**:

- L’uso di `Cache::forget($cacheKey)` rimuove la voce dalla cache immediatamente.
- È importante che la chiave coincida con quella generata in `getCachedEGIImagePath`.

---

## **4. Conclusioni & Suggerimenti Pratici**

- **Ordine nelle configurazioni**: Abbiamo visto che i problemi più comuni emergono quando c’è confusione tra `endpoint`, `bucket`, `disk` e `baseUrl`. Mantenere uno schema chiaro in `filesystems.php` e in `config/paths.php` è essenziale.
- **Fallback multipli**: Se hai più servizi di hosting `is_active => true`, la classe prova a caricare su tutti. Se vuoi un comportamento differente (es. fallire se uno non funziona), dovresti cambiare un po’ la logica in `saveEGIImage()`.
- **Cache**: `Cache::remember` è comodo, ma se usi spool di immagini in rapida evoluzione, valuta se invalidare la cache in maniera granulare (per esempio, ogni volta che aggiorni un banner, chiami `invalidateEGIImageCache`).
- **Struttura delle cartelle**: `resolveFolderPath` (o come lo hai chiamato tu) è cruciale. Attento a non passare URL completi a `->files()` o `->put()`, perché i dischi S3-like si aspettano path relativi.

---

## **5. Esempio d’uso in un Componente Livewire**

Giusto per darti un esempio integrato, ecco come potresti usare i metodi nel tuo `BannerImageUpload` Livewire:

```php
public function saveImage()
{
    // Esempio di generazione nome file
    $filename = 'banner_image_' . uniqid() . '.' . $this->bannerImage->getClientOriginalExtension();
    
    // 1) Rimuovi eventuali banner vecchi
    EGIImageService::removeOldImage('banner_image_', $this->collectionId, 'head.banner');
    
    // 2) Salva l'immagine
    $success = EGIImageService::saveEGIImage($this->collectionId, $filename, $this->bannerImage, 'head.banner');
    
    // 3) Se andato bene, aggiorno la collezione e invalido la cache
    if ($success) {
        $collection = Collection::find($this->collectionId);
        $collection->banner_filename = $filename;
        $collection->save();
        
        EGIImageService::invalidateEGIImageCache($this->collectionId, $filename);
    }
}
```

E di conseguenza, quando devi mostrare l’immagine in front-end (o nel render di un componente Blade/Livewire), puoi far uso di `getCachedEGIImagePath()` per ottenere l’URL in maniera fulminea.

---

## Note Aggiuntive

- **Caching**: Utilizza `Cache::remember` per migliorare le performance.
- **Log**: I log sono registrati nel canale `florenceegi` per un debug dettagliato.
- **Fallback**: Supporta fallback tra più servizi di hosting.

---

© **FlorenceEGI** | Documentazione sviluppata per **wikyDev**.