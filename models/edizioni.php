<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/private/database.php";
include_once $path;

class Edizioni extends Database {
    private static $table_name = "edizioni";

    function getAll() {
        return $this->select("SELECT * FROM " . self::$table_name . " ORDER BY anno DESC, id DESC");
    }

    function getActive() {
        return $this->select(
            "SELECT * FROM " . self::$table_name . " WHERE is_active = 1"
        );
    }

    function fromId($id) {
        return $this->select(
            "SELECT * FROM " . self::$table_name . " WHERE id = :id",
            array(
                array(':id', $id, PDO::PARAM_INT)
            )
        );
    }

    function addEdizione($anno, $nome, $is_active = 0) {
        try {
            $id = $this->insert(
                "INSERT INTO " . self::$table_name . " (anno, nome, is_active) VALUES (:anno, :nome, :is_active)",
                array(
                    array(':anno', $anno, PDO::PARAM_INT),
                    array(':nome', $nome, PDO::PARAM_STR),
                    array(':is_active', $is_active, PDO::PARAM_INT)
                )
            );
            return $this->fromId($id);
        } catch (Exception $e) {
            error_log("addEdizione error: " . $e->getMessage());
            return false;
        }
    }

    function setActive($id) {
        try {
            // Disattiva tutte le edizioni
            $this->insert(
                "UPDATE " . self::$table_name . " SET is_active = 0 WHERE 1",
                array()
            );
            // Attiva l'edizione specificata
            $this->insert(
                "UPDATE " . self::$table_name . " SET is_active = 1 WHERE id = :id",
                array(
                    array(':id', $id, PDO::PARAM_INT)
                )
            );
            return $this->fromId($id);
        } catch (Exception $e) {
            error_log("setActive error: " . $e->getMessage());
            return false;
        }
    }

    function updateEdizione($id, $anno, $nome) {
        try {
            $this->insert(
                "UPDATE " . self::$table_name . " SET anno = :anno, nome = :nome WHERE id = :id",
                array(
                    array(':anno', $anno, PDO::PARAM_INT),
                    array(':nome', $nome, PDO::PARAM_STR),
                    array(':id', $id, PDO::PARAM_INT)
                )
            );
            return $this->fromId($id);
        } catch (Exception $e) {
            error_log("updateEdizione error: " . $e->getMessage());
            return false;
        }
    }

    function deleteEdizione($id) {
        try {
            $this->insert(
                "DELETE FROM " . self::$table_name . " WHERE id = :id AND is_active = 0",
                array(
                    array(':id', $id, PDO::PARAM_INT)
                )
            );
            return true;
        } catch (Exception $e) {
            error_log("deleteEdizione error: " . $e->getMessage());
            return false;
        }
    }
}
