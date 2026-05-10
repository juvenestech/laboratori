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
            "SELECT DISTINCT `laboratori`.*, `categorie`.`nome` AS categoria_nome, `categorie`.`max_scelte`,
                COUNT(`scelte`.`id`) AS prenotazioni
            FROM `laboratori` 
                LEFT JOIN `scelte` ON `laboratori`.`id` = `scelte`.`id_laboratorio`
                LEFT JOIN `categorie` ON `laboratori`.`id_categoria` = `categorie`.`id`
            WHERE `laboratori`.`id` = :id
            GROUP BY `laboratori`.id",
            array(
                array(':id', $id, PDO::PARAM_INT)
            )
        );
    }

    function fromCategoria($id_categoria) {
        return $this->select(
            "SELECT DISTINCT `laboratori`.*, COUNT(`scelte`.`id`) AS prenotazioni
            FROM `laboratori`
                LEFT JOIN `scelte` ON `laboratori`.`id` = `scelte`.`id_laboratorio`
            WHERE `laboratori`.`id_categoria` = :id_categoria
            GROUP BY `laboratori`.id",
            array(
                array(':id_categoria', $id_categoria, PDO::PARAM_INT)
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
            "SELECT DISTINCT `laboratori`.*, `categorie`.`nome` AS categoria_nome, `categorie`.`max_scelte`,
                COUNT(scelte_settimana.`id`) AS prenotazioni
            FROM `laboratori` 
            LEFT JOIN `categorie` ON `laboratori`.`id_categoria` = `categorie`.`id`
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
            "SELECT DISTINCT `laboratori`.*, `categorie`.`nome` AS categoria_nome, `categorie`.`max_scelte`,
                COUNT(scelte_settimana.`id`) AS prenotazioni
            FROM `laboratori` 
            LEFT JOIN `categorie` ON `laboratori`.`id_categoria` = `categorie`.`id`
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

    function addLaboratorio($nome, $descrizione, $gif, $posti, $id_categoria) {
        try {
            $id = $this->insert(
                "INSERT INTO " . self::$table_name . " (nome, descrizione, gif, posti, id_categoria) VALUES (:nome, :descrizione, :gif, :posti, :id_categoria)",
                array(
                    array(':nome', $nome, PDO::PARAM_STR),
                    array(':descrizione', $descrizione, PDO::PARAM_STR),
                    array(':gif', $gif, PDO::PARAM_STR),
                    array(':posti', $posti, PDO::PARAM_INT),
                    array(':id_categoria', $id_categoria, PDO::PARAM_INT)
                )
            );
            return $this->fromId($id);
        } catch (Exception $e) {
            error_log("addLaboratorio error: " . $e->getMessage());
            return false;
        }
    }

    function updateLaboratorio($id, $nome, $descrizione, $gif, $posti, $id_categoria) {
        try {
            $this->insert(
                "UPDATE " . self::$table_name . " SET nome = :nome, descrizione = :descrizione, gif = :gif, posti = :posti, id_categoria = :id_categoria WHERE id = :id",
                array(
                    array(':nome', $nome, PDO::PARAM_STR),
                    array(':descrizione', $descrizione, PDO::PARAM_STR),
                    array(':gif', $gif, PDO::PARAM_STR),
                    array(':posti', $posti, PDO::PARAM_INT),
                    array(':id_categoria', $id_categoria, PDO::PARAM_INT),
                    array(':id', $id, PDO::PARAM_INT)
                )
            );
            return $this->fromId($id);
        } catch (Exception $e) {
            error_log("updateLaboratorio error: " . $e->getMessage());
            return false;
        }
    }

    function deleteLaboratorio($id) {
        try {
            $this->insert(
                "DELETE FROM " . self::$table_name . " WHERE id = :id",
                array(
                    array(':id', $id, PDO::PARAM_INT)
                )
            );
            return true;
        } catch (Exception $e) {
            error_log("deleteLaboratorio error: " . $e->getMessage());
            return false;
        }
    }
}
