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
    elseif (isset($_GET['attivita']))
        $ret = $scelte->fromAttivita($_GET['attivita']);
    else
        $ret = $scelte->getAll();

    echo json_encode($ret);
    if(!$ret) http_response_code(400);
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $scelte = new Scelte();

    if(isset($_POST['codice']) && isset($_POST['laboratorio'])) {
        $ret = $scelte->addScelta($_POST['codice'], $_POST['laboratorio']);
    } else {
        http_response_code(400);
    }

    echo json_encode($ret);
    if(!$ret) http_response_code(400);
} else
    http_response_code(404);
