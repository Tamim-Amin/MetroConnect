<?php
namespace App\Models;

use PDO;

class Comment {
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

    public static function create(int $postId, int $userId, string $content, ?int $parentId = null): int {
        $pdo = self::connect();
        $stmt = $pdo->prepare('INSERT INTO comments (post_id, user_id, parent_id, content) VALUES (?, ?, ?, ?)');
        $stmt->execute([$postId, $userId, $parentId, $content]);
        return (int)$pdo->lastInsertId();
    }


    public static function fetchByPost(int $postId): array {
        $stmt = self::connect()->prepare(
            'SELECT c.*, u.name, u.email, u.avatar_path
             FROM comments c
             JOIN users u ON c.user_id = u.id
             WHERE c.post_id = ?
             ORDER BY c.created_at ASC'
        );
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }


    public static function buildTree(array $rows): array {
        $items = [];
        foreach ($rows as $r) {
            $r['children'] = [];
            $items[$r['id']] = $r;
        }
        $tree = [];
        foreach ($items as $id => &$node) {
            if (!empty($node['parent_id']) && isset($items[$node['parent_id']])) {
                $items[$node['parent_id']]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }
        return $tree;
    }

    public static function findById(int $id): ?array {
        $stmt = self::connect()->prepare('SELECT * FROM comments WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
