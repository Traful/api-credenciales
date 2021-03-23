<?php
	use \Psr\Http\Message\ServerRequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;
	use \Firebase\JWT\JWT;
	require_once("objects/adhesiones.php");

	//[GET]

	$app->get("/adhesiones", function(Request $request, Response $response, array $args) {
		$adhesiones = new Adhesiones($this->db);
		return $response
			->withStatus(200)
			->withJson($adhesiones->getAdhesiones());
	});

	$app->get("/adhesiones/reparticion/{id}", function(Request $request, Response $response, array $args) {
		$adhesiones = new Adhesiones($this->db);
		return $response
			->withStatus(200)
			->withJson($adhesiones->getAdhesionesByReparticion($args["id"]));
	});
?>