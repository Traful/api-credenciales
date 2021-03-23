<?php
	date_default_timezone_set("America/Argentina/San_Luis");

	use \Psr\Http\Message\ServerRequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;
	use \Firebase\JWT\JWT;

	require(__DIR__ . "/vendor/autoload.php");

	$config["displayErrorDetails"] = true;
	$config["addContentLengthHeader"] = false;
	$config["jwt_key"] = "credencial_api_key";

	//Local
	$config["db"]["host"]   = "localhost";
	$config["db"]["user"]   = "root";
	$config["db"]["pass"]   = "";
	$config["db"]["dbname"] = "credenciales";

	$app = new \Slim\App(["settings" => $config]);

	$container = $app->getContainer();

	$container["db"] = function($c) {
		$db = $c["settings"]["db"];
		try {
			$pdo = new PDO("mysql:host=" . $db["host"] . ";dbname=" . $db["dbname"], $db["user"], $db["pass"], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			//$this->connection = null;
			die($e->getMessage());
		}
		return $pdo;
	};

	$container["upload_directory"] = __DIR__ . DIRECTORY_SEPARATOR . "uploads";
	$container["upload_directory_debito"] = __DIR__ . DIRECTORY_SEPARATOR . "uploads/debitos";
	$container["upload_directory_cambio"] = __DIR__ . DIRECTORY_SEPARATOR . "uploads/cambios";

	function moveUploadedFile($directory, $uploadedFile) {
		$extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
		$basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
		$filename = sprintf("%s.%0.8s", $basename, $extension);
		$uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
		return $filename;
	}

	$app->options("/{routes:.+}", function($request, $response, $args) {
		return $response;
	});
	
	//CORS
	$app->add(function($req, $res, $next) {
		$response = $next($req, $res);
		return $response
				->withHeader("Access-Control-Allow-Origin", "*")
				->withHeader("Access-Control-Allow-Headers", "X-Requested-With, Content-Type, Accept, Origin, Authorization")
				->withHeader("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, PATCH, OPTIONS");
	});

	//Middleware (Token::JWT)
	$app->add(function($request, $response, $next) {
		$Authorized = ["/", "user", "user/login"];
		if($request->isOptions() || in_array($request->getUri()->getPath(), $Authorized)) {
			$response = $next($request, $response);
			return $response;
		} else {
			$res = new stdClass();
			$res->err = true;
			$res->msg = "Unauthorized.";
			$authToken = trim($request->getHeader("Authorization")[0]);
			if($authToken === "") {
				return $response
						->withStatus(401)
						->withJson($res);
			} else {
				if(strpos(strtolower($authToken), "bearer") === false) {
					return $response
						->withStatus(401)
						->withJson($res);
				} else {
					$sep = explode(" ", $authToken);
					$jwt = $sep[1];
					//Validar este Token!
					try {
						$decoded = JWT::decode($jwt, $this->get("settings")["jwt_key"], array("HS256"));
						$response = $next($request, $response);
						return $response;
					} catch (\Exception $e) {
						$res->msg = "Unauthorized." . $e->getMessage();
						return $response
							->withStatus(401)
							->withJson($res);
					}
				}
			}
		}
	});

	//Rutas
	$app->get("/", function(Request $request, Response $response, array $args) {
		var_dump($this->db);
		$response->getBody()->write("Bienvenido");
		return $response;
	});

	include_once(__DIR__ . "/routes/r_users.php");
	include_once(__DIR__ . "/routes/r_tipos.php");
	include_once(__DIR__ . "/routes/r_credenciales.php");

	//CORS
	$app->map(["GET", "POST", "PUT", "DELETE", "PATCH"], "/{routes:.+}", function($req, $res) {
		$handler = $this->notFoundHandler;
		return $handler($req, $res);
	});

	$app->run();
?>