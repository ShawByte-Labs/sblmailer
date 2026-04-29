<?php
declare(strict_types=1);

final class Csrf
{
    public static function token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf'];
    }

    public static function verify(?string $token): void
    {
        if (!hash_equals(self::token(), (string)$token)) {
            http_response_code(419);
            echo 'Invalid CSRF token.';
            exit;
        }
    }
}
