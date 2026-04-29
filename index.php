<?php
/*

Coded by ShawByte Labs

sblmailer for documentation see github


*/
require_once __DIR__ . '/app/Url.php';
require_once __DIR__ . '/app/Auth.php';
require_once __DIR__ . '/app/Database.php';
$user = Auth::requireLogin();
$db = Database::connection();

$stats = [
    'campaigns' => (int)$db->query('SELECT COUNT(*) FROM campaigns')->fetchColumn(),
    'lists' => (int)$db->query('SELECT COUNT(*) FROM recipient_lists')->fetchColumn(),
    'recipients' => (int)$db->query('SELECT COUNT(*) FROM recipients WHERE subscribed = 1')->fetchColumn(),
    'sent' => (int)$db->query("SELECT COUNT(*) FROM campaigns WHERE status = 'sent'")->fetchColumn(),
];

include __DIR__ . '/_header.php';
?>
<h1>Dashboard</h1>
<p>Welcome, <?= htmlspecialchars($user['name']) ?>.</p>
<div class="grid">
    <div class="card"><div class="stat"><?= $stats['campaigns'] ?></div><p>Campaigns</p></div>
    <div class="card"><div class="stat"><?= $stats['lists'] ?></div><p>Lists</p></div>
    <div class="card"><div class="stat"><?= $stats['recipients'] ?></div><p>Subscribed recipients</p></div>
    <div class="card"><div class="stat"><?= $stats['sent'] ?></div><p>Sent campaigns</p></div>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
