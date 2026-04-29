<?php
/*

Coded by ShawByte Labs

sblmailer for documentation see github


*/
require_once __DIR__ . '/app/Url.php';
require_once __DIR__ . '/app/Auth.php';
require_once __DIR__ . '/app/Csrf.php';
require_once __DIR__ . '/app/Setting.php';
require_once __DIR__ . '/app/Mailer.php';

Auth::requireAdmin();

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify($_POST['_csrf'] ?? null);

    foreach ([
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'smtp_from_email',
        'smtp_from_name'
    ] as $key) {
        Setting::set($key, trim((string)($_POST[$key] ?? '')));
    }

    if (isset($_POST['send_test'])) {
        try {
            $testTo = trim($_POST['test_to'] ?? '');
            if (!filter_var($testTo, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Enter a valid test recipient email.');
            }
            Mailer::send($testTo, 'SMTP test email', '<p>Your SMTP settings are working.</p>');
            $message = 'Settings saved and test email sent.';
        } catch (Throwable $e) {
            $error = 'Settings saved, but test email failed: ' . $e->getMessage();
        }
    } else {
        $message = 'Settings saved.';
    }
}

$smtp = Setting::allSmtp();

include __DIR__ . '/_header.php';
?>
<h1>Settings</h1>
<div class="card">
    <h2>SMTP Email Settings</h2>
    <?php if ($message): ?><p class="success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <form method="post">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">

        <label>SMTP Host</label>
        <input name="smtp_host" value="<?= htmlspecialchars($smtp['host']) ?>" placeholder="smtp.example.com">

        <label>SMTP Port</label>
        <input name="smtp_port" value="<?= htmlspecialchars((string)$smtp['port']) ?>" placeholder="587">

        <label>Encryption</label>
        <select name="smtp_encryption">
            <?php foreach (['tls' => 'TLS', 'ssl' => 'SSL', '' => 'None'] as $value => $label): ?>
                <option value="<?= htmlspecialchars($value) ?>" <?= $smtp['encryption'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
        </select>

        <label>SMTP Username</label>
        <input name="smtp_username" value="<?= htmlspecialchars($smtp['username']) ?>">

        <label>SMTP Password</label>
        <input type="password" name="smtp_password" value="<?= htmlspecialchars($smtp['password']) ?>">

        <label>From Email</label>
        <input type="email" name="smtp_from_email" value="<?= htmlspecialchars($smtp['from_email']) ?>">

        <label>From Name</label>
        <input name="smtp_from_name" value="<?= htmlspecialchars($smtp['from_name']) ?>">

        <hr>
        <label>Send test email to</label>
        <input type="email" name="test_to" placeholder="you@example.com">

        <button class="btn" name="save" value="1">Save Settings</button>
        <button class="btn secondary" name="send_test" value="1">Save & Send Test</button>
    </form>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
