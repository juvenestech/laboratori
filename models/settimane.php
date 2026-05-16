<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/private/database.php";
include_once $path;

class Settimane extends Database {
    private static $table_name = "settimane";

    function getAll() {
        return $this->select(
            "SELECT `settimane`.*, `edizioni`.`nome` AS edizione_nome, `edizioni`.`anno` AS edizione_anno
            FROM " . self::$table_name . "
            LEFT JOIN `edizioni` ON `settimane`.`id_edizione` = `edizioni`.`id`
            ORDER BY `edizioni`.`anno` DESC, `settimane`.`id` ASC"
        );
    }

    function fromId($id) {
        return $this->select(
            "SELECT `settimane`.*, `edizioni`.`nome` AS edizione_nome
            FROM " . self::$table_name . "
            LEFT JOIN `edizioni` ON `settimane`.`id_edizione` = `edizioni`.`id`
            WHERE `settimane`.`id` = :id",
            array(
                array(':id', $id, PDO::PARAM_INT)
            )
        );
    }

    function fromEdizione($id_edizione) {
        return $this->select(
            "SELECT * FROM " . self::$table_name . " WHERE id_edizione = :id_edizione ORDER BY id ASC",
            array(
                array(':id_edizione', $id_edizione, PDO::PARAM_INT)
            )
        );
    }

    function addSettimana($nome, $id_edizione) {
        try {
            $id = $this->insert(
                "INSERT INTO " . self::$table_name . " (nome, id_edizione) VALUES (:nome, :id_edizione)",
                array(
                    array(':nome', $nome, PDO::PARAM_STR),
                    array(':id_edizione', $id_edizione, PDO::PARAM_INT)
                )
            );
            return $this->fromId($id);
        } catch (Exception $e) {
            error_log("addSettimana error: " . $e->getMessage());
            return false;
        }
    }

    function updateSettimana($id, $nome, $id_edizione) {
        try {
            $this->insert(
                "UPDATE " . self::$table_name . " SET nome = :nome, id_edizione = :id_edizione WHERE id = :id",
                array(
                    array(':nome', $nome, PDO::PARAM_STR),
                    array(':id_edizione', $id_edizione, PDO::PARAM_INT),
                    array(':id', $id, PDO::PARAM_INT)
                )
            );
            return $this->fromId($id);
        } catch (Exception $e) {
            error_log("updateSettimana error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Conta i codici associati a una settimana.
     * Usato per impedire la cancellazione se ci sono codici.
     */
    function countCodici($id) {
        $r = $this->select(
            "SELECT COUNT(*) AS n FROM `codici` WHERE id_settimana = :id",
            array(
                array(':id', $id, PDO::PARAM_INT)
            )
        );
        return $r && isset($r[0]['n']) ? (int)$r[0]['n'] : 0;
    }

    function deleteSettimana($id) {
        if ($this->countCodici($id) > 0) {
            return false;
        }
        try {
            return $this->delete(
                "DELETE FROM " . self::$table_name . " WHERE id = :id",
                array(
                    array(':id', $id, PDO::PARAM_INT)
                )
            ) > 0;
        } catch (Exception $e) {
            error_log("deleteSettimana error: " . $e->getMessage());
            return false;
        }
    }
}
