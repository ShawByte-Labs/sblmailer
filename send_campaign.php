<?php
/*

Coded by ShawByte Labs

sblmailer for documentation see github


*/
require_once __DIR__ . '/app/Url.php';
require_once __DIR__ . '/app/Auth.php';
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/Mailer.php';

$user = Auth::requireLogin();
$db = Database::connection();
$campaignId = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM campaigns WHERE id = ? AND status = "draft"');
$stmt->execute([$campaignId]);
$campaign = $stmt->fetch();

if (!$campaign) {
    http_response_code(404);
    echo 'Draft campaign not found.';
    exit;
}

$stmt = $db->prepare('SELECT * FROM recipients WHERE list_id = ? AND subscribed = 1');
$stmt->execute([(int)$campaign['list_id']]);
$recipients = $stmt->fetchAll();

foreach ($recipients as $recipient) {
    $ok = Mailer::sendMarketingEmail($recipient['email'], $campaign['subject'], $campaign['body_html']);

    $stmt = $db->prepare('INSERT INTO campaign_sends (campaign_id, recipient_id, status, error_message, sent_at) VALUES (?, ?, ?, ?, NOW())');
    $stmt->execute([
        $campaignId,
        $recipient['id'],
        $ok ? 'sent' : 'failed',
        $ok ? null : 'Mailer returned false',
    ]);
}

$stmt = $db->prepare('UPDATE campaigns SET status = "sent", sent_at = NOW() WHERE id = ?');
$stmt->execute([$campaignId]);

header('Location: ' . Url::to('campaigns.php'));
exit;
