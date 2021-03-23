<?php
	use \Psr\Http\Message\ServerRequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;
	use \Firebase\JWT\JWT;
	require_once("objects/config.php");

	//[GET]

	$app->get("/configs", function(Request $request, Response $response, array $args) {
		$config = new Config($this->db);
		return $response
			->withStatus(200)
			->withJson($config->getConfigs());
	});

	$app->get("/configs/reparticion/{idreparticion}", function(Request $request, Response $response, array $args) {
		$config = new Config($this->db);
		return $response
			->withStatus(200)
			->withJson($config->getConfigsByIdReparticion($args["idreparticion"]));
	});

	$app->get("/configs/setting/{id}", function(Request $request, Response $response, array $args) {
		$config = new Config($this->db);
		return $response
			->withStatus(200)
			->withJson($config->getConfigsByIdSetting($args["id"]));
	});

	$app->get("/configs/reparticion/item/{iditem}", function(Request $request, Response $response, array $args) {
		$config = new Config($this->db);
		return $response
			->withStatus(200)
			->withJson($config->getConfigsByIdItem($args["iditem"]));
	});

	$app->get("/config/{tabla}", function(Request $request, Response $response, array $args) {
		$config = new Config($this->db);
		return $response
			->withStatus(200)
			->withJson($config->getConfigByTabla($args["tabla"]));
	});
	
	$app->get("/config/boxs/{tabla}", function(Request $request, Response $response, array $args) {
		$config = new Config($this->db);
		return $response
			->withStatus(200)
			->withJson($config->getBoxsByTabla($args["tabla"]));
	});
?>