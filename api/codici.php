<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/models/codici.php";
include_once $path;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $codici = new Codici();

    if (isset($_GET['codice']))
        $ret = $codici->fromCodice($_GET['codice']);
    else {
        $path = $_SERVER['DOCUMENT_ROOT'];
        $path .= "/private/auth.php";
        include_once $path;
        if(!$AUTH) {
            http_response_code(401);
            echo "KO";
            return;
        }

        if (isset($_GET['iscritto']))
            $ret = $codici->fromIscritto($_GET['iscritto']);
        else
            $ret = $codici->getAll();
    }    

    echo json_encode($ret);
    if(!$ret) http_response_code(400);
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $path = $_SERVER['DOCUMENT_ROOT'];
    $path .= "/private/auth.php";
    include_once $path;
    if(!$AUTH) {
        http_response_code(401);
        echo "KO";
        return;
    }

    $codici = new Codici();

    if(isset($_POST['iscritto']) && isset($_POST['settimana'])) {
        $ret = $codici->addCodice($_POST['iscritto'], $_POST['settimana']);
    } else {
        http_response_code(400);
    }

    echo json_encode($ret);
    if(!$ret) http_response_code(400);
} else
    http_response_code(404);

