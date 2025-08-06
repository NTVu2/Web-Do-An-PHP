<?php
// Không cần kiểm tra session và include database vì đã có trong trangchuadmin.php

// Xử lý xuất Excel
if (isset($_POST['export_excel'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="thongke_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// Lấy tham số từ form
$reportType = isset($_GET['report_type']) ? $_GET['report_type'] : 'customer';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Điều kiện thời gian
$timeCondition = "AND hd.NgayBH BETWEEN '$startDate' AND '$endDate'";
$yearCondition = "AND YEAR(hd.NgayBH) = $selectedYear";

// Hàm lấy thống kê khách hàng - SỬA LẠI LOGIC
function getCustomerStats($con, $startDate, $endDate, $limit = 10) {
    // Debug: Kiểm tra cấu trúc bảng trước
    $checkTableQuery = "DESCRIBE khach";
    $tableResult = $con->query($checkTableQuery);
    error_log("=== KHÁCH TABLE STRUCTURE ===");
    if ($tableResult) {
        while ($row = $tableResult->fetch_assoc()) {
            error_log("Column: " . $row['Field'] . " - Type: " . $row['Type']);
        }
    }
    
    // Debug: Kiểm tra cấu trúc bảng hoadon
    $checkHoadonQuery = "DESCRIBE hoadon";
    $hoadonResult = $con->query($checkHoadonQuery);
    error_log("=== HOADON TABLE STRUCTURE ===");
    if ($hoadonResult) {
        while ($row = $hoadonResult->fetch_assoc()) {
            error_log("Column: " . $row['Field'] . " - Type: " . $row['Type']);
        }
    }
    
    // Debug: Kiểm tra dữ liệu mẫu
    $sampleDataQuery = "SELECT k.id, k.Tenkhach, k.Dienthoai, hd.SohieuHD, hd.NgayBH, hd.Tongtien, hd.Trangthai 
                       FROM khach k 
                       LEFT JOIN hoadon hd ON k.id = hd.id 
                       WHERE hd.Trangthai = 'Giao hàng thành công'
                       ORDER BY hd.NgayBH DESC LIMIT 5";
    $sampleResult = $con->query($sampleDataQuery);
    error_log("=== SAMPLE DATA ===");
    if ($sampleResult) {
        while ($row = $sampleResult->fetch_assoc()) {
            error_log("Customer: " . $row['Tenkhach'] . " | Phone: " . $row['Dienthoai'] . " | Order: " . $row['SohieuHD'] . " | Date: " . $row['NgayBH'] . " | Amount: " . $row['Tongtien'] . " | Status: " . $row['Trangthai']);
        }
    }
    
    // Query chính - SỬA LẠI để sử dụng đúng tên cột
    $query = "SELECT 
                k.Tenkhach, 
                k.Dienthoai as SDT, 
                COUNT(hd.SohieuHD) as TotalOrders, 
                SUM(CAST(hd.Tongtien AS DECIMAL(15,2))) as TotalSpent, 
                AVG(CAST(hd.Tongtien AS DECIMAL(15,2))) as AvgOrderValue
              FROM khach k
              INNER JOIN hoadon hd ON k.id = hd.id
              WHERE hd.Trangthai = 'Giao hàng thành công'
              AND hd.NgayBH BETWEEN '$startDate' AND '$endDate'
              GROUP BY k.id, k.Tenkhach, k.Dienthoai
              ORDER BY TotalSpent DESC
              LIMIT $limit";
    
    // Debug: Log query để kiểm tra
    error_log("Customer Stats Query: " . $query);
    error_log("Start Date: " . $startDate . ", End Date: " . $endDate);
    
    $result = $con->query($query);
    if (!$result) {
        error_log("SQL Error in getCustomerStats: " . $con->error);
        return false;
    }
    
    // Debug: Log số lượng kết quả
    error_log("Customer Stats Result Rows: " . $result->num_rows);
    
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
                  AND YEAR(hd.NgayBH) = $year
                  AND QUARTER(hd.NgayBH) = $q";
        $result = $con->query($query);
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
                  AND YEAR(hd.NgayBH) = $year
                  AND MONTH(hd.NgayBH) = $m";
        $result = $con->query($query);
        if (!$result) {
            error_log("SQL Error in getMonthlyStats for month $m: " . $con->error);
            $months[$m] = ['TotalRevenue' => 0, 'TotalOrders' => 0, 'UniqueCustomers' => 0];
        } else {
            $months[$m] = $result->fetch_assoc();
        }
    }
    return $months;
}

// Hàm lấy thống kê sản phẩm bán chạy
function getTopProductsStats($con, $startDate, $endDate, $limit = 10) {
    $query = "SELECT 
        h.Tenhang,
        SUM(ct.Soluong) as total_sold,
        SUM(ct.Soluong * ct.Dongia) as total_revenue,
        COUNT(DISTINCT hd.SohieuHD) as order_count
    FROM chitiethd ct
    JOIN hang h ON ct.Mahang = h.Mahang
    JOIN hoadon hd ON ct.SohieuHD = hd.SohieuHD
    WHERE hd.Trangthai = 'Giao hàng thành công'
    AND hd.NgayBH BETWEEN '$startDate' AND '$endDate'
    GROUP BY h.Mahang, h.Tenhang
    ORDER BY total_sold DESC
    LIMIT $limit";
    
    $result = $con->query($query);
    if (!$result) {
        error_log("SQL Error in getTopProductsStats: " . $con->error);
        return false;
    }
    
    return $result;
}

// Debug: Kiểm tra dữ liệu trong khoảng thời gian - SỬA LẠI
$debugQuery = "SELECT COUNT(*) as total_orders, 
               COUNT(DISTINCT hd.id) as unique_customers,
               SUM(CAST(hd.Tongtien AS DECIMAL(15,2))) as total_revenue
               FROM hoadon hd
               WHERE hd.Trangthai = 'Giao hàng thành công'
               AND hd.NgayBH BETWEEN '$startDate' AND '$endDate'";
$debugResult = $con->query($debugQuery);
if ($debugResult) {
    $debugData = $debugResult->fetch_assoc();
    error_log("Debug Data for period $startDate to $endDate:");
    error_log("Total Orders: " . $debugData['total_orders']);
    error_log("Unique Customers: " . $debugData['unique_customers']);
    error_log("Total Revenue: " . $debugData['total_revenue']);
}

// Debug: Kiểm tra định dạng ngày trong database
$dateFormatQuery = "SELECT NgayBH, Trangthai, Tongtien FROM hoadon WHERE Trangthai = 'Giao hàng thành công' ORDER BY NgayBH DESC LIMIT 5";
$dateFormatResult = $con->query($dateFormatQuery);
if ($dateFormatResult) {
    error_log("Sample dates from database:");
    while ($row = $dateFormatResult->fetch_assoc()) {
        error_log("Date: " . $row['NgayBH'] . ", Status: " . $row['Trangthai'] . ", Amount: " . $row['Tongtien']);
    }
}

// Debug: Kiểm tra dữ liệu khách hàng có đơn hàng thành công
$customerCheckQuery = "SELECT DISTINCT k.id, k.Tenkhach, k.Dienthoai, COUNT(hd.SohieuHD) as order_count
                       FROM khach k
                       INNER JOIN hoadon hd ON k.id = hd.id
                       WHERE hd.Trangthai = 'Giao hàng thành công'
                       AND hd.NgayBH BETWEEN '$startDate' AND '$endDate'
                       GROUP BY k.id, k.Tenkhach, k.Dienthoai";
$customerCheckResult = $con->query($customerCheckQuery);
error_log("=== CUSTOMERS WITH SUCCESSFUL ORDERS ===");
if ($customerCheckResult) {
    error_log("Found " . $customerCheckResult->num_rows . " customers with successful orders");
    while ($row = $customerCheckResult->fetch_assoc()) {
        error_log("Customer ID: " . $row['id'] . " | Name: " . $row['Tenkhach'] . " | Phone: " . $row['Dienthoai'] . " | Orders: " . $row['order_count']);
    }
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
                     AND YEAR(hd.NgayBH) = $selectedYear";
$yearlyTotalResult = $con->query($yearlyTotalQuery);
if (!$yearlyTotalResult) {
    error_log("SQL Error in yearlyTotalQuery: " . $con->error);
    $yearlyTotal = 0;
} else {
    $yearlyTotalData = $yearlyTotalResult->fetch_assoc();
    $yearlyTotal = $yearlyTotalData['TotalRevenue'] ? $yearlyTotalData['TotalRevenue'] : 0;
}
?>

<link rel="stylesheet" href="css/thongke.css">



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
        <a href="export_excel.php?report_type=<?php echo $reportType; ?>&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>&year=<?php echo $selectedYear; ?>" class="btn btn-export">
            <i class="fas fa-file-excel"></i> Xuất Excel
        </a>
    </div>

    <?php if ($reportType == 'customer'): ?>
        <!-- Debug Info -->
        
        
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
                                <td><?php echo $row['total_sold']; ?> sản phẩm</td>
                                <td><?php echo number_format($row['total_revenue'], 0, ',', '.'); ?></td>
                                <td><?php echo $row['order_count']; ?></td>
                                <td><?php echo number_format($row['total_revenue'] / $row['order_count'], 0, ',', '.'); ?></td>
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




