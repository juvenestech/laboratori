<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/private/database.php";
include_once $path;

class Laboratori extends Database {
    private static $table_name = "laboratori";

    function getAll() {
        return $this->select("SELECT * FROM " . self::$table_name);
    }

    function fromId($id) {
        return $this->select(
            "SELECT DISTINCT `laboratori`.*, COUNT(`scelte`.`id`) AS prenotazioni
            FROM `laboratori` 
                LEFT JOIN `scelte` ON `laboratori`.`id` = `scelte`.`id_laboratorio`
            WHERE `laboratori`.`id` = :id
            GROUP BY `laboratori`.id",
            array(
                array(':id', $id, PDO::PARAM_INT)
            )
        );
    }

    function fromSettimana($settimana) {
        $esiste = $this->select(
            "SELECT * FROM `settimane` WHERE `settimane`.`id` = :settimana",
            array(
                array(':settimana', $settimana, PDO::PARAM_INT)
            )
        );
        if(!$esiste){
            http_response_code(400);
            return [];
        }
        return $this->select(
            "SELECT DISTINCT `laboratori`.*, COUNT(scelte_settimana.`id`) AS prenotazioni
            FROM `laboratori` 
            LEFT JOIN (
                SELECT `scelte`.* 
                FROM `scelte` 
                INNER JOIN `codici`
                WHERE `codici`.`codice` = `scelte`.`codice`
                AND`codici`.`id_settimana` = :settimana
            ) scelte_settimana
            ON `laboratori`.`id` = scelte_settimana.`id_laboratorio`
            WHERE 1
            GROUP BY `laboratori`.id",
            array(
                array(':settimana', $settimana, PDO::PARAM_INT)
            )
        );
    }

    function fromCodice($codice) {
        $esiste = $this->select(
            "SELECT * FROM `codici` WHERE `codici`.`codice` = :codice",
            array(
                array(':codice', $codice, PDO::PARAM_STR)
            )
        );
        if(!$esiste){
            http_response_code(400);
            return [];
        }
        return $this->select(
            "SELECT DISTINCT `laboratori`.*, COUNT(scelte_settimana.`id`) AS prenotazioni
            FROM `laboratori` 
            LEFT JOIN (
                SELECT `scelte`.* 
                FROM `scelte` 
                INNER JOIN `codici`
                WHERE `codici`.`codice` = `scelte`.`codice`
                AND`codici`.`id_settimana` = (
                    SELECT `codici`.`id_settimana`
                    FROM `codici`
                    WHERE `codici`.`codice` = :codice
                )
            ) scelte_settimana
            ON `laboratori`.`id` = scelte_settimana.`id_laboratorio`
            WHERE 1
            GROUP BY `laboratori`.id",
            array(
                array(':codice', $codice, PDO::PARAM_STR)
            )
        );
    }
}
