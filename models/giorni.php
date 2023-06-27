<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/private/database.php";
include_once $path;

class Giorni extends Database {
    private static $table_name = "giorni";

    function getAll() {
        return $this->select("SELECT * FROM " . self::$table_name);
    }
    
    function fromSettimana($settimana) {
        return $this->select(
            "SELECT `giorni`.* 
            FROM `giorni` 
            WHERE AND `giorni`.id_settimana = :settimana", 
            array(
                array(':settimana', $settimana, PDO::PARAM_INT)
            )
        );
    }
}