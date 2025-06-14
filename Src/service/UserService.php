<?php
namespace App\Service;

use PDO;
use Exception;


class UserService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    
    public function register(string $email, string $password, string $name): array
    {
        // check if email already exists
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'userId' => null, 'error' => 'Email deja înregistrat.'];
        }

        // hash the password
        $hash = password_hash($password, PASSWORD_DEFAULT);
        if (!$hash) {
            return ['success' => false, 'userId' => null, 'error' => 'Eroare la criptarea parolei.'];
        }

        // insert user
        $insert = $this->pdo->prepare(
            'INSERT INTO users (name, email, password_hash, role, created_at) VALUES (:name, :email, :hash, :role, NOW())'
        );
        $success = $insert->execute([
            'name' => $name,
            'email' => $email,
            'hash' => $hash,
            'role' => 'user'
        ]);

        if (!$success) {
            return ['success' => false, 'userId' => null, 'error' => 'Eroare la crearea utilizatorului.'];
        }

        return ['success' => true, 'userId' => (int)$this->pdo->lastInsertId(), 'error' => null];
    }

  
    public function authenticate(string $email, string $password): array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return ['success' => false, 'user' => null, 'error' => 'Email sau parolă invalidă.'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'user' => null, 'error' => 'Email sau parolă invalidă.'];
        }

        // Remove password_hash before returning
        unset($user['password_hash']);
        return ['success' => true, 'user' => $user, 'error' => null];
    }


    public function getAllUsers(): array
    {
        $stmt = $this->pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

   
    public function getUserById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }
}


