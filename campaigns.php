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

    $stmt = $db->prepare('INSERT INTO campaigns (title, subject, body_html, list_id, created_by) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([
        trim($_POST['title'] ?? ''),
        trim($_POST['subject'] ?? ''),
        $_POST['body_html'] ?? '',
        (int)$_POST['list_id'],
        $user['id'],
    ]);

    header('Location: ' . Url::to('campaigns.php'));
    exit;
}

$lists = $db->query('SELECT * FROM recipient_lists ORDER BY name')->fetchAll();
$campaigns = $db->query('SELECT c.*, l.name AS list_name FROM campaigns c JOIN recipient_lists l ON l.id = c.list_id ORDER BY c.created_at DESC')->fetchAll();

include __DIR__ . '/_header.php';
?>
<h1>Campaigns</h1>
<div class="card">
    <h2>Create Campaign</h2>
    <form method="post">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token()) ?>">
        <label>Title</label>
        <input name="title" required>
        <label>Subject</label>
        <input name="subject" required>
        <label>Recipient List</label>
        <select name="list_id" required>
            <?php foreach ($lists as $list): ?>
                <option value="<?= (int)$list['id'] ?>"><?= htmlspecialchars($list['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label>HTML Email Body</label>
        <textarea name="body_html" rows="12" required><h1>Hello!</h1><p>Your marketing message goes here.</p></textarea>
        <button class="btn">Save Draft</button>
    </form>
</div>
<div class="card">
    <table>
        <tr><th>Title</th><th>Subject</th><th>List</th><th>Status</th><th></th></tr>
        <?php foreach ($campaigns as $campaign): ?>
            <tr>
                <td><?= htmlspecialchars($campaign['title']) ?></td>
                <td><?= htmlspecialchars($campaign['subject']) ?></td>
                <td><?= htmlspecialchars($campaign['list_name']) ?></td>
                <td><?= htmlspecialchars($campaign['status']) ?></td>
                <td>
                    <?php if ($campaign['status'] === 'draft'): ?>
                        <a class="btn" href="<?= htmlspecialchars(Url::to('send_campaign.php?id=' . (int)$campaign['id'])) ?>" onclick="return confirm('Send this campaign now?')">Send</a>
                    <?php else: ?>
                        Sent <?= htmlspecialchars((string)$campaign['sent_at']) ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
