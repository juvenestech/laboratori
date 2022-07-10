<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/private/database.php";
include_once $path;

class Scelte extends Database {
    private static $table_name = "scelte";

    function getAll() {
        return $this->select("SELECT * FROM " . self::$table_name);
    }

    function fromId($id) {
        return $this->select(
            "SELECT * FROM " .self::$table_name . " WHERE id = :id", 
            array(
                array(':id', $id, PDO::PARAM_INT)
            )
        );
    }

    function fromCodice($codice) {
        return $this->select(
            "SELECT * FROM " .self::$table_name . " WHERE codice = :codice",
            array(
                array(':codice', $codice, PDO::PARAM_STR)
            )
        );
    }

    function addScelta($codice, $id_laboratorio){
        $this->insert(
            "INSERT INTO " . self::$table_name . " (codice, id_laboratorio) VALUES (:codice, :id_laboratorio)",
            array(
                array(':codice', $codice, PDO::PARAM_STR),
                array(':id_laboratorio', $id_laboratorio, PDO::PARAM_INT)
            )
        );
        return $this->fromCodice($codice);
    }
}