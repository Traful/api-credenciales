<?php
	// "infos" object
	class Infos {
	
		// database connection and table name
		private $conn;
		private $table_name = "info";
	
		// object properties
		public $id;
		public $iditeminfo;
		public $titulo;
		public $data;
		public $activo;

		// constructor
		public function __construct($db) {
			$this->conn = $db;
		}

		public function getInfos() {
			$query = "SELECT * FROM " . $this->table_name;
			$stmt = $this->conn->prepare($query);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function getInfosByReparticion($idReparticion, $activo = false) {
			$query = "SELECT info.id, info.titulo, items_info.descripcion, items_info.icono, items_info.color FROM info";
			$query .= " INNER JOIN items_info ON info.iditeminfo = items_info.id";
			$query .= " WHERE items_info.idreparticion = :idreparticion";
			if($activo) {
				$query .= " AND info.activo = 1";
			}
			$query .= " ORDER BY items_info.id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":idreparticion", $idReparticion);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function getInfosItem($idreparticion, $activo = false) {
			$query = "SELECT * FROM items_info WHERE idreparticion = :idreparticion";
			if($activo) {
				$query .= " AND activo = 1";
			}
			$query .= " ORDER BY id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":idreparticion", $idreparticion);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function getInfosByItem($idItem, $activos = false) {
			$query = "SELECT * FROM " . $this->table_name;
			$query .= " WHERE iditeminfo = :iditeminfo";
			if($activos) {
				$query .= " AND activo = 1";
			}
			$query .= " ORDER BY id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":iditeminfo", $idItem);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return null;
		}

		public function getInfoById($id) {
			$query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":id", $id);
			if($stmt->execute()) {
				return $stmt->fetch(PDO::FETCH_OBJ);
			}
			return null;
		}

		public function setInfo($data) {
			$query = "INSERT INTO " . $this->table_name . " SET iditeminfo = :iditeminfo, titulo = :titulo, data = :data, activo = :activo";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":iditeminfo", $data->iditeminfo);
			$stmt->bindParam(":titulo", $data->titulo);
			$json = json_encode($data->data);
			$stmt->bindParam(":data", $json);
			$stmt->bindParam(":activo", $data->activo);
			if($stmt->execute()) {
				return true;
			}
			return false;
		}

		public function updateInfo($data) {
			$query = "UPDATE " . $this->table_name . " SET iditeminfo = :iditeminfo, titulo = :titulo, data = :data, activo = :activo WHERE id = :id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":iditeminfo", $data->iditeminfo);
			$stmt->bindParam(":titulo", $data->titulo);
			$json = json_encode($data->data);
			$stmt->bindParam(":data", $json);
			$stmt->bindParam(":activo", $data->activo);
			$stmt->bindParam(":id", $data->id);
			if($stmt->execute()) {
				return true;
			}
			return false;
		}
	}
?>