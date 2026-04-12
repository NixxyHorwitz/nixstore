<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
require '../config/database.php';
require '../includes/functions.php';

header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action){
    case 'get_all':
        $products = get_all_products($pdo);
        foreach($products as &$p){
            $p['first_image'] = get_first_product_image($pdo, $p['id']);
        }
        echo json_encode(['status' => 'success', 'data' => $products]);
        break;

    case 'get_single':
        $id = (int)($_GET['id'] ?? 0);
        $product = get_product($pdo, $id);
        if($product){
            $images = get_product_images($pdo, $id);
            if(empty($images) && $product['image']){
                $images[] = ['id' => 0, 'image_path' => $product['image'], 'is_mock' => true, 'sort_order' => 0, 'is_thumbnail' => 0];
            }
            $product['images'] = $images;
            // Decode custom_buttons JSON
            $product['custom_buttons'] = $product['custom_buttons'] ? json_decode($product['custom_buttons'], true) : [];
            echo json_encode(['status' => 'success', 'data' => $product]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Not found']);
        }
        break;

    case 'save':
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $title = $_POST['title'] ?? '';
        $desc = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $promo = empty($_POST['promo_price']) ? null : $_POST['promo_price'];
        $link = $_POST['demo_link'] ?? '';
        $youtube = $_POST['youtube_url'] ?? '';
        // custom_buttons comes as JSON string
        $custom_buttons_raw = $_POST['custom_buttons'] ?? '[]';
        $custom_buttons = json_decode($custom_buttons_raw, true);
        $custom_buttons_json = json_encode(is_array($custom_buttons) ? $custom_buttons : []);

        if(!$title || $price === ''){
            echo json_encode(['status' => 'error', 'message' => 'Title and Price required.']);
            exit;
        }

        if($id > 0){
            $stmt = $pdo->prepare("UPDATE products SET title=?, description=?, price=?, promo_price=?, demo_link=?, youtube_url=?, custom_buttons=? WHERE id=?");
            $stmt->execute([$title, $desc, $price, $promo, $link, $youtube, $custom_buttons_json, $id]);
            $product_id = $id;
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (title, description, price, promo_price, demo_link, image, youtube_url, custom_buttons) VALUES (?,?,?,?,?,'',?,?)");
            $stmt->execute([$title, $desc, $price, $promo, $link, $youtube, $custom_buttons_json]);
            $product_id = $pdo->lastInsertId();
        }

        // Complete replacement of product_images state based on final saved draft form
        $gallery_images_raw = $_POST['gallery_images'] ?? '[]';
        $gallery_images = json_decode($gallery_images_raw, true);

        // Wipe old images for this product
        $pdo->prepare("DELETE FROM product_images WHERE product_id=?")->execute([$product_id]);

        if (is_array($gallery_images) && !empty($gallery_images)) {
            $sortVal = 1;
            foreach ($gallery_images as $img_obj) {
                // Determine if it was just string (legacy compatibility) or object
                if (is_array($img_obj)) {
                    $img_name = $img_obj['name'] ?? '';
                    $is_thumb = !empty($img_obj['thumb']) ? 1 : 0;
                } else {
                    $img_name = $img_obj;
                    $is_thumb = ($sortVal === 1) ? 1 : 0;
                }

                if ($img_name) {
                    $pdo->prepare("INSERT INTO product_images (product_id, image_path, sort_order, is_thumbnail) VALUES (?, ?, ?, ?)")
                        ->execute([$product_id, $img_name, $sortVal++, $is_thumb]);
                }
            }
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Product saved.']);
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        $images = get_product_images($pdo, $id);
        foreach($images as $img){
            if(file_exists('../assets/uploads/'.$img['image_path'])) unlink('../assets/uploads/'.$img['image_path']);
        }
        $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Product deleted.']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
