<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';

final class Setting
{
    public static function get(string $key, ?string $default = null): ?string
    {
        $stmt = Database::connection()->prepare('SELECT setting_value FROM app_settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();

        return $value === false ? $default : (string)$value;
    }

    public static function set(string $key, string $value): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $stmt->execute([$key, $value]);
    }

    public static function allSmtp(): array
    {
        $config = require __DIR__ . '/../config.php';

        return [
            'host' => self::get('smtp_host', $config['smtp']['host'] ?? ''),
            'port' => (int)self::get('smtp_port', (string)($config['smtp']['port'] ?? 587)),
            'username' => self::get('smtp_username', $config['smtp']['username'] ?? ''),
            'password' => self::get('smtp_password', $config['smtp']['password'] ?? ''),
            'encryption' => self::get('smtp_encryption', $config['smtp']['encryption'] ?? 'tls'),
            'from_email' => self::get('smtp_from_email', $config['smtp']['from_email'] ?? ''),
            'from_name' => self::get('smtp_from_name', $config['smtp']['from_name'] ?? ''),
        ];
    }
}
