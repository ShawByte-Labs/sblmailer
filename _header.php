<?php
/*

Coded by ShawByte Labs

sblmailer for documentation see github


*/
require_once __DIR__ . '/app/Url.php';
require_once __DIR__ . '/app/Auth.php';
$user = Auth::user();
$config = require __DIR__ . '/config.php';
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>sblmailer - Email Marketing Portal</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(Url::to('assets/style.css')) ?>">
</head>
<body>
<div class="nav">
    <strong>sblmailer</strong> &nbsp; &nbsp;
    <?php if ($user): ?>
        <a href="<?= htmlspecialchars(Url::to('index.php')) ?>">Dashboard</a>
        <a href="<?= htmlspecialchars(Url::to('campaigns.php')) ?>">Campaigns</a>
        <a href="<?= htmlspecialchars(Url::to('lists.php')) ?>">Lists</a>
        <?php if ($user['role'] === 'admin'): ?><a href="<?= htmlspecialchars(Url::to('users.php')) ?>">Users</a><a href="<?= htmlspecialchars(Url::to('settings.php')) ?>">Settings</a><?php endif; ?>
        <a href="<?= htmlspecialchars(Url::to('logout.php')) ?>">Logout</a>
    <?php endif; ?>
</div>
<div class="container">
