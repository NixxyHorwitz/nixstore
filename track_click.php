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

$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if(strpos($ip, ',') !== false) {
    $ip = trim(explode(',', $ip)[0]);
}

$ua   = $_SERVER['HTTP_USER_AGENT'] ?? '';
$page = $source ?: ($_SERVER['HTTP_REFERER'] ?? '/');

// 1. User Agent Parsing
function parseUserAgent($ua) {
    $os = 'Unknown OS';
    $browser = 'Unknown Browser';
    $brand = 'Unknown Brand';

    // Desktop/PC OS
    if (preg_match('/windows nt 10/i', $ua)) $os = 'Windows 10/11';
    elseif (preg_match('/windows nt 6\.3/i', $ua)) $os = 'Windows 8.1';
    elseif (preg_match('/windows nt 6\.2/i', $ua)) $os = 'Windows 8';
    elseif (preg_match('/windows nt 6\.1/i', $ua)) $os = 'Windows 7';
    elseif (preg_match('/mac os x/i', $ua)) $os = 'Mac OS X';
    elseif (preg_match('/linux/i', $ua)) $os = 'Linux';
    // Mobile OS
    if (preg_match('/android/i', $ua)) $os = 'Android';
    elseif (preg_match('/iphone/i', $ua)) $os = 'iOS (iPhone)';
    elseif (preg_match('/ipad/i', $ua)) $os = 'iOS (iPad)';

    // Browser
    if (preg_match('/opr\/|opera/i', $ua)) $browser = 'Opera';
    elseif (preg_match('/edg/i', $ua)) $browser = 'Edge';
    elseif (preg_match('/chrome|crios/i', $ua)) $browser = 'Chrome';
    elseif (preg_match('/safari/i', $ua) && !preg_match('/chrome|crios|fxios/i', $ua)) $browser = 'Safari';
    elseif (preg_match('/firefox|fxios/i', $ua)) $browser = 'Firefox';

    // Brand
    if (preg_match('/iphone|ipad/i', $ua)) $brand = 'Apple Mobile';
    elseif (preg_match('/macintosh/i', $ua)) $brand = 'Apple Mac';
    elseif (preg_match('/samsung|sm-|sgh-|shv-/i', $ua)) $brand = 'Samsung';
    elseif (preg_match('/xiaomi|miui|redmi|poco/i', $ua)) $brand = 'Xiaomi';
    elseif (preg_match('/oppo|cph/i', $ua)) $brand = 'Oppo';
    elseif (preg_match('/vivo/i', $ua)) $brand = 'Vivo';
    elseif (preg_match('/huawei|honor/i', $ua)) $brand = 'Huawei';
    elseif (preg_match('/realme/i', $ua)) $brand = 'Realme';
    elseif (preg_match('/infinix/i', $ua)) $brand = 'Infinix';
    elseif (preg_match('/nokia/i', $ua)) $brand = 'Nokia';
    elseif (preg_match('/windows/i', $ua)) $brand = 'PC/Laptop';

    return ['os' => $os, 'browser' => $browser, 'brand' => $brand];
}
$devInfo = parseUserAgent($ua);

// 2. IP Geolocation (via ip-api)
$location = 'Unknown';
if (in_array($ip, ['127.0.0.1', '::1', 'unknown', 'localhost'])) {
    $location = 'Localhost / Local Network';
} else {
    $ch = curl_init("http://ip-api.com/json/{$ip}?fields=city,regionName,country,isp");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $json = curl_exec($ch);
    curl_close($ch);
    if ($json) {
        $locData = json_decode($json, true);
        if (isset($locData['city'], $locData['country'])) {
            $location = $locData['city'] . ', ' . $locData['regionName'] . ', ' . $locData['country'];
            if(isset($locData['isp'])) {
                $location .= ' (' . $locData['isp'] . ')';
            }
        }
    }
}

// 3. Save to Database
$stmt = $pdo->prepare("INSERT INTO contact_clicks (contact_type, ip_address, user_agent, page_source, location, device_os, device_brand, browser) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([
    strtolower($type), 
    $ip, 
    $ua, 
    $page, 
    $location, 
    $devInfo['os'], 
    $devInfo['brand'], 
    $devInfo['browser']
]);

echo json_encode(['ok' => true]);
