<?php
// Kiểm tra session đã được khởi tạo chưa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra nếu admin đã đăng nhập
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: loginADMIN.php");
    exit;
}

// Kiểm tra nếu biến $con chưa được định nghĩa thì include database
if (!isset($con)) {
    include '../db_connect.php';
}

// Xử lý xuất Excel
if (isset($_POST['export_excel'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="thongke_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// Lấy tham số từ form và validate
$reportType = isset($_GET['report_type']) ? $_GET['report_type'] : 'customer';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Validate ngày tháng
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-t');
}

// Validate năm
if (!is_numeric($selectedYear) || $selectedYear < 2020 || $selectedYear > date('Y') + 1) {
    $selectedYear = date('Y');
}

// Hàm lấy thống kê khách hàng - SỬA LẠI LOGIC
function getCustomerStats($con, $startDate, $endDate, $limit = 10) {
    // Sử dụng prepared statement để tránh SQL injection
    $query = "SELECT 
                k.Tenkhach, 
                k.Dienthoai as SDT, 
                COUNT(hd.SohieuHD) as TotalOrders, 
                SUM(CAST(hd.Tongtien AS DECIMAL(15,2))) as TotalSpent, 
                AVG(CAST(hd.Tongtien AS DECIMAL(15,2))) as AvgOrderValue
              FROM khach k
              INNER JOIN hoadon hd ON k.id = hd.id
              WHERE hd.Trangthai = 'Giao hàng thành công'
              AND hd.NgayBH BETWEEN ? AND ?
              GROUP BY k.id, k.Tenkhach, k.Dienthoai
              ORDER BY TotalSpent DESC
              LIMIT ?";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssi", $startDate, $endDate, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        error_log("SQL Error in getCustomerStats: " . $con->error);
        return false;
    }
    
    return $result;
}

// Hàm lấy thống kê theo quý
function getQuarterlyStats($con, $year) {
    $quarters = [];
    for ($q = 1; $q <= 4; $q++) {
        $query = "SELECT SUM(CAST(hd.Tongtien AS DECIMAL(15,2))) as TotalRevenue, 
                  COUNT(hd.SohieuHD) as TotalOrders,
                  COUNT(DISTINCT hd.id) as UniqueCustomers
                  FROM hoadon hd
                  WHERE hd.Trangthai = 'Giao hàng thành công'
                  AND YEAR(hd.NgayBH) = ?
                  AND QUARTER(hd.NgayBH) = ?";
        
        $stmt = $con->prepare($query);
        $stmt->bind_param("ii", $year, $q);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            error_log("SQL Error in getQuarterlyStats for quarter $q: " . $con->error);
            $quarters[$q] = ['TotalRevenue' => 0, 'TotalOrders' => 0, 'UniqueCustomers' => 0];
        } else {
            $quarters[$q] = $result->fetch_assoc();
        }
    }
    return $quarters;
}

// Hàm lấy thống kê theo tháng
function getMonthlyStats($con, $year) {
    $months = [];
    for ($m = 1; $m <= 12; $m++) {
        $query = "SELECT SUM(CAST(hd.Tongtien AS DECIMAL(15,2))) as TotalRevenue, 
                  COUNT(hd.SohieuHD) as TotalOrders,
                  COUNT(DISTINCT hd.id) as UniqueCustomers
                  FROM hoadon hd
                  WHERE hd.Trangthai = 'Giao hàng thành công'
                  AND YEAR(hd.NgayBH) = ?
                  AND MONTH(hd.NgayBH) = ?";
        
        $stmt = $con->prepare($query);
        $stmt->bind_param("ii", $year, $m);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            error_log("SQL Error in getMonthlyStats for month $m: " . $con->error);
            $months[$m] = ['TotalRevenue' => 0, 'TotalOrders' => 0, 'UniqueCustomers' => 0];
        } else {
            $months[$m] = $result->fetch_assoc();
        }
    }
    return $months;
}

// Hàm lấy thống kê sản phẩm bán chạy - SỬA LẠI LOGIC
function getTopProductsStats($con, $startDate, $endDate, $limit = 10) {
    // Sử dụng prepared statement và sửa logic tính toán
    $query = "SELECT 
                h.Tenhang,
                SUM(ct.Soluong) as total_sold,
                SUM(ct.Thanhtien) as total_revenue,
                COUNT(DISTINCT hd.SohieuHD) as order_count,
                AVG(ct.Thanhtien) as avg_price_per_item
              FROM chitiethd ct
              JOIN hang h ON ct.Mahang = h.Mahang
              JOIN hoadon hd ON ct.SohieuHD = hd.SohieuHD
              WHERE hd.Trangthai = 'Giao hàng thành công'
              AND hd.NgayBH BETWEEN ? AND ?
              GROUP BY h.Mahang, h.Tenhang
              ORDER BY total_sold DESC
              LIMIT ?";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("ssi", $startDate, $endDate, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        error_log("SQL Error in getTopProductsStats: " . $con->error);
        return false;
    }
    
    return $result;
}



// Lấy dữ liệu thống kê
$customerStats = getCustomerStats($con, $startDate, $endDate);
$quarterlyStats = getQuarterlyStats($con, $selectedYear);
$monthlyStats = getMonthlyStats($con, $selectedYear);
$topProductsStats = getTopProductsStats($con, $startDate, $endDate);

// Tính tổng doanh thu năm để tính tỉ lệ
$yearlyTotalQuery = "SELECT SUM(CAST(hd.Tongtien AS DECIMAL(15,2))) as TotalRevenue
                     FROM hoadon hd
                     WHERE hd.Trangthai = 'Giao hàng thành công'
                     AND YEAR(hd.NgayBH) = ?";
$yearlyTotalStmt = $con->prepare($yearlyTotalQuery);
$yearlyTotalStmt->bind_param("i", $selectedYear);
$yearlyTotalStmt->execute();
$yearlyTotalResult = $yearlyTotalStmt->get_result();

if (!$yearlyTotalResult) {
    error_log("SQL Error in yearlyTotalQuery: " . $con->error);
    $yearlyTotal = 0;
} else {
    $yearlyTotalData = $yearlyTotalResult->fetch_assoc();
    $yearlyTotal = $yearlyTotalData['TotalRevenue'] ? $yearlyTotalData['TotalRevenue'] : 0;
}
?>





<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê - ADMIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/trangchu.css">
    <link rel="stylesheet" href="css/dashboard_stats.css">
    <link rel="stylesheet" href="css/thongke.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container-fluid">



    <!-- Form chọn loại báo cáo -->
    <div class="stats-container">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Loại báo cáo:</label>
                <select name="report_type" class="form-select">
                    <option value="customer" <?php echo ($reportType == 'customer') ? 'selected' : ''; ?>>Khách hàng mua nhiều/ít</option>
                    <option value="quarterly" <?php echo ($reportType == 'quarterly') ? 'selected' : ''; ?>>Thống kê theo quý</option>
                    <option value="monthly" <?php echo ($reportType == 'monthly') ? 'selected' : ''; ?>>Thống kê theo tháng</option>
                    <option value="top-products" <?php echo ($reportType == 'top-products') ? 'selected' : ''; ?>>Sản phẩm bán chạy</option>
                </select>
        </div>
            
            <div class="col-md-3">
                <label class="form-label">Từ ngày:</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $startDate; ?>">
                </div>
            
            <div class="col-md-3">
                <label class="form-label">Đến ngày:</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $endDate; ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Năm:</label>
                <select name="year" class="form-select">
                    <?php for ($year = date('Y'); $year >= 2020; $year--): ?>
                        <option value="<?php echo $year; ?>" <?php echo ($year == $selectedYear) ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Nút xuất Excel -->
    <div class="text-end mb-3">
        <a href="export_excel.php?report_type=<?php echo urlencode($reportType); ?>&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>&year=<?php echo urlencode($selectedYear); ?>" class="btn btn-export">
            <i class="fas fa-file-excel"></i> Xuất Excel
        </a>
    </div>

    <?php if ($reportType == 'customer'): ?>
        <!-- Thống kê khách hàng -->
        <div class="stats-container">
            <h3><i class="fas fa-users"></i> Thống Kê Khách Hàng (<?php echo date('d/m/Y', strtotime($startDate)); ?> - <?php echo date('d/m/Y', strtotime($endDate)); ?>)</h3>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>STT</th>
                            <th>Tên Khách Hàng</th>
                            <th>Số Điện Thoại</th>
            <th>Số Đơn Hàng</th>
                            <th>Tổng Chi Tiêu (VNĐ)</th>
                            <th>Trung Bình/Đơn (VNĐ)</th>
                            <th>Xếp Hạng</th>
        </tr>
    </thead>
    <tbody>
        <?php 
                         $rank = 1;
                         if ($customerStats && $customerStats->num_rows > 0):
                             while ($row = $customerStats->fetch_assoc()): 
        ?>
            <tr>
                                <td><?php echo $rank; ?></td>
                                <td><?php echo htmlspecialchars($row['Tenkhach']); ?></td>
                                <td><?php echo htmlspecialchars($row['SDT']); ?></td>
                                <td><?php echo $row['TotalOrders']; ?></td>
                                <td><?php echo number_format($row['TotalSpent'], 0, ',', '.'); ?></td>
                                <td><?php echo number_format($row['AvgOrderValue'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ($rank <= 3): ?>
                                        <span class="badge bg-warning">Top <?php echo $rank; ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo $rank; ?></span>
                                    <?php endif; ?>
                                </td>
                                                         </tr>
                         <?php 
                         $rank++;
                         endwhile; 
                                                  endif; 
                         ?>
                     </tbody>
                 </table>
                 <?php if (!$customerStats || $customerStats->num_rows == 0): ?>
                     <div class="alert alert-info text-center">
                         <i class="fas fa-info-circle"></i> Không có dữ liệu khách hàng trong khoảng thời gian đã chọn.
                     </div>
                 <?php endif; ?>
            </div>
        </div>

    <?php elseif ($reportType == 'quarterly'): ?>
        <!-- Thống kê theo quý -->
        <div class="stats-container">
            <h3><i class="fas fa-chart-pie"></i> Thống Kê Theo Quý - Năm <?php echo $selectedYear; ?></h3>
            
            <div class="row">
                <?php foreach ($quarterlyStats as $quarter => $data): ?>
                    <div class="col-md-3">
                        <div class="quarter-card">
                            <h5>Quý <?php echo $quarter; ?></h5>
                            <p><strong>Doanh thu:</strong> <?php echo number_format($data['TotalRevenue'], 0, ',', '.'); ?> VNĐ</p>
                            <p><strong>Số đơn hàng:</strong> <?php echo $data['TotalOrders']; ?></p>
                            <p><strong>Khách hàng:</strong> <?php echo $data['UniqueCustomers']; ?></p>
                            <?php if ($yearlyTotal > 0): ?>
                                <p class="percentage">
                                    Tỉ lệ: <?php echo number_format(($data['TotalRevenue'] / $yearlyTotal) * 100, 1); ?>%
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Quý</th>
                            <th>Doanh Thu (VNĐ)</th>
                            <th>Số Đơn Hàng</th>
                            <th>Khách Hàng</th>
                            <th>Tỉ Lệ Doanh Thu</th>
                            <th>Trung Bình/Đơn (VNĐ)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quarterlyStats as $quarter => $data): ?>
                            <tr>
                                <td><strong>Quý <?php echo $quarter; ?></strong></td>
                                <td><?php echo number_format($data['TotalRevenue'], 0, ',', '.'); ?></td>
                                <td><?php echo $data['TotalOrders']; ?></td>
                                <td><?php echo $data['UniqueCustomers']; ?></td>
                                <td>
                                    <?php if ($yearlyTotal > 0): ?>
                                        <?php echo number_format(($data['TotalRevenue'] / $yearlyTotal) * 100, 1); ?>%
                                    <?php else: ?>
                                        0%
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $data['TotalOrders'] > 0 ? number_format($data['TotalRevenue'] / $data['TotalOrders'], 0, ',', '.') : 0; ?>
                                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
            </div>
        </div>

    <?php elseif ($reportType == 'monthly'): ?>
        <!-- Thống kê theo tháng -->
        <div class="stats-container">
            <h3><i class="fas fa-calendar-alt"></i> Thống Kê Theo Tháng - Năm <?php echo $selectedYear; ?></h3>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
        <tr>
            <th>Tháng</th>
            <th>Doanh Thu (VNĐ)</th>
            <th>Số Đơn Hàng</th>
                            <th>Khách Hàng</th>
                            <th>Tỉ Lệ Doanh Thu</th>
                            <th>Trung Bình/Đơn (VNĐ)</th>
                            <th>Trung Bình/Khách (VNĐ)</th>
        </tr>
    </thead>
    <tbody>
                        <?php foreach ($monthlyStats as $month => $data): ?>
                            <tr>
                                <td><strong>Tháng <?php echo $month; ?></strong></td>
                                <td><?php echo number_format($data['TotalRevenue'], 0, ',', '.'); ?></td>
                                <td><?php echo $data['TotalOrders']; ?></td>
                                <td><?php echo $data['UniqueCustomers']; ?></td>
                                <td>
                                    <?php if ($yearlyTotal > 0): ?>
                                        <?php echo number_format(($data['TotalRevenue'] / $yearlyTotal) * 100, 1); ?>%
                                    <?php else: ?>
                                        0%
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $data['TotalOrders'] > 0 ? number_format($data['TotalRevenue'] / $data['TotalOrders'], 0, ',', '.') : 0; ?>
                                </td>
                                <td>
                                    <?php echo $data['UniqueCustomers'] > 0 ? number_format($data['TotalRevenue'] / $data['UniqueCustomers'], 0, ',', '.') : 0; ?>
                                </td>
            </tr>
                        <?php endforeach; ?>
    </tbody>
</table>
            </div>

            <!-- Biểu đồ doanh thu theo tháng -->
            <div class="chart-container">
                <h4 class="text-center mb-3">Biểu Đồ Doanh Thu Theo Tháng</h4>
                <canvas id="monthlyChart" width="400" height="200"></canvas>
            </div>
        </div>
    <?php elseif ($reportType == 'top-products'): ?>
        <!-- Thống kê sản phẩm bán chạy -->
        <div class="stats-container">
            <h3><i class="fas fa-chart-line"></i> Thống Kê Sản Phẩm Bán Chạy (<?php echo date('d/m/Y', strtotime($startDate)); ?> - <?php echo date('d/m/Y', strtotime($endDate)); ?>)</h3>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>STT</th>
                            <th>Tên Sản Phẩm</th>
                            <th>Số Lượng Đã Bán</th>
                            <th>Tổng Doanh Thu (VNĐ)</th>
                            <th>Số Đơn Hàng</th>
                            <th>Trung Bình/Đơn (VNĐ)</th>
                            <th>Giá Trung Bình/Sản Phẩm (VNĐ)</th>
                            <th>Xếp Hạng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        if ($topProductsStats && $topProductsStats->num_rows > 0):
                            while ($row = $topProductsStats->fetch_assoc()): 
                        ?>
                            <tr>
                                <td><?php echo $rank; ?></td>
                                <td><?php echo htmlspecialchars($row['Tenhang']); ?></td>
                                <td><?php echo number_format($row['total_sold']); ?> sản phẩm</td>
                                <td><?php echo number_format($row['total_revenue'], 0, ',', '.'); ?></td>
                                <td><?php echo $row['order_count']; ?></td>
                                <td><?php echo number_format($row['total_revenue'] / $row['order_count'], 0, ',', '.'); ?></td>
                                <td><?php echo number_format($row['avg_price_per_item'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ($rank <= 3): ?>
                                        <span class="badge bg-warning">Top <?php echo $rank; ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo $rank; ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php 
                        $rank++;
                        endwhile; 
                        endif; 
                        ?>
                    </tbody>
                </table>
                <?php if (!$topProductsStats || $topProductsStats->num_rows == 0): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> Không có dữ liệu sản phẩm bán chạy trong khoảng thời gian đã chọn.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
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