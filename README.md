# API

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
### `[POST] form-data: iscritto=<iscritto>&settimana=<settimana>` 
Crea un nuovo codice per `iscritto`, `settimana`. Restituisce la lista di tutti i codici per il dato `iscritto`<br/>
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
### `[GET] ?settimana=<settimana>`
Restituisce i laboratori in una data `settimana`
- 200: Success
- 400: No result
### `[GET] ?codice=<codice>`
Restituisce i laboratori visibili per un dato `codice`
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
### `[GET] ?laboratorio=<laboratorio>&settimana=<settimana>`
Restituisce le scelte di un dato `laboratorio` in una determinata `settimana`<br/>
**⚠ AUTH REQUIRED**
- 200: Success
- 400: No result
- 401: Unauthorized
### `[GET] ?codice=<codice>`
Restituisce le scelte con il dato `codice`<br/>
- 200: Success
- 400: No result
### `[POST] form-data: codice=<codice>&laboratorio=<laboratorio>`
Inserisce la scelta per `codice`, `laboratorio`. Restituisce la lista di tutte le scelte per il dato `codice`<br/>
- 200: Success
- 400: No result
