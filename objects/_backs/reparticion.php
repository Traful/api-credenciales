<?php
	// "reparticion" object
	class Reparticion {
	
		// database connection and table name
		private $conn;
		private $table_name = "reparticiones";
	
		// object properties
		public $id;

		// constructor
		public function __construct($db) {
			$this->conn = $db;
		}

		public function getReparticiones() {
			$query = "SELECT * FROM " . $this->table_name . " ORDER BY id";
			$stmt = $this->conn->prepare($query);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return null;
		}

		public function getReparticionInfoById($id) {
			$query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":id", $id);
			if($stmt->execute()) {
				return $stmt->fetch(PDO::FETCH_OBJ);
			}
			return null;
		}

		public function getReparticionItems($idreparticion, $soloactivos = true) {
			$query = "SELECT * FROM items_reparticiones WHERE idreparticion = :idreparticion";
			if($soloactivos) {
				$query .= " AND activo = 1";
			}
			$query .= " ORDER BY id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":idreparticion", $idreparticion);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return null;
		}

		public function getReparticionAdhesionesById($id) {
			$query = "SELECT * FROM adhesion WHERE idreparticion = :idreparticion";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":idreparticion", $id);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}
	}
?>