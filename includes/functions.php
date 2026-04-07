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
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $page = $_SERVER['REQUEST_URI'];
    $date = date('Y-m-d');

    // Simple anti-spam: Only log once per session per day to avoid huge DB size from reloads
    if (!isset($_SESSION['visited_' . $date])) {
        $stmt = $pdo->prepare("INSERT INTO traffic (ip_address, user_agent, page_visited, visit_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ip, $ua, $page, $date]);
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

