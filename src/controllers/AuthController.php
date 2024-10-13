<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\UserService;
use App\Utils\JWTToken;

class AuthController {
    private $userService;
    private $jwt;

    public function __construct(UserService $userService, JWTToken $jwt) {
        $this->userService = $userService;
        $this->jwt = $jwt;
    }

    public function login(Request $request, Response $response): Response {
        $params = (array) $request->getParsedBody();
        $loginName = $params['loginName'] ?? null;
        $password = $params['password'] ?? null;

        $user = $this->userService->authenticate($loginName, $password);
        if ($user) {
            $token = $this->jwt->generateToken(['user_id' => $user['UserId']]);
            $response->getBody()->write(json_encode(['token' => $token]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode(['error' => 'Invalid credentials']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
}
