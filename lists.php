<?php
/*

Coded by ShawByte Labs

sblmailer for documentation see github


*/
require_once __DIR__ . '/app/Url.php';
require_once __DIR__ . '/app/Auth.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/Csrf.php';

$user = Auth::requireLogin();
$db = Database::connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify($_POST['_csrf'] ?? null);
    $name = trim($_POST['name'] ?? '');

    if ($name !== '') {
        $stmt = $db->prepare('INSERT INTO recipient_lists (name, created_by) VALUES (?, ?)');
        $stmt->execute([$name, $user['id']]);
    }

    header('Location: ' . Url::to('lists.php'));
    exit;
}

$lists = $db->query('SELECT l.*, COUNT(r.id) AS total FROM recipient_lists l LEFT JOIN recipients r ON r.list_id = l.id GROUP BY l.id ORDER BY l.created_at DESC')->fetchAll();

include __DIR__ . '/_header.php';
?>
<h1>Recipient Lists</h1>
<div class="card">
    <h2>Create List</h2>
    <form method="post">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
        <label>List name</label>
        <input name="name" required>
        <button class="btn">Create</button>
    </form>
</div>
<div class="card">
    <table>
        <tr><th>Name</th><th>Recipients</th><th></th></tr>
        <?php foreach ($lists as $list): ?>
            <tr>
                <td><?= htmlspecialchars($list['name']) ?></td>
                <td><?= (int)$list['total'] ?></td>
                <td><a class="btn secondary" href="<?= htmlspecialchars(Url::to('recipients.php?list_id=' . (int)$list['id'])) ?>">Manage</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
