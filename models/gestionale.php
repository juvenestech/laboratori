<?php
/**
 * Classe per la comunicazione con il gestionale dbjuvenes.juvenes.it
 * Gestisce autenticazione, import iscritti, e invio email (UPDATE.md §6)
 */
class GestionaleClient {
    private $baseUrl = 'https://dbjuvenes.juvenes.it/dbjuvenes';
    private $sessionCookie = null;

    /**
     * Autentica l'admin al gestionale e salva il cookie di sessione JSESSIONID
     */
    public function login($username, $password) {
        $ch = curl_init($this->baseUrl . '/j_spring_security_check');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'j_username' => $username,
                'j_password' => $password
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 15
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            return false;
        }

        // Estrai JSESSIONID dall'header Set-Cookie
        if (preg_match('/JSESSIONID=([^;]+)/', $response, $matches)) {
            $this->sessionCookie = $matches[1];
            return true;
        }

        return false;
    }

    /**
     * Imposta il cookie di sessione (per riutilizzo tra richieste)
     */
    public function setSession($jsessionid) {
        $this->sessionCookie = $jsessionid;
    }

    public function getSession() {
        return $this->sessionCookie;
    }

    /**
     * Esegue una GET autenticata al gestionale
     */
    private function authenticatedGet($endpoint, $params = []) {
        if (!$this->sessionCookie) {
            throw new Exception("Non autenticato al gestionale");
        }

        $url = $this->baseUrl . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Cookie: JSESSIONID=' . $this->sessionCookie,
                'Accept: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 401 || $httpCode == 403) {
            throw new Exception("Sessione gestionale scaduta");
        }

        return json_decode($response, true);
    }

    /**
     * Esegue una POST autenticata al gestionale
     */
    private function authenticatedPost($endpoint, $data = []) {
        if (!$this->sessionCookie) {
            throw new Exception("Non autenticato al gestionale");
        }

        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Cookie: JSESSIONID=' . $this->sessionCookie,
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 401 || $httpCode == 403) {
            throw new Exception("Sessione gestionale scaduta");
        }

        return [
            'status' => $httpCode,
            'body' => json_decode($response, true) ?: $response
        ];
    }

    /**
     * Recupera la lista delle attività disponibili dal gestionale
     * Endpoint: /attivita/index.json
     */
    public function getAttivita($anno = null, $progetto = null) {
        $params = [];
        if ($anno) $params['anno'] = $anno;
        if ($progetto) $params['progetto'] = $progetto;

        return $this->authenticatedGet('/attivita/index.json', $params);
    }

    /**
     * Recupera gli iscritti di una specifica attività
     * Endpoint: /attivita/iscrittiattivita.json
     */
    public function getIscrittiAttivita($idAttivita) {
        return $this->authenticatedGet('/attivita/iscrittiattivita.json', [
            'id' => $idAttivita,
            'length' => 1000 // per sicurezza, se ci sono molte iscrizioni
        ]);
    }

    /**
     * Invia un'email personalizzata tramite l'API del gestionale
     * Endpoint: /preiscritto/testamail (o equivalente)
     */
    public function inviaEmail($idIscritto, $oggetto, $corpoHtml) {
        return $this->authenticatedPost('/preiscritto/testamail', [
            'id' => $idIscritto,
            'oggetto' => $oggetto,
            'testo' => $corpoHtml
        ]);
    }

    /**
     * Prepara il corpo dell'email sostituendo i segnaposto nel template
     * Segnaposto supportati: ${nome}, ${cognome}, ${codice}, ${id_iscritto}
     */
    public static function renderEmailTemplate($template, $dati) {
        $replacements = [
            '${nome}' => $dati['nome'] ?? '',
            '${cognome}' => $dati['cognome'] ?? '',
            '${codice}' => $dati['codice'] ?? '',
            '${id_iscritto}' => $dati['id_iscritto'] ?? '',
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
