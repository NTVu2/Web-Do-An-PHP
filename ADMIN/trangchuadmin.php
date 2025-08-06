<?php
session_start();

// Kiểm tra nếu admin đã đăng nhập
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: loginADMIN.php");
    exit;
}

include '../db_connect.php'; // Kết nối database
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ ADMIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/trangchu.css">
    <link rel="stylesheet" href="css/dashboard_stats.css">
    <link rel="stylesheet" href="css/thongke.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>

    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container-fluid">
    <!-- Phần 1: Thống kê chi tiết -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="stats-container">
                <h2 class="stats-title">
                    <i class="fas fa-chart-line"></i> Thống Kê Chi Tiết
                </h2>
                <?php include 'thongke.php'; ?>
            </div>
        </div>
    </div>
    
    <!-- Phần 2: Thống kê tổng quan -->
    <div class="row">
        <div class="col-12">
            <h2 class="stats-title">
                <i class="fas fa-chart-bar"></i> Thống Kê Tổng Quan
            </h2>
            <?php include 'dashboard_stats.php'; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleMenu() {
        var tabs = document.querySelector('.tabs');
        tabs.classList.toggle('visible');
    }
</script>

<!-- Script cho biểu đồ thống kê -->
<script>
// Biểu đồ doanh thu theo tháng
<?php if (isset($reportType) && $reportType == 'monthly'): ?>
const ctx = document.getElementById('monthlyChart');
if (ctx) {
    const monthlyChart = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
            datasets: [{
                label: 'Doanh Thu (VNĐ)',
                data: [
                    <?php 
                    if (isset($monthlyStats)) {
                        foreach ($monthlyStats as $data) {
                            echo $data['TotalRevenue'] . ',';
                        }
                    }
                    ?>
                ],
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Doanh Thu (VNĐ)'
                    }
                }
            }
        }
    });
}
<?php endif; ?>
</script>
</body>
</html>

<?php $con->close(); ?>
