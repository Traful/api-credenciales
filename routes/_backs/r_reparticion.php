<?php
	use \Psr\Http\Message\ServerRequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;
	use \Firebase\JWT\JWT;
	require_once("objects/reparticion.php");

	//[GET]

	$app->get("/reparticiones", function(Request $request, Response $response, array $args) {
		$reparticion = new Reparticion($this->db);
		return $response
			->withStatus(200)
			->withJson($reparticion->getReparticiones());
	});

	$app->get("/reparticion/{id}", function(Request $request, Response $response, array $args) {
		$reparticion = new Reparticion($this->db);
		return $response
			->withStatus(200)
			->withJson($reparticion->getReparticionInfoById($args["id"]));
	});

	$app->get("/reparticion/{id}/items", function(Request $request, Response $response, array $args) {
		$reparticion = new Reparticion($this->db);
		return $response
			->withStatus(200)
			->withJson($reparticion->getReparticionItems($args["id"]));
	});

	$app->get("/reparticion/{id}/adhesiones", function(Request $request, Response $response, array $args) {
		$reparticion = new Reparticion($this->db);
		return $response
			->withStatus(200)
			->withJson($reparticion->getReparticionAdhesionesById($args["id"]));
	});
?>