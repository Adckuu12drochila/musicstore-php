<?php
namespace App\Models;

use PDO;

class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Находит пользователя по e-mail.
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Находит пользователя по ID.
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Создаёт нового пользователя и возвращает его ID.
     * @param array $data ['email','password_hash','name']
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (email, password_hash, name)
             VALUES (:email, :password_hash, :name)'
        );
        $stmt->execute([
            'email'         => $data['email'],
            'password_hash' => $data['password_hash'],
            'name'          => $data['name'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }
    /**
     * Возвращает всех пользователей
     * @return array
     */
    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT id, email, name, is_admin, created_at
             FROM users
             ORDER BY created_at DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Обновляет имя и флаг is_admin
     * @param int   $id
     * @param array $data ['name'=>string, 'is_admin'=>bool]
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET name = :name,
                 is_admin = :is_admin
             WHERE id = :id'
        );
        return $stmt->execute([
            'id'       => $id,
            'name'     => $data['name'],
            'is_admin' => $data['is_admin'] ? 't' : 'f',
        ]);
    }

    /**
     * Удаляет пользователя
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
    public function updatePassword(int $id, string $hash): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = :h WHERE id = :id');
        return $stmt->execute(['h'=>$hash,'id'=>$id]);
    }
}
