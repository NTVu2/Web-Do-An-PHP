<?php
session_start();

// Kiểm tra nếu admin đã đăng nhập
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: loginADMIN.php");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/trangchu.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    <style>


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
                <li><a href="loginADMIN.php" class="dangnhap">Đăng Nhập</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>

<nav>
     <!-- Biểu tượng Ba Gạch để Mở Menu -->
     <div class="hamburger" onclick="toggleForms()"></div>
    <div class="tabs">
        <a href="trangchuadmin.php" class="tab-button" style="background-color: #858382;"><i class="fa fa-home"></i> Trang chủ</a>   
        
        <!-- Sử dụng in_array để kiểm tra quyền trong mảng -->
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
            <a href="qltaikhaon.php" class="tab-button"><i class="fa fa-user"></i> Tài khoản</a>
        <?php endif; ?>

        <?php if (in_array('donhang', $_SESSION['quyen'])): ?>
            <a href="quanlydonhang.php" class="tab-button"><i class="fa fa-credit-card"></i> Đơn hàng</a>
        <?php endif; ?>

        <?php if (in_array('hoadon', $_SESSION['quyen'])): ?>
            <a href="xemhoadon.php" class="tab-button"><i class="fa fa-clipboard-list"></i> Hóa đơn</a>
        <?php endif; ?>
        <?php if (in_array('nhanvien', $_SESSION['quyen'])): ?>
            <a href="qlnhanvien.php" class="tab-button"><i class="fa fa-user-tie"></i> Nhân viên</a>
        <?php endif; ?>
    </div>
   
</nav>
<div class="nav-form-container">
<!-- Form chọn thời gian -->
<div class="form-container">
    
    <form method="GET" action="">
        <label for="month">Xem thông tin : Chọn tháng:</label>
        <select name="month" id="month">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>" <?php if ($selectedMonth == $m) echo 'selected'; ?>>Tháng <?php echo $m; ?></option>
            <?php endfor; ?>
        </select>

        <label for="year">Chọn năm:</label>
        <select name="year" id="year">
            <?php for ($i = 2020; $i <= date('Y'); $i++): ?> <!-- Cho phép chọn năm hiện tại và năm sau -->
                <option value="<?php echo $i; ?>" <?php if ($selectedYear == $i) echo 'selected'; ?>>Năm <?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
        
        <button type="submit" class="btn-submit">Xem</button>
    </form>
</div>
<!-- Thêm phần chọn từ ngày đến ngày -->
<div class="form-container">
    
    <form method="GET" action="">
        <label for="start_date">Xem hóa đơn Từ ngày:</label>
        <input type="date" name="start_date" id="start_date" required>

        <label for="end_date">Đến ngày:</label>
        <input type="date" name="end_date" id="end_date" required>
        
        <button type="submit" class="btn-submit">Xem</button>
    </form>
</div>

    <!-- Nút xem biểu đồ -->
    <div class="form-container">
        <button onclick="toggleChart()">Xem biểu đồ</button>

</div>
</div>
<script>
function toggleForms() {
    const navFormContainer = document.querySelector('.nav-form-container');
    if (navFormContainer) {
        navFormContainer.classList.toggle('active');
    } else {
        console.error('Element .nav-form-container not found');
    }
}
</script>
<?php
// Kiểm tra nếu có chọn ngày
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];

    // Truy vấn đơn hàng trong khoảng thời gian được chọn
    $orderQuery = "SELECT hd.SohieuHD, hd.NgayBH, hd.Tongtien, k.Tenkhach, hd.Trangthai
                   FROM hoadon hd
                   JOIN khach k ON hd.id = k.id
                   WHERE hd.NgayBH BETWEEN '$startDate' AND '$endDate'
                   ORDER BY hd.NgayBH DESC";
    $orderResult = $con->query($orderQuery);
}
?>
<div class="container">
        <!-- Div chứa biểu đồ, ban đầu ẩn -->
        <div id="chartDiv" style="display: none;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
<div id="container" class="container">

    <?php if (isset($startDate) && isset($endDate) && !empty($startDate) && !empty($endDate) && isset($orderResult)): 
        $formattedStartDate = DateTime::createFromFormat('Y-m-d', $startDate)->format('d/m/y');
    $formattedEndDate = DateTime::createFromFormat('Y-m-d', $endDate)->format('d/m/y');
?>
    <?php if ($orderResult->num_rows > 0): ?>
    <h2 style="text-align: center">Danh sách đơn hàng từ ngày <?php echo $formattedStartDate; ?> đến ngày <?php echo $formattedEndDate; ?></h2>
    <table class="table">
        <thead>
            <tr>
                <th>Mã HĐ</th>
                <th>Ngày Bán Hàng</th>
                <th>Tên Khách</th>
                <th>Tổng Tiền</th>
                <th>Chi Tiết</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $totalRevenue = 0; // Khởi tạo biến để tính tổng doanh thu
                while ($row = $orderResult->fetch_assoc()): 
                    $totalRevenue += $row['Tongtien']; // Cộng dồn tổng tiền của từng đơn hàng
            ?>
                <tr>
                    <td><?php echo $row['SohieuHD']; ?></td>
                    <td><?php echo $row['NgayBH']; ?></td>
                    <td><?php echo $row['Tenkhach']; ?></td>
                    <td><?php echo number_format($row['Tongtien']); ?> VND</td>
                    <td><a href="indonhang.php?SohieuHD=<?php echo $row['SohieuHD']; ?>" class="btn btn-primary">Xem Chi Tiết</a></td>
                </tr>
            <?php endwhile; ?>
            
            <!-- Hàng tổng doanh thu -->
            <tr>
                <td colspan="3" style=" font-weight: bold;font-size: 1.2em;">Tổng Doanh Thu:</td>
                <td colspan="2" style="font-weight: bold;font-size: 1.2em;"><?php echo number_format($totalRevenue); ?> VND</td>
            </tr>
        </tbody>
    </table>
<?php else: ?>
    <p>Không có đơn hàng nào trong khoảng thời gian được chọn.</p>
<?php endif; ?>
    <?php else: ?>

        <!-- Default View: Monthly, Quarterly, and Yearly Summaries -->
        <div class="row">
            <div class="col-md-6">
                <h2>Sản phẩm bán chạy trong tháng <?php echo $selectedMonth; ?></h2>
                <ul class="list-group">
                    <?php while ($row = $bestSellingResult->fetch_assoc()) { ?>
                        <li class="list-group-item"><?php echo $row['Tenhang'] . " - " . $row['TotalSold'] . " sản phẩm"; ?></li>
                    <?php } ?>
                </ul>
            </div>
            <div class="col-md-6">
                <h2>Sản phẩm bán chậm trong tháng <?php echo $selectedMonth; ?></h2>
                <ul class="list-group">
                    <?php while ($row = $slowSellingResult->fetch_assoc()) { ?>
                        <li class="list-group-item"><?php echo $row['Tenhang'] . " - " . $row['TotalSold'] . " sản phẩm"; ?></li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <div class="row my-4">
            <div class="col-md-6">
                <h2>Khách hàng mua nhiều trong tháng <?php echo $selectedMonth; ?>, năm <?php echo $selectedYear; ?></h2>
                <ul class="list-group">
                    <?php while ($row = $frequentBuyersResult->fetch_assoc()) { ?>
                        <li class="list-group-item"><?php echo $row['Tenkhach'] . " - " . $row['TotalOrders'] . " đơn hàng"; ?></li>
                    <?php } ?>
                </ul>
            </div>
            <div class="col-md-6">
                <h2>Doanh số & Doanh thu trong tháng <?php echo $selectedMonth; ?>, năm <?php echo $selectedYear; ?></h2>
                <p>Tổng số đơn hàng: <?php echo $monthlySalesData['TotalSales']; ?></p>
                <p>Tổng doanh thu: <?php echo number_format($monthlySalesData['TotalRevenue']); ?> VND</p>
            </div>
        </div>

        <div class="row my-4">
            <div class="col-md-6">
                <h2>Doanh số & Doanh thu trong quý của năm <?php echo $selectedYear; ?></h2>
                <p>Tổng số đơn hàng: <?php echo $quarterlySalesData['TotalSales']; ?></p>
                <p>Tổng doanh thu: <?php echo number_format($quarterlySalesData['TotalRevenue']); ?> VND</p>
            </div>
            <div class="col-md-6">
                <h2>Doanh số & Doanh thu trong năm <?php echo $selectedYear; ?></h2>
                <p>Tổng số đơn hàng: <?php echo $yearlySalesData['TotalSales']; ?></p>
                <p>Tổng doanh thu: <?php echo number_format($yearlySalesData['TotalRevenue']); ?> VND</p>
            </div>
        </div>
    <?php endif; ?>
     
    </div>

    <script>
        function toggleChart() {
    const chartDiv = document.getElementById('chartDiv');
    const container = document.getElementById('container'); // Lấy phần tử container

    // Kiểm tra nếu chartDiv và container tồn tại
    if (chartDiv) {
        chartDiv.style.display = chartDiv.style.display === 'none' ? 'block' : 'none';

        // Kiểm tra nếu container tồn tại
        if (container) {
            container.style.display = chartDiv.style.display === 'block' ? 'none' : 'block';
        }

        // Chỉ tải biểu đồ khi chartDiv hiển thị
        if (chartDiv.style.display === 'block') {
            loadChart();
        }
    } else {
        console.warn("chartDiv hoặc container không tồn tại.");
    }
}

        function loadChart() {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [<?php echo implode(',', array_map(fn($month) => "'Tháng $month'", range(1, 12))); ?>],
                    datasets: [
                        {
                            label: 'Doanh thu',
                            data: [
                                <?php
                                for ($month = 1; $month <= 12; $month++) {
                                    // Truy vấn doanh thu của từng tháng
                                    $monthlyRevenueQuery = "SELECT SUM(Tongtien) AS TotalRevenue
                                                            FROM hoadon
                                                            WHERE Trangthai = 'Giao hàng thành công'
                                                            AND MONTH(NgayBH) = $month
                                                            AND YEAR(NgayBH) = $selectedYear";
                                    $result = $con->query($monthlyRevenueQuery);
                                    $row = $result->fetch_assoc();
                                    echo $row['TotalRevenue'] ? $row['TotalRevenue'] : 0;
                                    if ($month < 12) echo ", ";
                                }
                                ?>
                            ],
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Doanh thu (VND)'
                            }
                        }
                    }
                }
            });
        }
    </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleMenu() {
        var tabs = document.querySelector('.tabs');
        tabs.classList.toggle('visible');
    }
</script>
</body>
</html>
