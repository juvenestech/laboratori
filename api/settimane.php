<?php
header("Access-Control-Allow-Origin: https://laboratori.juvenes.it");
header("Content-Type: application/json; charset=UTF-8");

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/private/auth.php";
include_once $path;

if (!$AUTH) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorizzato"]);
    return;
}

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/models/settimane.php";
include_once $path;

$settimane = new Settimane();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id'])) {
        $ret = $settimane->fromId($_GET['id']);
    } elseif (isset($_GET['edizione'])) {
        $ret = $settimane->fromEdizione($_GET['edizione']);
    } else {
        $ret = $settimane->getAll();
    }
    echo json_encode($ret);

} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Supporto DELETE via _method=DELETE
    if (isset($_POST['_method']) && strtoupper($_POST['_method']) === 'DELETE') {
        handleDeleteSettimana($settimane, $_POST);
        return;
    }

    $nome = $_POST['nome'] ?? null;
    $id_edizione = $_POST['id_edizione'] ?? null;
    $id = $_POST['id'] ?? null;

    if (!$nome || !$id_edizione) {
        http_response_code(400);
        echo json_encode(["error" => "Parametri mancanti (nome, id_edizione)"]);
        return;
    }

    if ($id) {
        $ret = $settimane->updateSettimana($id, $nome, $id_edizione);
    } else {
        $ret = $settimane->addSettimana($nome, $id_edizione);
    }

    if (!$ret) {
        http_response_code(400);
        echo json_encode(["error" => "Errore nel salvataggio"]);
        return;
    }
    echo json_encode($ret);

} elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $raw = file_get_contents("php://input");
    $params = json_decode($raw, true);
    if (!is_array($params) || empty($params)) {
        parse_str($raw, $params);
    }
    handleDeleteSettimana($settimane, $params);
} else {
    http_response_code(405);
}

function handleDeleteSettimana($settimane, $params) {
    $id = $params['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "ID mancante"]);
        return;
    }
    $count = $settimane->countCodici($id);
    if ($count > 0) {
        http_response_code(409);
        echo json_encode(["error" => "Impossibile eliminare: ci sono $count codici associati a questa settimana."]);
        return;
    }
    $ret = $settimane->deleteSettimana($id);
    echo json_encode(["success" => $ret]);
}
