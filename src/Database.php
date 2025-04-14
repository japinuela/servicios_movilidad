<?php
namespace App;

use PDO;
use PDOException;

class Database {
    private $host;
    private $port;
    private $dbname;
    private $user;
    private $pass;
    private $charset;
    private $pdo;

    public function __construct() {
        $this->host = $_ENV['DB_HOST'];
        $this->port = $_ENV['DB_PORT'];
        $this->dbname = $_ENV['DB_NAME'];
        $this->user = $_ENV['DB_USER'];
        $this->pass = $_ENV['DB_PASSWORD'];
        $this->charset = $_ENV['DB_CHARSET'];
    }

    // Método para obtener la conexión a la base de datos
    public function connect() {
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}";
        try {
            $this->pdo = new  \PDO($dsn, $this->user, $this->pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->pdo;
        } catch (PDOException $e) {
            // Manejo del error de conexión
            throw new PDOException($e->getMessage());
        }
    }
}
