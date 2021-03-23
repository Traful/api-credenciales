<?php
	use \Psr\Http\Message\ServerRequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;
	use \Firebase\JWT\JWT;
	require_once("objects/infos.php");

	//[GET]

	$app->get("/infos", function(Request $request, Response $response, array $args) {
		$infos = new Infos($this->db);
		return $response
			->withStatus(200)
			->withJson($infos->getInfos());
	});

	$app->get("/infos/reparticion/{id}", function(Request $request, Response $response, array $args) {
		$infos = new Infos($this->db);
		return $response
			->withStatus(200)
			->withJson($infos->getInfosByReparticion($args["id"], true));
	});

	$app->get("/info/{id}", function(Request $request, Response $response, array $args) {
		$infos = new Infos($this->db);
		return $response
			->withStatus(200)
			->withJson($infos->getInfoById($args["id"]));
	});

	$app->get("/infos/items/reparticion/{id}", function(Request $request, Response $response, array $args) {
		$infos = new Infos($this->db);
		return $response
			->withStatus(200)
			->withJson($infos->getInfosItem($args["id"], true));
	});

	$app->get("/infos/item/{id}", function(Request $request, Response $response, array $args) {
		$infos = new Infos($this->db);
		return $response
			->withStatus(200)
			->withJson($infos->getInfosByItem($args["id"], true));
	});

	$app->get("/infos/item/{id}/all", function(Request $request, Response $response, array $args) {
		$infos = new Infos($this->db);
		return $response
			->withStatus(200)
			->withJson($infos->getInfosByItem($args["id"], false));
	});

	//[POST]

	$app->post("/info", function(Request $request, Response $response, array $args) {
		$res = new stdClass();
		$res->err = true;
		$res->msg = "Ocurrió un error al ingresar la ayuda.";
		$res->errors = [];
		$fields = $request->getParsedBody();
		$validate = new Validate($this->db);
		if(!$validate->check($fields, array(
			"iditeminfo" => array(
				"required" => true,
				"numeric" => true,
				"mayorcero" => true
			),
			"titulo" => array(
				"required" => true,
				"min" => 3,
				"max" => 100
			),
			"activo" => array(
				"required" => true
			)
		))->passed()) {
			$res->errors = $validate->errors();
		} else {
			$fields = (object) $request->getParsedBody();
			$infos = new Infos($this->db);
			if($infos->setInfo($fields)) {
				$res->err = false;
				$res->msg = "La ayuda se guardó correctamente.";
				return $response
					->withStatus(201)
					->withJson($res);
			}
		}
		return $response
			->withStatus(409)
			->withJson($res);
	});

	//[PATCH]

	$app->patch("/info/{id}", function(Request $request, Response $response, array $args) {
		$res = new stdClass();
		$res->err = true;
		$res->msg = "Ocurrió un error al modificar la ayuda.";
		$res->errors = [];
		$fields = $request->getParsedBody();
		$validate = new Validate($this->db);
		if(!$validate->check($fields, array(
			"id" => array(
				"required" => true,
				"numeric" => true,
				"mayorcero" => true
			),
			"iditeminfo" => array(
				"required" => true,
				"numeric" => true,
				"mayorcero" => true
			),
			"titulo" => array(
				"required" => true,
				"min" => 3,
				"max" => 100
			),
			"activo" => array(
				"required" => true
			)
		))->passed()) {
			$res->errors = $validate->errors();
		} else {
			$fields = (object) $request->getParsedBody();
			$infos = new Infos($this->db);
			if($infos->updateInfo($fields)) {
				$res->err = false;
				$res->msg = "La ayuda se modifico correctamente.";
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