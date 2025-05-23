<?php
class Database {
    private $host = "localhost";
    private $db_name = "montink";
    private $username = "root";
    private $password = "";
    public $conn;

    public function conectar() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
        } catch (PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }

        return $this->conn;
    }
}
