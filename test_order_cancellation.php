<?php
session_start();
include 'db_connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo "Bạn cần đăng nhập với quyền admin để xem thông tin này.";
    exit;
}

// Xử lý test hủy đơn hàng
if (isset($_POST['test_cancel'])) {
    $sohieuHD = $_POST['sohieuHD'];
    
    // Lấy thông tin đơn hàng trước khi hủy
    $sql_before = "SELECT h.SohieuHD, h.Trangthai, c.Mahang, c.Soluong, hang.Tenhang, hang.Soluongton as current_stock
                   FROM hoadon h 
                   JOIN chitiethd c ON h.SohieuHD = c.SohieuHD 
                   JOIN hang ON c.Mahang = hang.Mahang 
                   WHERE h.SohieuHD = ?";
    $stmt_before = $con->prepare($sql_before);
    $stmt_before->bind_param("s", $sohieuHD);
    $stmt_before->execute();
    $result_before = $stmt_before->get_result();
    
    echo "<h3>Thông tin trước khi hủy đơn hàng:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Số hóa đơn</th><th>Trạng thái</th><th>Mã hàng</th><th>Tên hàng</th><th>Số lượng đặt</th><th>Tồn kho hiện tại</th></tr>";
    
    while ($row = $result_before->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['SohieuHD'] . "</td>";
        echo "<td>" . $row['Trangthai'] . "</td>";
        echo "<td>" . $row['Mahang'] . "</td>";
        echo "<td>" . $row['Tenhang'] . "</td>";
        echo "<td>" . $row['Soluong'] . "</td>";
        echo "<td>" . $row['current_stock'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Thực hiện hủy đơn hàng
    $update_order = "UPDATE hoadon SET Trangthai = 'Đã hủy' WHERE SohieuHD = ?";
    $stmt_update = $con->prepare($update_order);
    $stmt_update->bind_param("s", $sohieuHD);
    $stmt_update->execute();
    
    // Cập nhật lại số lượng tồn kho
    $sql_details = "SELECT Mahang, Soluong FROM chitiethd WHERE SohieuHD = ?";
    $stmt_details = $con->prepare($sql_details);
    $stmt_details->bind_param("s", $sohieuHD);
    $stmt_details->execute();
    $result_details = $stmt_details->get_result();

    while ($row = $result_details->fetch_assoc()) {
        $Mahang = $row['Mahang'];
        $SoluongBan = $row['Soluong'];

        // Cộng lại số lượng đã bán vào số lượng tồn
        $updateHang = "UPDATE hang SET Soluongton = Soluongton + ? WHERE Mahang = ?";
        $stmtUpdate = $con->prepare($updateHang);
        $stmtUpdate->bind_param("is", $SoluongBan, $Mahang);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }
    
    // Lấy thông tin sau khi hủy
    $sql_after = "SELECT h.SohieuHD, h.Trangthai, c.Mahang, c.Soluong, hang.Tenhang, hang.Soluongton as new_stock
                  FROM hoadon h 
                  JOIN chitiethd c ON h.SohieuHD = c.SohieuHD 
                  JOIN hang ON c.Mahang = hang.Mahang 
                  WHERE h.SohieuHD = ?";
    $stmt_after = $con->prepare($sql_after);
    $stmt_after->bind_param("s", $sohieuHD);
    $stmt_after->execute();
    $result_after = $stmt_after->get_result();
    
    echo "<h3>Thông tin sau khi hủy đơn hàng:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Số hóa đơn</th><th>Trạng thái</th><th>Mã hàng</th><th>Tên hàng</th><th>Số lượng đặt</th><th>Tồn kho mới</th></tr>";
    
    while ($row = $result_after->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['SohieuHD'] . "</td>";
        echo "<td>" . $row['Trangthai'] . "</td>";
        echo "<td>" . $row['Mahang'] . "</td>";
        echo "<td>" . $row['Tenhang'] . "</td>";
        echo "<td>" . $row['Soluong'] . "</td>";
        echo "<td>" . $row['new_stock'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p style='color: green;'><strong>Đơn hàng đã được hủy thành công và số lượng tồn kho đã được cập nhật!</strong></p>";
}

// Hiển thị danh sách đơn hàng có thể hủy
echo "<h2>Test hủy đơn hàng</h2>";
echo "<p>Chọn đơn hàng để test việc hủy và cập nhật số lượng tồn kho:</p>";

$sql_orders = "SELECT h.SohieuHD, h.NgayBH, h.Tongtien, h.Trangthai, 
                      GROUP_CONCAT(CONCAT(c.Mahang, ':', c.Soluong) SEPARATOR ', ') as products
               FROM hoadon h 
               JOIN chitiethd c ON h.SohieuHD = c.SohieuHD 
               WHERE h.Trangthai != 'Đã hủy'
               GROUP BY h.SohieuHD 
               ORDER BY h.NgayBH DESC 
               LIMIT 10";
$result_orders = $con->query($sql_orders);

if ($result_orders->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Số hóa đơn</th>";
    echo "<th>Ngày</th>";
    echo "<th>Tổng tiền</th>";
    echo "<th>Trạng thái</th>";
    echo "<th>Sản phẩm</th>";
    echo "<th>Hành động</th>";
    echo "</tr>";
    
    while ($row = $result_orders->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['SohieuHD']) . "</td>";
        echo "<td>" . htmlspecialchars($row['NgayBH']) . "</td>";
        echo "<td>" . number_format($row['Tongtien'], 0, ',', '.') . " VND</td>";
        echo "<td>" . htmlspecialchars($row['Trangthai']) . "</td>";
        echo "<td>" . htmlspecialchars($row['products']) . "</td>";
        echo "<td>";
        echo "<form method='POST' style='display: inline;'>";
        echo "<input type='hidden' name='sohieuHD' value='" . $row['SohieuHD'] . "'>";
        echo "<input type='submit' name='test_cancel' value='Test hủy' style='background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;'>";
        echo "</form>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Không có đơn hàng nào để test.</p>";
}

$con->close();
?> 