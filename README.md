# API
## `/api/attivita`
### `[GET]` 
Restituisce la lista di tutte le attività
- 200: Success
- 400: No result
### `[GET] ?id=<id>`
Restituisce l'attività con il dato `id`
- 200: Success
- 400: No result
### `[GET] ?giorno=<giorno>&settimana=<settimana>`
Restituisce le attività di un dato `giorno` e `settimana`
- 200: Success
- 400: No result
### `[GET] ?id_laboratorio=<id_laboratorio>`
Restituisce l'attività con il dato `id_laboratorio`
- 200: Success
- 400: No result
<hr/>

## `/api/codici`
### `[GET]` 
Restituisce la lista di tutti i codici<br/>
**⚠ AUTH REQUIRED**
- 200: Success
- 400: No result
- 401: Unauthorized
### `[GET] ?codice=<codice>` 
Restituisce il codice con il dato `codice`<br/>
- 200: Success
- 400: No result
### `[GET] ?iscritto=<iscritto>` 
Restituisce i codici associati al dato `iscritto`<br/>
**⚠ AUTH REQUIRED**
- 200: Success
- 400: No result
- 401: Unauthorized
### `[POST] form-data: iscritto=<iscritto>&id_settimana=<id_settimana>` 
Crea un nuovo codice per `iscritto`, `id_settimana`. Restituisce la lista di tutti i codici per il dato `iscritto`<br/>
**⚠ AUTH REQUIRED**
- 200: Success
- 400: No result
- 401: Unauthorized
<hr/>

## `/api/laboratori`
### `[GET]` 
Restituisce la lista di tutti i laboratori
- 200: Success
- 400: No result
### `[GET] ?id=<id>`
Restituisce il laboratorio con il dato `id`
- 200: Success
- 400: No result
- 400: No result
### `[GET] ?id_settimana=<id_settimana>`
Restituisce i laboratori in una data `id_settimana`
- 200: Success
- 400: No result
<hr/>

## `/api/scelte`
### `[GET]` 
Restituisce la lista di tutte le scelte<br/>
**⚠ AUTH REQUIRED**
- 200: Success
- 400: No result
- 401: Unauthorized
### `[GET] ?id=<id>`
Restituisce la scelta con il dato `id`<br/>
**⚠ AUTH REQUIRED**
- 200: Success
- 400: No result
- 401: Unauthorized
### `[GET] ?codice=<codice>`
Restituisce le scelta con il dato `codice`<br/>
- 200: Success
- 400: No result
### `[POST] form-data: codice=<codice>&id_laboratorio=<id_laboratorio>`
Inserisce la scelta per `codice`, `id_laboratorio`. Restituisce la lista di tutte le scelte per il dato `codice`<br/>
- 200: Success
- 400: No result