<?php
	// "adhesiones" object
	class Adhesiones {
	
		// database connection and table name
		private $conn;
		private $table_name = "adhesion";
	
		// object properties
		public $id;

		// constructor
		public function __construct($db) {
			$this->conn = $db;
		}

		public function getAdhesiones() {
			$query = "SELECT * FROM " . $this->table_name . " ORDER BY descripcion";
			$stmt = $this->conn->prepare($query);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function getAdhesionesByReparticion($idReparticion) {
			$query = "SELECT * FROM " . $this->table_name . " WHERE idreparticion = :idreparticion ORDER BY descripcion";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":idreparticion", $idReparticion);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}
	}
?>