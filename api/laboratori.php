<?php
header("Access-Control-Allow-Origin: https://laboratori.juvenes.it");
header("Content-Type: application/json; charset=UTF-8");

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/models/laboratori.php";
include_once $path;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $laboratori = new Laboratori();

    if (isset($_GET['id']))
        $ret = $laboratori->fromId($_GET['id']);
    elseif (isset($_GET['codice']))
        $ret = $laboratori->fromCodice($_GET['codice']);
    else {
        // V13: getAll e fromSettimana richiedono autenticazione admin
        $path = $_SERVER['DOCUMENT_ROOT'];
        $path .= "/private/auth.php";
        include_once $path;
        if(!$AUTH) {
            http_response_code(401);
            echo json_encode(["error" => "Non autorizzato"]);
            return;
        }

        if (isset($_GET['settimana']))
            $ret = $laboratori->fromSettimana($_GET['settimana']);
        else
            $ret = $laboratori->getAll();
    }

    if($ret === false) http_response_code(400);
    echo json_encode($ret);
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $path = $_SERVER['DOCUMENT_ROOT'];
    $path .= "/private/auth.php";
    include_once $path;
    if(!$AUTH) {
        http_response_code(401);
        echo json_encode(["error" => "Non autorizzato"]);
        return;
    }

    $laboratori = new Laboratori();

    if (isset($_POST['id'])) {
        $ret = $laboratori->updateLaboratorio(
            $_POST['id'], $_POST['nome'], $_POST['descrizione'] ?? '',
            $_POST['gif'] ?? '', $_POST['posti'] ?? 40, $_POST['id_categoria'] ?? null
        );
    } elseif (isset($_POST['nome'])) {
        $ret = $laboratori->addLaboratorio(
            $_POST['nome'], $_POST['descrizione'] ?? '',
            $_POST['gif'] ?? '', $_POST['posti'] ?? 40, $_POST['id_categoria'] ?? null
        );
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Parametri mancanti"]);
        return;
    }

    if($ret === false) http_response_code(400);
    echo json_encode($ret);
} else
    http_response_code(405);
