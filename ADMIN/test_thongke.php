<?php
// File test để kiểm tra thống kê
session_start();

// Kết nối database
include '../db_connect.php';

echo "<h2>Test Database Connection and Statistics</h2>";

// Test 1: Kiểm tra kết nối
echo "<h3>1. Database Connection Test</h3>";
if ($con) {
    echo "✅ Database connection successful<br>";
    echo "Database: " . $con->database . "<br>";
} else {
    echo "❌ Database connection failed: " . mysqli_connect_error() . "<br>";
    exit;
}

// Test 2: Kiểm tra cấu trúc bảng
echo "<h3>2. Table Structure Test</h3>";

// Kiểm tra bảng khach
$khachQuery = "DESCRIBE khach";
$khachResult = $con->query($khachQuery);
if ($khachResult) {
    echo "✅ Bảng khach tồn tại<br>";
    echo "Các cột trong bảng khach:<br>";
    while ($row = $khachResult->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
} else {
    echo "❌ Bảng khach không tồn tại hoặc có lỗi: " . $con->error . "<br>";
}

// Kiểm tra bảng hoadon
$hoadonQuery = "DESCRIBE hoadon";
$hoadonResult = $con->query($hoadonQuery);
if ($hoadonResult) {
    echo "✅ Bảng hoadon tồn tại<br>";
    echo "Các cột trong bảng hoadon:<br>";
    while ($row = $hoadonResult->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
} else {
    echo "❌ Bảng hoadon không tồn tại hoặc có lỗi: " . $con->error . "<br>";
}

// Test 3: Kiểm tra dữ liệu mẫu
echo "<h3>3. Sample Data Test</h3>";

// Lấy dữ liệu khách hàng
$customerQuery = "SELECT * FROM khach LIMIT 5";
$customerResult = $con->query($customerQuery);
if ($customerResult && $customerResult->num_rows > 0) {
    echo "✅ Có " . $customerResult->num_rows . " khách hàng trong database<br>";
    echo "Dữ liệu khách hàng mẫu:<br>";
    while ($row = $customerResult->fetch_assoc()) {
        echo "- ID: " . $row['id'] . ", Tên: " . $row['Tenkhach'] . ", ĐT: " . $row['Dienthoai'] . "<br>";
    }
} else {
    echo "❌ Không có dữ liệu khách hàng hoặc có lỗi: " . $con->error . "<br>";
}

// Lấy dữ liệu hóa đơn
$orderQuery = "SELECT * FROM hoadon WHERE Trangthai = 'Giao hàng thành công' LIMIT 5";
$orderResult = $con->query($orderQuery);
if ($orderResult && $orderResult->num_rows > 0) {
    echo "✅ Có " . $orderResult->num_rows . " hóa đơn thành công trong database<br>";
    echo "Dữ liệu hóa đơn mẫu:<br>";
    while ($row = $orderResult->fetch_assoc()) {
        echo "- Mã HD: " . $row['SohieuHD'] . ", Khách ID: " . $row['id'] . ", Ngày: " . $row['NgayBH'] . ", Tổng tiền: " . $row['Tongtien'] . "<br>";
    }
} else {
    echo "❌ Không có hóa đơn thành công hoặc có lỗi: " . $con->error . "<br>";
}

// Test 4: Kiểm tra thống kê theo thời gian
echo "<h3>4. Statistics Test</h3>";

$startDate = '2025-08-01';
$endDate = '2025-08-31';

// Kiểm tra tổng quan
$overviewQuery = "SELECT 
                    COUNT(*) as total_orders,
                    COUNT(DISTINCT hd.id) as unique_customers,
                    SUM(CAST(hd.Tongtien AS DECIMAL(15,2))) as total_revenue
                  FROM hoadon hd
                  WHERE hd.Trangthai = 'Giao hàng thành công'
                  AND hd.NgayBH BETWEEN '$startDate' AND '$endDate'";

$overviewResult = $con->query($overviewQuery);
if ($overviewResult) {
    $overview = $overviewResult->fetch_assoc();
    echo "📊 Thống kê tổng quan ($startDate đến $endDate):<br>";
    echo "- Tổng đơn hàng: " . $overview['total_orders'] . "<br>";
    echo "- Khách hàng unique: " . $overview['unique_customers'] . "<br>";
    echo "- Tổng doanh thu: " . number_format($overview['total_revenue'], 0, ',', '.') . " VNĐ<br>";
} else {
    echo "❌ Lỗi khi tính thống kê tổng quan: " . $con->error . "<br>";
}

// Test 5: Kiểm tra thống kê khách hàng chi tiết
echo "<h3>5. Customer Statistics Test</h3>";

$customerStatsQuery = "SELECT 
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
                      ORDER BY TotalSpent DESC";

$customerStatsResult = $con->query($customerStatsQuery);
if ($customerStatsResult) {
    echo "✅ Query thống kê khách hàng thành công<br>";
    echo "Số lượng khách hàng có đơn hàng: " . $customerStatsResult->num_rows . "<br>";
    
    if ($customerStatsResult->num_rows > 0) {
        echo "Dữ liệu thống kê khách hàng:<br>";
        $rank = 1;
        while ($row = $customerStatsResult->fetch_assoc()) {
            echo "<strong>$rank.</strong> " . $row['Tenkhach'] . " | ĐT: " . $row['SDT'] . " | Đơn hàng: " . $row['TotalOrders'] . " | Tổng chi: " . number_format($row['TotalSpent'], 0, ',', '.') . " VNĐ | TB/đơn: " . number_format($row['AvgOrderValue'], 0, ',', '.') . " VNĐ<br>";
            $rank++;
        }
    } else {
        echo "⚠️ Không có khách hàng nào có đơn hàng trong khoảng thời gian này<br>";
    }
} else {
    echo "❌ Lỗi khi tính thống kê khách hàng: " . $con->error . "<br>";
}

// Test 6: Kiểm tra dữ liệu theo năm 2025
echo "<h3>6. 2025 Data Test</h3>";

$year2025Query = "SELECT 
                    COUNT(*) as total_orders,
                    COUNT(DISTINCT hd.id) as unique_customers,
                    SUM(CAST(hd.Tongtien AS DECIMAL(15,2))) as total_revenue
                  FROM hoadon hd
                  WHERE hd.Trangthai = 'Giao hàng thành công'
                  AND YEAR(hd.NgayBH) = 2025";

$year2025Result = $con->query($year2025Query);
if ($year2025Result) {
    $year2025 = $year2025Result->fetch_assoc();
    echo "📊 Thống kê năm 2025:<br>";
    echo "- Tổng đơn hàng: " . $year2025['total_orders'] . "<br>";
    echo "- Khách hàng unique: " . $year2025['unique_customers'] . "<br>";
    echo "- Tổng doanh thu: " . number_format($year2025['total_revenue'], 0, ',', '.') . " VNĐ<br>";
} else {
    echo "❌ Lỗi khi tính thống kê năm 2025: " . $con->error . "<br>";
}

// Test 7: Kiểm tra tất cả dữ liệu hóa đơn
echo "<h3>7. All Orders Test</h3>";

$allOrdersQuery = "SELECT hd.*, k.Tenkhach 
                   FROM hoadon hd 
                   LEFT JOIN khach k ON hd.id = k.id 
                   ORDER BY hd.NgayBH DESC 
                   LIMIT 10";

$allOrdersResult = $con->query($allOrdersQuery);
if ($allOrdersResult && $allOrdersResult->num_rows > 0) {
    echo "📋 10 hóa đơn gần nhất:<br>";
    while ($row = $allOrdersResult->fetch_assoc()) {
        echo "- Mã: " . $row['SohieuHD'] . " | Khách: " . $row['Tenkhach'] . " | Ngày: " . $row['NgayBH'] . " | Trạng thái: " . $row['Trangthai'] . " | Tiền: " . $row['Tongtien'] . "<br>";
    }
} else {
    echo "❌ Không có dữ liệu hóa đơn hoặc có lỗi: " . $con->error . "<br>";
}

$con->close();
echo "<br><strong>Test completed!</strong>";
?> 