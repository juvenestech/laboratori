<?php

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    function __construct()
    {
        include "config.php";
        $this->host = $_DB_HOST;
        $this->db_name = $_DB_NAME;
        $this->username = $_DB_USERNAME;
        $this->password = $_DB_PASSWORD;
        $this->connect();
    }

    public function disconnect()
    {
        $this->conn = null;
    }

    public function connect()
    {
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
            print  "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
    

    public function select($query = "", $params = [])
    {
        try {
            $stmt = $this->executeStatement($query, $params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return false;
    }

    private function executeStatement($query = "", $params = [])
    {
        try {
            $stmt = $this->conn->prepare($query);
            if ($stmt === false)
                throw new Exception("Unable to do prepared statement: " . $query);

            foreach ($params as $param)
                $stmt->bindParam($param[0], $param[1], $param[2]);

            $stmt->execute();

            return $stmt;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function insert($query = "", $params = [])
    {
        try {
            $stmt = $this->executeStatement($query, $params);
            $id = $this->conn->lastInsertId();
            
            return $id;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return false;
    }
}
