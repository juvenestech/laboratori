<?php

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/private/users.php";
include_once $path;

session_start();
$AUTH = true;

if (!isset($_SESSION["username"]) || empty($_SESSION["username"])) {
    $AUTH = false;
}
