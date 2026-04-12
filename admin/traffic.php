<?php require 'includes/header.php'; ?>
<?php
// Handle clear actions via AJAX POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['admin_logged_in'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
    }
    if ($_POST['action'] === 'clear_traffic') {
        $pdo->exec("TRUNCATE TABLE traffic");
        echo json_encode(['status' => 'success', 'message' => 'Traffic log cleared.']); exit;
    }
    if ($_POST['action'] === 'clear_clicks') {
        $pdo->exec("TRUNCATE TABLE contact_clicks");
        echo json_encode(['status' => 'success', 'message' => 'Contact clicks log cleared.']); exit;
    }
    echo json_encode(['status' => 'error', 'message' => 'Unknown action']); exit;
}

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

$traffic = $pdo->query("SELECT * FROM traffic ORDER BY id DESC LIMIT 500")->fetchAll();
$clicks  = $pdo->query("SELECT * FROM contact_clicks ORDER BY id DESC LIMIT 500")->fetchAll();
?>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1>Traffic & Analytics</h1>
        <div class="bc">Monitor your page visits, device analytics, and contact clicks.</div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm" onclick="clearTraffic()">
            <i class='bx bx-trash me-1'></i> Clear Traffic
        </button>
        <button class="btn btn-sm" style="border:1px solid var(--err);color:var(--err);" onclick="clearClicks()">
            <i class='bx bx-message-x me-1'></i> Clear Clicks
        </button>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-4 col-md-6">
        <div class="sc blue">
            <div class="si blue"><i class='bx bx-bar-chart-alt-2'></i></div>
            <div class="sv"><?= array_sum($chart_data) ?></div>
            <div class="sl">Page Visits (7 Days)</div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="sc green">
            <div class="si green"><i class='bx bxl-whatsapp'></i></div>
            <div class="sv"><?= $total_wa ?></div>
            <div class="sl">WhatsApp Clicks</div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="sc mb">
            <div class="si mb"><i class='bx bxl-telegram'></i></div>
            <div class="sv"><?= $total_tg ?></div>
            <div class="sl">Telegram Clicks</div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-pills mb-4" id="analyticsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="visits-tab" data-bs-toggle="tab" data-bs-target="#tab-visits" type="button" role="tab" style="background-color: var(--surface); color: var(--text); border: 1px solid var(--border);">Page Visits</button>
    </li>
    <li class="nav-item ms-2" role="presentation">
        <button class="nav-link" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#tab-contacts" type="button" role="tab" style="background-color: var(--surface); color: var(--text); border: 1px solid var(--border);">Contact Clicks</button>
    </li>
</ul>

<style>
    .nav-pills .nav-link.active {
        background-color: var(--accent) !important;
        color: #fff !important;
        border-color: var(--accent) !important;
    }
</style>

<div class="tab-content" id="analyticsTabsContent">
    <!-- TAB: PAGE VISITS -->
    <div class="tab-pane fade show active" id="tab-visits" role="tabpanel">
        <div class="card-c mb-4">
            <div class="ch">
                <h3 style="font-size:16px;font-weight:600;margin:0;">Traffic Overview (7 Days)</h3>
            </div>
            <div class="cb" style="position:relative;height:300px;width:100%;">
                <canvas id="trafficChart"></canvas>
            </div>
        </div>
        
        <div class="card-c">
            <div class="ch">
                <h3 style="font-size:16px;font-weight:600;margin:0;">Recent Page Hits</h3>
            </div>
            <div class="cb p-0">
                <div class="table-responsive">
                    <table class="tbl datatable" id="trafficTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>Datetime</th>
                                <th>IP & Location</th>
                                <th>OS & Device</th>
                                <th>Page Source</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($traffic as $t): ?>
                            <tr>
                                <td><?= date('H:i:s d/m/Y', strtotime($t['created_at'])) ?></td>
                                <td>
                                    <code style="font-size:12px;display:block;"><?= htmlspecialchars($t['ip_address']) ?></code>
                                    <span style="font-size:11px;color:var(--mut);"><?= htmlspecialchars($t['location'] ?? 'Unknown location') ?></span>
                                </td>
                                <td>
                                    <div style="font-size:12px;font-weight:600;color:var(--text);" title="<?= htmlspecialchars($t['user_agent']) ?>">
                                        <?= htmlspecialchars($t['device_brand'] ?? 'Unknown') ?> - <?= htmlspecialchars($t['device_os'] ?? '') ?>
                                    </div>
                                    <div style="font-size:11px;color:var(--mut);">Browser: <?= htmlspecialchars($t['browser'] ?? 'Unknown') ?></div>
                                </td>
                                <td><span style="color:var(--mut);font-size:12px;"><?= htmlspecialchars($t['page_visited']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB: CONTACT CLICKS -->
    <div class="tab-pane fade" id="tab-contacts" role="tabpanel">
        <div class="card-c mb-4">
            <div class="ch">
                <h3 style="font-size:16px;font-weight:600;margin:0;">Contact Clicks (7 Days)</h3>
            </div>
            <div class="cb" style="position:relative;height:300px;width:100%;">
                <canvas id="clickChart"></canvas>
            </div>
        </div>
        
        <div class="card-c">
            <div class="ch border-0 justify-content-between">
                <h3 style="font-size:16px;font-weight:600;margin:0;">Contact Clicks Logs</h3>
                <div class="d-flex gap-3">
                    <span style="font-size:12px; font-weight:600;" class="bd bd-ok">WhatsApp: <?= $total_wa ?></span>
                    <span style="font-size:12px; font-weight:600; background:rgba(42,171,238,0.12); color:#2AABEE; padding:4px 8px; border-radius:6px;">Telegram: <?= $total_tg ?></span>
                </div>
            </div>
            <div class="cb p-0">
                <div class="table-responsive">
                    <table class="tbl datatable" id="clicksTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>Datetime</th>
                                <th>Platform</th>
                                <th>IP & Location</th>
                                <th>Page Source</th>
                                <th>OS & Device</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($clicks as $c): ?>
                            <tr>
                                <td><?= date('H:i:s d/m/Y', strtotime($c['clicked_at'])) ?></td>
                                <td>
                                    <?php if($c['contact_type'] === 'whatsapp'): ?>
                                    <span class="bd bd-ok px-2 py-1" style="font-size:11px;">WhatsApp</span>
                                    <?php else: ?>
                                    <span style="background:rgba(42,171,238,0.12);color:#2AABEE;padding:6px 10px;border-radius:20px;font-size:11px;font-weight:700;">Telegram</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code style="font-size:12px;display:block;"><?= htmlspecialchars($c['ip_address']) ?></code>
                                    <span style="font-size:11px;color:var(--mut);"><?= htmlspecialchars($c['location'] ?? 'Unknown location') ?></span>
                                </td>
                                <td style="color:var(--mut);font-size:12px;"><?= htmlspecialchars($c['page_source'] ?? '-') ?></td>
                                <td>
                                    <div style="font-size:12px;font-weight:600;color:var(--text);" title="<?= htmlspecialchars($c['user_agent']) ?>">
                                        <?= htmlspecialchars($c['device_brand'] ?? 'Unknown') ?> - <?= htmlspecialchars($c['device_os'] ?? '') ?>
                                    </div>
                                    <div style="font-size:11px;color:var(--mut);">Browser: <?= htmlspecialchars($c['browser'] ?? 'Unknown') ?></div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Page visits chart
new Chart(document.getElementById('trafficChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{ label:'Page Visits', data: <?= json_encode($chart_data) ?>, borderColor:'#818cf8', backgroundColor:'rgba(129,140,248,0.08)', borderWidth:2, tension:0.3, fill:true, pointBackgroundColor:'#818cf8' }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{ y:{beginAtZero:true,ticks:{precision:0,color:'var(--mut)'},grid:{color:'var(--border)'}}, x:{ticks:{color:'var(--mut)'},grid:{display:false}} } }
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
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{labels:{color:'var(--mut)'}}}, scales:{ y:{beginAtZero:true,ticks:{precision:0,color:'var(--mut)'},grid:{color:'var(--border)'}}, x:{ticks:{color:'var(--mut)'},grid:{display:false}} } }
});

$(document).ready(function() {
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        $($(e.target).data('bs-target')).find('.datatable').DataTable().columns.adjust().draw();
    });
});

function clearTraffic() {
    Swal.fire({
        title: 'Clear all traffic logs?',
        text: 'This will permanently delete all page visit records.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'var(--err)',
        cancelButtonColor: 'var(--mut)',
        confirmButtonText: 'Yes, clear it',
        background: 'var(--surface)', color: 'var(--text)'
    }).then(result => {
        if (result.isConfirmed) {
            $.post('', { action: 'clear_traffic' }, function(json) {
                if (json.status === 'success') {
                    Toast.fire({ icon: 'success', title: json.message });
                    setTimeout(() => location.reload(), 800);
                } else {
                    Toast.fire({ icon: 'error', title: json.message });
                }
            });
        }
    });
}

function clearClicks() {
    Swal.fire({
        title: 'Clear all click logs?',
        text: 'This will permanently delete all contact click records.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'var(--err)',
        cancelButtonColor: 'var(--mut)',
        confirmButtonText: 'Yes, clear it',
        background: 'var(--surface)', color: 'var(--text)'
    }).then(result => {
        if (result.isConfirmed) {
            $.post('', { action: 'clear_clicks' }, function(json) {
                if (json.status === 'success') {
                    Toast.fire({ icon: 'success', title: json.message });
                    setTimeout(() => location.reload(), 800);
                } else {
                    Toast.fire({ icon: 'error', title: json.message });
                }
            });
        }
    });
}
</script>

<?php require 'includes/footer.php'; ?>
