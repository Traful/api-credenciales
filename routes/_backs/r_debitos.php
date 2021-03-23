<?php
	use \Psr\Http\Message\ServerRequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;
	//use PHPMailer\PHPMailer\PHPMailer;
	//use PHPMailer\PHPMailer\SMTP;
	//use PHPMailer\PHPMailer\Exception;
	require_once("objects/debitos.php");
	require_once("utils/validate.php");
	require_once("utils/prepare.php");

	//[GET]

	$app->get("/debitos", function(Request $request, Response $response, array $args) {
		$debitos = new Debitos($this->db);
		return $response
			->withStatus(200)
			->withJson($debitos->getDebitos());
	});

	$app->get("/debitos/reparticion/{id}", function(Request $request, Response $response, array $args) {
		$debitos = new Debitos($this->db);
		return $response
			->withStatus(200)
			->withJson($debitos->getDebitosByReparticion($args["id"]));
	});

	//[POST]

	$app->post("/debito", function(Request $request, Response $response, array $args) {
		$res = new stdClass();
		$res->err = true;
		$res->msg = "Ocurrió un error al ingresar la solicitud.";
		$res->mail_send = false;
		$res->errors = [];
		$fields = $request->getParsedBody();
		$validate = new Validate($this->db);
		if(!$validate->check($fields, array(
			"apellido" => array(
				"required" => true,
				"min" => 3,
				"max" => 25
			),
			"nombre" => array(
				"required" => true,
				"min" => 3,
				"max" => 25
			),
			"telefono" => array(
				"required" => true,
				"min" => 6,
				"max" => 20
			),
			"email" => array(
				"required" => true,
				"max" => 150
			),
			"idreparticion" => array(
				"required" => true,
				"numeric" => true,
				"mayorcero" => true
			),
			"nrofincaconvenio" => array(
				"required" => true,
				"max" => 25
			),
			"idadhiere" => array(
				"required" => true,
				"numeric" => true,
				"mayorcero" => true
			),
			"idtipobanco" => array(
				"required" => true,
				"numeric" => true,
				"mayorcero" => true
			),
			"idtipocuenta" => array(
				"required" => true,
				"numeric" => true,
				"mayorcero" => true
			),
			"declaracion" => array(
				"required" => true
			)
		))->passed()) {
			$res->errors = $validate->errors();
		} else {
			$errorFiles = true;
			$dni = "";
			$cbu = "";
			$uploadedFiles = $request->getUploadedFiles();
			//Archivo DNI
			if(isset($uploadedFiles["dniFile"])) {
				$uploadedFile = $uploadedFiles["dniFile"];
				if($uploadedFile->getError() === UPLOAD_ERR_OK) {
					$filename = moveUploadedFile($this->upload_directory_debito, $uploadedFile);
					$dni = $filename;
				} else {
					$res->errors[] = "Error al cargar el archivo DNI.";
				}
			} else {
				$res->errors[] = "No se especificó el archivo DNI.";
			}
			//Archivo CBU
			if(isset($uploadedFiles["cbuFile"])) {
				$uploadedFile = $uploadedFiles["cbuFile"];
				if($uploadedFile->getError() === UPLOAD_ERR_OK) {
					$filename = moveUploadedFile($this->upload_directory_debito, $uploadedFile);
					$cbu = $filename;
				} else {
					$res->errors[] = "Error al cargar el archivo CBU.";
				}
			} else {
				$res->errors[] = "No se especificó el archivo CBU.";
			}
			if((strlen($dni) > 0) && (strlen($cbu) > 0)) {
				$fields["fecha"] = Prepare::getDate();
				$fields["hora"] = Prepare::getTime();
				$fields["dniFile"] = $dni;
				$fields["cbuFile"] = $cbu;
				$fields["idestado"] = 1; //Pendiente
				$debitos = new Debitos($this->db);
				Prepare::bindParsedBody($debitos, $fields);
				if($debitos->addDebito()) {
					/*
					$mail = new PHPMailer(true);
					try {
						//Server settings
						$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
						$mail->isSMTP();                                            // Send using SMTP
						$mail->Host       = "smtp.villamercedes.gob.ar";            // Set the SMTP server to send through
						$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
						$mail->Username   = "planesdepago@villamercedes.gob.ar";    // SMTP username
						$mail->Password   = "Planesdepago123";                      // SMTP password
						$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
						$mail->Port       = 465;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
						//Recipients
						$mail->setFrom("planesdepago@villamercedes.gob.ar", "Municipalidad de Villa Mercedes");
						$mail->addAddress($fields["email"], $fields["apellido"] . ", " . $fields["nombre"]);     // Add a recipient
						//$mail->addAddress("ellen@example.com");               // Name is optional
						$mail->addReplyTo("planesdepago@villamercedes.gob.ar", "Municipalidad de Villa Mercedes");
						//$mail->addCC("cc@example.com");
						//$mail->addBCC("bcc@example.com");
						// Attachments
						$mail->addAttachment("/uploads/debitos/" . $dni);         // Add attachments
						$mail->addAttachment("/uploads/debitos/" . $cbu);         // Add attachments
						//$mail->addAttachment("/tmp/image.jpg", "new.jpg");    // Optional name
						// Content
						$mail->isHTML(true);                                  // Set email format to HTML
						$mail->Subject = "Adhesión Débito Automático";
						$Body = "<body>";
						$Body .= "<div>Hola <strong>" . Prepare::UCfirst($fields["nombre"]) . "</strong> gracias por comunicarte con la Municipalidad de Villa Mercedes</div>";
						$Body .= "<div>Te escribimos para comunicarte que hemos recepcionado tu solicitud al débito automático.</div>";
						$Body .= "<div>A continuación te detallamos la infromación que nos brindaste.</div>";
						$Body .= "<ul>";
						$Body .= "<li>Fecha: " . Prepare::prepareDate($fields["fecha"]) . " - Hora: " . $fields["hora"] . "</li>";
						$Body .= "<li>Apellido y Nombre: " . Prepare::UCfirst($fields["apellido"]) . ", " . Prepare::UCfirst($fields["nombre"]) . "</li>";
						$Body .= "<li>Teléfono: " . $fields["telefono"] . "</li>";
						$Body .= "<li>Email: " . $fields["email"] . "</li>";
						$Body .= "</ul>";
						$adhiere = "";
						switch($fields["idadhiere"]) {
							case "1":
								$adhiere = "Cuota Plan de Pagos";
								break;
							case "2":
								$adhiere = "Tasa de Servicios Generales";
								break;
							case "3":
								$adhiere = "Tasa Comercial";
								break;
							default:
								$adhiere = "Error!";
								break;
						}
						$tipo = "";
						switch($fields["idtipocuenta"]) {
							case "1":
								$banco = "Cuenta Corriente";
								break;
							case "2":
								$banco = "Caja de ahorros";
								break;
							default:
								$banco = "Error!";
								break;
						}
						$banco = "";
						switch($fields["idtipobanco"]) {
							case "1":
								$banco = "Supervielle";
								break;
							case "2":
								$banco = "Nación";
								break;
							case "3":
								$banco = "HSBC";
								break;
							case "4":
								$banco = "Galicia";
								break;
							case "5":
								$banco = "Río";
								break;
							case "6":
								$banco = "Otro";
								break;
							default:
								$banco = "Error!";
								break;
						}
						$Body .= "<p>Solicitaste adherir al débito automático tu <strong>" . $adhiere . "</strong>, por medio de tu <strong>" . $tipo . "</strong> en el banco <strong>" . $banco . "</strong>.</p>";
						$Body .= "<p>En este momento tu solicitud está siendo procesada, te vamos contactar para continuar con el trámite en caso de ser necesario.</p>";
						$Body .= "<br/>";
						$Body .= "<p>Si vos no nos solicitastes esta adhesión por favor comunicate a la Oficina Virtual de Ingresos Municipales - 422112 (Int. 136 ó 157).</p>";
						$Body .= "<br/>";
						$Body .= "<p>Muchas gracias, saludos coordiales.</p>";
						$Body .= "<br/>";
						$Body .= "<p>Municipalidad de Villa Mercedes.</p>";
						$Body .= "</body>";
						$mail->Body    = $Body;
						$mail->AltBody = "Lo sentimos mucho, tu cliente de correo no tiene soporte para mensajes de tipo html :(";
						$mail->send();
						//echo "Message has been sent";
						$res->mail_send = true;
					} catch (Exception $e) {
						//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
					}
					*/
					$res->err = false;
					$res->msg = "La solicitud de adhesión al débito automático fué generada sactifactoriamente.";
					return $response
						->withStatus(201)
						->withJson($res);
				}
			}
		}
		return $response
			->withStatus(409)
			->withJson($res);
	});

	//[PATCH]

	$app->patch("/debito/{id}", function(Request $request, Response $response, array $args) {
		$res = new stdClass();
		$res->err = true;
		$res->msg = "Ocurrió un error al procesar la solicitud.";
		$res->errors = [];
		$fields = $request->getParsedBody();
		$validate = new Validate($this->db);
		if(!$validate->check($fields, array(
			"idestado" => array(
				"required" => true,
				"numeric" => true,
				"mayorcero" => true
			)
		))->passed()) {
			$res->errors = $validate->errors();
		} else {
			$debitos = new Debitos($this->db);
			$fields = (object) $request->getParsedBody();
			if($debitos->updateDebito($args["id"], $fields->idestado)) {
				$res->err = false;
				$res->msg = "El estado del debito ha sido actualizado.";
				return $response
					->withStatus(201)
					->withJson($res);
			} else {
				$res->msg = "Ocurrió un error al actualizar el estado del debito.";
			}
		}
		return $response
			->withStatus(409)
			->withJson($res);
	});
?>