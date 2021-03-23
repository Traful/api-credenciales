<?php
	// "tiposcuentas" object
	class TiposCuenta {
	
		// database connection and table name
		private $conn;
		private $table_name = "tipocuenta";
	
		// object properties
		public $id;

		// constructor
		public function __construct($db) {
			$this->conn = $db;
		}

		public function getTipos() {
			$query = "SELECT * FROM " . $this->table_name . " ORDER BY id";
			$stmt = $this->conn->prepare($query);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}
	}
?>