<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/models/attivita.php";
include_once $path;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $attivita = new Attivita();

    if (isset($_GET['id']))
        $ret = $attivita->fromId($_GET['id']);
    elseif (isset($_GET['settimana']) && isset($_GET['giorno']))
        $ret = $attivita->fromData($_GET['settimana'], $_GET['giorno']);
    elseif (isset($_GET['id_laboratorio']))
        $ret = $attivita->fromLaboratorio($_GET['id_laboratorio']);
    else
        $ret = $attivita->getAll();

    echo json_encode($ret);
    if(!$ret) http_response_code(400);
} else
    http_response_code(404);
