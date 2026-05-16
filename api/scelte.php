<?php
header("Access-Control-Allow-Origin: https://laboratori.juvenes.it");
header("Content-Type: application/json; charset=UTF-8");

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/models/scelte.php";
include_once $path;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $scelte = new Scelte();

    if (isset($_GET['codice'])){
        $ret = $scelte->fromCodice($_GET['codice']);
        echo json_encode($ret);
        return;
    }

    $path = $_SERVER['DOCUMENT_ROOT'];
    $path .= "/private/auth.php";
    include_once $path;
    if(!$AUTH) {
        http_response_code(401);
        echo json_encode(["error" => "Non autorizzato"]);
        return;
    }

    if (isset($_GET['id']))
        $ret = $scelte->fromId($_GET['id']);
    elseif (isset($_GET['laboratorio']) && isset($_GET['settimana']))
        $ret = $scelte->fromLaboratorio($_GET['laboratorio'],$_GET['settimana']);
    else
        $ret = $scelte->getAll();

    echo json_encode($ret);
    if(!$ret) http_response_code(400);
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $scelte = new Scelte();

    // Supporto tunneling DELETE via POST con _method=DELETE
    if (isset($_POST['_method']) && strtoupper($_POST['_method']) === 'DELETE') {
        handleDelete($scelte, $_POST);
        return;
    }

    if(isset($_POST['codice']) && isset($_POST['laboratorio'])) {
        // V9: Verifica che il codice non sia scaduto prima di inserire
        $path = $_SERVER['DOCUMENT_ROOT'];
        $path .= "/models/codici.php";
        include_once $path;

        $codici = new Codici();
        $codice_data = $codici->fromCodice($_POST['codice']);

        if (!$codice_data || empty($codice_data)) {
            http_response_code(400);
            echo json_encode(["error" => "Codice non valido"]);
            return;
        }

        if ($codice_data[0]['expired']) {
            http_response_code(403);
            echo json_encode(["error" => "Il codice è scaduto"]);
            return;
        }

        try {
            $ordine = isset($_POST['ordine']) ? (int)$_POST['ordine'] : null;
            $ret = $scelte->addScelta($_POST['codice'], $_POST['laboratorio'], $ordine);
            echo json_encode($ret);
            if(!$ret) http_response_code(400);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Parametri mancanti"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $scelte = new Scelte();
    // Tenta parsing JSON body, poi form-urlencoded
    $raw = file_get_contents('php://input');
    $params = json_decode($raw, true);
    if (!is_array($params) || empty($params)) {
        parse_str($raw, $params);
    }
    handleDelete($scelte, $params);
} else
    http_response_code(404);

/**
 * Gestisce DELETE di una scelta. Valida il codice prima di eliminare.
 * Non richiede auth: la conoscenza del codice è già la credenziale.
 */
function handleDelete($scelte, $params) {
    $codice = $params['codice'] ?? null;
    $laboratorio = $params['laboratorio'] ?? null;

    if (!$codice || !$laboratorio) {
        http_response_code(400);
        echo json_encode(["error" => "Parametri mancanti (codice, laboratorio)"]);
        return;
    }

    // Verifica che il codice esista e non sia scaduto
    $path = $_SERVER['DOCUMENT_ROOT'];
    $path .= "/models/codici.php";
    include_once $path;

    $codici = new Codici();
    $codice_data = $codici->fromCodice($codice);

    if (!$codice_data || empty($codice_data)) {
        http_response_code(400);
        echo json_encode(["error" => "Codice non valido"]);
        return;
    }

    if ($codice_data[0]['expired']) {
        http_response_code(403);
        echo json_encode(["error" => "Il codice è scaduto"]);
        return;
    }

    try {
        $ok = $scelte->deleteScelta($codice, (int)$laboratorio);
        echo json_encode([
            "deleted" => $ok,
            "scelte" => $scelte->fromCodice($codice)
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["error" => $e->getMessage()]);
    }
}
