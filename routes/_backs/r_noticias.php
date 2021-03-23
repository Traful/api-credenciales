<?php
	use \Psr\Http\Message\ServerRequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;
	use \Firebase\JWT\JWT;
	require_once("objects/noticias.php");

	//[GET]

	$app->get("/noticias/reparticion/{id}", function(Request $request, Response $response, array $args) {
		$noticias = new Noticias($this->db);
		return $response
			->withStatus(200)
			->withJson($noticias->getNoticiasByReparticion($args["id"]));
	});
?>