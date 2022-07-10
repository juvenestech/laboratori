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

    function fromData($settimana, $giorno) {
        return $this->select(
            "SELECT `attivita`.* 
            FROM `attivita` 
                INNER JOIN `laboratori` 
                    ON attivita.id_laboratorio = `laboratori`.id
                INNER JOIN `giorni`
                    ON `laboratori`.id_giorno = `giorni`.id
                INNER JOIN `settimane`
                    ON `giorni`.id_settimana = `settimane`.id
            WHERE `settimane`.id = :settimana
                AND `giorni`.id = :giorno", 
            array(
                array(':settimana', $settimana, PDO::PARAM_INT), 
                array(':giorno', $giorno, PDO::PARAM_INT)
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