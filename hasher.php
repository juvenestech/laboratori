<?php
if (isset($_POST)) {
    if (isset($_POST["password"]) || !empty($_POST["password"])) {
        $password = $_POST["password"];

        $hashed = hash("sha512", $password);
        echo $hashed;
    }
}
