<?php
	use \Psr\Http\Message\ServerRequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;
	require_once("objects/turnos.php");
	require_once("utils/validate.php");

	//[GET]
	
	$app->get("/turnos/{tabla}", function(Request $request, Response $response, array $args) { //Tabla????
		$turnos = new Turnos($this->db, $args["tabla"]);
		return $response
			->withStatus(200)
			->withJson($turnos->getTurnos());
	});

	$app->get("/turnos/{idsetting}/{desde}/{hasta}", function(Request $request, Response $response, array $args) {
		$turnos = new Turnos($this->db);
		return $response
			->withStatus(200)
			->withJson($turnos->getTurnosList($args["idsetting"], $args["desde"], $args["hasta"]));
	});

	/* Obtener los turnos disponibles para un determinado usuario en un determinado setting de turno */
	$app->get("/turnos/usuario/{idusuario}/disponibles/{idsetting}", function(Request $request, Response $response, array $args) {
		$turnos = new Turnos($this->db);
		return $response
			->withStatus(200)
			->withJson($turnos->getTurnosDisponiblesParaUsuario($args["idusuario"], $args["idsetting"]));
	});

	$app->get("/turnos/{idsetting}/desde/{desde}/hasta/{hasta}/csv", function(Request $request, Response $response, array $args) {
		$turnos = new Turnos($this->db);
		$registros = $turnos->getTurnosByRangeDateCsV($args["idsetting"], $args["desde"], $args["hasta"]);
		$filename = "turnos_" . $args["desde"] . "_" . $args["hasta"] . ".csv";
		$fp = fopen("php://output", "w");
		fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		fputcsv($fp, ["Nro.", "Fecha", "Hora", "Lugar", "Apellido", "Nombre", "Dni", "Tel./Cel.", "EMail"], ";");
		foreach ($registros as $registro) {
			fputcsv($fp, $registro, ";");
		}
		fclose($fp);
		return $response
			->withStatus(200)
			->withHeader("Content-type", "application/csv")
			->withHeader("Content-Disposition", "filename=" . $filename);
	});


	//[POST]

	$app->post("/turno/usuario", function(Request $request, Response $response, array $args) {
		$res = new stdClass();
		$res->err = true;
		$res->msg = "Ocurrió un error al procesar la solicitud del turno.";
		$res->errors = [];
		$fields = $request->getParsedBody();
		$validate = new Validate($this->db);
		if(!$validate->check($fields, array(
			"idsetting" => array(
				"required" => true,
				"numeric" => true,
				"mayorcero" => true
			),
			"idusuario" => array(
				"required" => true,
				"numeric" => true,
				"mayorcero" => true
			),
			"fecha" => array(
				"required" => true,
				"min" => 10
			),
			"hora" => array(
				"required" => true,
				"min" => 8
			),
			"box" => array(
				"required" => true,
				"numeric" => true,
				"mayorcero" => true
			)
		))->passed()) {
			$res->errors = $validate->errors();
		} else {
			$turnos = new Turnos($this->db);
			$fields = (object) $request->getParsedBody();
			$creado = $turnos->setTurno($fields);
			if(!$creado->err) {
				$res->err = false;
				$res->msg = "El turno ha sido concedido.";
				$res->data = $creado;
				return $response
					->withStatus(201)
					->withJson($creado);
			} else {
				$res->msg = $creado->msg;
			}
		}
		return $response
			->withStatus(409)
			->withJson($res);
	});

	// [DELETE]

	$app->delete("/turno/{id}", function(Request $request, Response $response, array $args) {
		$turnos = new Turnos($this->db);
		if($turnos->eliminarTurno($args["id"])) {
			return $response->withStatus(201);
		}
		return $response->withStatus(409);
	});
?>