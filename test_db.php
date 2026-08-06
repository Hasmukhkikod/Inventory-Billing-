<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/application/Models/Database.php';

$_SESSION['is_demo'] = true;
$db = new \App\Models\Database();
$email = 'testdemo123@gmail.com';
$existingUser = $db->query("SELECT id FROM users WHERE email = ? LIMIT 1", [$email])->fetch();
$existingOrg = $db->query("SELECT id FROM organizations WHERE email = ? LIMIT 1", [$email])->fetch();
echo "existingUser: "; var_dump($existingUser);
echo "existingOrg: "; var_dump($existingOrg);
