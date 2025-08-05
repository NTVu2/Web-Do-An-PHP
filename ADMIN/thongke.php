<?php
session_start();

// Kiểm tra nếu admin đã đăng nhập
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: loginAdmin.php");
    exit;
}

include '../db_connect.php'; // Kết nối database

// Lấy tháng và năm được chọn từ form, mặc định là tháng và năm hiện tại
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Điều kiện thời gian cho các truy vấn
$timeConditionMonth = "AND MONTH(hd.NgayBH) = $selectedMonth AND YEAR(hd.NgayBH) = $selectedYear";
$timeConditionQuarter = "AND QUARTER(hd.NgayBH) = QUARTER(STR_TO_DATE('$selectedMonth-1-$selectedYear', '%m-%d-%Y')) AND YEAR(hd.NgayBH) = $selectedYear";
$timeConditionYear = "AND YEAR(hd.NgayBH) = $selectedYear";

// Truy vấn Sản phẩm bán chạy trong tháng được chọn
$bestSellingQuery = "SELECT h.Tenhang, SUM(ct.Soluong) AS TotalSold 
                     FROM hang h
                     JOIN chitiethd ct ON h.Mahang = ct.Mahang
                     JOIN hoadon hd ON ct.SohieuHD = hd.SohieuHD
                     WHERE hd.Trangthai = 'Giao hàng thành công'
                     $timeConditionMonth
                     GROUP BY h.Mahang
                     ORDER BY TotalSold DESC LIMIT 5";
$bestSellingResult = $con->query($bestSellingQuery);

// Truy vấn Sản phẩm bán chậm trong tháng được chọn
$slowSellingQuery = "SELECT h.Tenhang, SUM(ct.Soluong) AS TotalSold 
                     FROM hang h
                     JOIN chitiethd ct ON h.Mahang = ct.Mahang
                     JOIN hoadon hd ON ct.SohieuHD = hd.SohieuHD
                     WHERE hd.Trangthai = 'Giao hàng thành công'
                     $timeConditionMonth
                     GROUP BY h.Mahang
                     ORDER BY TotalSold ASC LIMIT 5";
$slowSellingResult = $con->query($slowSellingQuery);

// Truy vấn Khách hàng mua nhiều trong tháng được chọn
$frequentBuyersQuery = "SELECT k.Tenkhach, COUNT(hd.SohieuHD) AS TotalOrders
                        FROM khach k
                        JOIN hoadon hd ON k.id = hd.id
                        WHERE hd.Trangthai = 'Giao hàng thành công'
                        $timeConditionMonth
                        GROUP BY k.id
                        ORDER BY TotalOrders DESC LIMIT 5";
$frequentBuyersResult = $con->query($frequentBuyersQuery);

// Truy vấn Doanh số và Doanh thu trong tháng
$monthlySalesQuery = "SELECT SUM(hd.Tongtien) AS TotalRevenue, COUNT(hd.SohieuHD) AS TotalSales
                      FROM hoadon hd
                      WHERE hd.Trangthai = 'Giao hàng thành công'
                      $timeConditionMonth";
$monthlySalesResult = $con->query($monthlySalesQuery);
$monthlySalesData = $monthlySalesResult->fetch_assoc();

// Truy vấn Doanh số và Doanh thu trong quý
$quarterlySalesQuery = "SELECT SUM(hd.Tongtien) AS TotalRevenue, COUNT(hd.SohieuHD) AS TotalSales
                        FROM hoadon hd
                        WHERE hd.Trangthai = 'Giao hàng thành công'
                        $timeConditionQuarter";
$quarterlySalesResult = $con->query($quarterlySalesQuery);
$quarterlySalesData = $quarterlySalesResult->fetch_assoc();

// Truy vấn Doanh số và Doanh thu trong năm
$yearlySalesQuery = "SELECT SUM(hd.Tongtien) AS TotalRevenue, COUNT(hd.SohieuHD) AS TotalSales
                     FROM hoadon hd
                     WHERE hd.Trangthai = 'Giao hàng thành công'
                     $timeConditionYear";
$yearlySalesResult = $con->query($yearlySalesQuery);
$yearlySalesData = $yearlySalesResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ ADMIN</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/trangchu.css">
    <link rel="stylesheet" href="css/trangchuadmin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    /* ==== Bảng thống kê ==== */
    .table-ngay, .table-nam {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 14px;
        background-color: #ffffff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .table-ngay th, .table-ngay td,
    .table-nam th, .table-nam td {
        border: 1px solid #dee2e6;
        padding: 8px 12px;
        text-align: center;
    }

    .table-ngay thead th,
    .table-nam thead th {
        background-color: #124dceff;
        color: #ffffff;
        font-weight: bold;
    }

    .table-ngay tbody tr:nth-child(even),
    .table-nam tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .table-ngay tbody tr:hover,
    .table-nam tbody tr:hover {
        background-color: #e2e6ea;
    }

    .table-ngay tfoot td,
    .table-nam tfoot td {
        font-weight: bold;
        background-color: #e9ecef;
    }

    /* === Tiêu đề bảng === */
    h2 {
        margin-top: 40px;
        color: #333;
        font-size: 18px;
        border-left: 4px solid #007bff;
        padding-left: 10px;
    }
</style>

</head>

<body>

    <header class="admin-header">
        <div class="header-container">
            <h1 class="admin-title">Bảng Điều Khiển Admin</h1>
            <ul class="user-actions">
                <?php if (isset($_SESSION['admin_logged_in'])): ?>
                    <li><a href="logout.php"><i class="fa fa-user"></i> <?php echo $_SESSION['admin_username']; ?></a></li>
                <?php else: ?>
                    <li><a href="loginAdmin.php" class="btn-primary">Đăng nhập</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <nav>
        <nav>
        <div class="tabs">
            <a href="trangchuadmin.php" class="tab-button"><i class="fa fa-home"></i> Trang chủ</a>   
            <?php if (in_array('sanpham', $_SESSION['quyen'])): ?>
                <a href="Nhap_SP.php" class="tab-button"><i class="fa fa-product-hunt"></i> Sản phẩm</a>
            <?php endif; ?>
            <?php if (in_array('danhmuc', $_SESSION['quyen'])): ?>
                <a href="Nhap_DM.php" class="tab-button"><i class="fa fa-list"></i> Danh mục</a>
            <?php endif; ?>
            <?php if (in_array('banner', $_SESSION['quyen'])): ?>
                <a href="Nhap_Banner.php" class="tab-button"><i class="fa fa-image"></i> Banner</a>
            <?php endif; ?>
            <?php if (in_array('taikhoan', $_SESSION['quyen'])): ?>
                <a href="qltaikhoan.php" class="tab-button"><i class="fa fa-user"></i> Tài khoản</a>
            <?php endif; ?>
            <?php if (in_array('donhang', $_SESSION['quyen'])): ?>
                <a href="quanlydonhang.php" class="tab-button"><i class="fa fa-credit-card"></i> Đơn hàng</a>
            <?php endif; ?>
            <?php if (in_array('hoadon', $_SESSION['quyen'])): ?>
                <a href="xemhoadon.php" class="tab-button"><i class="fa fa-clipboard-list"></i> Hóa đơn</a>
            <?php endif; ?>
            <?php if (in_array('nhanvien', $_SESSION['quyen'])): ?>
                <a href="qlnhanvien.php" class="tab-button" style="background-color: #858382;"><i class="fa fa-user-tie"></i> Nhân viên</a>
            <?php endif; ?>
             <a href="thongke.php" class="tab-button"><i class="fa fa-home"></i> Thống Kê</a>   
            <?php if (in_array('sanpham', $_SESSION['quyen'])): ?>
               
            <?php endif; ?>
        </div>
    </nav>
        <!-- <div class="tabs">
            <a href="trangchu.php" class="tab-button">Trang Chủ</a>
            <a href="thongke.php" class="tab-button">Thống Kê</a>
            <div class="dropdown">
                <button class="tab-button">Quản lý</button>
                <div class="dropdown-content">
                    <a href="qlnhanvien.php" class="tab-button">Quản Lý nhân viên</a>
                    <a href="qltaikhoan.php" class="tab-button">Quản Lý tài khoản</a>
                    <a href="quanlydonhang.php" class="tab-button">Quản Lý</a>
                </div>
            </div>
            <span class="hamburger" onclick="toggleNav()">&#9776;</span>
        </div> -->
    </nav>

    <div class="container">
        <form method="get" class="nav-form-container">
            <div class="form-container">
                <label for="month">Chọn Tháng:</label>
                <select name="month" id="month">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo ($i == $selectedMonth) ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <label for="year">Chọn Năm:</label>
                <select name="year" id="year">
                    <?php for ($year = date('Y'); $year >= 2000; $year--): ?>
                        <option value="<?php echo $year; ?>" <?php echo ($year == $selectedYear) ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <input type="submit" value="Xem Thống Kê" class="btn-submit">
            </div>
        </form>
    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Doanh Thu Tháng', 'Doanh Thu Quý', 'Doanh Thu Năm'],
                datasets: [{
                    label: 'Doanh Thu',
                    data: [
                        <?php echo htmlspecialchars($monthlySalesData['TotalRevenue']); ?>,
                        <?php echo htmlspecialchars($quarterlySalesData['TotalRevenue']); ?>,
                        <?php echo htmlspecialchars($yearlySalesData['TotalRevenue']); ?>
                    ],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
    </script>
<?php
//làm lại form cho các bảng thống  kê doanh thu theo tháng, quý, năm
// Add this block before closing the DB connection (before $con->close();)
// Calculate monthly revenue and order count for the selected year
$monthlyRevenue = [];
$monthlyOrder = [];
for ($m = 1; $m <= 12; $m++) {
    $query = "SELECT SUM(hd.Tongtien) AS TotalRevenue, COUNT(hd.SohieuHD) AS TotalSales
              FROM hoadon hd
              WHERE hd.Trangthai = 'Giao hàng thành công'
              AND MONTH(hd.NgayBH) = $m AND YEAR(hd.NgayBH) = $selectedYear";
    $result = $con->query($query);
    $row = $result->fetch_assoc();
    $monthlyRevenue[] = $row['TotalRevenue'] ? $row['TotalRevenue'] : 0;
    $monthlyOrder[] = $row['TotalSales'] ? $row['TotalSales'] : 0;
}
?>

<?php
// === DOANH THU THEO NGÀY TRONG THÁNG ===
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);

$dailyRevenue = [];
$dailyOrders = [];

for ($day = 1; $day <= $daysInMonth; $day++) {
    $query = "SELECT SUM(Tongtien) AS TotalRevenue, COUNT(SohieuHD) AS TotalSales
              FROM hoadon
              WHERE Trangthai = 'Giao hàng thành công'
              AND DAY(NgayBH) = $day 
              AND MONTH(NgayBH) = $selectedMonth 
              AND YEAR(NgayBH) = $selectedYear";
    $result = $con->query($query);
    $row = $result->fetch_assoc();
    $dailyRevenue[$day] = $row['TotalRevenue'] ?? 0;
    $dailyOrders[$day] = $row['TotalSales'] ?? 0;
}
?>
<h2>Doanh Số và Doanh Thu Theo Ngày (Tháng <?php echo $selectedMonth; ?>/<?php echo $selectedYear; ?>)</h2>
<table class="table-ngay">
    <thead>
        <tr>
            <th>Ngày</th>
            <th>Doanh Thu (VNĐ)</th>
            <th>Số Đơn Hàng</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $sumRevenue = 0;
        $sumOrders = 0;
        foreach ($dailyRevenue as $day => $revenue): 
            $sumRevenue += $revenue;
            $sumOrders += $dailyOrders[$day];
        ?>
            <tr>
                <td><?php echo $day; ?></td>
                <td><?php echo number_format($revenue, 0, ',', '.'); ?></td>
                <td><?php echo $dailyOrders[$day]; ?></td>
            </tr>
        <?php endforeach; ?>
        <tr style="font-weight:bold; background-color:#f2f2f2;">
            <td>Tổng Tháng</td>
            <td><?php echo number_format($sumRevenue, 0, ',', '.'); ?>    </td>
            <td><?php echo $sumOrders; ?></td>
        </tr>
    </tbody>
</table>

<h2>Doanh Số và Doanh Thu Theo Từng Tháng Năm <?php echo $selectedYear; ?></h2>
<table class="table-nam">
    <thead>
        <tr>
            <th>Tháng</th>
            <th>Doanh Thu (VNĐ)</th>
            <th>Số Đơn Hàng</th>
        </tr>
    </thead>
    <tbody>
        <?php for($i=1; $i<=12; $i++): ?>
            <tr>
                <td><?php echo $i; ?></td>
                <td><?php echo number_format($monthlyRevenue[$i-1], 0, ',', '.'); ?></td>
                <td><?php echo $monthlyOrder[$i-1]; ?></td>
            </tr>
        <?php endfor; ?>
        <tr style="font-weight:bold;background-color:#e9ecef;">
            <td>Tổng cả năm</td>
            <td><?php echo number_format($yearlySalesData['TotalRevenue'], 0, ',', '.'); ?></td>
            <td><?php echo $yearlySalesData['TotalSales']; ?></td>
        </tr>
    </tbody>
</table>
<?php $con->close(); ?>
</body>

</html>



