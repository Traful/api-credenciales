<?php
	// "estados" object
	class Estados {
	
		// database connection and table name
		private $conn;
		private $table_name = "estados";
	
		// object properties
		public $id;

		// constructor
		public function __construct($db) {
			$this->conn = $db;
		}

		public function getEstados() {
			$query = "SELECT * FROM " . $this->table_name . " ORDER BY descripcion";
			$stmt = $this->conn->prepare($query);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}
	}
?>