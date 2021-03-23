<?php
	// "config" object
	class Config {
	
		// database connection and table name
		private $conn;
		private $table_name = "settings";
	
		// object properties
		public $id;
		public $tabla;
		public $descripcion;
		public $telefono;
		public $interno;
		public $boxs;
		public $intervalo;
		public $desde_hora;
		public $hasta_hora;
		public $icono;
		public $clase;
		public $activo;

		// constructor
		public function __construct($db) {
			$this->conn = $db;
		}

		public function getConfigs() {
			$query = "SELECT * FROM " . $this->table_name . " WHERE activo = 1 ORDER BY id";
			$stmt = $this->conn->prepare($query);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function getConfigsByIdReparticion($id) {
			$query = "SELECT * FROM " . $this->table_name . " WHERE idreparticion = :idreparticion AND activo = 1 ORDER BY id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":idreparticion", $id);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function getConfigsByIdSetting($id) {
			$query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND activo = 1 ORDER BY id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":id", $id);
			if($stmt->execute()) {
				return $stmt->fetch(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function getConfigsByIdItem($id) {
			$query = "SELECT * FROM " . $this->table_name . " WHERE iditem_reparticion = :iditem_reparticion AND activo = 1 ORDER BY id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":iditem_reparticion", $id);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function getConfigByTabla($tabla) {
			$query = "SELECT * FROM " . $this->table_name . " WHERE tabla = :tabla";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":tabla", $tabla);
			if($stmt->execute()) {
				return $stmt->fetch(PDO::FETCH_OBJ);
			}
			return null;
		}

		public function getBoxsByTabla($tabla) {
			$query = "SELECT boxs FROM " . $this->table_name . " WHERE tabla = :tabla";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":tabla", $tabla);
			if($stmt->execute()) {
				return $stmt->fetch(PDO::FETCH_OBJ)->boxs;
			}
			return 0;
		}
	}
?>