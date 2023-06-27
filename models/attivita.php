<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/private/database.php";
include_once $path;

class Attivita extends Database {
    private static $table_name = "attivita";

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

    function fromGiorno($giorno) {
        return $this->select(
            "SELECT `attivita`.* 
            FROM `attivita` 
            WHERE AND `attivita`.id_giorno = :giorno", 
            array(
                array(':giorno', $giorno, PDO::PARAM_INT)
            )
        );
    }
    
    function fromSettimana($settimana) {
        return $this->select(
            "SELECT `attivita`.* 
            FROM `attivita` 
                INNER JOIN `giorni` ON `attivita`.id_giorno = `giorni`.id
            WHERE AND `giorni`.id_settimana = :settimana", 
            array(
                array(':settimana', $settimana, PDO::PARAM_INT)
            )
        );
    }

    function fromCodice($codice) {
        return $this->select(
            "SELECT DISTINCT `attivita`.* 
            FROM `attivita`
                INNER JOIN `giorni`
                    ON `attivita`.`id_giorno` = `giorni`.id  
            WHERE `giorni`.`id_settimana` = (
                SELECT `id_settimana` FROM `codici`
                WHERE `codici`.`codice` = :codice
            )", 
            array(
                array(':codice', $codice, PDO::PARAM_STR)
            )
        );
    }
    
    function fromLaboratorio($id_laboratorio) {
        return $this->select(
            "SELECT * FROM " .self::$table_name . " WHERE `id_laboratorio` = :id_laboratorio", 
            array(
                array(':id_laboratorio', $id_laboratorio, PDO::PARAM_INT)
            )
        );
    }
}