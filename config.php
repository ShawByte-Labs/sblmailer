<?php
declare(strict_types=1);

return [
    'app_name' => 'Marketing Email Portal',
    'base_url' => 'https://shawbytelabs.tech/sblmailer', // your url and folder

    'db' => [
        'host' => 'localhost', // server your database is on
        'database' => '', // your database name
        'username' => '', // your database username
        'password' => '', // your database password
        'charset' => 'utf8mb4',
    ],

    'smtp' => [ // SMTP can be changed here but DB overrides config
        'host' => '',
        'port' => 587,
        'username' => '',
        'password' => '',
        'encryption' => 'tls',
        'from_email' => 'marketing@example.com',
        'from_name' => 'Marketing Team',
    ],
];
