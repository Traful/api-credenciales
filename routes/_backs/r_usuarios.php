<?php
	use \Psr\Http\Message\ServerRequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;
	//use \Firebase\JWT\JWT;
	require_once("objects/usuarios.php");
	require_once("utils/prepare.php");
	require_once("utils/validate.php");

	//[GET]
	
	$app->get("/usuarios", function(Request $request, Response $response, array $args) {
		$usuarios = new Usuarios($this->db);
		return $response
			->withStatus(200)
			->withJson($usuarios->getUsuarios());
	});

	$app->get("/usuario/id/{id}", function(Request $request, Response $response, array $args) {
		$usuarios = new Usuarios($this->db);
		return $response
			->withStatus(200)
			->withJson($usuarios->getUsuarioById($args["id"]));
	});

	$app->get("/usuario/dni/{dni}", function(Request $request, Response $response, array $args) {
		$usuarios = new Usuarios($this->db);
		return $response
			->withStatus(200)
			->withJson($usuarios->getUsuarioByDni($args["dni"]));
	});

	// Historial del Usuario
	$app->get("/usuario/{id}/historial", function(Request $request, Response $response, array $args) {
		$usuarios = new Usuarios($this->db);
		return $response
			->withStatus(200)
			->withJson($usuarios->getUsuarioHistorial($args["id"]));
	});

	$app->get("/usuarios/csv", function(Request $request, Response $response, array $args) {
		$usuarios = new Usuarios($this->db);
		$registros = $usuarios->getUsuariosCsv();
		$filename = "usuarios.csv";
		$fp = fopen("php://output", "w");
		fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		fputcsv($fp, ["Id", "DNI", "EsPar", "Apellido", "Nombre", "Teléfono", "Email"], ";");
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

	$app->post("/usuario", function(Request $request, Response $response, array $args) {
		$res = new stdClass();
		$res->err = true;
		$res->msg = "Ocurrió un error al cargar los datos del nuevo Usuario.";
		$res->errors = [];
		$fields = $request->getParsedBody();
		$validate = new Validate($this->db);
		if(!$validate->check($fields, array(
			"dni" => array(
				"required" => true,
				"min" => 7,
				"max" => 8
			),
			"apellido" => array(
				"required" => true,
				"min" => 3
			),
			"nombre" => array(
				"required" => true,
				"min" => 3
			)
		))->passed()) {
			$res->errors = $validate->errors();
		} else {
			$usuarios = new Usuarios($this->db);
			$fields = (object) $request->getParsedBody();
			$fields->espar = Prepare::esNroPar($fields->dni) ? 1 : 0;
			$data = $usuarios->setUsuario($fields);
			if(!$data->err) {
				$res->err = false;
				$res->msg = "El Usuario fué creado.";
				$res->id = $data->lastId;
				return $response
					->withStatus(201)
					->withJson($res);
			} else {
				$res->msg = $data->msg;
			}
		}
		return $response
			->withStatus(409)
			->withJson($res);
	});
?>