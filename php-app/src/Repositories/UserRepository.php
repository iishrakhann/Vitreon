<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function upsertGoogleUser(array $googleUser): array
    {
        $googleSub = (string) ($googleUser['id'] ?? '');
        $email = (string) ($googleUser['email'] ?? '');
        $name = (string) ($googleUser['name'] ?? 'Google User');

        $existing = $this->findByGoogleSub($googleSub);
        if ($existing !== null) {
            $statement = $this->pdo->prepare(
                'UPDATE users SET full_name = :full_name, email = :email WHERE id = :id'
            );
            $statement->execute([
                'full_name' => $name,
                'email' => $email,
                'id' => $existing['id'],
            ]);

            return $this->findById((int) $existing['id']) ?? $existing;
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO users (google_sub, full_name, email, role) VALUES (:google_sub, :full_name, :email, :role)'
        );
        $statement->execute([
            'google_sub' => $googleSub,
            'full_name' => $name,
            'email' => $email,
            'role' => 'CUSTOMER',
        ]);

        return $this->findById((int) $this->pdo->lastInsertId()) ?? [];
    }

    public function findByEmailOrPhone(string $identity): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM users WHERE email = :identity OR phone_number = :identity LIMIT 1'
        );
        $statement->execute(['identity' => $identity]);
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    public function createOtpUser(array $payload): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (full_name, email, phone_number, role) VALUES (:full_name, :email, :phone_number, :role)'
        );
        $statement->execute([
            'full_name' => $payload['full_name'],
            'email' => $payload['email'],
            'phone_number' => $payload['phone_number'],
            'role' => $payload['role'] ?? 'CUSTOMER',
        ]);

        return $this->findById((int) $this->pdo->lastInsertId()) ?? [];
    }

    public function allByRole(string $role): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE role = :role ORDER BY created_at DESC, id DESC');
        $statement->execute(['role' => $role]);

        return $statement->fetchAll() ?: [];
    }

    public function phoneOrEmailExists(string $email, string $phoneNumber): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM users WHERE email = :email OR phone_number = :phone_number'
        );
        $statement->execute([
            'email' => $email,
            'phone_number' => $phoneNumber,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function countByRole(string $role): int
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE role = :role AND is_active = 1');
        $statement->execute(['role' => $role]);

        return (int) $statement->fetchColumn();
    }

    public function updateRole(int $userId, string $role): void
    {
        $statement = $this->pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
        $statement->execute([
            'role' => $role,
            'id' => $userId,
        ]);
    }

    public function updateActiveStatus(int $userId, bool $isActive): void
    {
        $statement = $this->pdo->prepare('UPDATE users SET is_active = :is_active WHERE id = :id');
        $statement->execute([
            'is_active' => $isActive ? 1 : 0,
            'id' => $userId,
        ]);
    }

    public function findPublicById(int $id): ?array
    {
        return $this->findById($id);
    }

    private function findByGoogleSub(string $googleSub): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE google_sub = :google_sub LIMIT 1');
        $statement->execute(['google_sub' => $googleSub]);
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    private function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }
}
