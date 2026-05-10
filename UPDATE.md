# Piano di Aggiornamento - Sito Scelta Laboratori

Questo documento delinea la strategia completa per l'aggiornamento e il refactoring della piattaforma "Scelta Laboratori", con l'obiettivo di renderla riutilizzabile, scalabile e facile da amministrare, migliorandone parallelamente l'accessibilità e l'usabilità per l'utente finale.

## 1. Analisi dello Stato Attuale e del Refactoring Necessario
Attualmente, la piattaforma è un'applicazione PHP 8 (su database MariaDB/MySQL con PDO) affiancata da un frontend che fa uso di Bootstrap 5 e jQuery. 
- **Flusso Dati**: Il login si basa su un `codice` UUID. Le limitazioni del sistema (massimo 5 scelte, limite dei posti disponibili) sono attualmente delegate a **Trigger SQL** (`limite_per_codice`, `limite_per_laboratorio`).
- **Limitazioni**: L'assenza di astrazioni per "Edizioni" o "Categorie" impedisce la riutilizzabilità diretta da un anno all'altro senza un reset manuale o l'introduzione di debito tecnico. Le immagini sono caricate manualmente. L'ordine di preferenza delle scelte (prima scelta, seconda scelta, ecc.) attualmente non viene tracciato.

Il refactoring mirerà a mantenere solido il core (API + PDO), andandone ad estendere le tabelle (aggiungendo ad esempio la colonna `ordine` nella tabella `scelte`), e sposterà gran parte delle regole di validazione anche sul Backend in PHP (in aggiunta ai trigger, resi dinamici) per garantire messaggi di errore parlanti e responsivi per la UI.

---

## 2. Riutilizzabilità Anno in Anno (Edizioni)
Per evitare di sovrascrivere o eliminare i dati delle annate passate, si procederà con l'introduzione di uno scope temporale.

* **Nuova Tabella `edizioni`** (`id`, `anno`, `nome`, `is_active`): Permette di impostare una o più annate/eventi in modo indipendente.
* **Modifica al Database**: 
  * Aggiunta della foreign key `id_edizione` sulla tabella `settimane`.
  * La piattaforma leggerà automaticamente solo l'edizione in cui `is_active = 1` o l'edizione collegata al codice utente, filtrando a monte le query nelle classi `models/`.
* **Vantaggi**: Si potrà consultare lo storico dei dati passati direttamente dal pannello admin, si evitano script di drop/truncate del database ogni giugno.

---

## 3. Gestione di Categorie Multiple di Laboratori
Il sito deve permettere logiche avanzate (es. "Scegli 2 laboratori espressivi e 3 mattutini").

* **Nuova Tabella `categorie`** (`id`, `nome`, `max_scelte`, `descrizione`): Oltre al nome, definirà il limite di scelte esprimibili dall'utente per quel raggruppamento.
* **Modifica Tabella `laboratori`**: Aggiunta della colonna `id_categoria` (foreign key).
* **Adeguamento Frontend (`index.php`)**:
  * I laboratori verranno renderizzati raggruppati per categoria. Si implementerà un sistema a `Tabs` (schede) di Bootstrap o a scorrimento verticale diviso da header, per facilitare la navigazione.
  * Il controllo Javascript che blocca le check (attualmente fisso a 5) leggerà l'attributo `max_scelte` dalla categoria di appartenenza.
* **Adeguamento Trigger / Backend**: I Trigger DB dovranno aggregare i conteggi delle `scelte` raggruppandoli per la categoria del laboratorio richiesto, incrociandoli col limite.

---

## 4. Sviluppo del Pannello di Amministrazione (Backend UI)
Verrà creata una directory `/admin` o un router interno, con accesso protetto da sessioni PHP (sviluppando il file base `login.php` già presente). Il pannello fornirà un'interfaccia user-friendly per chi gestisce le iscrizioni.

* **Dashboard Riassuntiva**: Statistiche in real-time sui posti occupati, percentuale di riempimento dei laboratori e codici ancora non utilizzati.
* **Gestione Categorie e Settimane**: CRUD (Create, Read, Update, Delete) per configurare i parametri base.
* **Gestione Laboratori**:
  * Form di inserimento e modifica: Titolo, Categoria, Posti massimi, Descrizione.
  * **Image Uploader**: Sistema PHP per l'upload di GIF/Immagini (per la colonna `gif` in DB), con eventuale resize o validazione del formato, bypassando FTP.
* **Gestore Codici**: Generazione massiva (o importazione CSV da software esterni del centro) di nuovi UUID per gli iscritti.
* **Esportazione Dati (Reportistica)**: 
  * Funzionalità "Scarica Risposte": Una vista SQL apposita aggregherà le scelte (incrociando `codici.iscritto` e i `laboratori` scelti) ritornando un file CSV formattato a colonne larghe (es. `Iscritto | Scelta 1 | Scelta 2 ...`), leggibile direttamente in Excel per procedere con l'organizzazione dei gruppi.

---

## 5. Miglioramento dell'Usabilità e dell'Accessibilità (UI/UX)
Il design andrà svecchiato e orientato a un utilizzo "Mobile-First", dato che i ragazzi o i genitori lo useranno prevalentemente da smartphone.

* **Responsive Design Migliorato**:
  * Le card (`.laboratorio`) saranno riadattate. Su smartphone (schermi piccoli), l'immagine sarà posta in cima (header della card) e il testo sotto, migliorando l'allineamento.
  * Il pulsante "Conferma" o la Bottom Bar diventeranno "Sticky" (fissi in fondo allo schermo), per non obbligare l'utente a scrollare la pagina per inviare le preferenze.
* **Feedback e Interattività**:
  * Aggiunta di un counter in tempo reale ("Hai selezionato 2 laboratori su 5").
  * **Ordine di Preferenza Chiaro**: L'ordine in cui l'utente clicca le card verrà tracciato e memorizzato nel database. Sulla card selezionata comparirà un "Badge" numerato (1, 2, 3...) per rendere inequivocabile la priorità assegnata. Deselezionando una card, le priorità successive scaleranno in automatico.
  * Feedback visivo forte al click: invece di un'invisibile checkbox, la card intera cambierà stile (bordo marcato, sfondo leggermente evidenziato) utilizzando classi CSS e transizioni.
  * *Disabled State*: Se un laboratorio esaurisce i posti (`prenotazioni >= posti`), verrà disattivato, "grigiato" e comparirà una label "ESAURITO", impedendo la frustrazione di scoprirlo solo al momento del salvataggio.
* **Accessibilità (A11y)**:
  * Utilizzo rigoroso del tag `<label>` per avvolgere la singola card e la checkbox. Questo renderà lo screen-reader in grado di leggere il nome del laboratorio e il suo stato (selezionato/non selezionato).
  * Controllo dei contrasti del font, garantendo che i font di famiglia *Montserrat* spicchino sullo sfondo, specialmente se viene applicata una Dark Mode o sfondi sfumati per le categorie.

---

## 6. Sincronizzazione con il Gestionale Iscrizioni
Per automatizzare e semplificare il flusso di lavoro degli educatori, il pannello di amministrazione integrerà una procedura guidata per la sincronizzazione con il gestionale esistente (`dbjuvenes.juvenes.it`).

* **Autenticazione al Gestionale**: L'admin inserirà username e password nel pannello, che provvederà ad autenticarsi e conservare i cookie di sessione (`JSESSIONID`) necessari per le API.
* **Importazione Iscritti da Attività**:
  * L'interfaccia interrogherà l'endpoint `/dbjuvenes/attivita/index.json` per mostrare la lista delle "Attività" disponibili, permettendo la ricerca per anno e progetto.
  * Una volta selezionata l'attività, il sistema chiamerà l'endpoint `/dbjuvenes/attivita/iscrittiattivita.json` per scaricare la lista completa degli iscritti (id, nome, cognome, ecc.).
* **Generazione Codici e Preparazione Mailing**:
  * Il sistema controllerà quali iscritti hanno già un codice generato e creerà gli UUID mancanti.
  * Verrà mostrata una tabella riassuntiva con `Iscritto | Email | Codice Generato | Stato Invio`.
* **Invio Email via API**:
  * Verrà sfruttata l'API del gestionale (`/dbjuvenes/preiscritto/testamail` o equivalente per l'invio massivo) inviando una POST request.
  * Il corpo dell'email sarà strutturato partendo da un template HTML predefinito (come il file `example_email.html` allegato alla codebase), i cui segnaposto dinamici (es. `${nome}`, `${cognome}`, `${iscritto_id}`) verranno valorizzati con i dati anagrafici reali dell'iscritto prima dell'invio.
  * La piattaforma itererà sulla lista inviando un'email personalizzata ai genitori, contenente il link di accesso diretto al form di scelta laboratori (`?codice=UUID_PERSONALE`).
