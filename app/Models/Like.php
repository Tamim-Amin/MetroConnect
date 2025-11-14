<?php
namespace App\Models;

use PDO;

class Like {
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

    public static function toggle(int $userId, int $postId): bool {
        $pdo = self::connect();
        try {
            $pdo->beginTransaction();

            // check existing
            $stmt = $pdo->prepare('SELECT id FROM likes WHERE user_id = ? AND post_id = ? LIMIT 1');
            $stmt->execute([$userId, $postId]);
            $row = $stmt->fetch();

            if ($row) {
                // unlike
                $del = $pdo->prepare('DELETE FROM likes WHERE id = ?');
                $del->execute([(int)$row['id']]);
                $pdo->commit();
                return false;
            } else {
                // like
                $ins = $pdo->prepare('INSERT INTO likes (user_id, post_id) VALUES (?, ?)');
                $ins->execute([$userId, $postId]);
                $pdo->commit();
                return true;
            }
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Like toggle error: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function countForPost(int $postId): int {
        $stmt = self::connect()->prepare('SELECT COUNT(*) as c FROM likes WHERE post_id = ?');
        $stmt->execute([$postId]);
        $row = $stmt->fetch();
        return (int)($row['c'] ?? 0);
    }

    public static function userLiked(int $userId, int $postId): bool {
        $stmt = self::connect()->prepare('SELECT 1 FROM likes WHERE user_id = ? AND post_id = ? LIMIT 1');
        $stmt->execute([$userId, $postId]);
        return (bool)$stmt->fetch();
    }
}
