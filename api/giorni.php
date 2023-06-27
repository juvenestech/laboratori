<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/models/giorni.php";
include_once $path;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $giorni = new Giorni();

    if (isset($_GET['settimana']))
        $ret = $giorni->fromSettimana($_GET['settimana']);
    else
        $ret = $giorni->getAll();

    echo json_encode($ret);
    if(!$ret) http_response_code(400);
} else
    http_response_code(404);
