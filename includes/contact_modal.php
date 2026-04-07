<?php
/**
 * includes/contact_modal.php
 * Global contact picker modal — include ONCE per page, before </body>
 * Reads $settings (must already be loaded), outputs modal HTML + JS
 */
$_cwa = $settings['wa_contact'] ?? '';
$_ctg = $settings['telegram_contact'] ?? '';
// Fallback jika keduanya kosong
if (!$_cwa && !$_ctg) {
    $_cwa = $settings['developer_contact'] ?? '#';
}
$_only_one = (!$_cwa || !$_ctg); // hanya satu pilihan
?>

<!-- ═══════════════════════════════════════════════
     GLOBAL CONTACT PICKER MODAL
     Trigger: openContactModal('source-label')
     ═══════════════════════════════════════════════ -->
<div id="contactModalOverlay" style="
    display:none; position:fixed; inset:0; z-index:9999;
    background:rgba(0,0,0,0.65); backdrop-filter:blur(6px);
    align-items:flex-end; justify-content:center;
    padding:0;
" onclick="if(event.target===this) closeContactModal()">

    <div id="contactModalSheet" style="
        background: var(--bg-surface, #121216);
        border: 1px solid var(--border-color, #26262c);
        border-radius: 24px 24px 0 0;
        width: 100%; max-width: 520px;
        padding: 32px 28px 36px;
        transform: translateY(100%);
        transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1);
        position: relative;
        box-shadow: 0 -20px 60px rgba(0,0,0,0.4);
    ">
        <!-- Drag handle -->
        <div style="width:40px;height:4px;background:var(--border-color,#333);border-radius:4px;margin:0 auto 24px;"></div>

        <!-- Header -->
        <div style="margin-bottom:6px;">
            <div style="font-size:11px;font-weight:700;color:#818cf8;letter-spacing:1px;text-transform:uppercase;margin-bottom:8px;">Yuk, ngobrol dulu! &#x1F44B;</div>
            <h3 style="font-size:20px;font-weight:700;margin:0;letter-spacing:-0.5px;color:var(--text-primary,#fff);">Mau Lewat Mana?</h3>
            <p style="font-size:13px;color:var(--text-muted,#888);margin:6px 0 0;">Santai aja, nggak ada yang dipaksa beli. Pilih yang paling nyaman buat kamu &#x1F60A;</p>
        </div>

        <!-- Contact Cards -->
        <div id="contactChoices" style="display:flex;flex-direction:column;gap:12px;margin-top:24px;">

            <?php if($_cwa): ?>
            <a href="<?= htmlspecialchars($_cwa) ?>" target="_blank" rel="noopener"
               id="cm-wa-btn"
               onclick="cmTrack('whatsapp')"
               style="
                display:flex; align-items:center; gap:16px;
                padding:18px 20px; border-radius:14px;
                background:rgba(37,211,102,0.07);
                border:1.5px solid rgba(37,211,102,0.2);
                text-decoration:none; color:var(--text-primary,#fff);
                transition:0.2s; cursor:pointer;
               "
               onmouseover="this.style.background='rgba(37,211,102,0.14)'; this.style.borderColor='rgba(37,211,102,0.5)'; this.style.transform='translateY(-2px)'"
               onmouseout="this.style.background='rgba(37,211,102,0.07)'; this.style.borderColor='rgba(37,211,102,0.2)'; this.style.transform='translateY(0)'">
                <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#25d366,#128C7E);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="white">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.787"/>
                    </svg>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:700;font-size:16px;color:#25d366;margin-bottom:2px;">WhatsApp</div>
                    <div style="font-size:12px;color:var(--text-muted,#888);">WA-in langsung, bales cepet! &#x26A1;</div>
                </div>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgba(37,211,102,0.6)" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <?php endif; ?>

            <?php if($_ctg): ?>
            <a href="<?= htmlspecialchars($_ctg) ?>" target="_blank" rel="noopener"
               id="cm-tg-btn"
               onclick="cmTrack('telegram')"
               style="
                display:flex; align-items:center; gap:16px;
                padding:18px 20px; border-radius:14px;
                background:rgba(42,171,238,0.07);
                border:1.5px solid rgba(42,171,238,0.2);
                text-decoration:none; color:var(--text-primary,#fff);
                transition:0.2s; cursor:pointer;
               "
               onmouseover="this.style.background='rgba(42,171,238,0.14)'; this.style.borderColor='rgba(42,171,238,0.5)'; this.style.transform='translateY(-2px)'"
               onmouseout="this.style.background='rgba(42,171,238,0.07)'; this.style.borderColor='rgba(42,171,238,0.2)'; this.style.transform='translateY(0)'">
                <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#2AABEE,#229ED9);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="white">
                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                    </svg>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:700;font-size:16px;color:#2AABEE;margin-bottom:2px;">Telegram</div>
                    <div style="font-size:12px;color:var(--text-muted,#888);">Ping di Telegram, kami gas! &#x1F680;</div>
                </div>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgba(42,171,238,0.6)" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <?php endif; ?>

        </div>

        <!-- Cancel -->
        <button onclick="closeContactModal()" style="
            display:block; width:100%; margin-top:16px;
            background:transparent; border:1px solid var(--border-color,#333);
            color:var(--text-muted,#888); border-radius:10px;
            padding:13px; font-size:13px; font-weight:500;
            cursor:pointer; font-family:inherit; transition:0.2s;
        " onmouseover="this.style.background='rgba(255,255,255,0.04)'"
           onmouseout="this.style.background='transparent'">
            Ntar dulu deh
        </button>
    </div>
</div>

<script>
var _cmSource = '/';

function openContactModal(source) {
    _cmSource = source || (window.location.pathname + window.location.search);
    var overlay = document.getElementById('contactModalOverlay');
    var sheet   = document.getElementById('contactModalSheet');
    overlay.style.display = 'flex';
    // Trigger animation next frame
    requestAnimationFrame(function() {
        requestAnimationFrame(function() {
            sheet.style.transform = 'translateY(0)';
        });
    });
    document.body.style.overflow = 'hidden';
}

function closeContactModal() {
    var overlay = document.getElementById('contactModalOverlay');
    var sheet   = document.getElementById('contactModalSheet');
    sheet.style.transform = 'translateY(100%)';
    setTimeout(function() {
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    }, 340);
}

function cmTrack(type) {
    var fd = new FormData();
    fd.append('type', type);
    fd.append('source', _cmSource);
    fetch('track_click.php', { method: 'POST', body: fd }).catch(function(){});
    // Modal tetap terbuka sementara browser buka tab baru
    setTimeout(closeContactModal, 600);
}

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeContactModal();
});

// Touch swipe down to dismiss
(function() {
    var startY = 0;
    var sheet = document.getElementById('contactModalSheet');
    sheet.addEventListener('touchstart', function(e) { startY = e.touches[0].clientY; }, { passive: true });
    sheet.addEventListener('touchend', function(e) {
        if (e.changedTouches[0].clientY - startY > 80) closeContactModal();
    }, { passive: true });
})();
</script>
