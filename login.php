<?php
/*

Coded by ShawByte Labs

sblmailer for documentation see github


*/
require_once __DIR__ . '/app/Url.php';
require_once __DIR__ . '/app/Auth.php';
require_once __DIR__ . '/app/Csrf.php';

Auth::start();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify($_POST['_csrf'] ?? null);

    if (Auth::attempt(trim($_POST['email'] ?? ''), $_POST['password'] ?? '')) {
        header('Location: ' . Url::to('index.php'));
        exit;
    }

    $error = 'Invalid email or password.';
}

include __DIR__ . '/_header.php';
?>
<div class="card" style="max-width:420px;margin:50px auto;">
    <h1>Login</h1>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button class="btn">Log in</button>
    </form>
    <p class="small"><a href="<?= htmlspecialchars(Url::to('forgot_password.php')) ?>">Forgot your password?</a></p>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
