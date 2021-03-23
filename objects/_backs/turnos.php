<?php
	// "turnos" object
	require_once("objects/usuarios.php");
	require_once("utils/prepare.php");
	use DeepCopy\DeepCopy;

	class Turnos {
	
		// database connection and table name
		private $conn;
		private $table_name = "turnos";
	
		// object properties
		public $id;
		public $idsetting;
		public $idusuario;
		public $dia;
		public $fecha;
		public $hora;
		public $box;

		// constructor
		public function __construct($db) {
			$this->conn = $db;
		}

		public function getTurnosList($idsetting, $desde, $hasta) {
			$query = "SELECT turnos.id, turnos.fecha, turnos.dia, turnos.fecha, turnos.hora, turnos.box, usuarios.dni, usuarios.apellido, usuarios.nombre, usuarios.telefono, usuarios.email FROM turnos";
			$query .= " INNER JOIN usuarios ON turnos.idusuario = usuarios.id";
			$query .= " WHERE turnos.idsetting = " . $idsetting . " AND turnos.fecha BETWEEN '" . $desde . "' AND '" . $hasta . "'";
			$query .= " ORDER BY turnos.fecha, turnos.hora, turnos.box";
			$stmt = $this->conn->prepare($query);
			if($stmt->execute()) {
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			return [];
		}

		public function getTurnosDisponiblesParaUsuario($idusuario, $idsetting, $solo_fechas = false, $limite_dias = 10) {
			$resp = new stdClass();
			$resp->err = true;
			$resp->msg = "Error!";
			$resp->existeusuario = false;
			$resp->tieneturno = false;
			$resp->detalleturno = null;
			$resp->data = null;
			$usuarios = new Usuarios($this->conn);
			$usuario = $usuarios->getUsuarioById($idusuario);
			if(!is_null($usuario)) {
				$resp->existeusuario = true;
				//Determinar si ya tiene un turno a posterior
				$query = "SELECT turnos.id, turnos.fecha, turnos.dia, turnos.hora, turnos.box, settings.descripcion, settings.icono, settings.color FROM turnos";
				$query .= " INNER JOIN settings ON turnos.idsetting = settings.id";
				$query .= " WHERE turnos.idsetting = :idsetting AND turnos.idusuario = :idusuario AND turnos.fecha >= :fecha";
				//$query = "SELECT id, fecha, hora, box FROM turnos WHERE idsetting = :idsetting AND idusuario = :idusuario AND fecha >= :fecha";
				$stmt = $this->conn->prepare($query);
				$stmt->bindParam(":idsetting", $idsetting);
				$stmt->bindParam(":idusuario", $idusuario);
				$f = Prepare::getDate();
				$stmt->bindParam(":fecha", $f);
				if($stmt->execute()) {
					if($stmt->rowCount() > 0) {
						$resp->err = false;
						$resp->msg = "El usuario ya tiene un turno reservado.";
						$resp->tieneturno = true;
						$resp->detalleturno = $stmt->fetch(PDO::FETCH_OBJ);
					} else {
						$resp->existeusuario = true;
						//Recrear el setting y buscar los próximos 10 días en los que el usuario pueda sacar un turno manteniendo las reglas
						$fechaActual = Prepare::getDate();
						$fechasValidas = [];
						$usuario_espar = $usuario->espar === "1" ? true : false; //Esto está al pedo si no se toma pares e impares
						//Se recrea un buffer de los turnos basado en el setting
						$query = "SELECT boxs, intervalo, desde_hora, hasta_hora FROM settings WHERE id = :id";
						$stmt_settings = $this->conn->prepare($query);
						$stmt_settings->bindParam(":id", $idsetting);
						if($stmt_settings->execute() && ($stmt_settings->rowCount() > 0)) {
							$setting = $stmt_settings->fetch(PDO::FETCH_OBJ);
							$desdeh = strtotime($setting->desde_hora);
							$hastah = strtotime($setting->hasta_hora);
							$intervalo = intval($setting->intervalo);
							// El intervalo no puede ser inferior a 1 minuto!
							if($intervalo < 1) {
								$intervalo = 1;
							}
							// Verificar que la cantidad de minutos no sobrepase 1 día!
							if($intervalo > 1440) {
								$intervalo = 1440;
							}
							$intervalo = $intervalo * 60; //Intervalo en minutos pasado a segundos
							$boxs = intval($setting->boxs);
							if($boxs < 1) {
								$boxs = 1;
							}
							//Se genera la data inicial de cada día, a la cual luego se le verificará su disponiblidad dependiendo de la fecha 
							$dataInit = new stdClass();
							$dataInit->fecha = null;
							$dataInit->nombre_dia = "";
							$dataInit->horarios = [];
							$dt = new DateTime();
							$turnos_por_dia = 0;
							for($h = $desdeh; $h < $hastah; $h = ($h + $intervalo)) {
								/*
									Si bien el último turno puede comenzar antes de la hora máxima el intervalo no debe superar la hora máxima
									en el caso de que se pase no se toma en cuenta.
								*/
								if(($h + $intervalo) < $hastah) {
									$turnos_por_dia++;
									for($b = 1; $b <= $boxs; $b++) {
										$horario = new stdClass();
										$dt->setTimestamp($h);
										$hora_full = $dt->format("H:i:s");
										$horario->hora = $hora_full;
										$horario->box = $b;
										$horario->disponible = true;
										$dataInit->horarios[] = $horario;
									}
								}
							}
							$turnos_por_dia = ($turnos_por_dia + 1) * $boxs;
							$error_en_setting = false;
							$buffer_data = [];
							$i = 0;
							$copier = new DeepCopy();
							while($i < $limite_dias) {
								$fechaActual = Prepare::dateAdd($fechaActual, 1); //Siempre es a partir del día siguiente a la fecha actual
								$fechaValida = true;
								//$fechaValida = false; Se inicia en false si se tiene en cuenta la verificacion de par e impar
								//Se verifica que la fecha y el usuario sean coincidentes en par o impar
								/*
								if(Prepare::esFechaPar($fechaActual) === $usuario_espar) {
									$fechaValida = true;
								}
								*/
								if($fechaValida) {
									//Se verifica que la fecha no sea un día feriado
									$query = "SELECT id FROM feriados WHERE fecha = :fecha";
									$stmt_feriados = $this->conn->prepare($query);
									$stmt_feriados->bindParam(":fecha", $fechaActual);
									if(!$stmt_feriados->execute() || ($stmt_feriados->rowCount() > 0)) {
										$fechaValida = false;
									}
								}
								if($fechaValida) {
									//Se verifica que no sea un Sábado o un Domingo
									$diaDeLaSemana = date("N", strtotime($fechaActual));
									if($diaDeLaSemana > 5) { //Solo de Lunes a Viernes
										$fechaValida = false;
									}
								}
								if($fechaValida) {
									//Verificar que exista la menos 1 turno disponible en esa fecha
									/*
										Primero se obtiene cualquier registro existente en esa fecha en ese setting
										ya que si no hay ningún registro significa que todos los horarios del día
										están disponibles.
									*/
									$query = "SELECT hora, box, idusuario FROM turnos WHERE idsetting = :idsetting AND fecha = :fecha";
									$stmt_turnos = $this->conn->prepare($query);
									$stmt_turnos->bindParam(":idsetting", $idsetting);
									$stmt_turnos->bindParam(":fecha", $fechaActual);
									if($stmt_turnos->execute()) {
										
										$ok = $copier->copy($dataInit);
										
										/*
										$total_registros = $stmt_turnos->rowCount();
										echo("Total de registros: " . $total_registros . "\n");
										echo("Turnos por día: " . $turnos_por_dia . "\n");
										echo((($stmt_turnos->rowCount() > 0) && ($stmt_turnos->rowCount() !== $turnos_por_dia)));
										die(false);
										*/

										if($stmt_turnos->rowCount() > 0) {
											$hay_al_menos_uno_disponible = false;
											if($stmt_turnos->rowCount() !== $turnos_por_dia) { //Se verifica que no esten todos los turnos posibles dados
												/* 
													En caso de que si existan turnos asignados verificar si hay al menos 1 disponible
													Para ello se sigue el siguiente criterio:
													se recorre el buffer inicial de datos basado en el setting y se compara con los resultados de la consulta ($turnos_existentes)
												*/
												$turnos_existentes = $stmt_turnos->fetchAll(PDO::FETCH_OBJ);
												
												foreach($ok->horarios as $horario) {
													$query = "SELECT id FROM turnos WHERE idsetting = :idsetting AND fecha = :fecha AND hora = :hora AND box = :box";
													$stmt_verifica = $this->conn->prepare($query);
													$stmt_verifica->bindParam(":idsetting", $idsetting);
													$stmt_verifica->bindParam(":fecha", $fechaActual);
													$stmt_verifica->bindParam(":hora", $horario->hora);
													$stmt_verifica->bindParam(":box", $horario->box);
													if($stmt_verifica->execute()) {
														if($stmt_verifica->rowCount() > 0) {
															$horario->disponible = false;
														} else {
															$hay_al_menos_uno_disponible = true;
														}
													}
													/*
													foreach($turnos_existentes as $existente) {
														if(($existente->box == $horario->box) && ($existente->hora == $horario->hora)) {
															$horario->disponible = false;
														} else {
															$hay_al_menos_uno_disponible = true;
														}
													}
													*/
												}
											}
											//if($hay_al_menos_uno_disponible === true) {
											if($hay_al_menos_uno_disponible) {												
												$ok->fecha = $fechaActual;
												$ok->nombre_dia = Prepare::nombreDia($fechaActual);
												if($solo_fechas) {
													unset($ok->horarios);
												}
												$buffer_data[] = $ok;
												$i++;
											}
										} else {
											//Si no hay registros significa que todos los turnos están disponibles
											$ok->fecha = $fechaActual;
											$ok->nombre_dia = Prepare::nombreDia($fechaActual);
											if($solo_fechas) {
												unset($ok->horarios);
											}
											$buffer_data[] = $ok;
											$i++;
										}
									} else {
										$resp->msg = "Ocurrió un error al obtener la disponibilidad de los turnos.";
										$error_en_setting = true;
										break;
									}
								}
							}
							if(!$error_en_setting) {
								//Si todo funcionó correctamenete data debería reflejar 10 días en los que al menos hay 1 turno disponible en cada uno de ellos.
								$resp->err = false;
								$resp->msg = "";
								$resp->data = $buffer_data;
							}
						} else {
							$resp->msg = "Ocurrió un error al obtener la configuración de los turnos.";
						}
					}
				} else {
					$resp->msg = "Ocurrió un error al intentar averiguar si el usuario ya posee un turno.";
				}
			} else {
				$resp->msg = "Error, no se pudo reconocer al usuario.";
			}
			return $resp;
		}

		public function getTurnosByRangeDateCsV($idsetting, $desde, $hasta) {
			$registros = $this->getTurnosList($idsetting, $desde, $hasta);
			$buffer = [];
			foreach($registros as $registro) {
				$buffer[] = array("id" => $registro->id, "fecha" => $registro->fecha, "hora" => $registro->hora, "lugar" => $registro->box, "apellido" => $registro->apellido, "nombre" => $registro->nombre, "dni" => $registro->dni, "telefono" => $registro->telefono, "email" => $registro->email);
			}
			return $buffer;
		}

		public function setTurno($data) {
			$resp = new stdClass();
			$resp->err = true;
			$resp->msg = "Error!";
			$resp->force_refresh = false;
			$resp->lastId = 0;
			//Se verifica que el turno no haya sido tomado
			$query = "SELECT id FROM " . $this->table_name . " WHERE idsetting = :idsetting AND fecha = :fecha AND hora = :hora AND box = :box";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":idsetting", $data->idsetting);
			$stmt->bindParam(":fecha", $data->fecha);
			$stmt->bindParam(":hora", $data->hora);
			$stmt->bindParam(":box", $data->box);
			if($stmt->execute()) {
				if($stmt->rowCount() > 0) {
					$resp->msg = "El turno ya ha sido asignado a otro usuario, por favor reintente.";
					$resp->force_refresh = true;
				} else {
					//Se grega el turno a la tabla
					$query = Prepare::smtpQueryInsert($this->table_name, $this);
					$data->dia = Prepare::nombreDia($data->fecha);
					$stmt = $this->conn->prepare($query);
					Prepare::bindDataToStmt($stmt, $data);
					if($stmt->execute()) {
						$resp->err = false;
						$resp->msg = "El turno se agregó correctamente.";
						$resp->lastId = $this->conn->lastInsertId();
					}
				}
			}
			return $resp;
		}

		public function eliminarTurno($id) {
			$query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(":id", $id);
			if($stmt->execute()) {
				return true;
			}
			return false;
		}
	}
?>