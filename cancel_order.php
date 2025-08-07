<?php
session_start();
include 'db_connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: Login_singup/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Xử lý yêu cầu hủy đơn hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sohieuHD = isset($_POST['sohieuHD']) ? $_POST['sohieuHD'] : '';
    
    if (empty($sohieuHD)) {
        $_SESSION['error'] = "Thiếu mã đơn hàng";
        header('Location: theodoi.php');
        exit;
    }
    
    // Kiểm tra xem đơn hàng có thuộc về người dùng này không
    $checkOrder = "SELECT Trangthai FROM hoadon WHERE SohieuHD = ? AND id = ?";
    $stmtCheck = $con->prepare($checkOrder);
    $stmtCheck->bind_param("si", $sohieuHD, $user_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    
    if ($resultCheck->num_rows == 0) {
        $_SESSION['error'] = "Không tìm thấy đơn hàng hoặc bạn không có quyền hủy đơn hàng này";
        header('Location: theodoi.php');
        exit;
    }
    
    $currentStatus = $resultCheck->fetch_assoc()['Trangthai'];
    
    // Chỉ cho phép hủy đơn hàng đang xử lý hoặc đang giao hàng
    if ($currentStatus == "Đã hủy" || $currentStatus == "Giao hàng thành công") {
        $_SESSION['error'] = "Không thể hủy đơn hàng ở trạng thái này";
        header('Location: theodoi.php');
        exit;
    }
    
    // Bắt đầu transaction
    $con->begin_transaction();
    
    try {
        // Cập nhật trạng thái đơn hàng thành "Đã hủy"
        $updateOrder = "UPDATE hoadon SET Trangthai = 'Đã hủy' WHERE SohieuHD = ? AND id = ?";
        $stmtUpdate = $con->prepare($updateOrder);
        $stmtUpdate->bind_param("si", $sohieuHD, $user_id);
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
        
        $stmtDetails->close();
        
        $_SESSION['success'] = "Đơn hàng đã được hủy thành công và số lượng tồn kho đã được hoàn trả";
        
        // Commit transaction
        $con->commit();
        
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $con->rollback();
        $_SESSION['error'] = "Lỗi khi hủy đơn hàng: " . $e->getMessage();
    }
    
    // Đóng statements sau khi commit/rollback
    if (isset($stmtCheck)) $stmtCheck->close();
    if (isset($stmtUpdate)) $stmtUpdate->close();
    
    header('Location: theodoi.php');
    exit;
    
} else {
    // Nếu không phải POST request, chuyển hướng về trang theo dõi đơn hàng
    header('Location: theodoi.php');
    exit;
}
?> 