<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/private/database.php";
include_once $path;

class Laboratori extends Database
{
    private static $table_name = "laboratori";

    function getAll()
    {
        return $this->select("SELECT * FROM " . self::$table_name);
    }

    function fromId($id)
    {
        return $this->select(
            "SELECT * FROM " . self::$table_name . " WHERE id = :id",
            array(
                array(':id', $id, PDO::PARAM_INT)
            )
        );
    }

    function fromSettimana($id_settimana)
    {
        return $this->select(
            "SELECT DISTINCT `laboratori`.* 
                FROM `laboratori` 
                    INNER JOIN `attivita`
                        ON `laboratori`.`id` = `attivita`.`id_laboratorio`
                    INNER JOIN `giorni`
                        ON `attivita`.`id_giorno` = `giorni`.id
                WHERE `giorni`.`id_settimana` = :id_settimana",
            array(
                array(':id_settimana', $id_settimana, PDO::PARAM_INT)
            )
        );
    }

    function fromCodice($codice)
    {
        return $this->select(
            "SELECT DISTINCT `laboratori`.* 
                FROM `laboratori` 
                    INNER JOIN `attivita`
                        ON `laboratori`.`id` = `attivita`.`id_laboratorio`
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
}
