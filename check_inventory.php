<?php
session_start();
include 'db_connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo "Bạn cần đăng nhập với quyền admin để xem thông tin này.";
    exit;
}

echo "<h2>Thông tin tồn kho hiện tại</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>Mã hàng</th>";
echo "<th>Tên hàng</th>";
echo "<th>Số lượng tồn</th>";
echo "<th>Đơn giá</th>";
echo "</tr>";

$sql = "SELECT Mahang, Tenhang, Soluongton, Dongia FROM hang ORDER BY Mahang";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Mahang']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Tenhang']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Soluongton']) . "</td>";
        echo "<td>" . number_format($row['Dongia'], 0, ',', '.') . " VND</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>Không có sản phẩm nào</td></tr>";
}

echo "</table>";

// Hiển thị thông tin đơn hàng gần đây
echo "<h2>Đơn hàng gần đây</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 20px;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>Số hóa đơn</th>";
echo "<th>Ngày</th>";
echo "<th>Tổng tiền</th>";
echo "<th>Trạng thái</th>";
echo "<th>Phương thức thanh toán</th>";
echo "</tr>";

$sql_orders = "SELECT h.SohieuHD, h.NgayBH, h.Tongtien, h.Trangthai, c.PTthanhtoan 
               FROM hoadon h 
               LEFT JOIN chitiethd c ON h.SohieuHD = c.SohieuHD 
               GROUP BY h.SohieuHD 
               ORDER BY h.NgayBH DESC 
               LIMIT 10";
$result_orders = $con->query($sql_orders);

if ($result_orders->num_rows > 0) {
    while ($row = $result_orders->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['SohieuHD']) . "</td>";
        echo "<td>" . htmlspecialchars($row['NgayBH']) . "</td>";
        echo "<td>" . number_format($row['Tongtien'], 0, ',', '.') . " VND</td>";
        echo "<td>" . htmlspecialchars($row['Trangthai']) . "</td>";
        echo "<td>" . htmlspecialchars($row['PTthanhtoan']) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>Không có đơn hàng nào</td></tr>";
}

echo "</table>";

$con->close();
?> 