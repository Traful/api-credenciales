<?php
	// "Tipos" object
	class Tipos {
	
		// database connection and table name
		private $conn;
		private $table_name = "tipos";
	
		// object properties
		public $id;
		public $descripcion;
		public $imagen;
		public $activo;
		public $tope;
		public $maximo;

		// constructor
		public function __construct($db) {
			$this->conn = $db;
		}

		public function getTipos($activos = false) {
			$query = "SELECT * FROM " . $this->table_name;
			if($activos) {
				$query .= " WHERE activo = 1";
			}
			$query .= " ORDER BY descripcion";
			$stmt = $this->conn->prepare($query);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function getTipo($id) {
			$query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":id", $id);
			if($stmt->execute()) {
				return $stmt->fetch(PDO::FETCH_OBJ);
			}
			return null;
		}
	}
?>