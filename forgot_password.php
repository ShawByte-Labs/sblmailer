<?php
/*

Coded by ShawByte Labs

sblmailer for documentation see github


*/
require_once __DIR__ . '/app/Url.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/Csrf.php';
require_once __DIR__ . '/app/Mailer.php';

$db = Database::connection();
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify($_POST['_csrf'] ?? null);

    $email = trim($_POST['email'] ?? '');
    $message = 'If an account exists for that email, a reset link has been sent.';

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $db->prepare('SELECT id, name, email FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);

            $stmt = $db->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
            $stmt->execute([$user['id'], $tokenHash]);

            $config = require __DIR__ . '/config.php';
            $baseUrl = rtrim($config['base_url'], '/');
            $resetUrl = $baseUrl . '/reset_password.php?token=' . urlencode($token);

            try {
                Mailer::sendPasswordReset($user['email'], $user['name'], $resetUrl);
            } catch (Throwable $e) {
                $error = 'The reset email could not be sent. Ask an admin to check SMTP settings.';
            }
        }
    }
}

include __DIR__ . '/_header.php';
?>
<div class="card" style="max-width:480px;margin:40px auto;">
    <h1>Forgot Password</h1>
    <?php if ($message): ?><p class="success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <form method="post">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
        <label>Email address</label>
        <input type="email" name="email" required>
        <button class="btn">Send Reset Link</button>
    </form>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
