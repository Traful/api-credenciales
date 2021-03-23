<?php
	use \Psr\Http\Message\ServerRequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;
	require_once("objects/reclamos.php");
	require_once("utils/validate.php");
	require_once("utils/prepare.php");

	//[GET]
	
	$app->get("/reclamos", function(Request $request, Response $response, array $args) {
		$reclamos = new Reclamos($this->db);
		return $response
			->withStatus(200)
			->withJson($reclamos->getReclamos());
	});

	$app->get("/reclamos/reparticion/{id}", function(Request $request, Response $response, array $args) {
		$reclamos = new Reclamos($this->db);
		return $response
			->withStatus(200)
			->withJson($reclamos->getReclamosByReparticion($args["id"]));
	});

	//[POST]

	$app->post("/reclamo", function(Request $request, Response $response, array $args) {
		$res = new stdClass();
		$res->err = true;
		$res->msg = "Ocurrió un error al procesar la solicitud del reclamo.";
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
			"dni" => array(
				"required" => true,
				"min" => 3,
				"max" => 8
			),
			"telefono" => array(
				"required" => true,
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
				"min" => 2,
				"max" => 25
			),
			"idadhiere" => array(
				"required" => true,
				"numeric" => true,
				"mayorcero" => true
			),
			"observaciones" => array(
				"required" => true
			)
		))->passed()) {
			$res->errors = $validate->errors();
		} else {
			$reclamos = new Reclamos($this->db);
			$fields = (object) $request->getParsedBody();
			$fields->fecha = Prepare::getDate();
			$fields->hora = Prepare::getTime();
			$fields->idestado = "1";
			if($reclamos->setReclamo($fields)) {
				$res->msg = "El reclamo ha sido generado.";
				return $response
					->withStatus(201)
					->withJson($res);
			} else {
				$res->msg = "Ocurrió un error al generar el reclamo.";
			}
		}
		return $response
			->withStatus(409)
			->withJson($res);
	});

	//[PATCH]

	$app->patch("/reclamo/{id}", function(Request $request, Response $response, array $args) {
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
			$reclamos = new Reclamos($this->db);
			$fields = (object) $request->getParsedBody();
			if($reclamos->updateReclamo($args["id"], $fields->idestado)) {
				$res->err = false;
				$res->msg = "El estado del reclamo ha sido actualizado.";
				return $response
					->withStatus(201)
					->withJson($res);
			} else {
				$res->msg = "Ocurrió un error al actualizar el estado del reclamo.";
			}
		}
		return $response
			->withStatus(409)
			->withJson($res);
	});
?>