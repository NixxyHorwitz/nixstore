<?php
function get_settings($pdo) {
    $stmt = $pdo->query("SELECT key_name, key_value FROM settings");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['key_name']] = $row['key_value'];
    }
    return $settings;
}

function get_setting($pdo, $key, $default = '') {
    $stmt = $pdo->prepare("SELECT key_value FROM settings WHERE key_name = ?");
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    return $val !== false ? $val : $default;
}

function log_traffic($pdo) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    if(strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
    }
    
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $page = $_SERVER['REQUEST_URI'];
    $date = date('Y-m-d');

    // Simple anti-spam
    if (!isset($_SESSION['visited_' . $date])) {
        
        $os = 'Unknown OS'; $browser = 'Unknown Browser'; $brand = 'Unknown Brand';
        if (preg_match('/windows nt 10/i', $ua)) $os = 'Windows 10/11';
        elseif (preg_match('/windows nt 6\.3/i', $ua)) $os = 'Windows 8.1';
        elseif (preg_match('/windows nt 6\.2/i', $ua)) $os = 'Windows 8';
        elseif (preg_match('/windows nt 6\.1/i', $ua)) $os = 'Windows 7';
        elseif (preg_match('/mac os x/i', $ua)) $os = 'Mac OS X';
        elseif (preg_match('/linux/i', $ua)) $os = 'Linux';
        if (preg_match('/android/i', $ua)) $os = 'Android';
        elseif (preg_match('/iphone/i', $ua)) $os = 'iOS (iPhone)';
        elseif (preg_match('/ipad/i', $ua)) $os = 'iOS (iPad)';

        if (preg_match('/opr\/|opera/i', $ua)) $browser = 'Opera';
        elseif (preg_match('/edg/i', $ua)) $browser = 'Edge';
        elseif (preg_match('/chrome|crios/i', $ua)) $browser = 'Chrome';
        elseif (preg_match('/safari/i', $ua) && !preg_match('/chrome|crios|fxios/i', $ua)) $browser = 'Safari';
        elseif (preg_match('/firefox|fxios/i', $ua)) $browser = 'Firefox';

        if (preg_match('/iphone|ipad/i', $ua)) $brand = 'Apple Mobile';
        elseif (preg_match('/macintosh/i', $ua)) $brand = 'Apple Mac';
        elseif (preg_match('/samsung|sm-|sgh-|shv-/i', $ua)) $brand = 'Samsung';
        elseif (preg_match('/xiaomi|miui|redmi|poco/i', $ua)) $brand = 'Xiaomi';
        elseif (preg_match('/oppo|cph/i', $ua)) $brand = 'Oppo';
        elseif (preg_match('/vivo/i', $ua)) $brand = 'Vivo';
        elseif (preg_match('/huawei|honor/i', $ua)) $brand = 'Huawei';
        elseif (preg_match('/nokia/i', $ua)) $brand = 'Nokia';
        elseif (preg_match('/windows/i', $ua)) $brand = 'PC/Laptop';

        $location = 'Unknown';
        if (in_array($ip, ['127.0.0.1', '::1', 'unknown', 'localhost'])) {
            $location = 'Localhost / Local Network';
        } else {
            $ch = curl_init("http://ip-api.com/json/".urlencode($ip)."?fields=city,regionName,country,isp");
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

        $stmt = $pdo->prepare("INSERT INTO traffic (ip_address, user_agent, page_visited, visit_date, location, device_os, device_brand, browser) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$ip, $ua, $page, $date, $location, $os, $brand, $browser]);
        $_SESSION['visited_' . $date] = true;
    }
}

function get_all_products($pdo) {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_product($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_product_images($pdo, $product_id) {
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_product_thumbnail($pdo, $product_id) {
    // Returns thumbnail image path (is_thumbnail=1), else first by sort_order
    $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY is_thumbnail DESC, sort_order ASC, id ASC LIMIT 1");
    $stmt->execute([$product_id]);
    return $stmt->fetchColumn();
}

function get_first_product_image($pdo, $product_id) {
    // Prefer thumbnail, fallback to sort_order first, then legacy image column
    $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY is_thumbnail DESC, sort_order ASC, id ASC LIMIT 1");
    $stmt->execute([$product_id]);
    $res = $stmt->fetchColumn();
    if(!$res){
        $stmt2 = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt2->execute([$product_id]);
        $res = $stmt2->fetchColumn();
    }
    return $res;
}

function get_all_faqs($pdo) {
    $stmt = $pdo->query("SELECT * FROM faqs ORDER BY display_order ASC, id ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_faq($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

