<?php
namespace App\Utils;
use Firebase\JWT\JWT;

class JWTToken {
    private $secretKey;

    public function __construct() {
        $this->secretKey = $_ENV['JWT_SECRET'];
    }

    public function generateToken(array $data) {
        $payload = array_merge($data, [
            'iss' => 'http://localhost:8080',
            'iat' => time(),
            'exp' => time() + 3600
        ]);
        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function validateToken($token) {
        try {
            return JWT::decode($token, new \Firebase\JWT\Key($this->secretKey, 'HS256'));
        } catch (\Exception $e) {
            return null;
        }
    }
}
