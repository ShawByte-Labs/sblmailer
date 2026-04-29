<?php
/*

Coded by ShawByte Labs

sblmailer for documentation see github


*/
require_once __DIR__ . '/app/Url.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/Csrf.php';

$db = Database::connection();
$token = $_GET['token'] ?? $_POST['token'] ?? '';
$tokenHash = hash('sha256', (string)$token);
$message = null;
$error = null;

$stmt = $db->prepare(
    'SELECT pr.*, u.email, u.name
     FROM password_resets pr
     JOIN users u ON u.id = pr.user_id
     WHERE pr.token_hash = ?
       AND pr.used_at IS NULL
       AND pr.expires_at > NOW()
     ORDER BY pr.id DESC
     LIMIT 1'
);
$stmt->execute([$tokenHash]);
$reset = $stmt->fetch();

if (!$reset) {
    $error = 'This password reset link is invalid or expired.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset) {
    Csrf::verify($_POST['_csrf'] ?? null);

    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $reset['user_id']]);

        $stmt = $db->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?');
        $stmt->execute([$reset['id']]);

        $message = 'Password updated. You can now log in.';
        $reset = null;
    }
}

include __DIR__ . '/_header.php';
?>
<div class="card" style="max-width:480px;margin:40px auto;">
    <h1>Reset Password</h1>

    <?php if ($message): ?><p class="success"><?= htmlspecialchars($message) ?></p><a class="btn" href="<?= htmlspecialchars(Url::to('login.php')) ?>">Go to login</a><?php endif; ?>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <?php if ($reset): ?>
        <form method="post">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars((string)$token) ?>">
            <label>New Password</label>
            <input type="password" name="password" minlength="8" required>
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" minlength="8" required>
            <button class="btn">Update Password</button>
        </form>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
