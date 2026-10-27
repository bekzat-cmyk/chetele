<?php
// Copy this file as config.php if you need to reconfigure the hosting.
return [
    'host' => 'your-mysql-host',
    'port' => 3306,
    'database' => 'your-database-name',
    'username' => 'your-mysql-user',
    'password' => 'your-mysql-password',
    // Admin panel (/admin.html). Required: without them the admin API stays closed.
    'admin_login' => 'your-admin-login',
    'admin_password' => 'long-random-password-12-plus-chars',
    // Generate a separate VAPID key pair for this site. Never publish the private key.
    'vapid' => [
        'public_key' => 'your-public-vapid-key',
        'private_key' => 'your-private-vapid-key',
        'subject' => 'mailto:you@example.com',
    ],
];
