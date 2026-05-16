# Fix List — Implementazioni Mancanti

Questo documento elenca le implementazioni necessarie per completare l'aggiornamento della piattaforma in base a `UPDATE.md`. Ogni fix è numerato per priorità.

---

## Fix 1 — Sync Gestionale: Step 2-4 (CRITICO)
**File:** `admin/admin.js`  
**Riferimento UPDATE.md:** §6

**Problema:** Il codice JS del wizard di sincronizzazione si interrompe dopo lo Step 1 (login). I gestori per i bottoni `#btnLoadAttivita`, `#btnGenCodiciSync` e `#btnInviaEmail` non sono implementati, rendendo l'intera funzionalità di sincronizzazione inutilizzabile.

**Da implementare:**
- `#btnGestLogin` → POST `api/sync?action=login`, rivela lo Step 2 on success
- `#btnLoadAttivita` → GET `api/sync?action=attivita&anno=X`, renderizza tabella attività con bottoni "Seleziona"
- Click "Seleziona attività" → GET `api/sync?action=iscritti&id_attivita=X`, salva lista iscritti in variabile, rivela Step 3, pre-carica il template HTML da `example_email.html` nella textarea dello Step 4
- `#btnGenCodiciSync` → POST `api/sync?action=genera_codici` con `iscritti` (JSON) e `settimana`, renderizza tabella riepilogativa (`Iscritto | Codice | Stato`), rivela Step 4
- `#btnInviaEmail` → POST `api/sync?action=invia_email` con `destinatari` (JSON), `template` e `oggetto`, mostra progress inline e tabella dei risultati per invio

---

## Fix 2 — Logout Server-Side
**File:** `logout.php` (nuovo), `admin/admin.js`  
**Riferimento UPDATE.md:** §4

**Problema:** Il pulsante `#btnLogout` in `admin.js` esegue solo `window.location.href = '../'` senza invalidare la sessione PHP lato server. La sessione rimane attiva fino alla sua scadenza naturale.

**Da implementare:**
- Creare `logout.php` nella root: chiama `session_start()`, `session_unset()`, `session_destroy()`, poi risponde `"OK"`
- Aggiornare `admin.js`: `#btnLogout` chiama `$.post('../logout')` e nel callback esegue il redirect

---

## Fix 3 — Creazione Categoria con Edizione Attiva
**File:** `admin/admin.js`  
**Riferimento UPDATE.md:** §3

**Problema:** In `admin.js` alla riga 130, alla creazione di una nuova categoria viene usato `id_edizione = 1` hardcoded invece dell'ID dell'edizione attiva corrente.

**Da implementare:**
- In `#btnSaveCategoria`, quando `$('#catId').val()` è vuoto (creazione), fare prima una GET a `api/edizioni?active=1` per ottenere l'ID dell'edizione attiva e usarlo come `id_edizione`

---

## Fix 4a — Backend: DELETE su `api/scelte.php`
**File:** `api/scelte.php`, `models/scelte.php`  
**Riferimento UPDATE.md:** §5 (interattività real-time)

**Problema:** L'API `scelte` supporta solo GET e POST. Per il wizard real-time del frontend (Fix 4b), è necessario poter rimuovere una scelta quando l'utente la deseleziona.

**Da implementare:**
- `models/scelte.php`: aggiungere `deleteScelta($codice, $id_laboratorio)` → DELETE WHERE codice = :codice AND id_laboratorio = :id_laboratorio
- `api/scelte.php`: gestire `DELETE` (o `POST` con `_method=DELETE`) — auth non richiesta, ma validare che il codice corrisponda alla scelta

---

## Fix 4b — Frontend: Wizard a Step per Categoria
**File:** `index.php`, `assets/js/script.js`, `assets/css/frontend.css`  
**Riferimento UPDATE.md:** §3, §5

**Problema:** Il frontend mostra tutti i laboratori in un'unica lista piatta, senza raggruppamento per categoria. `script.js` legge il `max_scelte` solo dal primo card (supporta solo una categoria). L'invio avviene in blocco alla pressione di "Conferma".

**Da implementare:**

**`index.php`:**
- Raggruppare `$lista` per `id_categoria` + `categoria_nome` + `max_scelte`
- Renderizzare ogni categoria come un "step" distinto (`div.categoria-step`) con attributi `data-step`, `data-max-scelte`, `data-categoria-id`
- Aggiungere un indicatore di step in cima (`div.step-indicator`: es. "Passo 1 di 2")
- Il pulsante della bottom bar diventa "AVANTI" per gli step intermedi e "CONFERMA" per l'ultimo

**`assets/js/script.js`:**
- Refactor completo: mantenere un `currentStep` e un `ordineScelte` **per categoria** (oggetto `{id_categoria: [labId, ...]}`
- `getMaxScelte()`: leggere l'attributo dallo step corrente
- `updateCounter()`: aggiornare il counter relativo allo step corrente
- Click sul card: inviare immediatamente `POST /api/scelte` (real-time); se il lab era già selezionato e viene deselezionato, chiamare `DELETE /api/scelte`; il posto è quindi riservato dinamicamente
- Pulsante "AVANTI": validare che lo step corrente abbia esattamente `max_scelte` scelte, poi mostrare lo step successivo (hide/show)
- Pulsante "CONFERMA" (ultimo step): validare l'ultimo step, poi redirect a `?done=codice`
- Al caricamento, le scelte già fatte (`.choosen`) devono essere assegnate allo step/categoria corretto

**`assets/css/frontend.css`:**
- Stili per `.step-indicator` (breadcrumb progress bar)
- `.categoria-step` visibile/nascosto con transizione
- Eventuale header di categoria (`h2.categoria-header`)

---

## Fix 5 — Image Uploader per GIF
**File:** `api/upload.php` (nuovo), `admin/index.php`, `admin/admin.js`  
**Riferimento UPDATE.md:** §4

**Problema:** Nel modal di modifica/creazione laboratorio, il campo `gif` è un semplice text input per il path. Non esiste un sistema di upload PHP per le immagini, costringendo a caricare i file manualmente via FTP.

**Da implementare:**
- Creare `api/upload.php` (auth required): accetta `$_FILES['gif']`, valida l'estensione (`gif`, `jpg`, `jpeg`, `png`, `webp`), valida la dimensione (max 5MB), genera un nome sicuro, salva in `assets/img/gif/`, ritorna `{"path": "assets/img/gif/nome.gif"}`
- `admin/index.php`: aggiungere un `<input type="file" id="labGifFile" accept="image/*,.gif">` sotto il campo testo `#labGif` nel modal laboratorio
- `admin/admin.js`: se `#labGifFile` ha un file selezionato, prima di salvare il laboratorio fare un upload via `FormData` + `$.ajax` a `api/upload`, poi impostare `#labGif` con il path ricevuto e procedere con il salvataggio

---

## Fix 6 — CRUD Settimane nel Pannello Admin
**File:** `api/settimane.php` (nuovo), `admin/index.php`, `admin/admin.js`  
**Riferimento UPDATE.md:** §4

**Problema:** Non esiste un'API né un'interfaccia admin per gestire le settimane. Gli amministratori non possono creare nuove settimane (associate a un'edizione) senza accesso diretto al database.

**Da implementare:**
- Creare `api/settimane.php`: GET (lista tutte le settimane con join `edizioni`), POST (crea/modifica: `nome`, `id_edizione`), DELETE (elimina per ID, solo se non ha codici associati) — tutte le operazioni richiedono auth
- `admin/index.php`: aggiungere voce "Settimane" nella sidebar; aggiungere sezione `#sec-settimane` con tabella (ID, Nome, Edizione, Azioni) e modal di creazione/modifica
- `admin/admin.js`: funzione `loadSettimane()`, handler `#btnSaveSettimana`, `editSettimana()`, `deleteSettimana()`

---

## Riepilogo Priorità

| # | Fix | Priorità | File Coinvolti |
|---|-----|----------|----------------|
| 1 | Sync Gestionale Steps 2-4 | **Alta** | `admin/admin.js` |
| 2 | Logout server-side | Media | `logout.php`, `admin/admin.js` |
| 3 | Categoria creation edizione attiva | Media | `admin/admin.js` |
| 4a | DELETE `api/scelte` | Media | `api/scelte.php`, `models/scelte.php` |
| 4b | Wizard a step per categoria | Media | `index.php`, `assets/js/script.js`, `assets/css/frontend.css` |
| 5 | Image uploader GIF | Bassa | `api/upload.php`, `admin/index.php`, `admin/admin.js` |
| 6 | CRUD Settimane admin | Bassa | `api/settimane.php`, `admin/index.php`, `admin/admin.js` |
