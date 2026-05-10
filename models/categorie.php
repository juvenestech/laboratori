<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/private/database.php";
include_once $path;

class Categorie extends Database {
    private static $table_name = "categorie";

    function getAll() {
        return $this->select("SELECT * FROM " . self::$table_name);
    }

    function fromId($id) {
        return $this->select(
            "SELECT * FROM " . self::$table_name . " WHERE id = :id",
            array(
                array(':id', $id, PDO::PARAM_INT)
            )
        );
    }

    function fromEdizione($id_edizione) {
        return $this->select(
            "SELECT * FROM " . self::$table_name . " WHERE id_edizione = :id_edizione",
            array(
                array(':id_edizione', $id_edizione, PDO::PARAM_INT)
            )
        );
    }

    function addCategoria($nome, $max_scelte, $descrizione, $id_edizione) {
        try {
            $id = $this->insert(
                "INSERT INTO " . self::$table_name . " (nome, max_scelte, descrizione, id_edizione) VALUES (:nome, :max_scelte, :descrizione, :id_edizione)",
                array(
                    array(':nome', $nome, PDO::PARAM_STR),
                    array(':max_scelte', $max_scelte, PDO::PARAM_INT),
                    array(':descrizione', $descrizione, PDO::PARAM_STR),
                    array(':id_edizione', $id_edizione, PDO::PARAM_INT)
                )
            );
            return $this->fromId($id);
        } catch (Exception $e) {
            error_log("addCategoria error: " . $e->getMessage());
            return false;
        }
    }

    function updateCategoria($id, $nome, $max_scelte, $descrizione) {
        try {
            $this->insert(
                "UPDATE " . self::$table_name . " SET nome = :nome, max_scelte = :max_scelte, descrizione = :descrizione WHERE id = :id",
                array(
                    array(':nome', $nome, PDO::PARAM_STR),
                    array(':max_scelte', $max_scelte, PDO::PARAM_INT),
                    array(':descrizione', $descrizione, PDO::PARAM_STR),
                    array(':id', $id, PDO::PARAM_INT)
                )
            );
            return $this->fromId($id);
        } catch (Exception $e) {
            error_log("updateCategoria error: " . $e->getMessage());
            return false;
        }
    }

    function deleteCategoria($id) {
        try {
            $this->insert(
                "DELETE FROM " . self::$table_name . " WHERE id = :id",
                array(
                    array(':id', $id, PDO::PARAM_INT)
                )
            );
            return true;
        } catch (Exception $e) {
            error_log("deleteCategoria error: " . $e->getMessage());
            return false;
        }
    }
}
