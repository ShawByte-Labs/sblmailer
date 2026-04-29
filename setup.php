<?php
/*

Coded by ShawByte Labs

sblmailer for documentation see github


*/
require_once __DIR__ . '/app/Url.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/Csrf.php';

$db = Database::connection();
$hasUsers = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify($_POST['_csrf'] ?? null);

    if ($hasUsers) {
        $error = 'Setup is locked because a user already exists.';
    } else {
        $name = trim($_POST['name'] ?? 'Admin');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } else {
            $stmt = $db->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), 'admin']);
            $message = 'Admin user created. You can now log in.';
            $hasUsers = true;
        }
    }
}

include __DIR__ . '/_header.php';
?>
<div class="card" style="max-width:520px;margin:40px auto;">
    <h1>Web Setup</h1>

    <?php if ($message): ?><p class="success"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <?php if ($hasUsers): ?>
        <p class="warning">Setup is locked because this portal already has at least one user.</p>
        <a class="btn" href="<?= htmlspecialchars(Url::to('login.php')) ?>">Go to login</a>
    <?php else: ?>
        <p>Create your first administrator account.</p>
        <form method="post">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
            <label>Name</label>
            <input name="name" value="Admin" required>
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <input type="password" name="password" minlength="8" required>
            <button class="btn">Create Admin</button>
        </form>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
