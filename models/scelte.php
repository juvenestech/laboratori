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

    function fromLaboratorio($laboratorio, $settimana) {
        return $this->select(
            "SELECT `scelte`.* from `scelte`
                INNER JOIN `codici` ON `scelte`.`codice` = `codici`.`codice`
            WHERE `scelte`.`id_laboratorio` = :laboratorio
                AND `codici`.`id_settimana` = :settimana",
            array(
                array(':id_attivita', $id_attivita, PDO::PARAM_INT),
                array(':settimana', $settimana, PDO::PARAM_INT)
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

    function addScelta($codice, $laboratorio){
        try {
            $this->insert(
                "INSERT INTO " . self::$table_name . " (codice, id_laboratorio) VALUES (:codice, :laboratorio)",
                array(
                    array(':codice', $codice, PDO::PARAM_STR),
                    array(':laboratorio', $laboratorio, PDO::PARAM_INT)
                )
            );
        } catch (PDOException $e) {
            return $e->getMessage();
        }
        return $this->fromCodice($codice);
    }
}