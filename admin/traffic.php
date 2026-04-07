<?php require 'includes/header.php'; ?>
<?php
// Chart: 7 days traffic
$chart_labels = [];
$chart_data   = [];
for ($i = 6; $i >= 0; $i--) {
    $date   = date('Y-m-d', strtotime("-$i days"));
    $stmt   = $pdo->prepare("SELECT COUNT(*) FROM traffic WHERE visit_date = ?");
    $stmt->execute([$date]);
    $chart_labels[] = date('d M', strtotime($date));
    $chart_data[]   = (int)$stmt->fetchColumn();
}

// Contact clicks stats
$stmt_wa  = $pdo->query("SELECT COUNT(*) FROM contact_clicks WHERE contact_type='whatsapp'");
$total_wa = (int)$stmt_wa->fetchColumn();
$stmt_tg  = $pdo->query("SELECT COUNT(*) FROM contact_clicks WHERE contact_type='telegram'");
$total_tg = (int)$stmt_tg->fetchColumn();
$total_clicks = $total_wa + $total_tg;

// Contact clicks chart: 7 days
$click_labels = [];
$click_wa_data = [];
$click_tg_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $s1 = $pdo->prepare("SELECT COUNT(*) FROM contact_clicks WHERE contact_type='whatsapp' AND DATE(clicked_at)=?");
    $s1->execute([$date]);
    $s2 = $pdo->prepare("SELECT COUNT(*) FROM contact_clicks WHERE contact_type='telegram' AND DATE(clicked_at)=?");
    $s2->execute([$date]);
    $click_labels[]  = date('d M', strtotime($date));
    $click_wa_data[] = (int)$s1->fetchColumn();
    $click_tg_data[] = (int)$s2->fetchColumn();
}

$traffic = $pdo->query("SELECT * FROM traffic ORDER BY id DESC LIMIT 100")->fetchAll();
$clicks  = $pdo->query("SELECT * FROM contact_clicks ORDER BY id DESC LIMIT 100")->fetchAll();
?>

<!-- Stats Row -->
<div class="grid-3" style="margin-bottom:30px;">
    <div class="card" style="flex-direction:row;align-items:center;gap:20px;padding:24px 28px;">
        <div style="width:48px;height:48px;border-radius:12px;background:rgba(129,140,248,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <div>
            <div class="stat-val" style="font-size:30px;"><?= array_sum($chart_data) ?></div>
            <div class="stat-label">Page Visits (7 Days)</div>
        </div>
    </div>
    <div class="card" style="flex-direction:row;align-items:center;gap:20px;padding:24px 28px;">
        <div style="width:48px;height:48px;border-radius:12px;background:rgba(37,211,102,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="#25d366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.787"/></svg>
        </div>
        <div>
            <div class="stat-val" style="font-size:30px;"><?= $total_wa ?></div>
            <div class="stat-label">WhatsApp Clicks</div>
        </div>
    </div>
    <div class="card" style="flex-direction:row;align-items:center;gap:20px;padding:24px 28px;">
        <div style="width:48px;height:48px;border-radius:12px;background:rgba(42,171,238,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="#2AABEE"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
        </div>
        <div>
            <div class="stat-val" style="font-size:30px;"><?= $total_tg ?></div>
            <div class="stat-label">Telegram Clicks</div>
        </div>
    </div>
</div>

<!-- Tabs -->
<div style="display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap;">
    <button class="btn btn-primary tab-btn" data-tab="tab-pagevisit" onclick="switchTab(this)">Page Visits</button>
    <button class="btn btn-outline tab-btn" data-tab="tab-contacts" onclick="switchTab(this)">Contact Clicks</button>
</div>

<!-- TAB: PAGE VISITS -->
<div class="tab-content" id="tab-pagevisit">
    <div class="card mb-4 card-p0">
        <div class="card-header">
            <h3 style="font-size:16px;font-weight:600;">Traffic Overview (7 Days)</h3>
        </div>
        <div style="padding:24px;position:relative;height:240px;width:100%;">
            <canvas id="trafficChart"></canvas>
        </div>
    </div>
    <div class="card card-p0">
        <div class="card-header">
            <h3 style="font-size:16px;font-weight:600;">Recent Page Hits</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>IP & Lokasi</th>
                        <th>OS & Device</th>
                        <th>Halaman</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($traffic as $t): ?>
                    <tr>
                        <td style="white-space:nowrap;"><?= date('H:i d/m/Y', strtotime($t['created_at'])) ?></td>
                        <td>
                            <code style="font-size:12px;display:block;"><?= htmlspecialchars($t['ip_address']) ?></code>
                            <span style="font-size:11px;color:#888;"><?= htmlspecialchars($t['location'] ?? 'Unknown location') ?></span>
                        </td>
                        <td>
                            <div style="font-size:12px;font-weight:600;color:#ddd;" title="<?= htmlspecialchars($t['user_agent']) ?>">
                                <?= htmlspecialchars($t['device_brand'] ?? 'Unknown') ?> - <?= htmlspecialchars($t['device_os'] ?? '') ?>
                            </div>
                            <div style="font-size:11px;color:#888;">Browser: <?= htmlspecialchars($t['browser'] ?? 'Unknown') ?></div>
                        </td>
                        <td><span style="color:#aaa;font-size:12px;"><?= htmlspecialchars($t['page_visited']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($traffic)): ?>
                    <tr><td colspan="4" style="text-align:center;padding:40px;color:#666;">Belum ada data traffic.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAB: CONTACT CLICKS -->
<div class="tab-content" id="tab-contacts" style="display:none;">
    <div class="card mb-4 card-p0">
        <div class="card-header">
            <h3 style="font-size:16px;font-weight:600;">Contact Clicks (7 Days)</h3>
        </div>
        <div style="padding:24px;position:relative;height:240px;width:100%;">
            <canvas id="clickChart"></canvas>
        </div>
    </div>
    <div class="card card-p0">
        <div class="card-header">
            <h3 style="font-size:16px;font-weight:600;">Log Klik Kontak</h3>
            <div style="display:flex;gap:16px;">
                <span style="font-size:12px;color:#25d366;font-weight:600;">● WA: <?= $total_wa ?></span>
                <span style="font-size:12px;color:#2AABEE;font-weight:600;">● TG: <?= $total_tg ?></span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Platform</th>
                        <th>IP & Lokasi</th>
                        <th>Halaman Sumber</th>
                        <th>OS & Device</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($clicks as $c): ?>
                    <tr>
                        <td style="white-space:nowrap;"><?= date('H:i d/m/Y', strtotime($c['clicked_at'])) ?></td>
                        <td>
                            <?php if($c['contact_type'] === 'whatsapp'): ?>
                            <span style="background:rgba(37,211,102,0.12);color:#25d366;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">WhatsApp</span>
                            <?php else: ?>
                            <span style="background:rgba(42,171,238,0.12);color:#2AABEE;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">Telegram</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code style="font-size:12px;display:block;"><?= htmlspecialchars($c['ip_address']) ?></code>
                            <span style="font-size:11px;color:#888;"><?= htmlspecialchars($c['location'] ?? 'Unknown location') ?></span>
                        </td>
                        <td style="color:#888;font-size:12px;"><?= htmlspecialchars($c['page_source'] ?? '-') ?></td>
                        <td>
                            <div style="font-size:12px;font-weight:600;color:#ddd;" title="<?= htmlspecialchars($c['user_agent']) ?>">
                                <?= htmlspecialchars($c['device_brand'] ?? 'Unknown') ?> - <?= htmlspecialchars($c['device_os'] ?? '') ?>
                            </div>
                            <div style="font-size:11px;color:#888;">Browser: <?= htmlspecialchars($c['browser'] ?? 'Unknown') ?></div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($clicks)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:40px;color:#666;">Belum ada klik kontak yang tercatat.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Tab switch
function switchTab(btn) {
    document.querySelectorAll('.tab-btn').forEach(function(b) {
        b.classList.remove('btn-primary'); b.classList.add('btn-outline');
    });
    document.querySelectorAll('.tab-content').forEach(function(c) { c.style.display='none'; });
    btn.classList.add('btn-primary'); btn.classList.remove('btn-outline');
    document.getElementById(btn.getAttribute('data-tab')).style.display = 'block';
}
// Activate first tab
document.querySelector('.tab-btn').click();

// Page visits chart
new Chart(document.getElementById('trafficChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{ label:'Page Visits', data: <?= json_encode($chart_data) ?>, borderColor:'#818cf8', backgroundColor:'rgba(129,140,248,0.08)', borderWidth:2, tension:0.3, fill:true, pointBackgroundColor:'#818cf8' }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{ y:{beginAtZero:true,ticks:{precision:0,color:'#aaa'},grid:{color:'rgba(255,255,255,0.04)'}}, x:{ticks:{color:'#aaa'},grid:{display:false}} } }
});

// Contact clicks chart
new Chart(document.getElementById('clickChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($click_labels) ?>,
        datasets: [
            { label:'WhatsApp', data: <?= json_encode($click_wa_data) ?>, backgroundColor:'rgba(37,211,102,0.7)', borderRadius:6 },
            { label:'Telegram', data: <?= json_encode($click_tg_data) ?>, backgroundColor:'rgba(42,171,238,0.7)', borderRadius:6 }
        ]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{labels:{color:'#aaa'}}}, scales:{ y:{beginAtZero:true,ticks:{precision:0,color:'#aaa'},grid:{color:'rgba(255,255,255,0.04)'}}, x:{ticks:{color:'#aaa'},grid:{display:false}} } }
});
</script>

<?php require 'includes/footer.php'; ?>
