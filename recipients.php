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
$listId = (int)($_GET['list_id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM recipient_lists WHERE id = ?');
$stmt->execute([$listId]);
$list = $stmt->fetch();

if (!$list) {
    http_response_code(404);
    echo 'List not found';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::verify($_POST['_csrf'] ?? null);

    $emails = preg_split('/\r\n|\r|\n/', trim($_POST['emails'] ?? ''));

    foreach ($emails as $email) {
        $email = trim($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $db->prepare('INSERT IGNORE INTO recipients (list_id, email) VALUES (?, ?)');
            $stmt->execute([$listId, $email]);
        }
    }

    header('Location: ' . Url::to('recipients.php?list_id=' . $listId));
    exit;
}

$stmt = $db->prepare('SELECT * FROM recipients WHERE list_id = ? ORDER BY created_at DESC');
$stmt->execute([$listId]);
$recipients = $stmt->fetchAll();

include __DIR__ . '/_header.php';
?>
<h1><?= htmlspecialchars($list['name']) ?></h1>
<div class="card">
    <h2>Add Recipients</h2>
    <form method="post">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
        <label>One email per line</label>
        <textarea name="emails" rows="8" required></textarea>
        <button class="btn">Import</button>
    </form>
</div>
<div class="card">
    <table>
        <tr><th>Email</th><th>Subscribed</th><th>Added</th></tr>
        <?php foreach ($recipients as $recipient): ?>
            <tr>
                <td><?= htmlspecialchars($recipient['email']) ?></td>
                <td><?= $recipient['subscribed'] ? 'Yes' : 'No' ?></td>
                <td><?= htmlspecialchars($recipient['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
