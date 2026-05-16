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
$path .= "/models/categorie.php";
include_once $path;

$categorie = new Categorie();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id']))
        $ret = $categorie->fromId($_GET['id']);
    elseif (isset($_GET['edizione']))
        $ret = $categorie->fromEdizione($_GET['edizione']);
    else
        $ret = $categorie->getAll();

    echo json_encode($ret);

} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id']) && isset($_POST['nome']) && isset($_POST['max_scelte'])) {
        $ret = $categorie->updateCategoria($_POST['id'], $_POST['nome'], $_POST['max_scelte'], $_POST['descrizione'] ?? '');
    } elseif (isset($_POST['nome']) && isset($_POST['max_scelte']) && isset($_POST['id_edizione'])) {
        $ret = $categorie->addCategoria($_POST['nome'], $_POST['max_scelte'], $_POST['descrizione'] ?? '', $_POST['id_edizione']);
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
        $ret = $categorie->deleteCategoria($data['id']);
        echo json_encode(["success" => $ret]);
    } else {
        http_response_code(400);
        echo json_encode(["error" => "ID mancante"]);
    }
} else
    http_response_code(405);
