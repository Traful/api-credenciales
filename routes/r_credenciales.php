<?php
	use \Psr\Http\Message\ServerRequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;
	use \Firebase\JWT\JWT;
	require_once("utils/validate.php");
	require_once("objects/credenciales.php");

	//[GET]

	$app->get("/credenciales", function(Request $request, Response $response, array $args) {
		$credenciales = new Credenciales($this->db);
		return $response
			->withStatus(200)
			->withJson($credenciales->getCredenciales());
	});

	$app->get("/credencial/dni/{nro}", function(Request $request, Response $response, array $args) {
		$credenciales = new Credenciales($this->db);
		return $response
			->withStatus(200)
			->withJson($credenciales->getCredencialByDni($args["nro"]));
	});

	//[POST]

	$app->post("/credencial", function(Request $request, Response $response, array $args) {
		$res = new stdClass();
		$res->err = true;
		$res->msg = "Ocurrió un error al ingresar la solicitud.";
		
		$res->errors = [];
		$fields = $request->getParsedBody();
		$validate = new Validate($this->db);
		if(!$validate->check($fields, array(
			"idtipo" => array(
				"required" => true,
				"numeric" => true,
				"mayorcero" => true
			),
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
			"domicilio" => array(
				"max" => 100
			),
			"dni" => array(
				"required" => true,
				"numeric" => true,
				"mayorcero" => true,
				"min" => 7,
				"max" => 8
			),
			"telefono" => array(
				"max" => 20
			),
			"email" => array(
				"max" => 100
			),
			"institucion" => array(
				"max" => 100
			),
			"emision" => array(
				"required" => true,
				"min" => 10,
				"max" => 10
			),
			"vencimiento" => array(
				"required" => true,
				"min" => 10,
				"max" => 10
			),
			"foto" => array(
				"required" => true
			)
		))->passed()) {
			$res->errors = $validate->errors();
		} else {
			$credenciales = new Credenciales($this->db);
			$data = $credenciales->procesarDatos($fields);
			if($data) {
				$res->err = false;
				$res->msg = "La solicitud fué procesada sactifactoriamente.";
				$res->data = $data;
				return $response
					->withStatus(201)
					->withJson($res);
			}
		}
		return $response
			->withStatus(409)
			->withJson($res);
	});

?>