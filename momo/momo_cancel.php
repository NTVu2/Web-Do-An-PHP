<?php
session_start();
include '../db_connect.php';

// Xử lý hủy đơn hàng MoMo
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sohieuHD = isset($_POST['sohieuHD']) ? $_POST['sohieuHD'] : '';
    
    if (empty($sohieuHD)) {
        echo json_encode(['success' => false, 'message' => 'Thiếu mã đơn hàng']);
        exit;
    }
    
    // Bắt đầu transaction
    $con->begin_transaction();
    
    try {
        // Kiểm tra trạng thái đơn hàng hiện tại
        $checkOrder = "SELECT Trangthai FROM hoadon WHERE SohieuHD = ?";
        $stmtCheck = $con->prepare($checkOrder);
        $stmtCheck->bind_param("s", $sohieuHD);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        
        if ($resultCheck->num_rows == 0) {
            throw new Exception("Không tìm thấy đơn hàng");
        }
        
        $currentStatus = $resultCheck->fetch_assoc()['Trangthai'];
        
        // Nếu đơn hàng đã bị hủy rồi
        if ($currentStatus == "Đã hủy") {
            throw new Exception("Đơn hàng đã bị hủy trước đó");
        }
        
        // Cập nhật trạng thái đơn hàng thành "Đã hủy"
        $updateOrder = "UPDATE hoadon SET Trangthai = 'Đã hủy' WHERE SohieuHD = ?";
        $stmtUpdate = $con->prepare($updateOrder);
        $stmtUpdate->bind_param("s", $sohieuHD);
        $stmtUpdate->execute();
        
        // Luôn hoàn trả số lượng khi hủy đơn hàng (vì đã trừ khi đặt hàng)
        $sqlDetails = "SELECT Mahang, Soluong FROM chitiethd WHERE SohieuHD = ?";
        $stmtDetails = $con->prepare($sqlDetails);
        $stmtDetails->bind_param("s", $sohieuHD);
        $stmtDetails->execute();
        $resultDetails = $stmtDetails->get_result();

        while ($row = $resultDetails->fetch_assoc()) {
            $Mahang = $row['Mahang'];
            $SoluongBan = $row['Soluong'];

            // Cộng lại số lượng đã bán vào số lượng tồn
            $updateHang = "UPDATE hang SET Soluongton = Soluongton + ? WHERE Mahang = ?";
            $stmtUpdateHang = $con->prepare($updateHang);
            $stmtUpdateHang->bind_param("is", $SoluongBan, $Mahang);
            
            if (!$stmtUpdateHang->execute()) {
                throw new Exception("Lỗi khi cập nhật số lượng tồn kho cho sản phẩm: " . $Mahang . " - " . $con->error);
            }
            
            $stmtUpdateHang->close();
        }
        
        echo json_encode(['success' => true, 'message' => 'Đơn hàng đã được hủy thành công và số lượng tồn kho đã được hoàn trả']);
        
        // Commit transaction
        $con->commit();
        
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $con->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
    $stmtCheck->close();
    $stmtUpdate->close();
    $stmtDetails->close();
    
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
}
?> 