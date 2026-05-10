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
} else
    http_response_code(404);
