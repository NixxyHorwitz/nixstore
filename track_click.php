<?php
/**
 * track_click.php — Contact click tracker endpoint
 * Called via fetch() from frontend when user clicks WA/Telegram button
 */
require 'config/database.php';

header('Content-Type: application/json');

$type   = trim($_POST['type'] ?? '');       // 'whatsapp' or 'telegram'
$source = trim($_POST['source'] ?? '');     // page origin e.g. '/', '/product.php?id=1'

if (!in_array($type, ['whatsapp', 'telegram'])) {
    echo json_encode(['ok' => false]);
    exit;
}

$ip = $_SERVER['HTTP_X_FORWARDED_FOR']
    ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]
    : ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

$ua   = $_SERVER['HTTP_USER_AGENT'] ?? '';
$page = $source ?: ($_SERVER['HTTP_REFERER'] ?? '/');

$stmt = $pdo->prepare("INSERT INTO contact_clicks (contact_type, ip_address, user_agent, page_source) VALUES (?, ?, ?, ?)");
$stmt->execute([strtolower($type), $ip, $ua, $page]);

echo json_encode(['ok' => true]);
