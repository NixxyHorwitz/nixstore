<?php
@session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require_once '../config/database.php';
require_once '../includes/functions.php';

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - WebSales</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Base CSS -->
    <link rel="stylesheet" href="assets/base.css?v=<?= time() ?>">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <!-- Quill -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <style>
        body { background: var(--bg); color: var(--text); }
        .form-control, .form-select {
            background-color: var(--hover);
            border: 1px solid var(--border);
            color: var(--text);
            font-size: 13.5px;
        }
        .form-control:focus, .form-select:focus {
            background-color: var(--surface);
            color: var(--text);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem var(--ag);
        }
        .modal-content {
            background-color: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--r);
        }
        .modal-header, .modal-footer {
            border-color: var(--border);
        }
        .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        ::placeholder { color: var(--mut) !important; }
        
        .ql-toolbar.ql-snow {
            background: var(--hover); border: 1px solid var(--border); border-radius: 8px 8px 0 0;
            padding: 8px 12px;
        }
        .ql-toolbar.ql-snow .ql-stroke { stroke: var(--text); }
        .ql-toolbar.ql-snow .ql-fill { fill: var(--text); }
        .ql-toolbar.ql-snow .ql-picker { color: var(--text); }
        .ql-container.ql-snow {
            background: var(--surface); border: 1px solid var(--border); border-radius: 0 0 8px 8px; color: var(--text); min-height: 250px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 14px;
        }
        .ql-editor { min-height: 250px; }
        
        tr td { vertical-align: middle; }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <a href="index.php" class="sb-brand">
        <div class="sb-logo"><i class='bx bx-cart'></i></div>
        <div class="sb-name">Web<span>Sales</span></div>
    </a>
    
    <div class="sb-scroll">
        <div class="sb-label">MENU UTAMA</div>
        <a href="index.php" class="sb-link <?= $current_page == 'index.php' ? 'active' : '' ?>">
            <i class='bx bx-home-alt'></i> Dashboard
        </a>
        <a href="products.php" class="sb-link <?= strpos($current_page,'product') !== false ? 'active' : '' ?>">
            <i class='bx bx-box'></i> Catalog Produk
        </a>
        <a href="gallery.php" class="sb-link <?= $current_page == 'gallery.php' ? 'active' : '' ?>">
            <i class='bx bx-images'></i> Media Gallery
        </a>
        <a href="traffic.php" class="sb-link <?= $current_page == 'traffic.php' ? 'active' : '' ?>">
            <i class='bx bx-bar-chart-alt-2'></i> Analytics
        </a>
        
        <div class="sb-label mt-3">KONTEN & PENGATURAN</div>
        <a href="faqs.php" class="sb-link <?= $current_page == 'faqs.php' ? 'active' : '' ?>">
            <i class='bx bx-message-rounded-dots'></i> FAQs
        </a>
        <a href="settings.php" class="sb-link <?= $current_page == 'settings.php' ? 'active' : '' ?>">
            <i class='bx bx-cog'></i> Settings
        </a>
    </div>
    
    <div class="sb-user">
        <img src="https://ui-avatars.com/api/?name=Admin&background=131d30&color=fff" class="sb-ava" alt="Admin">
        <div>
            <div class="sb-uname">Administrator</div>
            <div class="sb-urole">Super Admin</div>
        </div>
        <button class="sb-logout" onclick="window.location.href='logout.php'" title="Logout">
            <i class='bx bx-log-out'></i>
        </button>
    </div>
</div>

<div class="sb-overlay" onclick="toggleSidebar()"></div>

<div class="topbar">
    <div class="sb-toggle" onclick="toggleSidebar()">
        <i class='bx bx-menu'></i>
    </div>
    <h1 class="tb-title"><?= ucfirst(str_replace('.php', '', $current_page)) ?></h1>
    
    <div class="ms-auto d-flex align-items-center gap-2">
        <a href="../" target="_blank" class="tb-btn" title="View Website">
            <i class='bx bx-world'></i>
        </a>
    </div>
</div>

<div class="main-content">
    <div class="content-wrap">
