<?php
	use \Psr\Http\Message\ServerRequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;
	use \Firebase\JWT\JWT;
	require_once("objects/tiposcuenta.php");

	//[GET]

	$app->get("/tiposcuenta", function(Request $request, Response $response, array $args) {
		$tiposcuenta = new TiposCuenta($this->db);
		return $response
			->withStatus(200)
			->withJson($tiposcuenta->getTipos());
	});
?>