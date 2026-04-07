<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require '../config/database.php';
require '../includes/functions.php';

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - WebSales</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #0a0a0c;
            --bg-surface: #121216;
            --bg-surface-hover: #18181c;
            --border-color: #26262c;
            --text-primary: #ffffff;
            --text-muted: #888899;
            --accent: #e2e2e5;
            --danger: #ff4757;
            --success: #2ed573;
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg-base); color: var(--text-primary); display: flex; min-height: 100vh; overflow-x: hidden; letter-spacing: -0.3px; line-height: 1.5; }

        /* Sidebar */
        .sidebar { width: 260px; background: var(--bg-base); border-right: 1px solid var(--border-color); display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; transition: transform 0.3s; }
        .sidebar-brand { padding: 30px 24px; font-size: 20px; font-weight: 700; letter-spacing: -1px; border-bottom: 1px solid var(--border-color); color: var(--text-primary); text-decoration: none; display: flex; align-items: center; justify-content: space-between; }
        .sidebar-menu { list-style: none; padding: 20px 0; flex: 1; overflow-y: auto;}
        .sidebar-link { display: flex; align-items: center; justify-content: space-between; padding: 12px 24px; color: var(--text-muted); text-decoration: none; font-weight: 500; font-size: 14px; transition: 0.2s; border-left: 2px solid transparent;}
        .sidebar-link:hover, .sidebar-link.active { background: var(--bg-surface); color: var(--text-primary); border-left-color: var(--text-primary); }
        .sidebar-link.text-danger { color: var(--danger); justify-content: center;}
        .sidebar-link.text-danger:hover { background: rgba(255,71,87,0.1); border-color: transparent;}
        
        /* Main Layout */
        .main-wrapper { flex: 1; margin-left: 260px; display: flex; flex-direction: column; width: calc(100% - 260px); }
        .topbar { padding: 20px 40px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-base); position: sticky; top: 0; z-index: 50;}
        .page-title { font-size: 18px; font-weight: 600; }
        .user-avatar { width: 36px; height: 36px; background: var(--border-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; }
        .content { padding: 40px; max-width: 1200px; width: 100%; margin: 0 auto; }

        /* Mobile Adjustments */
        .menu-toggle { display: none; background: none; border: 1px solid var(--border-color); border-radius: 8px; color: #fff; width: 36px; height: 36px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 90; backdrop-filter: blur(2px);}
            .sidebar-overlay.show { display: block; }
            .main-wrapper { margin-left: 0; width: 100%; }
            .topbar { padding: 16px 20px; }
            .content { padding: 20px; }
        }
        @media(min-width: 901px){ .menu-toggle { display: none; } }

        /* Generic UI CSS */
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 30px; display: flex; flex-direction: column; }
        .card-p0 { padding: 0; overflow: hidden; }
        .card-header { padding: 20px 30px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }

        .stat-val { font-size: 42px; font-weight: 700; color: var(--text-primary); margin-top: 12px; line-height: 1; letter-spacing: -2px; }
        .stat-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }

        /* Tables Minimal */
        .table-responsive { overflow-x: auto; width: 100%; }
        .table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
        .table th { padding: 16px 30px; font-weight: 600; font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border-color); background: var(--bg-base); white-space: nowrap;}
        .table td { padding: 20px 30px; border-bottom: 1px solid var(--border-color); color: #ddd; vertical-align: middle; }
        .table tr:last-child td { border-bottom: none; }
        .table tr:hover td { background: rgba(255,255,255,0.02); }

        /* Forms */
        .form-group { margin-bottom: 24px; }
        .form-label { display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px; }
        .form-input { width: 100%; background: var(--bg-base); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: 8px; padding: 12px 16px; font-family: inherit; font-size: 14px; outline: none; transition: 0.2s; }
        .form-input:focus { border-color: #555; background: #000; }
        textarea.form-input { resize: vertical; min-height: 100px; }
        .form-hint { font-size: 12px; color: var(--text-muted); margin-top: 6px; display: block; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; transition: 0.2s; border: 1px solid transparent; text-decoration: none; font-family: inherit; white-space: nowrap;}
        .btn-primary { background: var(--text-primary); color: var(--bg-base); }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-outline { background: transparent; color: var(--text-primary); border-color: var(--border-color); }
        .btn-outline:hover { background: var(--bg-surface-hover); }
        .btn-danger-outline { background: transparent; color: var(--danger); border-color: var(--border-color); }
        .btn-danger-outline:hover { border-color: var(--danger); background: rgba(255,71,87,0.1); }
        .btn-icon { padding: 8px; min-width: 32px; height: 32px; font-size: 12px; border-radius: 6px; }

        /* Flex Utilities */
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 16px; }
        .gap-4 { gap: 24px; }
        .mb-2 { margin-bottom: 12px; }
        .mb-4 { margin-bottom: 30px; }
        .text-right { text-align: right; }

        /* Custom Modal */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); z-index: 1000; display: none; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; padding: 20px; }
        .modal-overlay.show { display: flex; opacity: 1; }
        .modal-box { background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); width: 100%; max-width: 600px; transform: scale(0.95); transition: transform 0.3s; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.5);}
        .modal-overlay.show .modal-box { transform: scale(1); }
        .modal-header { padding: 20px 30px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface); }
        .modal-title { font-size: 18px; font-weight: 600; }
        .modal-close { background: none; border: none; color: var(--text-muted); font-size: 24px; cursor: pointer; line-height: 1; padding: 0 5px;}
        .modal-close:hover { color: var(--text-primary); }
        .modal-body { padding: 30px; overflow-y: auto; flex: 1; }
        .modal-footer { padding: 20px 30px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 12px; background: var(--bg-base);}

        /* Custom Toast */
        .toast-container { position: fixed; bottom: 30px; right: 30px; z-index: 2000; display: flex; flex-direction: column; gap: 10px; pointer-events: none; }
        .toast { background: var(--bg-surface); border: 1px solid var(--border-color); padding: 16px 24px; border-radius: 12px; display: flex; align-items: center; gap: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); transform: translateY(150%); opacity: 0; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.success { border-bottom: 2px solid var(--success); }
        .toast.error { border-bottom: 2px solid var(--danger); }
        .toast-msg { font-size: 14px; font-weight: 500; }

        /* Grid Helper for Form */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
        .form-row .form-group { margin-bottom: 0; }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    
    <!-- Sidebar & Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <aside class="sidebar" id="sidebar">
        <a href="index.php" class="sidebar-brand">
            <span>WebSales.</span>
            <button class="menu-toggle" style="border:none;" onclick="toggleSidebar(event)">&times;</button>
        </a>
        <ul class="sidebar-menu">
            <li><a href="index.php" class="sidebar-link <?= $current_page == 'index.php' ? 'active' : '' ?>">Dashboard Overview</a></li>
            <li><a href="products.php" class="sidebar-link <?= strpos($current_page,'product') !== false ? 'active' : '' ?>">Product Catalog</a></li>
            <li><a href="faqs.php" class="sidebar-link <?= $current_page == 'faqs.php' ? 'active' : '' ?>">FAQ Manager</a></li>
            <li><a href="traffic.php" class="sidebar-link <?= $current_page == 'traffic.php' ? 'active' : '' ?>">Traffic Monitoring</a></li>
            <li><a href="settings.php" class="sidebar-link <?= $current_page == 'settings.php' ? 'active' : '' ?>">Settings & SEO</a></li>
        </ul>
        <div style="padding: 24px;">
            <a href="logout.php" class="sidebar-link text-danger" style="border: 1px solid var(--border-color); border-radius: 8px;">Log out secure</a>
        </div>
    </aside>

    <div class="main-wrapper">
        <header class="topbar">
            <div class="flex items-center gap-3">
                <button class="menu-toggle" onclick="toggleSidebar()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
                <h1 class="page-title"><?= ucfirst(str_replace('.php', '', $current_page)) ?></h1>
            </div>
            <div class="flex items-center gap-3">
                <span style="font-size:13px; color:var(--text-muted); font-weight:500;">Administrator</span>
                <div class="user-avatar">AD</div>
            </div>
        </header>
        <main class="content">
