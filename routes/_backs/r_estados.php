<?php
	use \Psr\Http\Message\ServerRequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;
	use \Firebase\JWT\JWT;
	require_once("objects/estados.php");

	//[GET]

	$app->get("/estados", function(Request $request, Response $response, array $args) {
		$estados = new Estados($this->db);
		return $response
			->withStatus(200)
			->withJson($estados->getEstados());
	});
?>