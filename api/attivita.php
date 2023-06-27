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
    elseif (isset($_GET['giorno']))
        $ret = $attivita->fromGiorno($_GET['giorno']);
    elseif (isset($_GET['settimana']))
        $ret = $attivita->fromSettimana($_GET['settimana']);
    elseif (isset($_GET['laboratorio']))
        $ret = $attivita->fromLaboratorio($_GET['laboratorio']);
    elseif (isset($_GET['codice']))
        $ret = $attivita->fromCodice($_GET['codice']);
    else
        $ret = $attivita->getAll();

    echo json_encode($ret);
    if(!$ret) http_response_code(400);
} else
    http_response_code(404);
