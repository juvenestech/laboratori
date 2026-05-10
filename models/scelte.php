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
                array(':laboratorio', $laboratorio, PDO::PARAM_INT),
                array(':settimana', $settimana, PDO::PARAM_INT)
            )
        );
    }

    function fromCodice($codice) {
        return $this->select(
            "SELECT * FROM " .self::$table_name . " WHERE codice = :codice ORDER BY ordine ASC",
            array(
                array(':codice', $codice, PDO::PARAM_STR)
            )
        );
    }

    function addScelta($codice, $laboratorio, $ordine = null){
        try {
            $this->insert(
                "INSERT INTO " . self::$table_name . " (codice, id_laboratorio, ordine) VALUES (:codice, :laboratorio, :ordine)",
                array(
                    array(':codice', $codice, PDO::PARAM_STR),
                    array(':laboratorio', $laboratorio, PDO::PARAM_INT),
                    array(':ordine', $ordine, PDO::PARAM_INT)
                )
            );
        } catch (PDOException $e) {
            error_log("addScelta error: " . $e->getMessage());
            throw new Exception("Errore nel salvataggio della scelta.", (int)$e->getCode());
        }
        return $this->fromCodice($codice);
    }

    /**
     * Restituisce le scelte aggregate per la reportistica CSV.
     * Incrocia codici.iscritto con i laboratori scelti.
     */
    function getReport($settimana) {
        return $this->select(
            "SELECT `codici`.`iscritto`, `codici`.`codice`, 
                    `laboratori`.`nome` AS laboratorio_nome,
                    `scelte`.`ordine`, `scelte`.`timestamp`
            FROM `scelte`
                INNER JOIN `codici` ON `scelte`.`codice` = `codici`.`codice`
                INNER JOIN `laboratori` ON `scelte`.`id_laboratorio` = `laboratori`.`id`
            WHERE `codici`.`id_settimana` = :settimana
            ORDER BY `codici`.`iscritto`, `scelte`.`ordine`",
            array(
                array(':settimana', $settimana, PDO::PARAM_INT)
            )
        );
    }
}