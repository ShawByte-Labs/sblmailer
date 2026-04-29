<?php
/*

Coded by ShawByte Labs

sblmailer for documentation see github


*/
require_once __DIR__ . '/app/Url.php';
require_once __DIR__ . '/app/Auth.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/Csrf.php';

$user = Auth::requireAdmin();
$db = Database::connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify($_POST['_csrf'] ?? null);

    $stmt = $db->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        trim($_POST['name'] ?? ''),
        trim($_POST['email'] ?? ''),
        password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT),
        $_POST['role'] === 'admin' ? 'admin' : 'marketer',
    ]);

    header('Location: ' . Url::to('users.php'));
    exit;
}

$users = $db->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();

include __DIR__ . '/_header.php';
?>
<h1>Users</h1>
<div class="card">
    <h2>Create User</h2>
    <form method="post">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
        <label>Name</label>
        <input name="name" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <label>Role</label>
        <select name="role"><option value="marketer">Marketer</option><option value="admin">Admin</option></select>
        <button class="btn">Create</button>
    </form>
</div>
<div class="card">
    <table>
        <tr><th>Name</th><th>Email</th><th>Role</th><th>Created</th></tr>
        <?php foreach ($users as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['role']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
