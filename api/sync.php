<?php
header("Access-Control-Allow-Origin: https://laboratori.juvenes.it");
header("Content-Type: application/json; charset=UTF-8");

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/private/auth.php";
include_once $path;

if(!$AUTH) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorizzato"]);
    return;
}

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/models/gestionale.php";
include_once $path;

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/models/codici.php";
include_once $path;

$client = new GestionaleClient();
$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {

        // === Login al gestionale ===
        case 'login':
            if (!isset($_POST['gest_user']) || !isset($_POST['gest_pass'])) {
                http_response_code(400);
                echo json_encode(["error" => "Credenziali mancanti"]);
                return;
            }
            $ok = $client->login($_POST['gest_user'], $_POST['gest_pass']);
            if ($ok) {
                $_SESSION['gest_session'] = $client->getSession();
                echo json_encode(["success" => true]);
            } else {
                http_response_code(401);
                echo json_encode(["error" => "Login gestionale fallito"]);
            }
            break;

        // === Lista attività ===
        case 'attivita':
            if (empty($_SESSION['gest_session'])) {
                http_response_code(401);
                echo json_encode(["error" => "Non autenticato al gestionale"]);
                return;
            }
            $client->setSession($_SESSION['gest_session']);
            $anno = $_GET['anno'] ?? null;
            $ret = $client->getAttivita($anno);
            echo json_encode($ret);
            break;

        // === Iscritti di un'attività ===
        case 'iscritti':
            if (empty($_SESSION['gest_session'])) {
                http_response_code(401);
                echo json_encode(["error" => "Non autenticato al gestionale"]);
                return;
            }
            if (!isset($_GET['id_attivita'])) {
                http_response_code(400);
                echo json_encode(["error" => "id_attivita mancante"]);
                return;
            }
            $client->setSession($_SESSION['gest_session']);
            $ret = $client->getIscrittiAttivita($_GET['id_attivita']);
            echo json_encode($ret);
            break;

        // === Genera codici mancanti per lista iscritti ===
        case 'genera_codici':
            if (!isset($_POST['iscritti']) || !isset($_POST['settimana'])) {
                http_response_code(400);
                echo json_encode(["error" => "Parametri mancanti"]);
                return;
            }
            $iscritti = json_decode($_POST['iscritti'], true);
            $settimana = (int)$_POST['settimana'];
            $codici = new Codici();
            $risultati = [];

            foreach ($iscritti as $iscritto) {
                $id = (int)$iscritto['id'];
                $codice = $codici->fromIscritto($id, $settimana);
                if (!$codice || empty($codice)) {
                    $codice = $codici->addCodice($id, $settimana);
                }
                $risultati[] = [
                    'id_iscritto' => $id,
                    'nome' => $iscritto['nome'] ?? '',
                    'cognome' => $iscritto['cognome'] ?? '',
                    'email' => $iscritto['email'] ?? '',
                    'codice' => $codice[0]['codice'] ?? null,
                    'nuovo' => empty($codici->fromIscritto($id, $settimana)) ? false : true
                ];
            }

            echo json_encode($risultati);
            break;

        // === Invio email personalizzate ===
        case 'invia_email':
            if (empty($_SESSION['gest_session'])) {
                http_response_code(401);
                echo json_encode(["error" => "Non autenticato al gestionale"]);
                return;
            }
            if (!isset($_POST['destinatari']) || !isset($_POST['template']) || !isset($_POST['oggetto'])) {
                http_response_code(400);
                echo json_encode(["error" => "Parametri mancanti"]);
                return;
            }

            $client->setSession($_SESSION['gest_session']);
            $destinatari = json_decode($_POST['destinatari'], true);
            $template = $_POST['template'];
            $oggetto = $_POST['oggetto'];
            $risultati = [];

            foreach ($destinatari as $dest) {
                $corpo = GestionaleClient::renderEmailTemplate($template, $dest);
                try {
                    $res = $client->inviaEmail($dest['id_iscritto'], $oggetto, $corpo);
                    $risultati[] = [
                        'id_iscritto' => $dest['id_iscritto'],
                        'nome' => $dest['nome'] ?? '',
                        'status' => ($res['status'] < 400) ? 'OK' : 'ERRORE'
                    ];
                } catch (Exception $e) {
                    $risultati[] = [
                        'id_iscritto' => $dest['id_iscritto'],
                        'nome' => $dest['nome'] ?? '',
                        'status' => 'ERRORE',
                        'dettaglio' => $e->getMessage()
                    ];
                }
                // Rate limiting: pausa tra invii per non sovraccaricare il gestionale
                usleep(300000); // 300ms
            }

            echo json_encode($risultati);
            break;

        default:
            http_response_code(400);
            echo json_encode(["error" => "Azione non riconosciuta. Azioni valide: login, attivita, iscritti, genera_codici, invia_email"]);
    }
} catch (Exception $e) {
    error_log("Sync API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
