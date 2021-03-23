<?php
	// "noticias" object
	class Noticias {
	
		// database connection and table name
		private $conn;
		private $table_name = "noticias";
	
		// object properties
		public $id;

		// constructor
		public function __construct($db) {
			$this->conn = $db;
		}

		public function getNoticiasByReparticion($id) {
			$query = "SELECT * FROM " . $this->table_name . " WHERE idreparticion = :idreparticion ORDER BY id DESC";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":idreparticion", $id);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}
	}
?>