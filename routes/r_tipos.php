<?php
	use \Psr\Http\Message\ServerRequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;
	use \Firebase\JWT\JWT;
	require_once("objects/tipos.php");

	//[GET]

	$app->get("/tipos", function(Request $request, Response $response, array $args) {
		$tipos = new Tipos($this->db);
		return $response
			->withStatus(200)
			->withJson($tipos->getTipos());
	});

	$app->get("/tipos/activos", function(Request $request, Response $response, array $args) {
		$tipos = new Tipos($this->db);
		return $response
			->withStatus(200)
			->withJson($tipos->getTipos(true));
	});

	$app->get("/tipo/{id}", function(Request $request, Response $response, array $args) {
		$tipos = new Tipos($this->db);
		return $response
			->withStatus(200)
			->withJson($tipos->getTipo($args["id"]));
	});
?>