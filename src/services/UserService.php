<?php
namespace App\Services;
use App\Database;
use PDO;

class UserService {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db->connect();
		error_log('Constructor UserService');
    }

    public function authenticate($loginName, $password) {
		// Preparar la consulta para obtener el usuario por loginName
        $stmt = $this->db->prepare('
            SELECT ul.UserId, ul.PasswordHash, ul.PasswordSalt, ha.AlgorithmName
            FROM user_login_data ul
            JOIN hashing_algorithms ha ON ul.HashAlgorithmId = ha.HashAlgorithmId
            WHERE ul.LoginName = :loginName
        ');
        $stmt->execute(['loginName' => $loginName]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            // Usuario no encontrado
            return null;
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
            return $user;
        } else {
            // Contraseña incorrecta
            return null;
        }
    }
}
