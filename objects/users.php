<?php
    // "user" object
    class Users {
    
        // database connection and table name
        private $conn;
        private $table_name = "users";
    
        // object properties
        public $id;
        public $username;
        public $password;
        public $apellido;
        public $nombre;
        public $esadmin = false;

        // constructor
        public function __construct($db) {
            $this->conn = $db;
        }

        function getUsers() {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY id";
            $stmt = $this->conn->prepare($query);
            if($stmt->execute()) {
                return $stmt->fetchAll(PDO::FETCH_OBJ);
            }
            return [];
        }

        function create() {
            if(!$this->usuarioExists()) {
                $query = "INSERT INTO " . $this->table_name . " SET username = :username, password = :password, apellido = :apellido, nombre = :nombre, esadmin = 0";
				$stmt = $this->conn->prepare($query);
				$this->username = htmlspecialchars(strip_tags($this->username));
				$this->password = htmlspecialchars(strip_tags($this->password));
                $stmt->bindParam(":username", $this->username);
                $stmt->bindParam(":apellido", $this->apellido);
                $stmt->bindParam(":nombre", $this->nombre);
                $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
                $stmt->bindParam(":password", $password_hash);
                if($stmt->execute()) {
                    return true;
                }
                return false;
            } else {
                return false;
            }
        }

        function usuarioExists() { 
            $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":username", $this->username);
			if($stmt->execute()) {
				if($stmt->rowCount() > 0) {
					return $stmt->fetch(PDO::FETCH_OBJ);
				}
			}
			return false;
        }
	}
?>