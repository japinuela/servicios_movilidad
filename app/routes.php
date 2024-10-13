<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Database;
use App\Controllers\AuthController;

return function (App $app) {
	$container = $app->getContainer();
	
    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world 2.0!');
        return $response;
    });
	
	// Ruta para autenticar un usuario (login)
    $app->post('/login', [AuthController::class, 'login']);
	
	// Grupo de rutas para usuarios
    $app->group('/usuarios', function ($group) {
        
        // Obtener todos los usuarios (GET /usuarios)
        $group->get('', function ($request, $response, $args) {
            // Crear instancia de la clase Database
			$db = $this->get(Database::class);
			$pdo = $db->connect();

			// Realizar una consulta a la base de datos
			$stmt = $pdo->query("SELECT * FROM user_account");
			$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// Retornar los resultados como JSON
			$response->getBody()->write(json_encode($users));
			return $response->withHeader('Content-Type', 'application/json');
        });
		
		// Obtener un usuario por ID (GET /usuarios/{id})
        $group->get('/{id}', function ($request, $response, $args) {
            $userId = $args['id'];
            // Aquí iría la lógica para obtener un usuario por su ID
            $response->getBody()->write("Obtener usuario con ID: $userId");
            return $response;
        });

        // Crear un nuevo usuario (POST /usuarios)
        $group->post('', function ($request, $response, $args) {
            $data = $request->getParsedBody();
            // Aquí iría la lógica para crear un nuevo usuario
            $response->getBody()->write("Crear un nuevo usuario");
            return $response;
        });

        // Actualizar un usuario por ID (PUT /usuarios/{id})
        $group->put('/{id}', function ($request, $response, $args) {
            $userId = $args['id'];
            $data = $request->getParsedBody();
            // Aquí iría la lógica para actualizar un usuario por su ID
            $response->getBody()->write("Actualizar usuario con ID: $userId");
            return $response;
        });

        // Eliminar un usuario por ID (DELETE /usuarios/{id})
        $group->delete('/{id}', function ($request, $response, $args) {
            $userId = $args['id'];
            // Aquí iría la lógica para eliminar un usuario por su ID
            $response->getBody()->write("Eliminar usuario con ID: $userId");
            return $response;
        });
	});
		
	$app->post('/old_login', function (Request $request, Response $response) {
    
		$params = (array) $request->getParsedBody();
        $loginName = $params['loginName'] ?? null;
        $password = $params['password'] ?? null;

        // Validar los datos de entrada
        if (!$loginName || !$password) {
            $response->getBody()->write(json_encode(['error' => 'Faltan campos requeridos']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Obtener la instancia de la base de datos a través del contenedor
        $db = $this->get(Database::class);
        $pdo = $db->connect();

        // Preparar la consulta para obtener el usuario por loginName
        $stmt = $pdo->prepare('
            SELECT ul.PasswordHash, ul.PasswordSalt, ha.AlgorithmName
            FROM user_login_data ul
            JOIN hashing_algorithms ha ON ul.HashAlgorithmId = ha.HashAlgorithmId
            WHERE ul.LoginName = :loginName
        ');
        $stmt->execute(['loginName' => $loginName]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Usuario no encontrado
            $response->getBody()->write(json_encode(['error' => 'Usuario no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Obtener el hash y el salt del usuario
        $passwordHash = $user['PasswordHash'];
        $passwordSalt = $user['PasswordSalt'];
        $hashAlgorithm = $user['AlgorithmName'];

        // Verificar la contraseña según el algoritmo de hash
        $hashedInputPassword = '';
        if ($hashAlgorithm === 'MD5') {
            $hashedInputPassword = md5($password . $passwordSalt);
        } elseif ($hashAlgorithm === 'SHA256') {
            $hashedInputPassword = hash('sha256', $password . $passwordSalt);
        } elseif ($hashAlgorithm === 'BCRYPT') {
            // Para bcrypt, se usa password_verify
            if (password_verify($password, $passwordHash)) {
                $hashedInputPassword = $passwordHash;
            }
        }

        // Comparar el hash generado con el almacenado
        if ($hashedInputPassword === $passwordHash) {
            // Login exitoso
            $response->getBody()->write(json_encode(['message' => 'OK']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            // Contraseña incorrecta
            $response->getBody()->write(json_encode(['error' => 'Contraseña incorrecta']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    });
	
    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
};
