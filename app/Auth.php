<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Url.php';

final class Auth
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function user(): ?array
    {
        self::start();

        if (empty($_SESSION['user_id'])) {
            return null;
        }

        $stmt = Database::connection()->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);

        return $stmt->fetch() ?: null;
    }

    public static function attempt(string $email, string $password): bool
    {
        self::start();

        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];

        return true;
    }

    public static function requireLogin(): array
    {
        $user = self::user();

        if (!$user) {
            header('Location: ' . Url::to('login.php'));
            exit;
        }

        return $user;
    }

    public static function requireAdmin(): array
    {
        $user = self::requireLogin();

        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }

        return $user;
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }
}
