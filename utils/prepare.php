<?php
	// "prepare" object
	class Prepare {

		// prepare class
		public static function sanitizeAndBind($clase, $stmt, $id = false) {
			$properties = get_object_vars($clase);
			foreach($properties as $key => $value) {
				if($key === "id" && $id === true) {
					// sanitize
					$clase->{$key} = htmlspecialchars(strip_tags($value));
					// bind
					$stmt->bindParam(":" . $key, $clase->{$key});
				} else if($key !== "id") {
					// sanitize
					$clase->{$key} = htmlspecialchars(strip_tags($value));
					// bind
					$stmt->bindParam(":" . $key, $clase->{$key});
				}
			}
		}

		// prepare ParsedBody
		public static function bindParsedBody($clase, $parsedBody, $id = false) {
			$parsedBody = (object) $parsedBody;
			$properties = get_object_vars($clase);
			foreach($properties as $key => $value) {
				if($key === "id" && $id === true) {
					// set propertie
					if(isset($parsedBody->{$key})) {
						$clase->{$key} = $parsedBody->{$key};
					}
				} else if($key !== "id") {
					// set propertie
					$clase->{$key} = $parsedBody->{$key};
				}
			}
		}

		// bindData
		public static function bindData($data, $fields, $id = false) {
			$properties = get_object_vars($data);
			foreach($properties as $key => $value) {
				if($key === "id" && $id === true) {
					if(isset($fields->{$key})) {
						$data->{$key} = $fields->{$key};
					}
				} else if($key !== "id") {
					if(isset($fields->{$key})) {
						$data->{$key} = $fields->{$key};
					}
				}
			}
		}

		//Generate smtpQuery Insert
		public static function smtpQueryInsert($table, $clase, $id = false) {
			//INSERT INTO `codigos` (`id`, `codigo`, `descripcion`, `recibo`, `fecha_v1`, `importe_v1`, `fecha_v2`, `importe_v2`) VALUES (NULL, '1233412', '4123441235', '22341234', '2020-03-29', '55.63', '2020-03-24', '30');
			$query = "INSERT INTO " . $table . " SET ";
			$properties = get_object_vars($clase);
			$i = 0;
			$total = count($properties) - 1;
			foreach($properties as $key => $value) {
				if($key === "id" && $id === true) {
					$query .= $key . " = :" . $key;
					if($i !== $total) {
						$query .= ", ";
					}
				} else if($key !== "id") {
					$query .= $key . " = :" . $key;
					if($i !== $total) {
						$query .= ", ";
					}
				}
				$i++;
			}
			return $query;
		}

		//Generate smtpQuery Update
		public static function smtpQueryUpdate($table, $clase, $id) {
			$query = "UPDATE " . $table . " SET ";
			$properties = get_object_vars($clase);
			$i = 0;
			$total = count($properties) - 1;
			foreach($properties as $key => $value) {
				if($key !== "id") {
					$query .= $key . " = :" . $key;
					if($i !== $total) {
						$query .= ", ";
					}
				}
				$i++;
			}
			$query .= " WHERE id = " . $id;
			return $query;
		}

		public static function bindDataToStmt($stmt, $data, $id = false) {
			$properties = get_object_vars($data);
			foreach($properties as $key => $value) {
				if($key === "id" && $id === true) {
					// sanitize
					$data->{$key} = htmlspecialchars(strip_tags($value));
					// bind
					$stmt->bindParam(":" . $key, $data->{$key});
				} else if($key !== "id") {
					// sanitize
					$data->{$key} = htmlspecialchars(strip_tags($value));
					// bind
					//echo(":" . $key . " - " . $data->{$key} . "\n");
					$stmt->bindParam(":" . $key, $data->{$key});
				}
			}
		}

		//Generate smtpQueryPlain
		public static function smtpQueryInsertPlain($table, $clase, $id = false) {
			//INSERT INTO `codigos` (`id`, `codigo`, `descripcion`, `recibo`, `fecha_v1`, `importe_v1`, `fecha_v2`, `importe_v2`) VALUES (NULL, '1233412', '4123441235', '22341234', '2020-03-29', '55.63', '2020-03-24', '30');
			$query = "INSERT INTO " . $table . " SET ";
			$properties = get_object_vars($clase);
			$i = 0;
			$total = count($properties) - 1;
			foreach($properties as $key => $value) {
				if($key === "id" && $id === true) {
					$query .= $key . " = '" . $value . "'";
					if($i !== $total) {
						$query .= ", ";
					}
				} else if($key !== "id") {
					$query .= $key . " = '" . $value . "'";
					if($i !== $total) {
						$query .= ", ";
					}
				}
				$i++;
			}
			return $query;
		}

		// Generar cadena aleatoria
		public static function generateRandomString($length = 10) {
			$characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
			$charactersLength = strlen($characters);
			$randomString = "";
			for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			return $randomString;
		}

		// UCfirst
		public static function UCfirst($texto, $encode = "UTF-8") {
			$texto = trim($texto);
			$resp = str_replace(",", "", $texto);
			$resp = mb_strtolower($resp, $encode);
			$resp = ucwords($resp);
			return $resp;
		}

		// Lower Case
		public static function Lower($texto, $encode = "UTF-8") {
			$texto = trim($texto);
			$resp = str_replace(",", "", $texto);
			$resp = mb_strtolower($resp, $encode);
			return $resp;
		}

		// Get Date
		public static function getDate() {
			return date("Y-m-d");
		}

		public static function dateAdd($fecha, $dias) {
			$nFecha = new DateTime($fecha);
			$nFecha->add(new DateInterval("P" . $dias . "D"));
			return $nFecha->format("Y-m-d");
		}

		// Get Time
		public static function getTime() {
			return date("H:i:s");
		}

		public static function ValidateDate($date) {
			$dataFecha = new stdClass();
			$dataFecha->valid = false;
			if(strpos($date, "-") !== false) { //Formato válido Inglés Y-m-d -- 2020-04-11
				$fecha = explode("-", $date);
				if(checkdate($fecha[1] , $fecha[2] , $fecha[0])) {
					$dataFecha->valid = true;
					$dataFecha->dia = $fecha[2];
					$dataFecha->mes = $fecha[1];
					$dataFecha->anio = $fecha[0];
					$dataFecha->fecha = $date;
					return $dataFecha;
				}
			} else if(strpos($date, "/") !== false) { //Formato válido Castellano d-m-Y -- 11/04/2020
				$fecha = explode("/", $date);
				if(checkdate($fecha[1] , $fecha[0] , $fecha[2])) {
					$dataFecha->valid = true;
					$dataFecha->dia = $fecha[0];
					$dataFecha->mes = $fecha[1];
					$dataFecha->anio = $fecha[2];
					$dataFecha->fecha = $date;
					return $dataFecha;
				}
			}
			return $dataFecha;
		}

		// Only Numbers
		public static function OnlyNumbers($mixed_input) {
			return filter_var($mixed_input, FILTER_SANITIZE_NUMBER_INT);
		}

		public static function prepareDate($date) {
			if(strpos($date, "-") !== false) { //Formato válido Inglés Y-m-d -- 2020-04-11
				$fecha = explode("-", $date);
				return $fecha[2] . "/" . $fecha[1] . "/" . $fecha[0];
			} else if(strpos($date, "/") !== false) { //Formato válido Castellano d-m-Y -- 11/04/2020
				$fecha = explode("/", $date);
				return $fecha[2] . "-" . $fecha[1] . "-" . $fecha[0];
			}
			return false;
		}

		public static function esFechaPar($fecha) { //Se espera en formato Y-m-d
			$nro = explode("-", $fecha);
			$nro = $nro[2];
			return (($nro % 2) === 0);
		}

		public static function esNroPar($nro) {
			return (($nro % 2) === 0);
		}

		public static function esFechaFinDeSemana($fecha) { //Se espera en formato Y-m-d
			$nro = explode("-", $fecha);
			$nro = $nro[2];
			return (($nro % 2) === 0);
		}

		public static function nombreDia($fecha) {
			$dias_semana = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"];
			$nro_dia = date("N", strtotime($fecha));
			return $dias_semana[$nro_dia - 1];
		}

		//Bytes
		public static function return_bytes($val) {
			preg_match('/(?<value>\d+)(?<option>.?)/i', trim($val), $matches);
			$inc = array(
				'g' => 1073741824, // (1024 * 1024 * 1024)
				'm' => 1048576, // (1024 * 1024)
				'k' => 1024
			);

			$value = (int) $matches['value'];
			$key = strtolower(trim($matches['option']));
			if (isset($inc[$key])) {
				$value *= $inc[$key];
			}
			return $value;
		}

		public static function FormatNumber($valor, $decimales = 2) {
			//1083.20
			if(!is_numeric($valor)) {
				return false;
			} else {
				return number_format(floatval($valor), $decimales, "." , "");
			}
		}

		public static function esBisiesto($year = NULL) {
			$year = ($year == NULL) ? date("Y") : $year;
			return ( ($year % 4 == 0 && $year % 100 != 0) || $year % 400 == 0 );
		}
	}
?>