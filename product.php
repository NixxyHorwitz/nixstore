<?php
require 'config/database.php';
require 'includes/functions.php';

log_traffic($pdo);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = get_product($pdo, $id);

if (!$product) {
    header("Location: index.php");
    exit;
}

// Fetch recommendations
$stmt_rec = $pdo->prepare("SELECT * FROM products WHERE id != ? ORDER BY id DESC LIMIT 4");
$stmt_rec->execute([$id]);
$recs = $stmt_rec->fetchAll();

$settings    = get_settings($pdo);
$site_title  = htmlspecialchars($settings['site_title'] ?? 'Premium Web');
$contact_url = $settings['developer_contact'] ?? '#';
$wa_url      = $settings['wa_contact'] ?? '';
$tg_url      = $settings['telegram_contact'] ?? '';
if (!$wa_url && !$tg_url) $wa_url = $contact_url;
// Branding
$site_favicon = $settings['site_favicon'] ?? '';
$og_image_global = $settings['og_image'] ?? $settings['site_banner'] ?? '';
$og_title_set    = $settings['og_title'] ?? '';
$og_desc_set     = $settings['og_description'] ?? $settings['site_description'] ?? '';

$images = get_product_images($pdo, $id);
if (empty($images) && !empty($product['image'])) {
    $images[] = ['image_path' => $product['image']];
}
$has_multiple = count($images) > 1;
// OG image: use first product image if available, else fall back to global OG image
$prod_first_img = get_first_product_image($pdo, $id);
$og_img_url = '';
if ($prod_first_img && file_exists(__DIR__ . '/assets/uploads/' . $prod_first_img)) {
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $og_img_url = $base_url . '/assets/uploads/' . htmlspecialchars($prod_first_img);
} elseif ($og_image_global && file_exists(__DIR__ . '/' . $og_image_global)) {
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $og_img_url = $base_url . '/' . htmlspecialchars($og_image_global);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['title']) ?> — <?= $site_title ?></title>
    <meta name="description" content="<?= htmlspecialchars(mb_substr($product['description'] ?? '', 0, 160)) ?>">
    <?php $gv = $settings['google_verification'] ?? ''; if($gv): ?>
    <meta name="google-site-verification" content="<?= htmlspecialchars($gv) ?>">
    <?php endif; ?>
    <!-- Favicon -->
    <?php if($site_favicon && file_exists(__DIR__.'/'.$site_favicon)): ?>
    <link rel="icon" href="<?= htmlspecialchars($site_favicon) ?>">
    <link rel="shortcut icon" href="<?= htmlspecialchars($site_favicon) ?>">
    <?php endif; ?>
    <!-- Open Graph -->
    <meta property="og:type" content="product">
    <meta property="og:site_name" content="<?= $site_title ?>">
    <meta property="og:title" content="<?= htmlspecialchars($product['title']) ?> — <?= $site_title ?>">
    <meta property="og:description" content="<?= htmlspecialchars(mb_substr($product['description'] ?? '', 0, 160)) ?>">
    <?php if($og_img_url): ?>
    <meta property="og:image" content="<?= $og_img_url ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <?php endif; ?>
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($product['title']) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars(mb_substr($product['description'] ?? '', 0, 160)) ?>">
    <?php if($og_img_url): ?>
    <meta name="twitter:image" content="<?= $og_img_url ?>">
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /*
         * PRODUCT PAGE
         * Semua class pakai prefix "pv-" supaya zero conflict sama style.css
         */

        .pv-wrap {
            padding: 76px 16px 60px;
            max-width: 960px;
            margin: 0 auto;
        }

        .pv-grid {
            display: flex;
            gap: 36px;
        }

        /* ─── GALLERY ─── */
        .pv-gallery {
            flex: 1 1 55%;
            min-width: 0;
        }

        .pv-slider {
            width: 100%;
            aspect-ratio: 4 / 3;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid var(--border, #eaebef);
            background: var(--bg-surface, #fff);
        }

        .pv-slider .swiper,
        .pv-slider .swiper-wrapper,
        .pv-slider .swiper-slide {
            width: 100% !important;
            height: 100% !important;
        }

        .pv-slider .swiper-slide {
            display: flex !important;
            align-items: center;
            justify-content: center;
            padding: 16px;
            box-sizing: border-box;
        }

        .pv-slider .swiper-slide img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block;
            border-radius: 6px;
        }

        .pv-slider .swiper-pagination-bullet {
            background: rgba(128,128,128,0.5);
            opacity: 1;
            width: 6px;
            height: 6px;
        }
        .pv-slider .swiper-pagination-bullet-active {
            background: var(--accent, #212121);
            width: 18px;
            border-radius: 3px;
        }

        /* ─── DETAILS ─── */
        .pv-details {
            flex: 1 1 45%;
            min-width: 0;
        }

        .pv-title {
            font-size: 26px;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -.5px;
            margin-bottom: 14px;
            word-break: break-word;
        }

        .pv-prices {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 10px;
            padding-bottom: 16px;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--border, #eaebef);
        }

        .pv-price     { font-size: 24px; font-weight: 700; letter-spacing: -.5px; }
        .pv-old-price { font-size: 14px; color: var(--text-muted, #666); text-decoration: line-through; }

        .pv-desc {
            font-size: 14px;
            line-height: 1.7;
            color: var(--text-muted, #666);
            margin-bottom: 28px;
            white-space: pre-wrap;
            word-break: break-word;
        }
        
        .pv-desc.content-markup { line-height: 1.8; font-size: 15px; white-space: normal; }
        .pv-desc.content-markup p { margin-bottom: 15px; }
        .pv-desc.content-markup ul, .pv-desc.content-markup ol { margin-bottom: 15px; padding-left: 20px; }
        .pv-desc.content-markup li { margin-bottom: 6px; }
        .pv-desc.content-markup a { color: var(--text-primary); text-decoration: underline; }
        
        .pv-thumb-slide { width: 70px; height: 70px; cursor: pointer; opacity: 0.5; transition: opacity 0.2s; border-radius: 8px; overflow: hidden; border: 2px solid transparent; box-sizing: border-box; }
        .pv-thumb-slide.swiper-slide-thumb-active { opacity: 1; border-color: var(--text-primary, #111); }

        .pv-actions { display: flex; gap: 10px; }
        .pv-btn {
            flex: 1;
            padding: 13px 16px;
            text-align: center;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            font-family: inherit;
            cursor: pointer;
            transition: .15s;
        }
        .pv-btn-order { background: var(--accent, #212121); color: var(--bg-base, #f9f9fb); border: 2px solid var(--accent, #212121); }
        .pv-btn-order:hover { opacity: .85; }
        .pv-btn-demo  { background: transparent; color: var(--text-primary, #111); border: 2px solid var(--border, #eaebef); }
        .pv-btn-demo:hover { border-color: var(--accent, #212121); }

        /* ─── MOBILE ─── */
        @media (max-width: 680px) {
            .pv-grid    { flex-direction: column; gap: 20px; }
            .pv-wrap    { padding: 70px 14px 40px; }
            .pv-title   { font-size: 20px; }
            .pv-price   { font-size: 20px; }
            .pv-actions { flex-direction: column; }
            .pv-btn     { width: 100%; }
        }

        /* ─── FLOATING LEAVES ─── */
        .pv-leaves {
            position: fixed;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
            z-index: 0;
        }
        .pv-leaf {
            position: absolute;
            top: 0;
            will-change: transform, opacity;
            transform-origin: 50% 15%;
        }
        .pv-leaf svg {
            display: block;
            filter: drop-shadow(0 3px 6px rgba(0,0,0,.18));
        }
        /* Wind-blown falling: leaves drift sideways with gusts, tumble as they go */
        @keyframes leafDrop {
            0%   { transform: translate(0, -90px) rotate(0deg);                        opacity: 0;           }
            6%   { transform: translate(var(--x0), 6vh)  rotate(var(--a0))  scaleX(1); opacity: var(--lo);  }
            22%  { transform: translate(var(--x1), 22vh) rotate(var(--a1))  scaleX(0.96);                   }
            40%  { transform: translate(var(--x2), 41vh) rotate(var(--a2))  scaleX(1.02);                   }
            58%  { transform: translate(var(--x3), 59vh) rotate(var(--a3))  scaleX(0.97);                   }
            76%  { transform: translate(var(--x4), 77vh) rotate(var(--a4))  scaleX(1);                      }
            92%  { transform: translate(var(--x5), 93vh) rotate(var(--a5));             opacity: var(--lo); }
            100% { transform: translate(var(--x6), 112vh) rotate(var(--a6));            opacity: 0;          }
        }

        /* ─── LIGHTBOX ─── */
        .pv-slider .swiper-slide img { cursor: zoom-in; }

        #pvLightbox {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0,0,0,0.93);
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 14px;
            animation: lbFadeIn .18s ease;
        }
        #pvLightbox.open { display: flex; }
        @keyframes lbFadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        #pvLightbox .lb-img-wrap {
            max-width: 92vw;
            max-height: 82vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        #pvLightbox .lb-img-wrap img {
            max-width: 92vw;
            max-height: 82vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 8px 60px rgba(0,0,0,.7);
            user-select: none;
            display: block;
        }

        /* counter */
        #pvLbCounter {
            position: absolute;
            top: 14px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,.55);
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            padding: 4px 14px;
            border-radius: 20px;
            letter-spacing: .5px;
            pointer-events: none;
            white-space: nowrap;
        }

        /* close button */
        #pvLbClose {
            position: fixed;
            top: 16px;
            right: 20px;
            background: rgba(255,255,255,.12);
            border: none;
            color: #fff;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 22px;
            line-height: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s;
            z-index: 10000;
        }
        #pvLbClose:hover { background: rgba(255,255,255,.25); }

        /* prev / next */
        .lb-nav {
            position: fixed;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,.1);
            border: none;
            color: #fff;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s;
            z-index: 10000;
        }
        .lb-nav:hover { background: rgba(255,255,255,.25); }
        #pvLbPrev { left: 14px; }
        #pvLbNext { right: 14px; }
        .lb-nav.hidden { display: none; }

        /* thumbnail strip */
        #pvLbStrip {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            max-width: 90vw;
            padding: 4px 2px;
            scrollbar-width: none;
        }
        #pvLbStrip::-webkit-scrollbar { display: none; }
        #pvLbStrip img {
            width: 54px;
            height: 54px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid transparent;
            cursor: pointer;
            opacity: .55;
            transition: opacity .2s, border-color .2s;
            flex-shrink: 0;
        }
        #pvLbStrip img.active {
            opacity: 1;
            border-color: #fff;
        }
    </style>
</head>
<body>

<!-- ── FLOATING LEAVES ─────────────────────────────────────────────────── -->
<div class="pv-leaves" aria-hidden="true" id="pvLeaves"></div>

<nav class="navbar">
    <div class="container nav-container">
        <a href="index.php" class="brand">&larr; Kembali</a>
        <div class="nav-links">
            <button class="theme-toggle" id="theme-btn" aria-label="Toggle Theme">
                <svg id="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                <svg id="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </button>
        </div>
    </div>
</nav>

<div class="pv-wrap">
    <div class="pv-grid">

        <!-- GALLERY -->
        <div class="pv-gallery">
            <?php if (!empty($images)): ?>
            <div class="pv-slider">
                <div class="swiper" id="pvSwiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($images as $i => $img): ?>
                        <div class="swiper-slide">
                            <img src="assets/uploads/<?= htmlspecialchars($img['image_path']) ?>"
                                 alt="<?= htmlspecialchars($product['title']) ?>"
                                 onclick="pvLbOpen(<?= $i ?>)"
                                 title="Klik untuk perbesar">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <?php if ($has_multiple): ?>
            <div class="pv-thumbs" style="margin-top: 12px;">
                <div class="swiper" id="pvThumbsSwiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($images as $img): ?>
                        <div class="swiper-slide pv-thumb-slide">
                            <img src="assets/uploads/<?= htmlspecialchars($img['image_path']) ?>" 
                                 style="width:100%; height:100%; object-fit:cover;" 
                                 alt="<?= htmlspecialchars($product['title']) ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="pv-slider" style="display:flex;align-items:center;justify-content:center;">
                <span style="color:var(--text-muted);font-size:13px;">Belum ada gambar</span>
            </div>
            <?php endif; ?>
        </div>

        <!-- DETAILS -->
        <div class="pv-details">
            <h1 class="pv-title"><?= htmlspecialchars($product['title']) ?></h1>
            <div class="pv-prices">
                <?php if ($product['promo_price'] > 0): ?>
                    <span class="pv-old-price">Rp <?= number_format($product['price'],0,',','.') ?></span>
                    <span class="pv-price">Rp <?= number_format($product['promo_price'],0,',','.') ?></span>
                <?php else: ?>
                    <span class="pv-price">Rp <?= number_format($product['price'],0,',','.') ?></span>
                <?php endif; ?>
            </div>
            <div class="pv-desc content-markup"><?= !empty($product['description']) ? $product['description'] : '' ?></div>
            <div class="pv-actions">
                <button onclick="openContactModal('product-page-<?= $id ?>')" class="pv-btn pv-btn-order" style="cursor:pointer;border:none;font-family:inherit;">Pesan Sekarang</button>
                <?php if (!empty($product['demo_link'])): ?>
                <a href="<?= htmlspecialchars($product['demo_link']) ?>" class="pv-btn pv-btn-demo" target="_blank">Lihat Demo</a>
                <?php endif; ?>
                <?php
                // ── Custom Buttons ──────────────────────────────────────────
                $custom_btns = [];
                if (!empty($product['custom_buttons'])) {
                    $decoded = json_decode($product['custom_buttons'], true);
                    if (is_array($decoded)) $custom_btns = $decoded;
                }
                foreach ($custom_btns as $cbtn):
                    $clabel   = htmlspecialchars($cbtn['label'] ?? '');
                    $curl     = htmlspecialchars($cbtn['url']   ?? '#');
                    $cbg      = preg_replace('/[^#a-fA-F0-9]/', '', $cbtn['bg']    ?? '#6c63ff');
                    $cclr     = preg_replace('/[^#a-fA-F0-9]/', '', $cbtn['color'] ?? '#ffffff');
                    $cshape   = $cbtn['shape']   ?? 'rounded';
                    $cvariant = $cbtn['variant']  ?? 'solid';
                    $radius   = $cshape === 'pill' ? '50px' : ($cshape === 'square' ? '4px' : '10px');
                    if ($cvariant === 'outline') {
                        $cstyle = "background:transparent;color:{$cbg};border:2px solid {$cbg};border-radius:{$radius};";
                    } elseif ($cvariant === 'ghost') {
                        $cstyle = "background:{$cbg}22;color:{$cbg};border:2px solid transparent;border-radius:{$radius};";
                    } else {
                        $cstyle = "background:{$cbg};color:{$cclr};border:2px solid {$cbg};border-radius:{$radius};";
                    }
                    if ($clabel && $curl):
                ?>
                <a href="<?= $curl ?>" class="pv-btn" style="<?= $cstyle ?> padding:12px 24px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;transition:.2s;font-family:inherit;" target="_blank"><?= $clabel ?></a>
                <?php endif; endforeach; ?>
            </div>
        </div>

    </div>
</div>

<?php if (!empty($recs)): ?>
<div class="container" style="margin-bottom: 70px; margin-top: 20px;">
    <div style="font-size:20px; font-weight:700; margin-bottom: 24px; letter-spacing:-0.5px;">Rekomendasi Lainnya</div>
    <div class="product-grid">
        <?php foreach($recs as $r): ?>
        <div class="card" onclick="window.location.href='product.php?id=<?= $r['id'] ?>'" style="cursor:pointer;">
            <div class="card-img-wrapper">
                <?php $fi = get_first_product_image($pdo, $r['id']); ?>
                <?php if($fi): ?>
                    <img src="assets/uploads/<?= htmlspecialchars($fi) ?>" class="card-img" loading="lazy">
                <?php else: ?>
                    <span style="color:var(--text-muted);font-size:12px;">No Pic</span>
                <?php endif; ?>
                <?php if($r['promo_price'] > 0): ?>
                    <span class="badge badge-sale">DISKON</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <h3 class="card-title"><?= htmlspecialchars($r['title']) ?></h3>
                <div class="card-price-row">
                    <?php if($r['promo_price'] > 0): ?>
                        <span class="old-price">Rp <?= number_format($r['price'],0,',','.') ?></span>
                        <span class="current-price">Rp <?= number_format($r['promo_price'],0,',','.') ?></span>
                    <?php else: ?>
                        <span class="current-price">Rp <?= number_format($r['price'],0,',','.') ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-actions">
                    <button class="btn-buy" onclick="event.stopPropagation(); openContactModal('product-card-<?= $r['id'] ?>')">Pesan</button>
                    <?php if(!empty($r['demo_link'])): ?>
                        <a href="<?= htmlspecialchars($r['demo_link']) ?>" class="btn-demo" target="_blank" onclick="event.stopPropagation()">Demo</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<footer class="footer">
    <div class="container"><p>&copy; <?= date('Y') ?> <?= $site_title ?>. All rights reserved.</p></div>
</footer>

<?php require 'includes/contact_modal.php'; ?>

<!-- ── LIGHTBOX ───────────────────────────────────────────────── -->
<div id="pvLightbox" role="dialog" aria-modal="true" aria-label="Image viewer">
    <span id="pvLbCounter"></span>
    <button id="pvLbClose" aria-label="Tutup" onclick="pvLbClose()">&#10005;</button>
    <button id="pvLbPrev"  class="lb-nav" aria-label="Sebelumnya" onclick="pvLbMove(-1)">&#8592;</button>
    <button id="pvLbNext"  class="lb-nav" aria-label="Berikutnya"  onclick="pvLbMove(1)">&#8594;</button>
    <div class="lb-img-wrap">
        <img id="pvLbImg" src="" alt="">
    </div>
    <div id="pvLbStrip"></div>
</div>

<script src="assets/js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
// ─── Swiper init ──────────────────────────────────────────────────────────────
var _pvMainSwiper = null;
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('pvSwiper');
    if (!el) return;

    var thumbsEl = document.getElementById('pvThumbsSwiper');
    var thumbsSwiper = null;
    if (thumbsEl) {
        thumbsSwiper = new Swiper(thumbsEl, {
            spaceBetween: 10,
            slidesPerView: 'auto',
            freeMode: true,
            watchSlidesProgress: true,
        });
    }

    _pvMainSwiper = new Swiper(el, {
        slidesPerView  : 1,
        spaceBetween   : 0,
        grabCursor     : true,
        simulateTouch  : true,
        allowTouchMove : true,
        thumbs: thumbsSwiper ? { swiper: thumbsSwiper } : undefined
    });
});

// ─── Lightbox ─────────────────────────────────────────────────────────────────
(function() {
    // Collect all gallery image srcs from PHP-rendered slides
    var _imgs = [];
    var _cur  = 0;

    document.addEventListener('DOMContentLoaded', function() {
        // Build image list from swiper slides
        document.querySelectorAll('#pvSwiper .swiper-slide img').forEach(function(img) {
            _imgs.push(img.src);
        });

        // Build thumbnail strip
        var strip = document.getElementById('pvLbStrip');
        _imgs.forEach(function(src, i) {
            var t = document.createElement('img');
            t.src = src;
            t.alt = '';
            t.dataset.i = i;
            t.onclick = function() { pvLbGoto(i); };
            strip.appendChild(t);
        });

        // Backdrop click closes
        document.getElementById('pvLightbox').addEventListener('click', function(e) {
            if(e.target === this) pvLbClose();
        });

        // Keyboard
        document.addEventListener('keydown', function(e) {
            var lb = document.getElementById('pvLightbox');
            if(!lb.classList.contains('open')) return;
            if(e.key === 'Escape')      pvLbClose();
            if(e.key === 'ArrowLeft')   pvLbMove(-1);
            if(e.key === 'ArrowRight')  pvLbMove(1);
        });
    });

    window.pvLbOpen = function(index) {
        if(_imgs.length === 0) return;
        _cur = index;
        _render();
        document.getElementById('pvLightbox').classList.add('open');
        document.body.style.overflow = 'hidden';
    };

    window.pvLbClose = function() {
        document.getElementById('pvLightbox').classList.remove('open');
        document.body.style.overflow = '';
        // Sync main swiper to lightbox position
        if(_pvMainSwiper) _pvMainSwiper.slideTo(_cur, 0);
    };

    window.pvLbMove = function(dir) {
        _cur = (_cur + dir + _imgs.length) % _imgs.length;
        _render();
    };

    window.pvLbGoto = function(i) {
        _cur = i;
        _render();
    };

    function _render() {
        document.getElementById('pvLbImg').src = _imgs[_cur];
        document.getElementById('pvLbCounter').textContent = (_cur + 1) + ' / ' + _imgs.length;

        // Nav visibility
        var prev = document.getElementById('pvLbPrev');
        var next = document.getElementById('pvLbNext');
        if(_imgs.length <= 1) {
            prev.classList.add('hidden');
            next.classList.add('hidden');
        } else {
            prev.classList.remove('hidden');
            next.classList.remove('hidden');
        }

        // Thumbnail strip active state
        document.querySelectorAll('#pvLbStrip img').forEach(function(t, i) {
            t.classList.toggle('active', i === _cur);
        });
        // Scroll strip to active thumb
        var activeTh = document.querySelector('#pvLbStrip img.active');
        if(activeTh) activeTh.scrollIntoView({ inline: 'center', behavior: 'smooth', block: 'nearest' });
    }
})();
</script>

<!-- ── FLOATING LEAVES JS ─────────────────────────────────────────────── -->
<script>
(function(){
    var container = document.getElementById('pvLeaves');
    if(!container) return;

    function r(min, max){ return min + Math.random() * (max - min); }
    function ri(min, max){ return Math.round(r(min, max)); }

    // ── 5 REAL botanical leaf SVG builders ─────────────────────────────────────
    var leafTypes = [

        // 1 — Classic elongated leaf (most natural / universal)
        function(id, f1, f2, vn, w) {
            var h = Math.round(w * 1.85);
            return '<svg viewBox="0 0 34 60" width="'+w+'" height="'+h+'" xmlns="http://www.w3.org/2000/svg">'
                +'<defs><linearGradient id="g'+id+'" x1="0.3" y1="0" x2="0.7" y2="1">'
                +'<stop offset="0%" stop-color="'+f1+'"/>'
                +'<stop offset="100%" stop-color="'+f2+'"/>'
                +'</linearGradient></defs>'
                +'<path d="M17 2 C24 5 29 18 27 32 C25 45 21 54 17 57 C13 54 9 45 7 32 C5 18 10 5 17 2Z" fill="url(#g'+id+')"/>'
                +'<path d="M17 3 L17 56" stroke="'+vn+'" stroke-width="0.85" opacity="0.65" fill="none" stroke-linecap="round"/>'
                +'<path d="M17 15 Q12 13 9 11" stroke="'+vn+'" stroke-width="0.6" opacity="0.55" fill="none" stroke-linecap="round"/>'
                +'<path d="M17 24 Q11 22 8 20" stroke="'+vn+'" stroke-width="0.6" opacity="0.55" fill="none" stroke-linecap="round"/>'
                +'<path d="M17 33 Q12 31 9 30" stroke="'+vn+'" stroke-width="0.6" opacity="0.5"  fill="none" stroke-linecap="round"/>'
                +'<path d="M17 42 Q13 41 11 40" stroke="'+vn+'" stroke-width="0.55" opacity="0.45" fill="none" stroke-linecap="round"/>'
                +'<path d="M17 15 Q22 13 25 11" stroke="'+vn+'" stroke-width="0.6" opacity="0.55" fill="none" stroke-linecap="round"/>'
                +'<path d="M17 24 Q23 22 26 20" stroke="'+vn+'" stroke-width="0.6" opacity="0.55" fill="none" stroke-linecap="round"/>'
                +'<path d="M17 33 Q22 31 25 30" stroke="'+vn+'" stroke-width="0.6" opacity="0.5"  fill="none" stroke-linecap="round"/>'
                +'<path d="M17 42 Q21 41 23 40" stroke="'+vn+'" stroke-width="0.55" opacity="0.45" fill="none" stroke-linecap="round"/>'
                +'<path d="M17 57 Q15 62 13 66" stroke="'+vn+'" stroke-width="1.1" opacity="0.5" fill="none" stroke-linecap="round"/>'
                +'</svg>';
        },

        // 2 — Rounder shorter leaf (like ficus / lemon)
        function(id, f1, f2, vn, w) {
            var h = Math.round(w * 1.35);
            return '<svg viewBox="0 0 44 56" width="'+w+'" height="'+h+'" xmlns="http://www.w3.org/2000/svg">'
                +'<defs><linearGradient id="g'+id+'" x1="0.2" y1="0" x2="0.8" y2="1">'
                +'<stop offset="0%" stop-color="'+f1+'"/>'
                +'<stop offset="100%" stop-color="'+f2+'"/>'
                +'</linearGradient></defs>'
                +'<path d="M22 3 C34 3 41 12 40 24 C38 38 32 50 22 53 C12 50 6 38 4 24 C3 12 10 3 22 3Z" fill="url(#g'+id+')"/>'
                +'<path d="M22 4 L22 52" stroke="'+vn+'" stroke-width="0.9" opacity="0.6" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 14 Q15 11 11 10" stroke="'+vn+'" stroke-width="0.65" opacity="0.5" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 23 Q14 20 9 19"  stroke="'+vn+'" stroke-width="0.65" opacity="0.5" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 32 Q15 30 10 29" stroke="'+vn+'" stroke-width="0.6"  opacity="0.45" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 40 Q16 39 13 38" stroke="'+vn+'" stroke-width="0.55" opacity="0.4" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 14 Q29 11 33 10" stroke="'+vn+'" stroke-width="0.65" opacity="0.5" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 23 Q30 20 35 19"  stroke="'+vn+'" stroke-width="0.65" opacity="0.5" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 32 Q29 30 34 29" stroke="'+vn+'" stroke-width="0.6"  opacity="0.45" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 40 Q28 39 31 38" stroke="'+vn+'" stroke-width="0.55" opacity="0.4" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 52 Q20 57 18 61" stroke="'+vn+'" stroke-width="1.1" opacity="0.45" fill="none" stroke-linecap="round"/>'
                +'</svg>';
        },

        // 3 — Narrow willow / bamboo leaf
        function(id, f1, f2, vn, w) {
            var sw = Math.round(w * 0.55);
            var h  = Math.round(w * 2.4);
            return '<svg viewBox="0 0 22 70" width="'+sw+'" height="'+h+'" xmlns="http://www.w3.org/2000/svg">'
                +'<defs><linearGradient id="g'+id+'" x1="0.3" y1="0" x2="0.7" y2="1">'
                +'<stop offset="0%" stop-color="'+f1+'"/>'
                +'<stop offset="100%" stop-color="'+f2+'"/>'
                +'</linearGradient></defs>'
                +'<path d="M11 2 C16 7 18 20 17 38 C16 52 14 63 11 66 C8 63 6 52 5 38 C4 20 6 7 11 2Z" fill="url(#g'+id+')"/>'
                +'<path d="M11 3 L11 65" stroke="'+vn+'" stroke-width="0.8" opacity="0.6" fill="none" stroke-linecap="round"/>'
                +'<path d="M11 20 Q8 18 6 17"  stroke="'+vn+'" stroke-width="0.5" opacity="0.5" fill="none" stroke-linecap="round"/>'
                +'<path d="M11 20 Q14 18 16 17" stroke="'+vn+'" stroke-width="0.5" opacity="0.5" fill="none" stroke-linecap="round"/>'
                +'<path d="M11 35 Q8 33 5 32"  stroke="'+vn+'" stroke-width="0.5" opacity="0.5" fill="none" stroke-linecap="round"/>'
                +'<path d="M11 35 Q14 33 17 32" stroke="'+vn+'" stroke-width="0.5" opacity="0.5" fill="none" stroke-linecap="round"/>'
                +'<path d="M11 48 Q8 47 6 46"  stroke="'+vn+'" stroke-width="0.45" opacity="0.4" fill="none" stroke-linecap="round"/>'
                +'<path d="M11 48 Q14 47 16 46" stroke="'+vn+'" stroke-width="0.45" opacity="0.4" fill="none" stroke-linecap="round"/>'
                +'<path d="M11 65 Q10 69 9 73"  stroke="'+vn+'" stroke-width="1" opacity="0.4" fill="none" stroke-linecap="round"/>'
                +'</svg>';
        },

        // 4 — Heart-shaped leaf (like Judas tree)
        function(id, f1, f2, vn, w) {
            var h = Math.round(w * 1.5);
            return '<svg viewBox="0 0 44 60" width="'+w+'" height="'+h+'" xmlns="http://www.w3.org/2000/svg">'
                +'<defs><linearGradient id="g'+id+'" x1="0.3" y1="0" x2="0.7" y2="1">'
                +'<stop offset="0%" stop-color="'+f1+'"/>'
                +'<stop offset="100%" stop-color="'+f2+'"/>'
                +'</linearGradient></defs>'
                +'<path d="M22 8 C28 2 42 6 42 18 C42 30 34 42 22 55 C10 42 2 30 2 18 C2 6 16 2 22 8Z" fill="url(#g'+id+')"/>'
                +'<path d="M22 9 L22 53" stroke="'+vn+'" stroke-width="0.9" opacity="0.6" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 18 Q16 14 11 13" stroke="'+vn+'" stroke-width="0.65" opacity="0.5" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 27 Q14 24 9 23"  stroke="'+vn+'" stroke-width="0.65" opacity="0.5" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 36 Q16 34 12 33" stroke="'+vn+'" stroke-width="0.6"  opacity="0.45" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 44 Q17 43 14 42" stroke="'+vn+'" stroke-width="0.55" opacity="0.4" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 18 Q28 14 33 13" stroke="'+vn+'" stroke-width="0.65" opacity="0.5" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 27 Q30 24 35 23"  stroke="'+vn+'" stroke-width="0.65" opacity="0.5" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 36 Q28 34 32 33" stroke="'+vn+'" stroke-width="0.6"  opacity="0.45" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 44 Q27 43 30 42" stroke="'+vn+'" stroke-width="0.55" opacity="0.4" fill="none" stroke-linecap="round"/>'
                +'<path d="M22 54 Q21 59 19 63" stroke="'+vn+'" stroke-width="1.1" opacity="0.45" fill="none" stroke-linecap="round"/>'
                +'</svg>';
        },

        // 5 — Asymmetric wind-curled leaf
        function(id, f1, f2, vn, w) {
            var h = Math.round(w * 1.7);
            return '<svg viewBox="0 0 36 58" width="'+w+'" height="'+h+'" xmlns="http://www.w3.org/2000/svg">'
                +'<defs><linearGradient id="g'+id+'" x1="0.1" y1="0" x2="0.9" y2="1">'
                +'<stop offset="0%" stop-color="'+f1+'"/>'
                +'<stop offset="100%" stop-color="'+f2+'"/>'
                +'</linearGradient></defs>'
                +'<path d="M18 3 C26 5 32 16 31 28 C30 40 26 52 19 56 C13 53 8 42 7 30 C5 18 9 5 18 3Z" fill="url(#g'+id+')"/>'
                +'<path d="M18 4 Q17 30 19 55" stroke="'+vn+'" stroke-width="0.85" opacity="0.65" fill="none" stroke-linecap="round"/>'
                +'<path d="M18 14 Q13 12 10 11" stroke="'+vn+'" stroke-width="0.6" opacity="0.55" fill="none" stroke-linecap="round"/>'
                +'<path d="M18 14 Q23 12 27 11" stroke="'+vn+'" stroke-width="0.6" opacity="0.55" fill="none" stroke-linecap="round"/>'
                +'<path d="M18 23 Q12 21 8 20"  stroke="'+vn+'" stroke-width="0.6" opacity="0.5" fill="none" stroke-linecap="round"/>'
                +'<path d="M18 23 Q23 21 28 20"  stroke="'+vn+'" stroke-width="0.6" opacity="0.5" fill="none" stroke-linecap="round"/>'
                +'<path d="M18 32 Q13 30 9 29"  stroke="'+vn+'" stroke-width="0.55" opacity="0.45" fill="none" stroke-linecap="round"/>'
                +'<path d="M18 32 Q23 30 27 29"  stroke="'+vn+'" stroke-width="0.55" opacity="0.45" fill="none" stroke-linecap="round"/>'
                +'<path d="M18 41 Q14 40 11 39"  stroke="'+vn+'" stroke-width="0.5" opacity="0.4" fill="none" stroke-linecap="round"/>'
                +'<path d="M18 41 Q22 40 25 39"  stroke="'+vn+'" stroke-width="0.5" opacity="0.4" fill="none" stroke-linecap="round"/>'
                +'<path d="M19 55 Q18 60 16 65" stroke="'+vn+'" stroke-width="1.1" opacity="0.45" fill="none" stroke-linecap="round"/>'
                +'</svg>';
        }
    ];

    // Color triplets: [light fill, dark fill, vein color]
    var palettes = [
        ['#6ee75a','#2d8c1e','#1a5c0f'],  // vivid green
        ['#86efac','#22c55e','#14532d'],  // mint
        ['#a3e635','#65a30d','#3f6212'],  // lime-yellow
        ['#4ade80','#16a34a','#052e16'],  // emerald
        ['#bbf7d0','#34d399','#065f46'],  // pale mint
        ['#fde68a','#d97706','#78350f'],  // golden autumn
        ['#fed7aa','#ea580c','#7c2d12'],  // orange autumn
        ['#fca5a5','#dc2626','#7f1d1d'],  // red autumn
    ];

    var TOTAL = 16;

    for(var i = 0; i < TOTAL; i++){
        var leaf   = document.createElement('div');
        leaf.className = 'pv-leaf';

        var pal    = palettes[ri(0, palettes.length - 1)];
        var type   = i % leafTypes.length;
        var size   = ri(24, 46);
        var startX = r(2, 95);
        var dur    = r(9, 18);
        var delay  = -r(0, dur);
        var lo     = r(0.3, 0.65);

        // Wind drift: sinusoidal path with directional bias
        var windDir = Math.random() > 0.42 ? 1 : -1;
        var base    = windDir * r(30, 100);
        function wp(frac){ return Math.round(base * frac + r(-22, 22)); }
        var x0=wp(0.05), x1=wp(0.18), x2=wp(0.38), x3=wp(0.55), x4=wp(0.72), x5=wp(0.88), x6=wp(1);

        // Tumbling rotation along the wind path
        var baseAngle = windDir * r(15, 35);
        function ang(frac, e){ return Math.round(baseAngle * frac + e); }
        var a0=ang(0.1,r(-15,15)),  a1=ang(0.3,r(-30,30)),
            a2=ang(0.5,r(-45,45)),  a3=ang(0.65,r(-60,60)),
            a4=ang(0.8,r(-80,80)),  a5=ang(0.9,r(-90,90)),
            a6=ang(1.0,r(-120,120));

        leaf.style.cssText =
            'left:' + startX + '%;' +
            '--x0:'+x0+'px;--x1:'+x1+'px;--x2:'+x2+'px;--x3:'+x3+'px;--x4:'+x4+'px;--x5:'+x5+'px;--x6:'+x6+'px;' +
            '--a0:'+a0+'deg;--a1:'+a1+'deg;--a2:'+a2+'deg;--a3:'+a3+'deg;--a4:'+a4+'deg;--a5:'+a5+'deg;--a6:'+a6+'deg;' +
            '--lo:'+lo+';' +
            'animation:leafDrop '+dur.toFixed(1)+'s ease-in-out '+delay.toFixed(2)+'s infinite;';

        leaf.innerHTML = leafTypes[type](i, pal[0], pal[1], pal[2], size);
        container.appendChild(leaf);
    }
})();
</script>

<?php $ga = $settings['google_analytics_id'] ?? ''; if($ga): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($ga) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= htmlspecialchars($ga) ?>');</script>
<?php endif; ?>

</body>
</html>
