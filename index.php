<?php
require 'config/database.php';
require 'includes/functions.php';

log_traffic($pdo);

$settings  = get_settings($pdo);
$products  = get_all_products($pdo);
$faqs      = get_all_faqs($pdo);

$site_title  = htmlspecialchars($settings['site_title'] ?? 'Premium Web');
$site_desc   = $settings['site_description'] ?? 'Solusi digital terpercaya untuk bisnis investasi &amp; web Anda.';
$meta_keys   = htmlspecialchars($settings['meta_keywords'] ?? '');
$contact_url = $settings['developer_contact'] ?? '#';
$wa_url      = $settings['wa_contact'] ?? '';
$tg_url      = $settings['telegram_contact'] ?? '';
// Fallback: jika wa/tg kosong, gunakan developer_contact lama
if (!$wa_url && !$tg_url) $wa_url = $contact_url;
$gv          = $settings['google_verification'] ?? '';
$ga          = $settings['google_analytics_id'] ?? '';
// Branding
$site_favicon = $settings['site_favicon'] ?? '';
$site_banner  = $settings['site_banner'] ?? '';
$og_image     = $settings['og_image'] ?? $site_banner;
$og_title     = $settings['og_title'] ?? $settings['site_title'] ?? '';
$og_desc      = $settings['og_description'] ?? $settings['site_description'] ?? '';

// Homepage copy — all with fallbacks so the old default text still shows if not set
$hero_eyebrow         = $settings['hero_eyebrow']         ?? 'Script Premium · Sewa Web · Jasa Custom';
$hero_title           = $settings['hero_title']           ?? 'Solusi Digital untuk Bisnis';
$hero_title_highlight = $settings['hero_title_highlight'] ?? 'Investasi &amp; Web';
$hero_subtitle        = $settings['hero_subtitle']        ?? $site_desc;
$hero_btn_primary     = $settings['hero_btn_primary']     ?? 'Lihat Produk &amp; Harga';
$hero_btn_secondary   = $settings['hero_btn_secondary']   ?? '💬 Konsultasi Gratis';
$services_eyebrow     = $settings['services_eyebrow']     ?? 'Layanan Kami';
$services_title       = $settings['services_title']       ?? 'Apa yang Kami Tawarkan?';
$services_subtitle    = $settings['services_subtitle']    ?? 'Dari script siap pakai hingga jasa custom penuh — kami siap jadi mitra digital Anda';
$products_eyebrow     = $settings['products_eyebrow']     ?? 'Produk';
$products_title       = $settings['products_title']       ?? 'Pilihan Script &amp; Paket';
$products_subtitle    = $settings['products_subtitle']    ?? 'Template premium dan script siap pakai, langsung bisa dipakai untuk bisnis Anda';
$contact_eyebrow      = $settings['contact_eyebrow']      ?? 'Hubungi Kami';
$contact_title        = $settings['contact_title']        ?? 'Siap Mulai? Tanya Dulu Gratis!';
$contact_subtitle     = $settings['contact_subtitle']     ?? 'Tidak ada kewajiban beli. Konsultasi via WA atau Telegram — kami respond cepat!';
$footer_text          = $settings['footer_text']          ?? 'All rights reserved.';
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $site_title ?> — Script Investasi & Jasa Web</title>
    <meta name="description" content="<?= htmlspecialchars(strip_tags($site_desc)) ?>">
    <meta name="keywords" content="<?= $meta_keys ?>">
    <?php if($gv): ?><meta name="google-site-verification" content="<?= htmlspecialchars($gv) ?>"><?php endif; ?>
    <!-- Favicon -->
    <?php if($site_favicon && file_exists(__DIR__.'/'.$site_favicon)): ?>
    <link rel="icon" href="<?= htmlspecialchars($site_favicon) ?>?v=<?= filemtime(__DIR__.'/'.$site_favicon) ?>">
    <link rel="shortcut icon" href="<?= htmlspecialchars($site_favicon) ?>">
    <?php endif; ?>
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= htmlspecialchars($og_title ?: $site_title) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($og_title ?: $site_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($og_desc ?: $site_desc) ?>">
    <?php if($og_image && file_exists(__DIR__.'/'.$og_image)): ?>
    <meta property="og:image" content="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . htmlspecialchars($og_image) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <?php endif; ?>
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($og_title ?: $site_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($og_desc ?: $site_desc) ?>">
    <?php if($og_image && file_exists(__DIR__.'/'.$og_image)): ?>
    <meta name="twitter:image" content="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . htmlspecialchars($og_image) ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ── SVG FLOATING BADGES ── */
        .floating-badge {
            position: absolute;
            pointer-events: none;
            opacity: 0.85;
            animation: floatBadge 6s ease-in-out infinite;
            filter: drop-shadow(0 8px 20px rgba(0,0,0,0.18));
        }
        .floating-badge:nth-child(2) { animation-delay: -2s; }
        .floating-badge:nth-child(3) { animation-delay: -4s; }

        @keyframes floatBadge {
            0%,100% { transform: translateY(0px) rotate(var(--rot,0deg)); }
            50%      { transform: translateY(-14px) rotate(var(--rot,0deg)); }
        }

        /* ── HERO ── */
        .hero {
            padding: 120px 0 80px;
            background: var(--hero-gradient);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero-bg-blur {
            position: absolute;
            top: -100px; left: 50%;
            transform: translateX(-50%);
            width: 700px; height: 700px;
            background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(99,102,241,0.1);
            border: 1px solid rgba(99,102,241,0.25);
            color: #818cf8;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 16px;
            border-radius: 30px;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .hero-title {
            font-size: clamp(34px, 6vw, 58px);
            font-weight: 800;
            line-height: 1.08;
            margin-bottom: 20px;
            letter-spacing: -2px;
        }
        .hero-title .highlight {
            background: linear-gradient(135deg, #818cf8 0%, #c084fc 50%, #f472b6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-subtitle {
            font-size: clamp(14px,2vw,17px);
            color: var(--text-muted);
            max-width: 520px;
            margin: 0 auto 36px;
            line-height: 1.7;
        }
        .hero-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
        .btn-hero-primary {
            padding: 15px 32px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 50px;
            background: linear-gradient(135deg, #818cf8, #c084fc);
            color: #fff;
            text-decoration: none;
            transition: 0.25s;
            box-shadow: 0 8px 24px rgba(129,140,248,0.35);
        }
        .btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(129,140,248,0.45); }
        .btn-hero-secondary {
            padding: 15px 32px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 50px;
            border: 1.5px solid var(--border);
            color: var(--text-primary);
            text-decoration: none;
            transition: 0.25s;
        }
        .btn-hero-secondary:hover { border-color: #818cf8; color: #818cf8; }

        /* ── STATS BAR ── */
        .stats-bar {
            padding: 40px 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            text-align: center;
          
        }
        .stat-item { padding: 10px; }
        .stat-num  { font-size: 32px; font-weight: 800; letter-spacing: -1px; }
        .stat-lbl  { font-size: 12px; color: var(--text-muted); margin-top: 4px; font-weight: 500; }

        /* ── SERVICES ── */
        .section-eyebrow {
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #818cf8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        .section-title { text-align: center; margin-bottom: 48px; }
        .section-title h2 { font-size: clamp(24px,4vw,34px); font-weight: 700; letter-spacing: -1px; margin-bottom: 10px; }
        .section-title p  { color: var(--text-muted); font-size: 15px; max-width: 480px; margin: 0 auto; }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 70px;
        
        }
        .service-card {
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            background: var(--bg-surface);
            transition: 0.3s;
            position: relative;
            overflow: hidden;
        }
        .service-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, #818cf8, #c084fc);
            opacity: 0;
            transition: 0.3s;
        }
        .service-card:hover { transform: translateY(-4px); border-color: rgba(129,140,248,0.3); }
        .service-card:hover::before { opacity: 1; }
        .service-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 18px; }
        .service-title { font-size: 16px; font-weight: 700; margin-bottom: 8px; }
        .service-desc  { font-size: 13px; color: var(--text-muted); line-height: 1.6; }

        /* ── PRODUCT GRID ── */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-right: 1rem;
            margin-left: 0.8rem;
        }

        /* ── CARD ── */
        .card {
            background: var(--bg-surface);
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid var(--border);
            transition: 0.3s;
            display: flex;
            flex-direction: column;
        }
        .card:hover { transform: translateY(-4px); border-color: rgba(129,140,248,0.3); box-shadow: 0 16px 40px rgba(0,0,0,0.08); }
        .card-img-wrapper {
            position: relative;
            width: 100%;
            overflow: hidden;
            background: var(--bg-surface);
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--border);
        }
        .card-img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: contain;
            transition: transform 0.4s ease;
        }
        .card:hover .card-img { transform: scale(1.03); }
        .badge { position:absolute; top:10px; right:10px; padding:3px 10px; border-radius:20px; font-size:10px; font-weight:700; backdrop-filter:blur(4px); }
        .badge-sale { background: linear-gradient(135deg, #f43f5e, #ec4899); color: #fff; }
        .card-body { padding: 16px; flex:1; display:flex; flex-direction:column; }
        .card-title { font-size:15px; font-weight:700; margin-bottom:4px; line-height:1.3; }
        .card-desc  { font-size:12px; color:var(--text-muted); margin-bottom:12px; flex:1; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        .card-price-row { margin-bottom:12px; }
        .old-price     { font-size:11px; color:var(--text-muted); text-decoration:line-through; margin-right:4px; }
        .current-price { font-size:17px; font-weight:800; letter-spacing:-0.5px; }
        .card-actions  { display:flex; gap:8px; }
        .btn-buy  { flex:1; text-align:center; padding:9px; border-radius:8px; font-size:12px; font-weight:700; text-decoration:none; transition:0.2s; background:var(--text-primary); color:var(--bg-base); border:1.5px solid var(--text-primary); }
        .btn-buy:hover { background:transparent; color:var(--text-primary); }
        .btn-demo { flex:1; text-align:center; padding:9px; border-radius:8px; font-size:12px; font-weight:600; text-decoration:none; transition:0.2s; background:transparent; color:var(--text-primary); border:1.5px solid var(--border); }
        .btn-demo:hover { background:var(--border); }

        /* ── CTA BANNER ── */
        .cta-section {
            margin: 70px 0;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #a21caf 100%);
            border-radius: 24px;
            padding: 60px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .cta-section::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='30'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .cta-section h2 { font-size: clamp(24px,4vw,36px); font-weight: 800; color: #fff; margin-bottom: 14px; letter-spacing: -1px; position:relative; }
        .cta-section p  { color: rgba(255,255,255,0.75); font-size: 16px; margin-bottom: 32px; position:relative; }
        .btn-cta {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 36px;
            background: #fff;
            color: #4f46e5;
            border-radius: 50px;
            font-weight: 800;
            font-size: 15px;
            text-decoration: none;
            transition: 0.25s;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        .btn-cta:hover { transform: translateY(-2px) scale(1.02); }

        /* ── FAQ ── */
        .faq-section { margin: 70px 0; }
        .faq-list { max-width: 760px; margin: 0 auto; }
        .faq-item {
            border: 1px solid var(--border);
            border-radius: 14px;
            margin-bottom: 12px;
            overflow: hidden;
            background: var(--bg-surface);
            transition: 0.2s;
        }
        .faq-item:hover { border-color: rgba(129,140,248,0.3); }
        .faq-question {
            width: 100%;
            background: none;
            border: none;
            padding: 20px 24px;
            text-align: left;
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            font-family: inherit;
            line-height: 1.4;
        }
        .faq-question:hover { color: #818cf8; }
        .faq-chevron {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            border: 1.5px solid var(--border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s, background 0.3s;
        }
        .faq-chevron svg { transition: transform 0.3s; }
        .faq-item.open .faq-chevron { background: #818cf8; border-color: #818cf8; }
        .faq-item.open .faq-chevron svg { transform: rotate(180deg); }
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, padding 0.3s;
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.7;
        }
        .faq-answer-inner { padding: 0 24px 20px; }

        /* ── FOOTER ── */
        .footer { padding: 40px 0; text-align: center; border-top: 1px solid var(--border); color: var(--text-muted); font-size: 13px; margin-top: 40px; }

        /* ── NAVBAR ── */
        .navbar { position:fixed; top:0; width:100%; z-index:1000; backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px); background:color-mix(in srgb, var(--bg-surface) 80%, transparent); border-bottom:1px solid var(--border); transition:all 0.3s; }
        .nav-container { display:flex; justify-content:space-between; align-items:center; height:62px; }
        .brand { font-size:18px; font-weight:800; color:var(--text-primary); text-decoration:none; letter-spacing:-0.8px; }
        .nav-links { display:flex; align-items:center; gap:20px; }
        .nav-links a { color:var(--text-primary); text-decoration:none; font-size:13px; font-weight:500; transition:color 0.2s; }
        .nav-links a:hover { color:var(--text-muted); }
        .theme-toggle { background:transparent; border:1px solid var(--border); color:var(--text-primary); cursor:pointer; display:flex; align-items:center; padding:7px; border-radius:50%; transition:0.2s; }
        .theme-toggle:hover { background:var(--border); }
        .btn-primary-sm { background:linear-gradient(135deg, #818cf8, #c084fc); color:#fff !important; padding:9px 18px; border-radius:30px; text-decoration:none; font-weight:700; display:inline-block; transition:0.2s; font-size:13px; }
        .btn-primary-sm:hover { opacity:0.9; transform:translateY(-1px); }

        main.main-content { padding:0px 0; min-height:50vh; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .product-grid { grid-template-columns: repeat(2,1fr); gap: 12px; }
            .card-title { font-size: 13px; }
            .current-price { font-size: 15px; }
            .card-desc { display: none; }
            .stats-grid { grid-template-columns: repeat(2,1fr); }
            .cta-section { padding: 40px 20px; }
            .services-grid { grid-template-columns: 1fr 1fr; gap: 12px;       margin-right: 1rem;
            margin-left: 0.8rem;}
            .service-card { padding: 18px 14px; }
            .service-icon { width: 38px; height: 38px; border-radius: 10px; margin-bottom: 12px; }
            .service-title { font-size: 13px; }
            .service-desc { font-size: 11px; line-height: 1.5; }
        }
        @media (max-width: 480px) {
            .hero-actions { flex-direction: column; align-items: center; }
            .product-grid { gap: 10px; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="container nav-container">
        <a href="#" class="brand"><?= htmlspecialchars(mb_substr($site_title, 0, 22)) ?></a>
        <div class="nav-links">
            <a href="#services" style="display:none;" id="navServices">Layanan</a>
            <a href="#products">Produk</a>
            <a href="#faq">FAQ</a>
            <a href="#contact" id="navContact">Kontak</a>
            <button class="theme-toggle" id="theme-btn" aria-label="Toggle Theme">
                <svg id="moon-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                <svg id="sun-icon"  width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </button>
        </div>
    </div>
</nav>

<!-- HERO -->
<header class="hero">
    <div class="hero-bg-blur"></div>

    <!-- Floating SVG Badges -->
    <!-- Badge: DISKON tag -->
    <svg class="floating-badge" style="top:14%; left:7%; --rot:-12deg; width:90px;" viewBox="0 0 90 90" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="90" height="90" rx="18" fill="url(#g1)" opacity="0.9"/>
        <path d="M44 22l7 7-20 20-7-7 20-20z" fill="white" opacity="0.6"/>
        <circle cx="38" cy="38" r="5" fill="white" opacity="0.8"/>
        <text x="45" y="68" text-anchor="middle" fill="white" font-size="11" font-weight="bold" font-family="sans-serif">SALE</text>
        <defs><linearGradient id="g1" x1="0" y1="0" x2="90" y2="90"><stop stop-color="#f43f5e"/><stop offset="1" stop-color="#ec4899"/></linearGradient></defs>
    </svg>

    <!-- Badge: Kado/Gift -->
    <svg class="floating-badge" style="top:12%; right:8%; --rot:10deg; width:80px;" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="80" height="80" rx="16" fill="url(#g2)" opacity="0.9"/>
        <rect x="20" y="38" width="40" height="24" rx="4" fill="white" opacity="0.3"/>
        <rect x="20" y="32" width="40" height="10" rx="3" fill="white" opacity="0.5"/>
        <rect x="36" y="24" width="8" height="38" rx="4" fill="white" opacity="0.5"/>
        <path d="M40 24 C35 18 26 22 30 28 C34 34 40 24 40 24z" fill="white" opacity="0.7"/>
        <path d="M40 24 C45 18 54 22 50 28 C46 34 40 24 40 24z" fill="white" opacity="0.7"/>
        <defs><linearGradient id="g2" x1="0" y1="0" x2="80" y2="80"><stop stop-color="#818cf8"/><stop offset="1" stop-color="#c084fc"/></linearGradient></defs>
    </svg>

    <!-- Badge: Bintang / Rating -->
    <svg class="floating-badge" style="bottom:18%; left:5%; --rot:8deg; width:75px;" viewBox="0 0 75 75" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="75" height="75" rx="15" fill="url(#g3)" opacity="0.9"/>
        <polygon points="37,18 42,30 56,30 45,39 49,52 37,44 25,52 29,39 18,30 32,30" fill="white" opacity="0.85"/>
        <defs><linearGradient id="g3" x1="0" y1="0" x2="75" y2="75"><stop stop-color="#f59e0b"/><stop offset="1" stop-color="#f97316"/></linearGradient></defs>
    </svg>

    <!-- Badge: Rocket / Launch -->
    <svg class="floating-badge" style="bottom:20%; right:6%; --rot:-8deg; width:78px;" viewBox="0 0 78 78" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="78" height="78" rx="16" fill="url(#g4)" opacity="0.9"/>
        <path d="M39 16 C39 16 52 20 52 35 L52 50 L39 58 L26 50 L26 35 C26 20 39 16 39 16z" fill="white" opacity="0.4"/>
        <circle cx="39" cy="34" r="7" fill="white" opacity="0.8"/>
        <path d="M29 50 L22 58 L29 55z" fill="white" opacity="0.6"/>
        <path d="M49 50 L56 58 L49 55z" fill="white" opacity="0.6"/>
        <defs><linearGradient id="g4" x1="0" y1="0" x2="78" y2="78"><stop stop-color="#06b6d4"/><stop offset="1" stop-color="#3b82f6"/></linearGradient></defs>
    </svg>

    <div class="container" style="position:relative;">
        <div class="hero-eyebrow">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor"><circle cx="6" cy="6" r="6"/></svg>
            <?= htmlspecialchars($hero_eyebrow) ?>
        </div>
        <h1 class="hero-title">
            <?= nl2br(htmlspecialchars($hero_title)) ?><span class="highlight"> <?= htmlspecialchars($hero_title_highlight) ?></span>
        </h1>
        <p class="hero-subtitle"><?= nl2br(htmlspecialchars($hero_subtitle)) ?></p>
        <div class="hero-actions">
            <a href="#products" class="btn-hero-primary"><?= htmlspecialchars($hero_btn_primary) ?></a>
            <button onclick="openContactModal('hero')" class="btn-hero-secondary" style="cursor:pointer;background:none;font-family:inherit;"><?= htmlspecialchars($hero_btn_secondary) ?></button>
        </div>
    </div>
</header>

<!-- STATS BAR -->
<div class="stats-bar">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-num" style="background:linear-gradient(135deg,#818cf8,#c084fc);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">50+</div>
                <div class="stat-lbl">Klien Puas</div>
            </div>
            <div class="stat-item">
                <div class="stat-num" style="background:linear-gradient(135deg,#f43f5e,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">100%</div>
                <div class="stat-lbl">Support Aktif</div>
            </div>
            <div class="stat-item">
                <div class="stat-num" style="background:linear-gradient(135deg,#10b981,#06b6d4);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">24/7</div>
                <div class="stat-lbl">Konsultasi</div>
            </div>
            <div class="stat-item">
                <div class="stat-num" style="background:linear-gradient(135deg,#f59e0b,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Fast</div>
                <div class="stat-lbl">Penyelesaian</div>
            </div>
        </div>
    </div>
</div>

<main class="main-content container">

    <!-- SERVICES -->
    <section id="services" style="padding-top:60px; margin-bottom:70px;">
        <div class="section-eyebrow"><?= htmlspecialchars($services_eyebrow) ?></div>
        <div class="section-title">
            <h2><?= htmlspecialchars($services_title) ?></h2>
            <p><?= htmlspecialchars($services_subtitle) ?></p>
        </div>
        <div class="services-grid">
            <!-- Investasi Script -->
            <div class="service-card">
                <div class="service-icon" style="background:rgba(129,140,248,0.15);">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <div class="service-title">Script Investasi / Ponzi</div>
                <div class="service-desc">Script website investasi lengkap: manajemen member, deposit, bonus referral, dan withdrawal otomatis. Siap pakai dan bisa dikustom.</div>
            </div>
            <!-- Sewa Web -->
            <div class="service-card">
                <div class="service-icon" style="background:rgba(192,132,252,0.15);">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#c084fc" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                </div>
                <div class="service-title">Sewa Website Investasi</div>
                <div class="service-desc">Tak mau beli script? Sewa saja! Website investasi siap pakai dengan domain & hosting — cocok untuk yang ingin trial bisnis online.</div>
            </div>
            <!-- Jasa Web -->
            <div class="service-card">
                <div class="service-icon" style="background:rgba(244,63,94,0.12);">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                </div>
                <div class="service-title">Jasa Pembuatan Web</div>
                <div class="service-desc">Butuh website custom sesuai bisnis? Kami kerjakan dari nol: company profile, landing page, toko online, hingga sistem khusus.</div>
            </div>
            <!-- Konsultasi -->
            <div class="service-card">
                <div class="service-icon" style="background:rgba(16,185,129,0.12);">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div class="service-title">Konsultasi Gratis</div>
                <div class="service-desc">Bingung mulai dari mana? Tanya dulu, gratis! Tidak ada kewajiban beli. Kami senang bantu Anda menemukan solusi terbaik.</div>
            </div>
        </div>
    </section>

    <!-- PRODUCTS -->
    <section id="products" style="margin-bottom: 70px;">
        <div class="section-eyebrow"><?= htmlspecialchars($products_eyebrow) ?></div>
        <div class="section-title">
            <h2><?= htmlspecialchars($products_title) ?></h2>
            <p><?= htmlspecialchars($products_subtitle) ?></p>
        </div>

        <div class="product-grid">
            <?php foreach($products as $p): ?>
            <div class="card" onclick="window.location.href='product.php?id=<?= $p['id'] ?>'" style="cursor:pointer;">
                <div class="card-img-wrapper">
                    <?php $fi = get_first_product_image($pdo, $p['id']); ?>
                    <?php if($fi): ?>
                        <img src="assets/uploads/<?= htmlspecialchars($fi) ?>" alt="<?= htmlspecialchars($p['title']) ?>" class="card-img" loading="lazy">
                    <?php else: ?>
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <?php endif; ?>
                    <?php if($p['promo_price'] > 0): ?>
                        <span class="badge badge-sale">DISKON</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h3 class="card-title"><?= htmlspecialchars($p['title']) ?></h3>
                    <p class="card-desc"><?= htmlspecialchars(mb_substr(strip_tags($p['description'] ?? ''), 0, 80)) ?></p>
                    <div class="card-price-row">
                        <?php if($p['promo_price'] > 0): ?>
                            <span class="old-price">Rp <?= number_format($p['price'],0,',','.') ?></span>
                            <span class="current-price">Rp <?= number_format($p['promo_price'],0,',','.') ?></span>
                        <?php else: ?>
                            <span class="current-price">Rp <?= number_format($p['price'],0,',','.') ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-actions">
                        <button class="btn-buy" onclick="event.stopPropagation(); openContactModal('product-card-<?= $p['id'] ?>')">Pesan</button>
                        <?php if(!empty($p['demo_link'])): ?>
                            <a href="<?= htmlspecialchars($p['demo_link']) ?>" class="btn-demo" target="_blank" onclick="event.stopPropagation()">Demo</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if(empty($products)): ?>
            <div style="grid-column:1/-1;text-align:center;padding:60px;color:var(--text-muted);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" opacity="0.3" style="margin-bottom:16px;"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                <p>Belum ada produk yang tersedia.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CONTACT / CTA (trigger modal) -->
    <section id="contact" style="margin: 70px 0; text-align:center;">
        <div class="section-eyebrow"><?= htmlspecialchars($contact_eyebrow) ?></div>
        <div class="section-title">
            <h2><?= htmlspecialchars($contact_title) ?></h2>
            <p><?= htmlspecialchars($contact_subtitle) ?></p>
        </div>
        <button onclick="openContactModal('cta-section')" style="
            padding:16px 40px; background:linear-gradient(135deg,#818cf8,#c084fc);
            color:#fff; border:none; border-radius:50px; font-size:15px;
            font-weight:700; cursor:pointer; font-family:inherit;
            box-shadow:0 8px 24px rgba(129,140,248,0.35); transition:0.25s;
        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 30px rgba(129,140,248,0.45)'"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 24px rgba(129,140,248,0.35)'">
            &#x1F4AC; Hubungi Kami Sekarang
        </button>
    </section>

    <!-- FAQ -->
    <section id="faq" class="faq-section">
        <div class="section-eyebrow">FAQ</div>
        <div class="section-title">
            <h2>Pertanyaan yang Sering Ditanya</h2>
            <p>Temukan jawaban cepat atas pertanyaan umum seputar layanan kami</p>
        </div>
        <div class="faq-list">
            <?php if(!empty($faqs)): ?>
            <?php foreach($faqs as $i => $faq): ?>
            <div class="faq-item" id="faq-<?= $faq['id'] ?>">
                <button class="faq-question" onclick="toggleFaq(<?= $faq['id'] ?>)">
                    <span><?= htmlspecialchars($faq['question']) ?></span>
                    <span class="faq-chevron">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="white" stroke-width="2"><polyline points="2,4 6,8 10,4"/></svg>
                    </span>
                </button>
                <div class="faq-answer" id="ans-<?= $faq['id'] ?>">
                    <div class="faq-answer-inner"><?= nl2br(htmlspecialchars($faq['answer'])) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p style="text-align:center; color:var(--text-muted);">FAQ belum tersedia. Silakan tambah di halaman admin.</p>
            <?php endif; ?>
        </div>
    </section>

</main>

<footer class="footer">
    <div class="container">
        <p>&copy; <?= date('Y') ?> <?= $site_title ?>. <?= htmlspecialchars($footer_text) ?></p>
    </div>
</footer>

<?php require 'includes/contact_modal.php'; ?>

<script src="assets/js/main.js"></script>
<?php if($ga): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($ga) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= htmlspecialchars($ga) ?>');</script>
<?php endif; ?>
<script>
if (window.innerWidth > 768) document.getElementById('navServices').style.display = 'inline';

// Navbar contact link opens modal
document.getElementById('navContact').addEventListener('click', function(e) {
    e.preventDefault();
    openContactModal('navbar');
});

// FAQ accordion
function toggleFaq(id) {
    var item = document.getElementById('faq-' + id);
    var ans  = document.getElementById('ans-' + id);
    var isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item.open').forEach(function(el) {
        el.classList.remove('open');
        el.querySelector('.faq-answer').style.maxHeight = '0';
    });
    if (!isOpen) {
        item.classList.add('open');
        ans.style.maxHeight = ans.scrollHeight + 'px';
    }
}
<?php if(!empty($faqs)): ?>
toggleFaq(<?= $faqs[0]['id'] ?>);
<?php endif; ?>
</script>

<style>
/* Contact Cards */
.contact-card {
    display: flex;
    align-items: center;
    gap: 18px;
    flex: 1 1 260px;
    max-width: 340px;
    padding: 22px 24px;
    border-radius: 16px;
    border: 1px solid var(--border);
    background: var(--bg-surface);
    text-decoration: none;
    color: var(--text-primary);
    transition: 0.25s;
    position: relative;
    overflow: hidden;
}
.contact-card::before {
    content:'';
    position:absolute; inset:0;
    opacity:0;
    transition:opacity 0.25s;
    background: linear-gradient(135deg, rgba(129,140,248,0.04), rgba(192,132,252,0.04));
}
.contact-card:hover { transform: translateY(-4px); border-color: rgba(129,140,248,0.35); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
.contact-card:hover::before { opacity: 1; }
.contact-icon { width: 60px; height: 60px; border-radius: 14px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.contact-info { flex:1; }
.contact-name { font-size: 17px; font-weight: 700; margin-bottom: 3px; }
.contact-sub  { font-size: 12px; color: var(--text-muted); }
.contact-arrow { color: var(--text-muted); transition: transform 0.2s; }
.contact-card:hover .contact-arrow { transform: translateX(4px); color: var(--text-primary); }
@media (max-width: 480px) {
    .contact-card { flex: 1 1 100%; max-width: 100%; }
}
</style>
</body>
</html>
