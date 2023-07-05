<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/private/database.php";
include_once $path;

class Codici extends Database {
    private static $table_name = "codici";

    function getAll() {
        return $this->select("SELECT * FROM " . self::$table_name);
    }

    function fromIscritto($iscritto, $settimana) {
        return $this->select(
            "SELECT codice AS `codice`, iscritto, id_settimana, expired FROM " . self::$table_name . 
            " WHERE iscritto = :iscritto AND id_settimana = :settimana", 
            array(
                array(':iscritto', $iscritto, PDO::PARAM_INT),
                array(':settimana', $settimana, PDO::PARAM_INT)
            )
        );
    }

    function fromCodice($codice) {
        return $this->select(
            "SELECT codice AS `codice`, iscritto, id_settimana, expired FROM " . self::$table_name . 
            " WHERE codice = :codice",
            array(
                array(':codice', $codice, PDO::PARAM_STR)
            )
        );
    }

    function addCodice($iscritto, $settimana) {
        $codice = $this->fromIscritto($iscritto, $settimana);
        if(!$codice){
            try {
                $this->insert(
                    "INSERT INTO " . self::$table_name . " (iscritto, id_settimana) VALUES (:iscritto, :settimana)",
                    array(
                        array(':iscritto', $iscritto, PDO::PARAM_INT),
                        array(':settimana', $settimana, PDO::PARAM_STR)
                    )
                );
            } catch (Exception $e) {
                return;
            }
            $codice = $this->fromIscritto($iscritto, $settimana);
        }
        return $codice;
    }
}