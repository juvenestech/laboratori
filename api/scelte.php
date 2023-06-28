<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
        echo "KO";
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
        try {
            $ret = $scelte->addScelta($_POST['codice'], $_POST['laboratorio']);
            echo json_encode($ret);
            if(!$ret) http_response_code(400);
        } catch (Exception $e) {
            http_response_code(400);
            echo $e->getCode();
        }
    } else {
        http_response_code(400);
    }
} else
    http_response_code(404);
