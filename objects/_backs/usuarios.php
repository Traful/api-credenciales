<?php
	// "usuarios" object
	class Usuarios {
	
		// database connection and table name
		private $conn;
		private $table_name = "usuarios";
	
		// object properties
		public $id;
		public $dni;
		public $espar;
		public $apellido;
		public $nombre;
		public $telefono;
		public $email;

		// constructor
		public function __construct($db) {
			$this->conn = $db;
		}

		public function getUsuarios() {
			$query = "SELECT * FROM " . $this->table_name;
			$query .= " ORDER BY id";
			$stmt = $this->conn->prepare($query);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function getUsuariosCsv() {
			$query = "SELECT * FROM " . $this->table_name;
			$query .= " ORDER BY id";
			$stmt = $this->conn->prepare($query);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
			return [];
		}

		public function getUsuarioById($id) {
			$query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":id", $id);
			if($stmt->execute()) {
				return $stmt->fetch(PDO::FETCH_OBJ);
			}
			return null;
		}

		public function getUsuarioByDni($dni) {
			$query = "SELECT * FROM " . $this->table_name . " WHERE dni = :dni";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":dni", $dni);
			if($stmt->execute()) {
				return $stmt->fetch(PDO::FETCH_OBJ);
			}
			return null;
		}

		// Historial
		public function getUsuarioHistorial($idusuario) {
			$query = "SELECT turnos.id, turnos.fecha, turnos.dia, turnos.hora, turnos.box, settings.descripcion, settings.icono, settings.color FROM turnos";
			$query .= " INNER JOIN settings ON turnos.idsetting = settings.id";
			$query .= " WHERE turnos.idusuario = :idusuario AND turnos.fecha < :fecha";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":idusuario", $idusuario);
			$f = Prepare::getDate();
			$stmt->bindParam(":fecha", $f);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return null;
		}

		public function setUsuario($data) {
			$res = new stdClass();
			$res->err = true;
			$res->msg = "Ocurrió un error al dar de alta el Usuario.";
			$res->lastId = 0;
			//Preset
			$data->apellido = UCfirst($data->apellido);
			$data->nombre = UCfirst($data->nombre);
			Prepare::bindParsedBody($this, $data);
			$query = Prepare::smtpQueryInsert($this->table_name, $this);
			$stmt = $this->conn->prepare($query);
			Prepare::sanitizeAndBind($this, $stmt);
			if($stmt->execute()) {
				$res->err = false;
				$res->msg = "El Usuario fué dado de alta.";
				$res->lastId = $this->conn->lastInsertId();
			}
			return $res;
		}
	}
?>