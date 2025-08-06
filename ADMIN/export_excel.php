<?php
session_start();
$loggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
$quyen = isset($_SESSION['quyen']) ? $_SESSION['quyen'] : [];  // Lấy quyền từ session, mặc định là mảng trống nếu không có
if (!in_array('thongke', $quyen)) {
    echo "Bạn không có quyền truy cập trang này.";
    header("Location: loginADMIN.php");
    exit;
}
// Kiểm tra nếu admin đã đăng nhập
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: loginAdmin.php");
    exit;
}

include '../db_connect.php';

// Lấy tham số từ form
$reportType = isset($_GET['report_type']) ? $_GET['report_type'] : 'customer';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Hàm lấy thống kê khách hàng
function getCustomerStats($con, $startDate, $endDate) {
    $query = "SELECT k.Tenkhach, k.SDT, COUNT(hd.SohieuHD) as TotalOrders, 
              SUM(hd.Tongtien) as TotalSpent, AVG(hd.Tongtien) as AvgOrderValue
              FROM khach k
              JOIN hoadon hd ON k.id = hd.id
              WHERE hd.Trangthai = 'Giao hàng thành công'
              AND hd.NgayBH BETWEEN '$startDate' AND '$endDate'
              GROUP BY k.id
              ORDER BY TotalSpent DESC";
    $result = $con->query($query);
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
        $query = "SELECT SUM(hd.Tongtien) as TotalRevenue, 
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
        $query = "SELECT SUM(hd.Tongtien) as TotalRevenue, 
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

// Tính tổng doanh thu năm
$yearlyTotalQuery = "SELECT SUM(hd.Tongtien) as TotalRevenue
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

// Thiết lập header cho Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="thongke_' . $reportType . '_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Bắt đầu xuất Excel
echo '<table border="1">';

if ($reportType == 'customer') {
    // Header cho thống kê khách hàng
    echo '<tr style="background-color: #4CAF50; color: white; font-weight: bold;">';
    echo '<td colspan="7" style="text-align: center; font-size: 16px;">THỐNG KÊ KHÁCH HÀNG MUA NHIỀU/ÍT</td>';
    echo '</tr>';
    echo '<tr style="background-color: #4CAF50; color: white; font-weight: bold;">';
    echo '<td colspan="7" style="text-align: center;">Từ ngày: ' . date('d/m/Y', strtotime($startDate)) . ' - Đến ngày: ' . date('d/m/Y', strtotime($endDate)) . '</td>';
    echo '</tr>';
    echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
    echo '<td>STT</td>';
    echo '<td>Tên Khách Hàng</td>';
    echo '<td>Số Điện Thoại</td>';
    echo '<td>Số Đơn Hàng</td>';
    echo '<td>Tổng Chi Tiêu (VNĐ)</td>';
    echo '<td>Trung Bình/Đơn (VNĐ)</td>';
    echo '<td>Xếp Hạng</td>';
    echo '</tr>';

    $customerStats = getCustomerStats($con, $startDate, $endDate);
    $rank = 1;
    if ($customerStats && $customerStats->num_rows > 0) {
        while ($row = $customerStats->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $rank . '</td>';
        echo '<td>' . $row['Tenkhach'] . '</td>';
        echo '<td>' . $row['SDT'] . '</td>';
        echo '<td>' . $row['TotalOrders'] . '</td>';
        echo '<td>' . number_format($row['TotalSpent'], 0, ',', '.') . '</td>';
        echo '<td>' . number_format($row['AvgOrderValue'], 0, ',', '.') . '</td>';
        echo '<td>' . ($rank <= 3 ? 'Top ' . $rank : $rank) . '</td>';
        echo '</tr>';
        $rank++;
        }
    }

} elseif ($reportType == 'quarterly') {
    // Header cho thống kê theo quý
    echo '<tr style="background-color: #2196F3; color: white; font-weight: bold;">';
    echo '<td colspan="6" style="text-align: center; font-size: 16px;">THỐNG KÊ THEO QUÝ - NĂM ' . $selectedYear . '</td>';
    echo '</tr>';
    echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
    echo '<td>Quý</td>';
    echo '<td>Doanh Thu (VNĐ)</td>';
    echo '<td>Số Đơn Hàng</td>';
    echo '<td>Khách Hàng</td>';
    echo '<td>Tỉ Lệ Doanh Thu (%)</td>';
    echo '<td>Trung Bình/Đơn (VNĐ)</td>';
    echo '</tr>';

    $quarterlyStats = getQuarterlyStats($con, $selectedYear);
    foreach ($quarterlyStats as $quarter => $data) {
        echo '<tr>';
        echo '<td>Quý ' . $quarter . '</td>';
        echo '<td>' . number_format($data['TotalRevenue'], 0, ',', '.') . '</td>';
        echo '<td>' . $data['TotalOrders'] . '</td>';
        echo '<td>' . $data['UniqueCustomers'] . '</td>';
        echo '<td>' . ($yearlyTotal > 0 ? number_format(($data['TotalRevenue'] / $yearlyTotal) * 100, 1) : 0) . '</td>';
        echo '<td>' . ($data['TotalOrders'] > 0 ? number_format($data['TotalRevenue'] / $data['TotalOrders'], 0, ',', '.') : 0) . '</td>';
        echo '</tr>';
    }

    // Tổng cộng
    echo '<tr style="background-color: #e9ecef; font-weight: bold;">';
    echo '<td>TỔNG CỘNG</td>';
    echo '<td>' . number_format($yearlyTotal, 0, ',', '.') . '</td>';
    echo '<td>' . array_sum(array_column($quarterlyStats, 'TotalOrders')) . '</td>';
    echo '<td>' . array_sum(array_column($quarterlyStats, 'UniqueCustomers')) . '</td>';
    echo '<td>100%</td>';
    echo '<td>-</td>';
    echo '</tr>';

} elseif ($reportType == 'monthly') {
    // Header cho thống kê theo tháng
    echo '<tr style="background-color: #FF9800; color: white; font-weight: bold;">';
    echo '<td colspan="7" style="text-align: center; font-size: 16px;">THỐNG KÊ THEO THÁNG - NĂM ' . $selectedYear . '</td>';
    echo '</tr>';
    echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
    echo '<td>Tháng</td>';
    echo '<td>Doanh Thu (VNĐ)</td>';
    echo '<td>Số Đơn Hàng</td>';
    echo '<td>Khách Hàng</td>';
    echo '<td>Tỉ Lệ Doanh Thu (%)</td>';
    echo '<td>Trung Bình/Đơn (VNĐ)</td>';
    echo '<td>Trung Bình/Khách (VNĐ)</td>';
    echo '</tr>';

    $monthlyStats = getMonthlyStats($con, $selectedYear);
    foreach ($monthlyStats as $month => $data) {
        echo '<tr>';
        echo '<td>Tháng ' . $month . '</td>';
        echo '<td>' . number_format($data['TotalRevenue'], 0, ',', '.') . '</td>';
        echo '<td>' . $data['TotalOrders'] . '</td>';
        echo '<td>' . $data['UniqueCustomers'] . '</td>';
        echo '<td>' . ($yearlyTotal > 0 ? number_format(($data['TotalRevenue'] / $yearlyTotal) * 100, 1) : 0) . '</td>';
        echo '<td>' . ($data['TotalOrders'] > 0 ? number_format($data['TotalRevenue'] / $data['TotalOrders'], 0, ',', '.') : 0) . '</td>';
        echo '<td>' . ($data['UniqueCustomers'] > 0 ? number_format($data['TotalRevenue'] / $data['UniqueCustomers'], 0, ',', '.') : 0) . '</td>';
        echo '</tr>';
    }

    // Tổng cộng
    echo '<tr style="background-color: #e9ecef; font-weight: bold;">';
    echo '<td>TỔNG CỘNG</td>';
    echo '<td>' . number_format($yearlyTotal, 0, ',', '.') . '</td>';
    echo '<td>' . array_sum(array_column($monthlyStats, 'TotalOrders')) . '</td>';
    echo '<td>' . array_sum(array_column($monthlyStats, 'UniqueCustomers')) . '</td>';
    echo '<td>100%</td>';
    echo '<td>-</td>';
    echo '<td>-</td>';
    echo '</tr>';

} elseif ($reportType == 'top-products') {
    // Header cho thống kê sản phẩm bán chạy
    echo '<tr style="background-color: #9C27B0; color: white; font-weight: bold;">';
    echo '<td colspan="7" style="text-align: center; font-size: 16px;">THỐNG KÊ SẢN PHẨM BÁN CHẠY</td>';
    echo '</tr>';
    echo '<tr style="background-color: #9C27B0; color: white; font-weight: bold;">';
    echo '<td colspan="7" style="text-align: center;">Từ ngày: ' . date('d/m/Y', strtotime($startDate)) . ' - Đến ngày: ' . date('d/m/Y', strtotime($endDate)) . '</td>';
    echo '</tr>';
    echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
    echo '<td>STT</td>';
    echo '<td>Tên Sản Phẩm</td>';
    echo '<td>Số Lượng Đã Bán</td>';
    echo '<td>Tổng Doanh Thu (VNĐ)</td>';
    echo '<td>Số Đơn Hàng</td>';
    echo '<td>Trung Bình/Đơn (VNĐ)</td>';
    echo '<td>Xếp Hạng</td>';
    echo '</tr>';

    $topProductsStats = getTopProductsStats($con, $startDate, $endDate);
    $rank = 1;
    if ($topProductsStats && $topProductsStats->num_rows > 0) {
        while ($row = $topProductsStats->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $rank . '</td>';
            echo '<td>' . $row['Tenhang'] . '</td>';
            echo '<td>' . $row['total_sold'] . ' sản phẩm</td>';
            echo '<td>' . number_format($row['total_revenue'], 0, ',', '.') . '</td>';
            echo '<td>' . $row['order_count'] . '</td>';
            echo '<td>' . number_format($row['total_revenue'] / $row['order_count'], 0, ',', '.') . '</td>';
            echo '<td>' . ($rank <= 3 ? 'Top ' . $rank : $rank) . '</td>';
            echo '</tr>';
            $rank++;
        }
    }
}

echo '</table>';

$con->close();
?> 