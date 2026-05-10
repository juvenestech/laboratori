<?php
if (
    $_SERVER['REQUEST_METHOD'] == 'POST' &&
    isset($_POST["username"]) && isset($_POST["password"]) &&
    !empty($_POST["username"]) && !empty($_POST["password"])
) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    $path = $_SERVER['DOCUMENT_ROOT'];
    $path .= "/private/users.php";
    include_once $path;

    if (array_key_exists($username, $users) && password_verify($password, $users[$username])) {
        session_start();
        session_regenerate_id(true);
        $_SESSION["username"] = $username;       
        echo "OK";
    } else {
        http_response_code(401);
        echo "KO";
    }
} else 
    http_response_code(404);
