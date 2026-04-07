<?php require 'includes/header.php'; ?>
<?php
$stmt = $pdo->query("SELECT count(*) FROM products");
$total_products = $stmt->fetchColumn();

$date = date('Y-m-d');
$stmt = $pdo->prepare("SELECT count(*) FROM traffic WHERE visit_date = ?");
$stmt->execute([$date]);
$traffic_today = $stmt->fetchColumn();

$month = date('Y-m');
$stmt = $pdo->prepare("SELECT count(*) FROM traffic WHERE visit_date LIKE ?");
$stmt->execute([$month . '%']);
$traffic_month = $stmt->fetchColumn();
?>

<div class="grid-3">
    <div class="card" style="padding: 24px;">
        <span class="stat-label">Total Products</span>
        <div class="stat-val"><?= $total_products ?></div>
    </div>
    <div class="card" style="padding: 24px;">
        <span class="stat-label" style="color:var(--text-primary);">Traffic Today (<?= $date ?>)</span>
        <div class="stat-val"><?= $traffic_today ?></div>
    </div>
    <div class="card" style="padding: 24px;">
        <span class="stat-label">Traffic This Month</span>
        <div class="stat-val"><?= $traffic_month ?></div>
    </div>
</div>

<div class="card">
    <h3 style="margin-bottom: 8px; font-weight: 600;">Welcome to WebSales Admin</h3>
    <p style="color: var(--text-muted); font-size: 14px;">Manage your premium digital assets with our modern suite.</p>
</div>

<?php require 'includes/footer.php'; ?>
