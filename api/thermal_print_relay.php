<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * WiFi/LAN Thermal Printer Relay
 *
 * Browsers have no raw TCP socket API, so a network receipt printer (raw port
 * 9100, the near-universal "AppSocket"/JetDirect protocol most network
 * printers speak) can't be reached directly from client-side JS. This
 * endpoint relays the already-rasterized ESC/POS payload to the printer over
 * a plain TCP socket on the server's behalf.
 *
 * Only works if this web server itself has network access to the printer -
 * true for on-premise/LAN installs, not for a cloud-hosted deployment printing
 * to a device behind the shop's own router.
 */

require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$auth = new Auth($db);
if (!$auth->check()) Helpers::jsonResponse(false, 'Session expired. Please log in again.');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(false, 'Invalid method');
if (!Helpers::verifyCsrf()) Helpers::jsonResponse(false, 'CSRF verification failed.');

$ip = trim($_POST['ip'] ?? '');
$port = (int)($_POST['port'] ?? 9100);
$dataB64 = $_POST['data'] ?? '';

if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
    Helpers::jsonResponse(false, 'Enter a valid printer IP address.');
}
if ($port <= 0 || $port > 65535) {
    Helpers::jsonResponse(false, 'Invalid port.');
}
if (empty($dataB64)) {
    Helpers::jsonResponse(false, 'No print data received.');
}

$binary = base64_decode($dataB64, true);
if ($binary === false || strlen($binary) === 0) {
    Helpers::jsonResponse(false, 'Print data was corrupted in transit.');
}
// Sanity cap - a rasterized receipt image is at most a few hundred KB; refuse
// anything wildly larger rather than relaying it blindly.
if (strlen($binary) > 5 * 1024 * 1024) {
    Helpers::jsonResponse(false, 'Print data is unexpectedly large.');
}

$conn = @fsockopen($ip, $port, $errno, $errstr, 3);
if (!$conn) {
    Helpers::jsonResponse(false, "Could not reach $ip:$port ($errstr). If this server isn't on the printer's local network, WiFi/LAN printing needs a machine that is.");
}

try {
    stream_set_timeout($conn, 5);
    $written = fwrite($conn, $binary);
    if ($written === false || $written < strlen($binary)) {
        Helpers::jsonResponse(false, 'Connected to the printer, but sending the print data failed partway through.');
    }
    Helpers::jsonResponse(true, 'Sent to network printer.');
} finally {
    fclose($conn);
}
