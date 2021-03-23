<?php
	// "reclamos" object
	class Reclamos {
	
		// database connection and table name
		private $conn;
		private $table_name = "reclamos";
	
		// object properties
		public $id;
		public $fecha;
		public $hora;
		public $apellido;
		public $nombre;
		public $dni;
		public $telefono;
		public $email;
		public $idreparticion;
		public $nrofincaconvenio;
		public $idadhiere;
		public $observaciones;
		public $idestado;

		// constructor
		public function __construct($db) {
			$this->conn = $db;
		}

		public function getReclamos() {
			$query = "SELECT * FROM " . $this->table_name;
			$stmt = $this->conn->prepare($query);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function getReclamosByReparticion($idreparticion) {
			$query = "SELECT reclamos.*, adhesion.descripcion AS adhesion, estados.descripcion AS estado, estados.color FROM reclamos";
			$query .= " INNER JOIN adhesion ON reclamos.idadhiere = adhesion.id";
			$query .= " INNER JOIN estados ON reclamos.idestado = estados.id";
			$query .= " WHERE reclamos.idreparticion = :idreparticion";
			$query .= " ORDER BY reclamos.fecha, reclamos.hora DESC";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":idreparticion", $idreparticion);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		/*
		public function getReclamosByReparticion($idReparticion, $activo = false) {
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
		*/

		public function setReclamo($data) {
			Prepare::bindParsedBody($this, $data);
			$this->apellido = Prepare::UCfirst($this->apellido);
			$this->nombre = Prepare::UCfirst($this->nombre);
			$query = Prepare::smtpQueryInsert($this->table_name, $this);
			$stmt = $this->conn->prepare($query);
			Prepare::sanitizeAndBind($this, $stmt);
			if($stmt->execute()) {
				return true;
			}
			return false;
		}

		public function updateReclamo($id, $idestado) {
			$query = "UPDATE " . $this->table_name . " SET idestado = :idestado WHERE id = :id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":idestado", $idestado);
			$stmt->bindParam(":id", $id);
			if($stmt->execute()) {
				return true;
			}
			return false;
		}
	}
?>