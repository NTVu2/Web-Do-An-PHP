<?php
session_start();
include 'db_connect.php';

// Test debug việc hủy đơn hàng
echo "<h2>Test Debug Hủy Đơn Hàng</h2>";

// Kiểm tra có đơn hàng nào để test không
$sql = "SELECT h.SohieuHD, h.Trangthai, h.id, k.Tenkhach 
        FROM hoadon h 
        LEFT JOIN khach k ON h.id = k.id 
        WHERE h.Trangthai != 'Đã hủy' 
        ORDER BY h.NgayBH DESC 
        LIMIT 5";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    echo "<h3>Đơn hàng có thể test:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Số HĐ</th><th>Khách hàng</th><th>Trạng thái</th><th>Test</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['SohieuHD'] . "</td>";
        echo "<td>" . ($row['Tenkhach'] ?? 'N/A') . "</td>";
        echo "<td>" . $row['Trangthai'] . "</td>";
        echo "<td><a href='?test_cancel=" . $row['SohieuHD'] . "'>Test Hủy</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Không có đơn hàng nào để test.</p>";
}

// Xử lý test hủy đơn hàng
if (isset($_GET['test_cancel'])) {
    $sohieuHD = $_GET['test_cancel'];
    
    echo "<hr><h3>Test hủy đơn hàng: $sohieuHD</h3>";
    
    // Lấy thông tin trước khi hủy
    echo "<h4>1. Thông tin trước khi hủy:</h4>";
    $sql_before = "SELECT h.SohieuHD, h.Trangthai, c.Mahang, c.Soluong, hang.Tenhang, hang.Soluongton
                   FROM hoadon h
                   JOIN chitiethd c ON h.SohieuHD = c.SohieuHD
                   JOIN hang ON c.Mahang = hang.Mahang
                   WHERE h.SohieuHD = ?";
    $stmt = $con->prepare($sql_before);
    $stmt->bind_param("s", $sohieuHD);
    $stmt->execute();
    $result_before = $stmt->get_result();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Mã hàng</th><th>Tên hàng</th><th>SL đặt</th><th>Tồn kho hiện tại</th></tr>";
    
    $products = [];
    while ($row = $result_before->fetch_assoc()) {
        $products[] = $row;
        echo "<tr>";
        echo "<td>" . $row['Mahang'] . "</td>";
        echo "<td>" . $row['Tenhang'] . "</td>";
        echo "<td>" . $row['Soluong'] . "</td>";
        echo "<td>" . $row['Soluongton'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Thực hiện hủy đơn hàng
    echo "<h4>2. Thực hiện hủy đơn hàng...</h4>";
    
    $con->begin_transaction();
    
    try {
        // Cập nhật trạng thái
        $updateOrder = "UPDATE hoadon SET Trangthai = 'Đã hủy - Test' WHERE SohieuHD = ?";
        $stmtUpdate = $con->prepare($updateOrder);
        $stmtUpdate->bind_param("s", $sohieuHD);
        
        if (!$stmtUpdate->execute()) {
            throw new Exception("Lỗi cập nhật trạng thái: " . $con->error);
        }
        echo "✅ Cập nhật trạng thái thành công<br>";
        
        // Hoàn trả số lượng
        $sqlDetails = "SELECT Mahang, Soluong FROM chitiethd WHERE SohieuHD = ?";
        $stmtDetails = $con->prepare($sqlDetails);
        $stmtDetails->bind_param("s", $sohieuHD);
        $stmtDetails->execute();
        $resultDetails = $stmtDetails->get_result();
        
        $updated_count = 0;
        while ($row = $resultDetails->fetch_assoc()) {
            $Mahang = $row['Mahang'];
            $SoluongBan = $row['Soluong'];
            
            echo "Hoàn trả $SoluongBan cho sản phẩm $Mahang...<br>";
            
            // Cộng lại số lượng
            $updateHang = "UPDATE hang SET Soluongton = Soluongton + ? WHERE Mahang = ?";
            $stmtUpdateHang = $con->prepare($updateHang);
            $stmtUpdateHang->bind_param("is", $SoluongBan, $Mahang);
            
            if (!$stmtUpdateHang->execute()) {
                throw new Exception("Lỗi cập nhật tồn kho cho $Mahang: " . $con->error);
            }
            
            $affected_rows = $stmtUpdateHang->affected_rows;
            echo "Affected rows: $affected_rows<br>";
            $updated_count++;
            $stmtUpdateHang->close();
        }
        
        echo "✅ Hoàn trả $updated_count sản phẩm thành công<br>";
        
        $con->commit();
        echo "<strong style='color: green;'>✅ Transaction committed thành công!</strong><br>";
        
    } catch (Exception $e) {
        $con->rollback();
        echo "<strong style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</strong><br>";
    }
    
    // Kiểm tra kết quả sau khi hủy
    echo "<h4>3. Thông tin sau khi hủy:</h4>";
    $sql_after = "SELECT h.SohieuHD, h.Trangthai, c.Mahang, c.Soluong, hang.Tenhang, hang.Soluongton
                  FROM hoadon h
                  JOIN chitiethd c ON h.SohieuHD = c.SohieuHD
                  JOIN hang ON c.Mahang = hang.Mahang
                  WHERE h.SohieuHD = ?";
    $stmt_after = $con->prepare($sql_after);
    $stmt_after->bind_param("s", $sohieuHD);
    $stmt_after->execute();
    $result_after = $stmt_after->get_result();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Trạng thái</th><th>Mã hàng</th><th>Tên hàng</th><th>SL đặt</th><th>Tồn kho sau hủy</th><th>Thay đổi</th></tr>";
    
    $i = 0;
    while ($row = $result_after->fetch_assoc()) {
        $old_stock = $products[$i]['Soluongton'];
        $new_stock = $row['Soluongton'];
        $change = $new_stock - $old_stock;
        
        echo "<tr>";
        echo "<td>" . $row['Trangthai'] . "</td>";
        echo "<td>" . $row['Mahang'] . "</td>";
        echo "<td>" . $row['Tenhang'] . "</td>";
        echo "<td>" . $row['Soluong'] . "</td>";
        echo "<td>" . $new_stock . "</td>";
        echo "<td style='color: " . ($change > 0 ? 'green' : 'red') . ";'>+" . $change . "</td>";
        echo "</tr>";
        $i++;
    }
    echo "</table>";
    
    if ($change > 0) {
        echo "<p style='color: green;'><strong>✅ Số lượng đã được hoàn trả thành công!</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>❌ Số lượng KHÔNG được hoàn trả!</strong></p>";
    }
}

$con->close();
?>