<?php
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $this->conn = new PDO("mysql:host=".DB_HOST.";dbname=doodlesense", "root", DB_PASS);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function getRow($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }

    public function query($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }

    public function insert($table, $data) {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($data);
            
            return $this->conn->lastInsertId();
        } catch(PDOException $e) {
            error_log("Insert Error: " . $e->getMessage());
            return false;
        }
    }

    public function update($table, $data, $where, $whereParams = []) {
        try {
            $set = [];
            foreach ($data as $key => $value) {
                $set[] = "$key = :$key";
            }
            $set = implode(', ', $set);
            
            $query = "UPDATE $table SET $set WHERE $where";
            $stmt = $this->conn->prepare($query);
            
            // Merge data and where params with proper naming
            $mergedParams = $data;
            foreach ($whereParams as $key => $value) {
                $mergedParams["where_$key"] = $value;
            }
            
            $stmt->execute($mergedParams);
            return $stmt->rowCount();
        } catch(PDOException $e) {
            error_log("Update Error: " . $e->getMessage());
            return false;
        }
    }
}
?>