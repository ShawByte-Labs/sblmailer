<?php
declare(strict_types=1);

final class Url
{
    public static function basePath(): string
    {
        $config = require __DIR__ . '/../config.php';
        $baseUrl = rtrim((string)($config['base_url'] ?? ''), '/');
        $path = parse_url($baseUrl, PHP_URL_PATH);

        if (!$path || $path === '/') {
            return '';
        }

        return rtrim($path, '/');
    }

    public static function to(string $path): string
    {
        return self::basePath() . '/' . ltrim($path, '/');
    }
}
