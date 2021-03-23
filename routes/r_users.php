<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;
    use \Firebase\JWT\JWT;
	require_once("objects/users.php");
	require_once("utils/prepare.php");
	require_once("utils/validate.php");

	//[GET]
	
	/*
		Listar a todos los usuarios
		url: http://localhost/api-mid/users (servidor local)
		metodo: GET
		Return: [JSON] (Array tipo JSON [no se devuelven las contraseñas, de todas formas están encriptadas en la base de datos])
	*/
    $app->get("/users", function(Request $request, Response $response, array $args) {
        $users = new Users($this->db);
        return $response
            ->withStatus(200)
            ->withJson($users->getUsers());
	});
	
	//[POST]
	
	/*
		Dar de alta un nuevo suario
		url: http://localhost/api-mid/user (servidor local)
		metodo: POST
		body: [JSON]
		{
			"username": "hans",
			"password": "123",
			"apellido": "Araujo",
			"nombre": "Hans"
		}
		Return: JSON (Codigo 201 [created] en caso de existo o 409 [conflict] en caso de fallo)
		ej respuesta codigo 201:
		{
			"err": false,
			"msg": "El usuario ha sido creado."
		}
		ej respuesta codigo 409:
		{
			"err": true,
			"msg": "Ocurrió un error al crear el usuario o el usuario ya existe.",
			"errors": [Array de tipo string con los errores en caso de que no se cumpla con las reglas de solicitud]
		}
	*/
	$app->post("/user", function(Request $request, Response $response, array $args) {
        $res = new stdClass();
        $res->err = true;
		$res->msg = "Ocurrió un error al crear el usuario o el usuario ya existe.";
		$res->errors = [];

		$fields = $request->getParsedBody();
		$validate = new Validate($this->db);
		if(!$validate->check($fields, array(
			"username" => array(
				"required" => true,
				"min" => 3
			),
			"password" => array(
				"required" => true,
				"min" => 3
			),
			"apellido" => array(
				"required" => true,
				"min" => 3
			),
			"nombre" => array(
				"required" => true,
				"min" => 3
			)
		))->passed()) {
			$res->errors = $validate->errors();
		} else {
			$user = new Users($this->db);
			$fields = (object) $request->getParsedBody();
			$fields->esadmin = 0;
			Prepare::bindParsedBody($user, $fields);
			if($user->create()) {
				$res->err = false;
				$res->msg = "El usuario ha sido creado.";
				return $response
					->withStatus(201)
					->withJson($res);
			}
		}
		return $response
			->withStatus(409)
			->withJson($res);
    });
    
	/*
		Obtener el token de logueo
		url: http://localhost/api-mid/user/login (servidor local)
		metodo:  POST
		body: [JSON]
		{
			"username": "hans",
			"password": "123"
		}
		Return: JSON (Codigo 200 [ok] en caso de existo o 401 [unauthorized] en caso de fallo)
		ej respuesta codigo 200:
		{
			"err" => false,
			"msg" => "Usuario válido.",
			"errors": [],
			"token" => "Bearer XXXXXXXXXXXXXXXXXXXX...",
			"user" => array(
				"id" => 1,
				"apellido" => "Araujo",
				"nombre" => "Hans"
			)
		}
		ej respuesta codigo 401:
		{
			"err" = true,
			"msg" = "Nombre de usuario o contraseña no válido.",
			"errors": [Array de tipo string con los errores en caso de que no se cumpla con las reglas de solicitud],
			"token" = "",
			"user" = null
		}
	*/
	$app->post("/user/login", function(Request $request, Response $response, array $args) {
		$res = new stdClass();
        $res->err = true;
		$res->msg = "Nombre de usuario o contraseña no válido.";
		$res->errors = [];
		$res->token = "";
		$res->user = null;
		
		$fields = $request->getParsedBody();
		$validate = new Validate($this->db);
		if(!$validate->check($fields, array(
			"username" => array(
				"required" => true,
				"min" => 3
			),
			"password" => array(
				"required" => true,
				"min" => 3
			)
		))->passed()) {
			$res->errors = $validate->errors();
		} else {
			$parsedBody = (object) $request->getParsedBody();
			$user = new Users($this->db);
			$user->username = $parsedBody->username;
			
			$data_user = $user->usuarioExists();

			if($data_user && password_verify($parsedBody->password, $data_user->password)) {
				$iss = "https://credencialesmvm.000webhostapp.com";
				$aud = "https://credencialesmvm.000webhostapp.com";
				$iat = time();
				$exp = $iat + (3600 * 16); // Expire (16Hs)
				$nbf = $iat;
				$token = array(
					"iss" => $iss,
					"aud" => $aud,
					"iat" => $iat,
					"exp" => $exp,
					"nbf" => $nbf,
					"data" => array(
						"id" => $data_user->id,
						"apellido" => $data_user->apellido,
						"nombre" => $data_user->nombre,
						"expireTime" => "16 Horas",
					)
				);
				$res->err = false;
				$res->msg = "Usuario válido.";
				$res->token = "Bearer " . JWT::encode($token, $this->get("settings")["jwt_key"]);
				$res->user = array(
					"id" => $data_user->id,
					"apellido" => $data_user->apellido,
					"nombre" => $data_user->nombre
				);
				return $response
					->withStatus(200)
					->withJson($res);
			}
		}
		return $response
			->withStatus(409)
			->withJson($res);
    });
?>