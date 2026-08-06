<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../application/Models/Helpers.php';

header('Content-Type: application/json');

$langCode = $_GET['lang'] ?? 'en';
$langFile = __DIR__ . '/../application/lang/' . basename($langCode) . '.json';

if (file_exists($langFile)) {
    echo file_get_contents($langFile);
} else {
    echo json_encode([]);
}
