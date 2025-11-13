<?php
namespace App\Models;

use PDO;

class Post {
    private static function connect(): PDO {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $db = getenv('DB_NAME') ?: 'authboard';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    }

    public static function create(int $userId, string $content, ?string $imagePath = null): int {
        $pdo = self::connect();
        $stmt = $pdo->prepare('INSERT INTO posts (user_id, content, image_path) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $content, $imagePath]);
        return (int)$pdo->lastInsertId();
    }

    public static function all(): array {
        $stmt = self::connect()->prepare(
            'SELECT p.*, u.name, u.email FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array {
        $stmt = self::connect()->prepare(
            'SELECT p.*, u.name, u.email FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function update(int $id, string $content): bool {
        $pdo = self::connect();
        $stmt = $pdo->prepare('UPDATE posts SET content = ?, edited_at = NOW() WHERE id = ?');
        return $stmt->execute([$content, $id]);
    }
}
