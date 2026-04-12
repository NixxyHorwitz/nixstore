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

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1>Dashboard Overview</h1>
        <div class="bc">Manage your premium digital assets with our modern suite.</div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4 col-md-6">
        <div class="sc blue">
            <div class="si blue"><i class='bx bx-box'></i></div>
            <div class="sv"><?= $total_products ?></div>
            <div class="sl">Total Products</div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="sc green">
            <div class="si green"><i class='bx bx-show'></i></div>
            <div class="sv"><?= $traffic_today ?></div>
            <div class="sl">Traffic Today (<?= date('d M Y') ?>)</div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="sc purple">
            <div class="si purple"><i class='bx bx-bar-chart-alt-2'></i></div>
            <div class="sv"><?= $traffic_month ?></div>
            <div class="sl">Traffic This Month</div>
        </div>
    </div>
</div>

<div class="card-c">
    <div class="cb">
        <h3 class="mb-2" style="font-weight: 600; font-size: 18px;">Welcome to WebSales Admin</h3>
        <p class="mb-0 text-muted" style="font-size: 14px;">Use the sidebar navigation to manage products, view analytics, and adjust site settings.</p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
