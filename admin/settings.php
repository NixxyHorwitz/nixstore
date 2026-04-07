<?php require 'includes/header.php'; ?>
<?php
$msg = '';
$msg_type = 'success';

// Helper: save a single uploaded file to assets/branding/, return path
function save_branding_file($file_key, $allowed = ['image/jpeg','image/png','image/gif','image/webp','image/x-icon','image/vnd.microsoft.icon']) {
    if (empty($_FILES[$file_key]['name'])) return null;
    $f    = $_FILES[$file_key];
    $mime = mime_content_type($f['tmp_name']);
    if (!in_array($mime, $allowed)) return false; // invalid type
    $ext  = pathinfo($f['name'], PATHINFO_EXTENSION);
    $name = $file_key . '_' . time() . '.' . $ext;
    $dest = __DIR__ . '/../assets/branding/' . $name;
    if (!move_uploaded_file($f['tmp_name'], $dest)) return false;
    return 'assets/branding/' . $name;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['form_action'] ?? 'general';

    if ($action === 'general') {
        $updates = [
            'site_title'         => $_POST['site_title'] ?? '',
            'site_description'   => $_POST['site_description'] ?? '',
            'meta_keywords'      => $_POST['meta_keywords'] ?? '',
            'developer_contact'  => $_POST['developer_contact'] ?? '',
            'wa_contact'         => trim($_POST['wa_contact'] ?? ''),
            'telegram_contact'   => trim($_POST['telegram_contact'] ?? ''),
        ];
        foreach ($updates as $key => $val) {
            $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
            $stmt->execute([$key, $val]);
        }
        $msg = "General settings saved!";
    }

    if ($action === 'branding') {
        // OG text fields
        $og_updates = [
            'og_title'       => trim($_POST['og_title'] ?? ''),
            'og_description' => trim($_POST['og_description'] ?? ''),
        ];
        foreach ($og_updates as $key => $val) {
            $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)")->execute([$key, $val]);
        }

        // Favicon upload
        $fav = save_branding_file('site_favicon', ['image/x-icon','image/vnd.microsoft.icon','image/png','image/gif','image/jpeg','image/webp']);
        if ($fav === false) { $msg = "Favicon: format file tidak didukung."; $msg_type = 'warning'; }
        elseif ($fav) {
            // delete old file if exists
            $old = $pdo->query("SELECT key_value FROM settings WHERE key_name='site_favicon'")->fetchColumn();
            if ($old && file_exists(__DIR__ . '/../' . $old)) @unlink(__DIR__ . '/../' . $old);
            $pdo->prepare("INSERT INTO settings (key_name,key_value) VALUES ('site_favicon',?) ON DUPLICATE KEY UPDATE key_value=VALUES(key_value)")->execute([$fav]);
        }

        // Banner upload
        $ban = save_branding_file('site_banner', ['image/jpeg','image/png','image/webp','image/gif']);
        if ($ban === false) { $msg = "Banner: format file tidak didukung (gunakan JPG/PNG/WEBP)."; $msg_type = 'warning'; }
        elseif ($ban) {
            $old = $pdo->query("SELECT key_value FROM settings WHERE key_name='site_banner'")->fetchColumn();
            if ($old && file_exists(__DIR__ . '/../' . $old)) @unlink(__DIR__ . '/../' . $old);
            $pdo->prepare("INSERT INTO settings (key_name,key_value) VALUES ('site_banner',?) ON DUPLICATE KEY UPDATE key_value=VALUES(key_value)")->execute([$ban]);
        }

        // OG Image upload (can be same as banner or separate)
        $ogi = save_branding_file('og_image', ['image/jpeg','image/png','image/webp']);
        if ($ogi === false) { $msg = "OG Image: format file tidak didukung (gunakan JPG/PNG/WEBP)."; $msg_type = 'warning'; }
        elseif ($ogi) {
            $old = $pdo->query("SELECT key_value FROM settings WHERE key_name='og_image'")->fetchColumn();
            if ($old && file_exists(__DIR__ . '/../' . $old)) @unlink(__DIR__ . '/../' . $old);
            $pdo->prepare("INSERT INTO settings (key_name,key_value) VALUES ('og_image',?) ON DUPLICATE KEY UPDATE key_value=VALUES(key_value)")->execute([$ogi]);
        }

        if (!$msg) $msg = "Branding & media berhasil disimpan!";
    }


    if ($action === 'seo_verification') {
        $updates = [
            'google_verification'  => trim($_POST['google_verification'] ?? ''),
            'google_analytics_id'  => trim($_POST['google_analytics_id'] ?? ''),
            'sitemap_url'          => trim($_POST['sitemap_url'] ?? ''),
        ];
        foreach ($updates as $key => $val) {
            $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
            $stmt->execute([$key, $val]);
        }

        // Handle HTML file verification
        $file_code = trim($_POST['google_verification_file'] ?? '');
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
        $stmt->execute(['google_verification_file', $file_code]);

        // Auto-generate the HTML file at root
        if (!empty($file_code)) {
            $filename = preg_match('/^google[a-f0-9]+$/', $file_code) ? $file_code . '.html' : null;
            if ($filename) {
                $filepath = __DIR__ . '/../' . $filename;
                $content  = "google-site-verification: {$filename}";
                file_put_contents($filepath, $content);
                $msg = "SEO verification saved! File '{$filename}' created at website root.";
            } else {
                $msg = "SEO settings saved. File code format invalid — file not generated. Use format: google1234abcd";
                $msg_type = 'warning';
            }
        } else {
            $msg = "SEO verification settings saved!";
        }
    }

    if ($action === 'robots') {
        $robots = $_POST['robots_txt'] ?? '';
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
        $stmt->execute(['robots_txt', $robots]);

        // Write robots.txt to root
        file_put_contents(__DIR__ . '/../robots.txt', $robots);
        $msg = "robots.txt saved and written to website root!";
    }
}

$settings = get_settings($pdo);
?>

<?php if ($msg): ?>
<div style="background: <?= $msg_type === 'warning' ? 'rgba(255,165,0,0.1)' : 'rgba(46,213,115,0.1)' ?>; color: <?= $msg_type === 'warning' ? '#e6a700' : 'var(--success)' ?>; padding: 16px; border-radius: 8px; margin-bottom: 30px; border: 1px solid <?= $msg_type === 'warning' ? 'rgba(255,165,0,0.2)' : 'rgba(46,213,115,0.2)' ?>; font-weight: 500;">
    <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<!-- Tab Navigation -->
<div style="display:flex; gap:8px; margin-bottom:30px; flex-wrap:wrap;">
    <button class="btn btn-primary tab-btn active" data-tab="tab-general" onclick="switchTab(this)">General</button>
    <button class="btn btn-outline tab-btn" data-tab="tab-branding" onclick="switchTab(this)">🖼 Branding & Media</button>
    <button class="btn btn-outline tab-btn" data-tab="tab-seo" onclick="switchTab(this)">SEO & Verification</button>
    <button class="btn btn-outline tab-btn" data-tab="tab-robots" onclick="switchTab(this)">Robots.txt</button>
</div>

<!-- ══════ TAB 1: GENERAL ══════ -->
<div class="tab-content" id="tab-general">
<div class="card" style="max-width: 800px;">
    <h3 style="font-weight:600; margin-bottom:24px;">General Configuration</h3>
    <form method="POST">
        <input type="hidden" name="form_action" value="general">
        <div class="form-group">
            <label class="form-label">Site Title</label>
            <input type="text" name="site_title" class="form-input" value="<?= htmlspecialchars($settings['site_title'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Site Meta Description</label>
            <textarea name="site_description" class="form-input"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Site Meta Keywords</label>
            <input type="text" name="meta_keywords" class="form-input" value="<?= htmlspecialchars($settings['meta_keywords'] ?? '') ?>">
            <span class="form-hint">Pisahkan dengan koma (contoh: template, php, modern, shop)</span>
        </div>
        <div class="form-group">
            <label class="form-label">WhatsApp URL</label>
            <input type="text" name="wa_contact" class="form-input" value="<?= htmlspecialchars($settings['wa_contact'] ?? '') ?>" placeholder="https://wa.me/628123456789">
            <span class="form-hint">Format: <code>https://wa.me/628xxxx</code> (tanpa tanda +). Kosongkan untuk menyembunyikan tombol WA.</span>
        </div>
        <div class="form-group">
            <label class="form-label">Telegram URL</label>
            <input type="text" name="telegram_contact" class="form-input" value="<?= htmlspecialchars($settings['telegram_contact'] ?? '') ?>" placeholder="https://t.me/username">
            <span class="form-hint">Format: <code>https://t.me/username</code>. Kosongkan untuk menyembunyikan tombol Telegram.</span>
        </div>
        <div class="form-group mb-4" style="display:none;">
            <label class="form-label">Developer Contact URL (Legacy)</label>
            <input type="text" name="developer_contact" class="form-input" value="<?= htmlspecialchars($settings['developer_contact'] ?? '') ?>" placeholder="https://wa.me/...">
            <span class="form-hint">Legacy field, dipakai sebagai fallback. Isi salah satu WA/Telegram di atas.</span>
        </div>
        <button type="submit" class="btn btn-primary">Save General Settings</button>
    </form>
</div>
</div>

<!-- ══════ TAB 2: SEO VERIFICATION ══════ -->
<div class="tab-content" id="tab-seo" style="display:none;">
<div class="card" style="max-width: 800px; margin-bottom: 24px;">
    <h3 style="font-weight:600; margin-bottom:8px;">Google Search Console</h3>
    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 24px;">Verifikasi kepemilikan website kamu di Google Search Console.</p>
    <form method="POST">
        <input type="hidden" name="form_action" value="seo_verification">

        <div class="form-group">
            <label class="form-label">Meta Tag Verification Code</label>
            <input type="text" name="google_verification" class="form-input" value="<?= htmlspecialchars($settings['google_verification'] ?? '') ?>" placeholder="contoh: ABcDeFgHiJkLmNoPqRsTuV">
            <span class="form-hint">Dari Google Search Console → Settings → Ownership verification → HTML tag.<br>Cukup isi bagian <code>content="..."</code> saja, tanpa tag meta lengkapnya.</span>
        </div>

        <div class="form-group">
            <label class="form-label">HTML File Verification Code</label>
            <input type="text" name="google_verification_file" class="form-input" value="<?= htmlspecialchars($settings['google_verification_file'] ?? '') ?>" placeholder="contoh: google1234567890abcdef">
            <span class="form-hint">Dari Google Search Console → HTML file upload method.<br>Isi kode file-nya saja (tanpa .html). Sistem akan otomatis membuat file di root website.</span>
        </div>

        <?php
        $vf = $settings['google_verification_file'] ?? '';
        if ($vf && file_exists(__DIR__ . '/../' . $vf . '.html')):
        ?>
        <div style="background: rgba(46,213,115,0.08); border: 1px solid rgba(46,213,115,0.2); border-radius: 8px; padding: 14px; margin-bottom: 24px; font-size: 13px;">
            <span style="color: var(--success); font-weight: 600;">✓ File verifikasi aktif:</span>
            <code style="margin-left: 8px; color: #ccc;"><?= htmlspecialchars($vf) ?>.html</code>
        </div>
        <?php endif; ?>

        <div style="border-top: 1px solid var(--border-color, #26262c); margin: 24px 0; padding-top: 24px;">
            <h3 style="font-weight:600; margin-bottom:8px;">Google Analytics</h3>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 24px;">Tracking pengunjung website secara otomatis.</p>
        </div>

        <div class="form-group">
            <label class="form-label">Google Analytics Measurement ID</label>
            <input type="text" name="google_analytics_id" class="form-input" value="<?= htmlspecialchars($settings['google_analytics_id'] ?? '') ?>" placeholder="contoh: G-XXXXXXXXXX">
            <span class="form-hint">Dari Google Analytics → Admin → Data Streams → Measurement ID.<br>Format: G-XXXXXXXXXX (GA4) atau UA-XXXXXXXXX-X (Universal).</span>
        </div>

        <div style="border-top: 1px solid var(--border-color, #26262c); margin: 24px 0; padding-top: 24px;">
            <h3 style="font-weight:600; margin-bottom:8px;">Sitemap</h3>
        </div>

        <div class="form-group mb-4">
            <label class="form-label">Sitemap URL</label>
            <input type="text" name="sitemap_url" class="form-input" value="<?= htmlspecialchars($settings['sitemap_url'] ?? '') ?>" placeholder="contoh: https://domain.com/sitemap.xml">
            <span class="form-hint">URL sitemap yang sudah di-submit ke Google Search Console.</span>
        </div>

        <button type="submit" class="btn btn-primary">Save SEO Settings</button>
    </form>
</div>
</div>

<!-- ══════ TAB 3: ROBOTS.TXT ══════ -->
<div class="tab-content" id="tab-robots" style="display:none;">
<div class="card" style="max-width: 800px;">
    <h3 style="font-weight:600; margin-bottom: 8px;">Robots.txt Editor</h3>
    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 24px;">Atur instruksi crawling untuk mesin pencari. Sistem akan menulis file <code>robots.txt</code> ke root website.</p>
    <form method="POST">
        <input type="hidden" name="form_action" value="robots">
        <div class="form-group mb-4">
            <label class="form-label">Content</label>
            <textarea name="robots_txt" class="form-input" rows="10" style="font-family: monospace; font-size: 13px;"><?= htmlspecialchars($settings['robots_txt'] ?? "User-agent: *\nAllow: /") ?></textarea>
            <span class="form-hint">Contoh dasar yang mengizinkan semua bot:<br><code>User-agent: *</code><br><code>Allow: /</code><br><code>Sitemap: https://domain.com/sitemap.xml</code></span>
        </div>
        <button type="submit" class="btn btn-primary">Save & Write robots.txt</button>
    </form>

    <?php if (file_exists(__DIR__ . '/../robots.txt')): ?>
    <div style="margin-top: 24px; background: rgba(46,213,115,0.08); border: 1px solid rgba(46,213,115,0.2); border-radius: 8px; padding: 14px; font-size: 13px;">
        <span style="color: var(--success); font-weight: 600;">✓ robots.txt exists at root</span>
        <code style="margin-left: 8px; color: #ccc;">Last modified: <?= date('d M Y H:i', filemtime(__DIR__ . '/../robots.txt')) ?></code>
    </div>
    <?php endif; ?>
</div>
</div>

<!-- ══════ TAB 4: BRANDING & MEDIA ══════ -->
<div class="tab-content" id="tab-branding" style="display:none;">
<div class="card" style="max-width: 800px;">
    <h3 style="font-weight:600; margin-bottom:8px;">Branding & Media</h3>
    <p style="color:var(--text-muted); font-size:13px; margin-bottom:28px;">Upload favicon, hero banner, dan gambar Open Graph (OG) untuk share preview di sosmed/WA.</p>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="form_action" value="branding">

        <!-- FAVICON -->
        <div style="border-bottom:1px solid var(--border-color); padding-bottom:24px; margin-bottom:24px;">
            <h4 style="font-size:14px; font-weight:600; margin-bottom:16px; color:var(--text-muted); letter-spacing:0.5px; text-transform:uppercase;">Favicon</h4>
            <?php $fav_path = $settings['site_favicon'] ?? ''; ?>
            <?php if($fav_path && file_exists(__DIR__.'/../'.$fav_path)): ?>
            <div style="margin-bottom:14px; display:flex; align-items:center; gap:14px;">
                <img src="../<?= htmlspecialchars($fav_path) ?>?v=<?= time() ?>" style="width:40px;height:40px;object-fit:contain;border-radius:6px;border:1px solid var(--border-color);background:#fff;padding:4px;">
                <span style="font-size:13px; color:var(--success);">✓ Favicon aktif</span>
            </div>
            <?php endif; ?>
            <div class="form-group mb-2">
                <label class="form-label">Upload Favicon Baru</label>
                <input type="file" name="site_favicon" class="form-input" accept=".ico,.png,.gif,.jpg,.webp">
                <span class="form-hint">Rekomendasi: file <code>.ico</code> atau PNG 32×32px / 64×64px. Format: ICO, PNG, GIF, WEBP, JPG.</span>
            </div>
        </div>

        <!-- HERO BANNER -->
        <div style="border-bottom:1px solid var(--border-color); padding-bottom:24px; margin-bottom:24px;">
            <h4 style="font-size:14px; font-weight:600; margin-bottom:16px; color:var(--text-muted); letter-spacing:0.5px; text-transform:uppercase;">Hero Banner</h4>
            <?php $ban_path = $settings['site_banner'] ?? ''; ?>
            <?php if($ban_path && file_exists(__DIR__.'/../'.$ban_path)): ?>
            <div style="margin-bottom:14px;">
                <img src="../<?= htmlspecialchars($ban_path) ?>?v=<?= time() ?>" style="width:100%;max-height:200px;object-fit:cover;border-radius:10px;border:1px solid var(--border-color);">
            </div>
            <?php endif; ?>
            <div class="form-group mb-2">
                <label class="form-label">Upload Banner Baru</label>
                <input type="file" name="site_banner" class="form-input" accept=".jpg,.jpeg,.png,.webp,.gif">
                <span class="form-hint">Banner yang ditampilkan di bagian hero halaman depan. Rekomendasi: 1200×600px, JPG/PNG/WEBP.</span>
            </div>
        </div>

        <!-- OG IMAGE -->
        <div style="border-bottom:1px solid var(--border-color); padding-bottom:24px; margin-bottom:24px;">
            <h4 style="font-size:14px; font-weight:600; margin-bottom:16px; color:var(--text-muted); letter-spacing:0.5px; text-transform:uppercase;">Open Graph Image</h4>
            <p style="font-size:12px; color:var(--text-muted); margin-bottom:14px;">Gambar yang muncul saat link website dibagikan di WhatsApp, Telegram, Facebook, Twitter, dll.</p>
            <?php $og_path = $settings['og_image'] ?? ''; ?>
            <?php if($og_path && file_exists(__DIR__.'/../'.$og_path)): ?>
            <div style="margin-bottom:14px;">
                <img src="../<?= htmlspecialchars($og_path) ?>?v=<?= time() ?>" style="width:100%;max-height:180px;object-fit:cover;border-radius:10px;border:1px solid var(--border-color);">
            </div>
            <?php endif; ?>
            <div class="form-group mb-2">
                <label class="form-label">Upload OG Image Baru</label>
                <input type="file" name="og_image" class="form-input" accept=".jpg,.jpeg,.png,.webp">
                <span class="form-hint">Rekomendasi: 1200×630px, JPG/PNG/WEBP. Isi beda dari banner jika perlu.</span>
            </div>
        </div>

        <!-- OG TEXT -->
        <div style="margin-bottom:24px;">
            <h4 style="font-size:14px; font-weight:600; margin-bottom:16px; color:var(--text-muted); letter-spacing:0.5px; text-transform:uppercase;">OG Title & Description</h4>
            <div class="form-group">
                <label class="form-label">OG Title (judul share preview)</label>
                <input type="text" name="og_title" class="form-input" value="<?= htmlspecialchars($settings['og_title'] ?? '') ?>" placeholder="Contoh: Jasa Script Investasi Terpercaya">
                <span class="form-hint">Jika kosong, akan menggunakan Site Title. Maksimal ~60 karakter.</span>
            </div>
            <div class="form-group mb-2">
                <label class="form-label">OG Description (deskripsi share preview)</label>
                <textarea name="og_description" class="form-input" rows="3" placeholder="Deskripsi singkat yang muncul saat link dibagikan..."><?= htmlspecialchars($settings['og_description'] ?? '') ?></textarea>
                <span class="form-hint">Jika kosong, akan menggunakan Site Description. Maksimal ~155 karakter.</span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">💾 Simpan Branding</button>
    </form>
</div>
</div>

<script>
function switchTab(btn) {
    // Deactivate all tabs
    document.querySelectorAll('.tab-btn').forEach(function(b) {
        b.classList.remove('active');
        b.classList.add('btn-outline');
        b.classList.remove('btn-primary');
    });
    document.querySelectorAll('.tab-content').forEach(function(c) {
        c.style.display = 'none';
    });

    // Activate clicked tab
    btn.classList.add('active');
    btn.classList.remove('btn-outline');
    btn.classList.add('btn-primary');
    document.getElementById(btn.getAttribute('data-tab')).style.display = 'block';
}
</script>

<?php require 'includes/footer.php'; ?>
