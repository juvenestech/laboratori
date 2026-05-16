<?php
session_start();
$_SESSION = array();

// Distruggi anche il cookie di sessione se presente
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_unset();
session_destroy();

header("Content-Type: text/plain; charset=UTF-8");
echo "OK";
