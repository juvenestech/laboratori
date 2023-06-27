<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/models/laboratori.php";
include_once $path;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $laboratori = new Laboratori();

    if (isset($_GET['id']))
        $ret = $laboratori->fromId($_GET['id']);
    elseif (isset($_GET['settimana']))
        $ret = $laboratori->fromSettimana($_GET['settimana']);
    elseif (isset($_GET['codice']))
        $ret = $laboratori->fromCodice($_GET['codice']);
    else
        $ret = $laboratori->getAll();

    echo json_encode($ret);
    if(!$ret) http_response_code(400);
} else
    http_response_code(404);
