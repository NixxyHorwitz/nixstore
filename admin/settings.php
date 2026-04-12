<?php
@session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Helper: save a single uploaded file to assets/branding/, return path
function save_branding_file($file_key, $allowed = ['image/jpeg','image/png','image/gif','image/webp','image/x-icon','image/vnd.microsoft.icon']) {
    if (empty($_FILES[$file_key]['name'])) return null;
    $f = $_FILES[$file_key];
    
    // Check if upload was actually successful
    if ($f['error'] !== UPLOAD_ERR_OK) return false;

    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico'];

    // Get mime
    $mime = clone_mime_type($f);

    $allowed[] = 'image/x-png';
    $allowed[] = 'image/pjpeg';

    $isValid = false;
    if (in_array($mime, $allowed) || in_array($ext, $allowed_exts)) $isValid = true;
    if (!$isValid) return false;

    $name = $file_key . '_' . time() . '.' . $ext;
    $dest = __DIR__ . '/../assets/branding/' . $name;
    
    if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0755, true);
    if (!move_uploaded_file($f['tmp_name'], $dest)) return false;
    
    return 'assets/branding/' . $name;
}

function clone_mime_type($f) {
    if (function_exists('finfo_open')) {
        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) { $m = @finfo_file($finfo, $f['tmp_name']); finfo_close($finfo); if ($m) return $m; }
    }
    if (function_exists('mime_content_type')) { $m = @mime_content_type($f['tmp_name']); if ($m) return $m; }
    return $f['type'] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
    }
    
    header('Content-Type: application/json');
    $action = $_POST['action'];
    $msg = "Settings saved successfully";
    $status = "success";

    try {
        if ($action === 'general') {
            $updates = [
                'site_title'            => $_POST['site_title'] ?? '',
                'site_description'      => $_POST['site_description'] ?? '',
                'meta_keywords'         => $_POST['meta_keywords'] ?? '',
                'developer_contact'     => $_POST['developer_contact'] ?? '',
                'wa_contact'            => trim($_POST['wa_contact'] ?? ''),
                'telegram_contact'      => trim($_POST['telegram_contact'] ?? ''),
                // Homepage copy
                'hero_eyebrow'          => trim($_POST['hero_eyebrow'] ?? ''),
                'hero_title'            => trim($_POST['hero_title'] ?? ''),
                'hero_title_highlight'  => trim($_POST['hero_title_highlight'] ?? ''),
                'hero_subtitle'         => trim($_POST['hero_subtitle'] ?? ''),
                'hero_btn_primary'      => trim($_POST['hero_btn_primary'] ?? ''),
                'hero_btn_secondary'    => trim($_POST['hero_btn_secondary'] ?? ''),
                'services_eyebrow'      => trim($_POST['services_eyebrow'] ?? ''),
                'services_title'        => trim($_POST['services_title'] ?? ''),
                'services_subtitle'     => trim($_POST['services_subtitle'] ?? ''),
                'products_eyebrow'      => trim($_POST['products_eyebrow'] ?? ''),
                'products_title'        => trim($_POST['products_title'] ?? ''),
                'products_subtitle'     => trim($_POST['products_subtitle'] ?? ''),
                'cta_title'             => trim($_POST['cta_title'] ?? ''),
                'cta_subtitle'          => trim($_POST['cta_subtitle'] ?? ''),
                'contact_eyebrow'       => trim($_POST['contact_eyebrow'] ?? ''),
                'contact_title'         => trim($_POST['contact_title'] ?? ''),
                'contact_subtitle'      => trim($_POST['contact_subtitle'] ?? ''),
                'footer_text'           => trim($_POST['footer_text'] ?? ''),
            ];
            foreach ($updates as $key => $val) {
                $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)")->execute([$key, $val]);
            }
        }

        if ($action === 'branding') {
            $og_updates = [
                'og_title'       => trim($_POST['og_title'] ?? ''),
                'og_description' => trim($_POST['og_description'] ?? ''),
            ];
            foreach ($og_updates as $key => $val) {
                $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)")->execute([$key, $val]);
            }

            $fav = save_branding_file('site_favicon');
            if ($fav === false) { $status = 'warning'; $msg = "Favicon format invalid."; }
            elseif ($fav) {
                $old = $pdo->query("SELECT key_value FROM settings WHERE key_name='site_favicon'")->fetchColumn();
                if ($old && file_exists(__DIR__ . '/../' . $old)) @unlink(__DIR__ . '/../' . $old);
                $pdo->prepare("INSERT INTO settings (key_name,key_value) VALUES ('site_favicon',?) ON DUPLICATE KEY UPDATE key_value=VALUES(key_value)")->execute([$fav]);
            }

            $ban = save_branding_file('site_banner');
            if ($ban === false) { $status = 'warning'; $msg = "Banner format invalid."; }
            elseif ($ban) {
                $old = $pdo->query("SELECT key_value FROM settings WHERE key_name='site_banner'")->fetchColumn();
                if ($old && file_exists(__DIR__ . '/../' . $old)) @unlink(__DIR__ . '/../' . $old);
                $pdo->prepare("INSERT INTO settings (key_name,key_value) VALUES ('site_banner',?) ON DUPLICATE KEY UPDATE key_value=VALUES(key_value)")->execute([$ban]);
            }

            $ogi = save_branding_file('og_image');
            if ($ogi === false) { $status = 'warning'; $msg = "OG Image format invalid."; }
            elseif ($ogi) {
                $old = $pdo->query("SELECT key_value FROM settings WHERE key_name='og_image'")->fetchColumn();
                if ($old && file_exists(__DIR__ . '/../' . $old)) @unlink(__DIR__ . '/../' . $old);
                $pdo->prepare("INSERT INTO settings (key_name,key_value) VALUES ('og_image',?) ON DUPLICATE KEY UPDATE key_value=VALUES(key_value)")->execute([$ogi]);
            }
        }

        if ($action === 'seo_verification') {
            $updates = [
                'google_verification'  => trim($_POST['google_verification'] ?? ''),
                'google_analytics_id'  => trim($_POST['google_analytics_id'] ?? ''),
                'sitemap_url'          => trim($_POST['sitemap_url'] ?? ''),
            ];
            foreach ($updates as $key => $val) {
                $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)")->execute([$key, $val]);
            }

            $file_code = trim($_POST['google_verification_file'] ?? '');
            $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)")->execute(['google_verification_file', $file_code]);

            if (!empty($file_code)) {
                $filename = preg_match('/^google[a-f0-9]+$/', $file_code) ? $file_code . '.html' : null;
                if ($filename) {
                    $filepath = __DIR__ . '/../' . $filename;
                    file_put_contents($filepath, "google-site-verification: {$filename}");
                    $msg = "SEO Settings saved. File '{$filename}' created.";
                } else {
                    $msg = "SEO Settings saved. Note: File format invalid, file not generated.";
                    $status = 'warning';
                }
            }
        }

        if ($action === 'robots') {
            $robots = $_POST['robots_txt'] ?? '';
            $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)")->execute(['robots_txt', $robots]);
            file_put_contents(__DIR__ . '/../robots.txt', $robots);
        }

        echo json_encode(['status' => $status, 'message' => $msg]);
    } catch(Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}

if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }
require 'includes/header.php';
$settings = get_settings($pdo);
?>

<div class="page-header mb-4">
    <h1>Global Settings</h1>
    <div class="bc">Manage platform preferences, branding, and connectivity.</div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card-c sticky-top" style="top: 20px;">
            <div class="cb p-2">
                <div class="nav flex-column nav-pills" id="settingsTabs" role="tablist" aria-orientation="vertical">
                    <button class="nav-link active text-start mb-1" id="general-tab" data-bs-toggle="pill" data-bs-target="#tab-general" type="button" role="tab"><i class='bx bx-cog me-2'></i> General</button>
                    <button class="nav-link text-start mb-1" id="branding-tab" data-bs-toggle="pill" data-bs-target="#tab-branding" type="button" role="tab"><i class='bx bx-image me-2'></i> Branding</button>
                    <button class="nav-link text-start mb-1" id="seo-tab" data-bs-toggle="pill" data-bs-target="#tab-seo" type="button" role="tab"><i class='bx bx-search-alt me-2'></i> SEO & Auth</button>
                    <button class="nav-link text-start" id="robots-tab" data-bs-toggle="pill" data-bs-target="#tab-robots" type="button" role="tab"><i class='bx bx-bot me-2'></i> Robots.txt</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="tab-content" id="settingsTabsContent">
            
            <!-- GENERAL -->
            <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                <div class="card-c">
                    <div class="ch"><h3 class="m-0" style="font-size:16px;">General Configuration</h3></div>
                    <div class="cb">
                        <form class="ajax-form">
                            <input type="hidden" name="action" value="general">
                            
                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold">Site Title</label>
                                <input type="text" name="site_title" class="form-control" value="<?= htmlspecialchars($settings['site_title'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold">Site Meta Description</label>
                                <textarea name="site_description" class="form-control" rows="3"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold">Site Meta Keywords</label>
                                <input type="text" name="meta_keywords" class="form-control" value="<?= htmlspecialchars($settings['meta_keywords'] ?? '') ?>">
                                <div class="form-text" style="color:var(--mut);">Comma separated. Example: template, fast, modern</div>
                            </div>
                            
                            <hr style="border-color:var(--border); margin:24px 0;">
                            <h5 style="font-size:14px; font-weight:600; color:var(--text); margin-bottom:16px;">Contact Integration</h5>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold"><i class='bx bxl-whatsapp me-1'></i> WhatsApp URL</label>
                                <input type="text" name="wa_contact" class="form-control" value="<?= htmlspecialchars($settings['wa_contact'] ?? '') ?>" placeholder="https://wa.me/...">
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label text-muted fw-semibold"><i class='bx bxl-telegram me-1'></i> Telegram URL</label>
                                <input type="text" name="telegram_contact" class="form-control" value="<?= htmlspecialchars($settings['telegram_contact'] ?? '') ?>" placeholder="https://t.me/...">
                            </div>

                            <hr style="border-color:var(--border); margin:24px 0;">
                            <h5 style="font-size:14px; font-weight:600; color:var(--text); margin-bottom:4px;"><i class='bx bx-home-alt me-2'></i>Homepage Copy</h5>
                            <p style="font-size:12px;color:var(--mut);margin-bottom:18px;">Customise all text content shown on the landing page.</p>

                            <div class="mb-3 p-3" style="background:var(--hover);border-radius:10px;border:1px solid var(--border);">
                                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--accent);margin-bottom:12px;">Hero Section</div>
                                <div class="mb-2">
                                    <label class="form-label text-muted fw-semibold" style="font-size:11px;">Eyebrow Tag <span style="color:var(--mut);font-weight:400;">(small pill above title)</span></label>
                                    <input type="text" name="hero_eyebrow" class="form-control form-control-sm" value="<?= htmlspecialchars($settings['hero_eyebrow'] ?? 'Script Premium · Sewa Web · Jasa Custom') ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-muted fw-semibold" style="font-size:11px;">Title <span style="color:var(--mut);font-weight:400;">(before gradient text)</span></label>
                                    <input type="text" name="hero_title" class="form-control form-control-sm" value="<?= htmlspecialchars($settings['hero_title'] ?? 'Solusi Digital untuk Bisnis') ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-muted fw-semibold" style="font-size:11px;">Title Highlight <span style="color:var(--mut);font-weight:400;">(gradient colored part)</span></label>
                                    <input type="text" name="hero_title_highlight" class="form-control form-control-sm" value="<?= htmlspecialchars($settings['hero_title_highlight'] ?? 'Investasi &amp; Web') ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-muted fw-semibold" style="font-size:11px;">Subtitle</label>
                                    <textarea name="hero_subtitle" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($settings['hero_subtitle'] ?? '') ?></textarea>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label text-muted fw-semibold" style="font-size:11px;">Primary Button Text</label>
                                        <input type="text" name="hero_btn_primary" class="form-control form-control-sm" value="<?= htmlspecialchars($settings['hero_btn_primary'] ?? 'Lihat Produk &amp; Harga') ?>">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted fw-semibold" style="font-size:11px;">Secondary Button Text</label>
                                        <input type="text" name="hero_btn_secondary" class="form-control form-control-sm" value="<?= htmlspecialchars($settings['hero_btn_secondary'] ?? '💬 Konsultasi Gratis') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 p-3" style="background:var(--hover);border-radius:10px;border:1px solid var(--border);">
                                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--ok);margin-bottom:12px;">Services Section</div>
                                <div class="mb-2">
                                    <label class="form-label text-muted fw-semibold" style="font-size:11px;">Eyebrow</label>
                                    <input type="text" name="services_eyebrow" class="form-control form-control-sm" value="<?= htmlspecialchars($settings['services_eyebrow'] ?? 'Layanan Kami') ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-muted fw-semibold" style="font-size:11px;">Section Title</label>
                                    <input type="text" name="services_title" class="form-control form-control-sm" value="<?= htmlspecialchars($settings['services_title'] ?? 'Apa yang Kami Tawarkan?') ?>">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label text-muted fw-semibold" style="font-size:11px;">Subtitle</label>
                                    <input type="text" name="services_subtitle" class="form-control form-control-sm" value="<?= htmlspecialchars($settings['services_subtitle'] ?? 'Dari script siap pakai hingga jasa custom penuh — kami siap jadi mitra digital Anda') ?>">
                                </div>
                            </div>

                            <div class="mb-3 p-3" style="background:var(--hover);border-radius:10px;border:1px solid var(--border);">
                                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--warn);margin-bottom:12px;">Products Section</div>
                                <div class="mb-2">
                                    <label class="form-label text-muted fw-semibold" style="font-size:11px;">Eyebrow</label>
                                    <input type="text" name="products_eyebrow" class="form-control form-control-sm" value="<?= htmlspecialchars($settings['products_eyebrow'] ?? 'Produk') ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-muted fw-semibold" style="font-size:11px;">Section Title</label>
                                    <input type="text" name="products_title" class="form-control form-control-sm" value="<?= htmlspecialchars($settings['products_title'] ?? 'Pilihan Script &amp; Paket') ?>">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label text-muted fw-semibold" style="font-size:11px;">Subtitle</label>
                                    <input type="text" name="products_subtitle" class="form-control form-control-sm" value="<?= htmlspecialchars($settings['products_subtitle'] ?? 'Template premium dan script siap pakai, langsung bisa dipakai untuk bisnis Anda') ?>">
                                </div>
                            </div>

                            <div class="mb-3 p-3" style="background:var(--hover);border-radius:10px;border:1px solid var(--border);">
                                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--pur);margin-bottom:12px;">Contact &amp; CTA Section</div>
                                <div class="mb-2">
                                    <label class="form-label text-muted fw-semibold" style="font-size:11px;">Contact Eyebrow</label>
                                    <input type="text" name="contact_eyebrow" class="form-control form-control-sm" value="<?= htmlspecialchars($settings['contact_eyebrow'] ?? 'Hubungi Kami') ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-muted fw-semibold" style="font-size:11px;">Contact Title</label>
                                    <input type="text" name="contact_title" class="form-control form-control-sm" value="<?= htmlspecialchars($settings['contact_title'] ?? 'Siap Mulai? Tanya Dulu Gratis!') ?>">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label text-muted fw-semibold" style="font-size:11px;">Contact Subtitle</label>
                                    <input type="text" name="contact_subtitle" class="form-control form-control-sm" value="<?= htmlspecialchars($settings['contact_subtitle'] ?? 'Tidak ada kewajiban beli. Konsultasi via WA atau Telegram — kami respond cepat!') ?>">
                                </div>
                            </div>

                            <div class="mb-4 p-3" style="background:var(--hover);border-radius:10px;border:1px solid var(--border);">
                                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--sub);margin-bottom:12px;">Footer</div>
                                <div class="mb-0">
                                    <label class="form-label text-muted fw-semibold" style="font-size:11px;">Footer Additional Text <span style="color:var(--mut);font-weight:400;">(optional, shown after copyright)</span></label>
                                    <input type="text" name="footer_text" class="form-control form-control-sm" value="<?= htmlspecialchars($settings['footer_text'] ?? '') ?>" placeholder="All rights reserved.">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-save w-100">Save General Settings</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- BRANDING -->
            <div class="tab-pane fade" id="tab-branding" role="tabpanel">
                <div class="card-c">
                    <div class="ch"><h3 class="m-0" style="font-size:16px;">Branding & Media</h3></div>
                    <div class="cb">
                        <form class="ajax-form" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="branding">
                            
                            <!-- Favicon -->
                            <div class="mb-4 d-flex align-items-center gap-3">
                                <?php $fav_path = $settings['site_favicon'] ?? ''; ?>
                                <?php if($fav_path && file_exists(__DIR__.'/../'.$fav_path)): ?>
                                    <div style="width:48px;height:48px;background:#fff;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><img src="../<?= htmlspecialchars($fav_path) ?>?v=<?= time() ?>" style="width:24px;height:24px;"></div>
                                <?php else: ?>
                                    <div style="width:48px;height:48px;background:var(--hover);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--mut);"><i class='bx bx-image'></i></div>
                                <?php endif; ?>
                                <div class="w-100">
                                    <label class="form-label text-muted fw-semibold mb-1">Site Favicon</label>
                                    <input type="file" name="site_favicon" class="form-control form-control-sm" accept=".ico,.png,.webp">
                                </div>
                            </div>
                            
                            <!-- Banner -->
                            <div class="mb-4">
                                <label class="form-label text-muted fw-semibold mb-2">Hero Banner</label>
                                <?php $ban_path = $settings['site_banner'] ?? ''; ?>
                                <?php if($ban_path && file_exists(__DIR__.'/../'.$ban_path)): ?>
                                <div class="mb-2"><img src="../<?= htmlspecialchars($ban_path) ?>?v=<?= time() ?>" style="width:100%;max-height:180px;object-fit:cover;border-radius:8px;border:1px solid var(--border);"></div>
                                <?php endif; ?>
                                <input type="file" name="site_banner" class="form-control form-control-sm" accept=".jpg,.png,.webp">
                            </div>

                            <!-- OG Image -->
                            <div class="mb-4">
                                <label class="form-label text-muted fw-semibold mb-2">Open Graph (Social Share) Image</label>
                                <?php $og_path = $settings['og_image'] ?? ''; ?>
                                <?php if($og_path && file_exists(__DIR__.'/../'.$og_path)): ?>
                                <div class="mb-2"><img src="../<?= htmlspecialchars($og_path) ?>?v=<?= time() ?>" style="width:100%;max-height:180px;object-fit:cover;border-radius:8px;border:1px solid var(--border);"></div>
                                <?php endif; ?>
                                <input type="file" name="og_image" class="form-control form-control-sm" accept=".jpg,.png,.webp">
                            </div>
                            
                            <hr style="border-color:var(--border); margin:24px 0;">
                            
                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold">OG Title</label>
                                <input type="text" name="og_title" class="form-control" value="<?= htmlspecialchars($settings['og_title'] ?? '') ?>" placeholder="Defaults to Site Title if empty">
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted fw-semibold">OG Description</label>
                                <textarea name="og_description" class="form-control" rows="2" placeholder="Defaults to Meta Description if empty"><?= htmlspecialchars($settings['og_description'] ?? '') ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-save w-100">Save Media Assets</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="tab-pane fade" id="tab-seo" role="tabpanel">
                <div class="card-c">
                    <div class="ch"><h3 class="m-0" style="font-size:16px;">SEO & Service Integrations</h3></div>
                    <div class="cb">
                        <form class="ajax-form">
                            <input type="hidden" name="action" value="seo_verification">
                            
                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold">GSC Meta Tag</label>
                                <input type="text" name="google_verification" class="form-control" value="<?= htmlspecialchars($settings['google_verification'] ?? '') ?>" placeholder="ABcDeFgHiJkLmNoPqRsTuV">
                                <div class="form-text" style="color:var(--mut);">Search Console verification tag content.</div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label text-muted fw-semibold">GSC HTML File</label>
                                <input type="text" name="google_verification_file" class="form-control" value="<?= htmlspecialchars($settings['google_verification_file'] ?? '') ?>" placeholder="google1234567890abcdef">
                                <?php $vf = $settings['google_verification_file'] ?? ''; ?>
                                <?php if($vf && file_exists(__DIR__ . '/../' . $vf . '.html')): ?>
                                <div class="mt-2 bd bd-ok text-center" style="font-size:12px;">File Active: <?= htmlspecialchars($vf) ?>.html</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted fw-semibold">Google Analytics ID</label>
                                <input type="text" name="google_analytics_id" class="form-control" value="<?= htmlspecialchars($settings['google_analytics_id'] ?? '') ?>" placeholder="G-XXXXXXXXXX">
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label text-muted fw-semibold">Sitemap URL</label>
                                <input type="text" name="sitemap_url" class="form-control" value="<?= htmlspecialchars($settings['sitemap_url'] ?? '') ?>" placeholder="https://domain.com/sitemap.xml">
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-save w-100">Save Integrations</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ROBOTS -->
            <div class="tab-pane fade" id="tab-robots" role="tabpanel">
                <div class="card-c">
                    <div class="ch"><h3 class="m-0" style="font-size:16px;">Robots.txt Configuration</h3></div>
                    <div class="cb">
                        <form class="ajax-form">
                            <input type="hidden" name="action" value="robots">
                            <div class="mb-4">
                                <textarea name="robots_txt" class="form-control" rows="10" style="font-family:monospace; background:var(--bg); border:1px solid var(--border); color:var(--text);"><?= htmlspecialchars($settings['robots_txt'] ?? "User-agent: *\nAllow: /") ?></textarea>
                            </div>
                            <?php if (file_exists(__DIR__ . '/../robots.txt')): ?>
                            <div class="mb-3 text-center" style="font-size:12px; color:var(--mut);">
                                File exists on server. Last modified: <?= date('d M Y H:i', filemtime(__DIR__ . '/../robots.txt')) ?>
                            </div>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary btn-save w-100">Write Robots.txt</button>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<style>
    .nav-pills .nav-link { color: var(--mut); padding: 12px 16px; border-radius: 8px; transition: all 0.2s; font-weight: 500; font-size:14px; }
    .nav-pills .nav-link:hover { color: var(--text); background: var(--hover); }
    .nav-pills .nav-link.active { background: var(--accent) !important; color: #fff !important; }
</style>

<script>
$(document).ready(function() {
    $('.ajax-form').on('submit', function(e) {
        e.preventDefault();
        let $form = $(this);
        let $btn = $form.find('.btn-save');
        let ogText = $btn.html();
        
        $btn.prop('disabled', true).html("<i class='bx bx-loader-alt bx-spin me-2'></i> Saving...");
        let fd = new FormData(this);
        
        $.ajax({
            url: 'settings.php',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(res) {
                $btn.prop('disabled', false).html(ogText);
                if(res.status === 'success') {
                    Toast.fire({ icon: 'success', title: res.message });
                    if(fd.get('action') === 'branding') setTimeout(()=>location.reload(), 1000);
                } else if (res.status === 'warning') {
                    Swal.fire({ icon: 'warning', title: 'Notice', text: res.message, background: 'var(--surface)', color: 'var(--text)' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: 'var(--surface)', color: 'var(--text)' });
                }
            },
            error: function() {
                $btn.prop('disabled', false).html(ogText);
                Toast.fire({ icon: 'error', title: 'Network Error' });
            }
        });
    });
});
</script>

<?php require 'includes/footer.php'; ?>
