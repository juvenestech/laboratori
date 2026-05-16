<?php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    function __construct() {
        include "config.php";
        $this->host = $_DB_HOST;
        $this->db_name = $_DB_NAME;
        $this->username = $_DB_USERNAME;
        $this->password = $_DB_PASSWORD;
        $this->connect();
    }

    public function disconnect() {
        $this->conn = null;
    }

    public function connect() {
        $this->disconnect();

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            throw new Exception("Errore di connessione al database.");
        }

        return $this->conn;
    }
    

    public function select($query = "", $params = []) {
        try {
            $stmt = $this->executeStatement($query, $params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return false;
    }

    private function executeStatement($query = "", $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            if ($stmt === false) {
                $errorInfo = $this->conn->errorInfo();
                throw new Exception("Unable to do prepared statement: " . $query . " - Error: " . $errorInfo[2]);
            }

            foreach ($params as $param)
                $stmt->bindValue($param[0], $param[1], $param[2]);

            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Execution failed: " . $errorInfo[2]);
            }

            return $stmt;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function insert($query = "", $params = []) {
        try {
            $stmt = $this->executeStatement($query, $params);
            return $this->conn->lastInsertId();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return false;
    }

    public function delete($query = "", $params = []) {
        try {
            $stmt = $this->executeStatement($query, $params);
            return $stmt->rowCount();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return false;
    }
}
