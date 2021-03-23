<?php
	require_once("utils/prepare.php");
	// "debitos" object
	class Debitos {
	
		// database connection and table name
		private $conn;
		private $table_name = "debitos";
	
		// object properties
		public $id;
		public $fecha;
		public $hora;
		public $apellido;
		public $nombre;
		public $telefono;
		public $email;
		public $idreparticion;
		public $nrofincaconvenio;
		public $idadhiere;
		public $idtipobanco;
		public $idtipocuenta;
		public $declaracion;
		public $dniFile;
		public $cbuFile;
		public $idestado;

		// constructor
		public function __construct($db) {
			$this->conn = $db;
		}

		public function getDebitos() {
			//$query = "SELECT " . $this->table_name . ".*,  FROM " . $this->table_name . " ORDER BY id";
			$query = "SELECT debitos.*, adhesion.descripcion AS descripcionadhesion, bancos.nombre AS nombrebanco, tipocuenta.descripcion AS descripciontipocuenta, estados.descripcion AS descripcionestado FROM debitos";
			$query .= " INNER JOIN adhesion ON debitos.idadhiere = adhesion.id";
			$query .= " INNER JOIN bancos ON debitos.idtipobanco = bancos.id";
			$query .= " INNER JOIN tipocuenta ON debitos.idtipocuenta = tipocuenta.id";
			$query .= " INNER JOIN estados ON debitos.idestado = estados.id";
			$query .= " ORDER BY debitos.id DESC";
			$stmt = $this->conn->prepare($query);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function getDebitosByReparticion($idReparticion) {
			$query = "SELECT debitos.*, adhesion.descripcion AS descripcionadhesion, bancos.nombre AS nombrebanco, tipocuenta.descripcion AS descripciontipocuenta, estados.descripcion AS descripcionestado, estados.color FROM debitos";
			$query .= " INNER JOIN adhesion ON debitos.idadhiere = adhesion.id";
			$query .= " INNER JOIN bancos ON debitos.idtipobanco = bancos.id";
			$query .= " INNER JOIN tipocuenta ON debitos.idtipocuenta = tipocuenta.id";
			$query .= " INNER JOIN estados ON debitos.idestado = estados.id";
			$query .= " WHERE debitos.idreparticion = :idreparticion";
			$query .= " ORDER BY debitos.id DESC";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":idreparticion", $idReparticion);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function addDebito() {
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

		public function updateDebito($id, $idestado) {
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