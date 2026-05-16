<?php
header("Access-Control-Allow-Origin: https://laboratori.juvenes.it");
header("Content-Type: application/json; charset=UTF-8");

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/private/auth.php";
include_once $path;

if(!$AUTH) {
    http_response_code(401);
    echo json_encode(["error" => "Non autorizzato"]);
    return;
}

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/models/edizioni.php";
include_once $path;

$edizioni = new Edizioni();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id']))
        $ret = $edizioni->fromId($_GET['id']);
    elseif (isset($_GET['active']))
        $ret = $edizioni->getActive();
    else
        $ret = $edizioni->getAll();

    echo json_encode($ret);

} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'set_active' && isset($_POST['id'])) {
        $ret = $edizioni->setActive($_POST['id']);
    } elseif (isset($_POST['id']) && isset($_POST['anno']) && isset($_POST['nome'])) {
        $ret = $edizioni->updateEdizione($_POST['id'], $_POST['anno'], $_POST['nome']);
    } elseif (isset($_POST['anno']) && isset($_POST['nome'])) {
        $ret = $edizioni->addEdizione($_POST['anno'], $_POST['nome']);
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Parametri mancanti"]);
        return;
    }

    if($ret === false) http_response_code(400);
    echo json_encode($ret);

} elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    parse_str(file_get_contents("php://input"), $data);
    if (isset($data['id'])) {
        $ret = $edizioni->deleteEdizione($data['id']);
        echo json_encode(["success" => $ret]);
    } else {
        http_response_code(400);
        echo json_encode(["error" => "ID mancante"]);
    }
} else
    http_response_code(405);
