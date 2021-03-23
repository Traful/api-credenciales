<?php
	require_once("utils/prepare.php");
	// "cambios" object
	class Cambios {
	
		// database connection and table name
		private $conn;
		private $table_name = "cambios";
	
		// object properties
		public $id;
		public $fecha;
		public $hora;
		public $apellido;
		public $nombre;
		public $domicilio;
		public $telefono;
		public $email;
		public $idreparticion;
		public $nrofincaconvenio;
		public $observaciones;
		public $declaracion;
		public $dniFile;
		public $acreditacionFile;
		public $idtipocambio;
		public $idestado;

		// constructor
		public function __construct($db) {
			$this->conn = $db;
		}

		public function getCambios() {
			$query = "SELECT " . $this->table_name . ".*  FROM " . $this->table_name . " ORDER BY id";
			$stmt = $this->conn->prepare($query);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function getCambiosByReparticionAndTipo($idReparticion, $idTipo) {
			$query = "SELECT " . $this->table_name . ".*, estados.color  FROM " . $this->table_name;
			$query .= " INNER JOIN estados ON " . $this->table_name . ".idestado = estados.id";
			$query .= " WHERE ". $this->table_name . ".idreparticion = :idreparticion";
			$query .= " AND ". $this->table_name . ".idtipocambio = :idtipo";
			$query .= " ORDER BY " . $this->table_name . ".id DESC";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":idreparticion", $idReparticion);
			$stmt->bindParam(":idtipo", $idTipo);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function addCambio() {
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

		public function updateCambio($id, $idestado) {
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