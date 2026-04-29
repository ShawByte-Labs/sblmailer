<?php
declare(strict_types=1);

require_once __DIR__ . '/Setting.php';

final class Mailer
{
    public static function sendMarketingEmail(string $to, string $subject, string $html): bool
    {
        return self::send($to, $subject, $html);
    }

    public static function sendPasswordReset(string $to, string $name, string $resetUrl): bool
    {
        $subject = 'Reset your password';
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');

        $html = "
            <p>Hello {$safeName},</p>
            <p>Click the button below to reset your password.</p>
            <p><a href=\"{$safeUrl}\" style=\"background:#2563eb;color:#ffffff;padding:10px 14px;border-radius:6px;text-decoration:none;\">Reset password</a></p>
            <p>If the button does not work, copy this link into your browser:</p>
            <p>{$safeUrl}</p>
            <p>This link expires in 1 hour.</p>
        ";

        return self::send($to, $subject, $html);
    }

    public static function send(string $to, string $subject, string $html): bool
    {
        $smtp = Setting::allSmtp();

        if (empty($smtp['host'])) {
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . self::formatAddress($smtp['from_email'], $smtp['from_name']),
            ];
            return mail($to, $subject, $html, implode("\r\n", $headers));
        }

        return self::smtpSend($smtp, $to, $subject, $html);
    }

    private static function smtpSend(array $smtp, string $to, string $subject, string $html): bool
    {
        $host = $smtp['host'];
        $port = (int)$smtp['port'];
        $encryption = strtolower(trim((string)$smtp['encryption']));
        $remote = ($encryption === 'ssl' ? 'ssl://' : '') . $host;

        $fp = fsockopen($remote, $port, $errno, $errstr, 20);
        if (!$fp) {
            throw new RuntimeException("SMTP connection failed: {$errstr} ({$errno})");
        }

        stream_set_timeout($fp, 20);

        self::expect($fp, [220]);
        self::cmd($fp, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), [250]);

        if ($encryption === 'tls') {
            self::cmd($fp, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Could not enable TLS encryption.');
            }
            self::cmd($fp, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), [250]);
        }

        if (!empty($smtp['username'])) {
            self::cmd($fp, 'AUTH LOGIN', [334]);
            self::cmd($fp, base64_encode($smtp['username']), [334]);
            self::cmd($fp, base64_encode($smtp['password']), [235]);
        }

        $fromEmail = $smtp['from_email'];
        $fromName = $smtp['from_name'];

        self::cmd($fp, "MAIL FROM:<{$fromEmail}>", [250]);
        self::cmd($fp, "RCPT TO:<{$to}>", [250, 251]);
        self::cmd($fp, 'DATA', [354]);

        $headers = [
            'From: ' . self::formatAddress($fromEmail, $fromName),
            'To: <' . $to . '>',
            'Subject: ' . self::encodeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Date: ' . date(DATE_RFC2822),
        ];

        $body = implode("\r\n", $headers) . "\r\n\r\n" . $html . "\r\n.";
        self::write($fp, $body);
        self::expect($fp, [250]);
        self::cmd($fp, 'QUIT', [221]);

        fclose($fp);
        return true;
    }

    private static function cmd($fp, string $command, array $codes): string
    {
        self::write($fp, $command);
        return self::expect($fp, $codes);
    }

    private static function write($fp, string $line): void
    {
        fwrite($fp, $line . "\r\n");
    }

    private static function expect($fp, array $codes): string
    {
        $response = '';
        while (($line = fgets($fp, 515)) !== false) {
            $response .= $line;
            if (preg_match('/^\d{3}\s/', $line)) {
                break;
            }
        }

        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new RuntimeException('SMTP error: ' . trim($response));
        }

        return $response;
    }

    private static function formatAddress(string $email, string $name): string
    {
        return sprintf('"%s" <%s>', addslashes($name), $email);
    }

    private static function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }
}
