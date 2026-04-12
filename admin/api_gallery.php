<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
require '../config/database.php';

header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$upload_dir = '../assets/uploads/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

switch($action) {
    case 'get_all':
        $files = [];
        $scanned = scandir($upload_dir);
        foreach ($scanned as $f) {
            if ($f !== '.' && $f !== '..') {
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $files[] = [
                        'name' => $f,
                        'url' => $upload_dir . $f,
                        'time' => filemtime($upload_dir . $f),
                        'size' => filesize($upload_dir . $f)
                    ];
                }
            }
        }
        usort($files, function($a, $b) { return $b['time'] - $a['time']; });
        echo json_encode(['status' => 'success', 'data' => $files]);
        break;

    case 'upload':
        if (!empty($_FILES['files']['name'][0])) {
            $uploadedCount = 0;
            foreach ($_FILES['files']['name'] as $key => $name) {
                if ($_FILES['files']['error'][$key] == 0) {
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $new_name = time() . "_" . mt_rand(100, 999) . "." . $ext;
                        if (move_uploaded_file($_FILES['files']['tmp_name'][$key], $upload_dir . $new_name)) {
                            $uploadedCount++;
                        }
                    }
                }
            }
            if ($uploadedCount > 0) {
                echo json_encode(['status' => 'success', 'message' => "$uploadedCount files uploaded!"]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to upload or invalid file types']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No files sent']);
        }
        break;

    case 'delete':
        $name = $_POST['name'] ?? '';
        if ($name && file_exists($upload_dir . $name)) {
            unlink($upload_dir . $name);
            // Optionally remove from product_images to keep db clean
            $pdo->prepare("DELETE FROM product_images WHERE image_path=?")->execute([$name]);
            echo json_encode(['status' => 'success', 'message' => 'File deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File not found']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
        break;
}
