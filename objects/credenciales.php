<?php
	require_once("utils/prepare.php");
	// "Credenciales" object
	class Credenciales {
	
		// database connection and table name
		private $conn;
		private $table_name = "credenciales";
	
		// object properties
		public $id;
		public $idtipo;
		public $apellido;
		public $nombre;
		public $domicilio;
		public $dni;
		public $telefono;
		public $email;
		public $institucion;
		public $emision;
		public $vencimiento;
		public $foto;

		// constructor
		public function __construct($db) {
			$this->conn = $db;
		}

		public function getCredenciales() {
			$query = "SELECT * FROM " . $this->table_name . " ORDER BY emision";
			$stmt = $this->conn->prepare($query);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function getCredencialById($id) {
			$query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":id", $id);
			if($stmt->execute()) {
				return $stmt->fetch(PDO::FETCH_OBJ);
			}
			return null;
		}

		public function getCredencialByDni($nro) {
			$query = "SELECT * FROM " . $this->table_name . " WHERE dni = :dni";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":dni", $nro);
			if($stmt->execute()) {
				return $stmt->fetch(PDO::FETCH_OBJ);
			}
			return null;
		}

		//Agrega o actualiza un registro
		public function procesarDatos($fields) {
			$foto = $fields["foto"];

			$fields["apellido"] = Prepare::UCfirst($fields["apellido"]);
			$fields["nombre"] = Prepare::UCfirst($fields["nombre"]);
			Prepare::bindParsedBody($this, $fields);
			$query = "";
			if($fields["id"] == "0") { //nuevo registro
				$query = Prepare::smtpQueryInsert($this->table_name, $this);
			} else { //editar registro
				$query = Prepare::smtpQueryUpdate($this->table_name, $this, $fields["id"]);
			}
			$stmt = $this->conn->prepare($query);
			
			Prepare::sanitizeAndBind($this, $stmt);
			
			$stmt->bindParam(":foto", $foto); //Arreglo

			if($stmt->execute()) {
				if($fields["id"] == "0") {
					$lastId = $this->conn->lastInsertId();
					if($lastId) {
						return $this->getCredencialById($lastId);
					} else {
						return false;
					}
				} else {
					return $this->getCredencialById($fields["id"]);
				}
				return $stmt->fetch(PDO::FETCH_OBJ);
			}
			return false;
		}
	}
?>