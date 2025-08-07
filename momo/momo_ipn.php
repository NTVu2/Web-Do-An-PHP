<?php
session_start();
include '../db_connect.php';

// MoMo will send payment notifications to this URL
$data = json_decode(file_get_contents("php://input"), true);

if ($data['resultCode'] == '0') {
    // Payment successful, process the order
    $orderId = $data['orderId'];
    
    // Extract the order ID from the MoMo order ID (format: timestamp + HD + uniqid)
    $sohieuHD = substr($orderId, 10); // Remove the timestamp part
    
    // Check if this order has already been processed
    $checkOrder = "SELECT * FROM hoadon WHERE SohieuHD = ?";
    $stmtCheck = $con->prepare($checkOrder);
    $stmtCheck->bind_param("s", $sohieuHD);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    
    if ($resultCheck->num_rows == 0) {
        // Order doesn't exist yet, create it from session data
        if (isset($_SESSION['selectedProducts']) && isset($_SESSION['totalPrice']) && isset($_SESSION['customerId'])) {
            $selectedProducts = $_SESSION['selectedProducts'];
            $totalPrice = $_SESSION['totalPrice'];
            $customerId = $_SESSION['customerId'];
            $paymentMethod = $_SESSION['paymentMethod'];
            $ngayBH = date('Y-m-d');
            $trangthai = 'Đang xử lý';
            
            // Begin transaction
            $con->begin_transaction();
            
            try {
                // Create the invoice
                $stmt_hoadon = $con->prepare("INSERT INTO hoadon (SohieuHD, id, NgayBH, Tongtien, Trangthai) VALUES (?, ?, ?, ?, ?)");
                $stmt_hoadon->bind_param('sssds', $sohieuHD, $customerId, $ngayBH, $totalPrice, $trangthai);
                if (!$stmt_hoadon->execute()) {
                    throw new Exception("Không thể tạo hóa đơn.");
                }
                
                // Prepare queries for order details
                $stmt_chitiethd = $con->prepare("INSERT INTO chitiethd (SohieuHD, Mahang, Soluong, Thanhtien, PTthanhtoan, id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_chitietdh = $con->prepare("INSERT INTO chitietdh (Madonhang, Mahang, Soluong, Thanhtien) VALUES (?, ?, ?, ?)");
                
                // Process each product và trừ số lượng tồn kho
                foreach ($selectedProducts as $product) {
                    $Mahang = $product['mahang'];
                    $quantity = $product['quantity'];
                    $price = $product['price'];
                    $Thanhtien = $price * $quantity;
                    
                    // Kiểm tra số lượng tồn kho hiện tại
                    $checkStock = "SELECT Soluongton FROM hang WHERE Mahang = ?";
                    $stmtCheckStock = $con->prepare($checkStock);
                    $stmtCheckStock->bind_param("s", $Mahang);
                    $stmtCheckStock->execute();
                    $resultStock = $stmtCheckStock->get_result();
                    
                    if ($resultStock->num_rows == 0) {
                        throw new Exception("Không tìm thấy sản phẩm với mã: " . $Mahang);
                    }
                    
                    $currentStock = $resultStock->fetch_assoc()['Soluongton'];
                    
                    // Kiểm tra xem có đủ hàng không
                    if ($currentStock < $quantity) {
                        throw new Exception("Sản phẩm " . $Mahang . " chỉ còn " . $currentStock . " trong kho, không đủ cho đơn hàng.");
                    }
                    
                    // Trừ số lượng tồn kho ngay lập tức
                    $updateStock = "UPDATE hang SET Soluongton = Soluongton - ? WHERE Mahang = ?";
                    $stmtUpdateStock = $con->prepare($updateStock);
                    $stmtUpdateStock->bind_param("is", $quantity, $Mahang);
                    
                    if (!$stmtUpdateStock->execute()) {
                        throw new Exception("Lỗi khi cập nhật số lượng tồn kho cho sản phẩm: " . $Mahang);
                    }
                    
                    // Insert into chitiethd
                    $stmt_chitiethd->bind_param('ssidsi', $sohieuHD, $Mahang, $quantity, $Thanhtien, $paymentMethod, $customerId);
                    if (!$stmt_chitiethd->execute()) {
                        throw new Exception("Lỗi khi thêm chi tiết hóa đơn.");
                    }
                    
                    // Insert into chitietdh
                    $stmt_chitietdh->bind_param('ssis', $sohieuHD, $Mahang, $quantity, $Thanhtien);
                    if (!$stmt_chitietdh->execute()) {
                        throw new Exception("Lỗi khi thêm chi tiết đơn hàng.");
                    }
                    
                    $stmtCheckStock->close();
                    $stmtUpdateStock->close();
                }
                
                // Commit transaction
                $con->commit();
                
                // Clear session data
                unset($_SESSION['selectedProducts']);
                unset($_SESSION['totalPrice']);
                unset($_SESSION['customerId']);
                unset($_SESSION['paymentMethod']);
                unset($_SESSION['momo_orderId']);
                
            } catch (Exception $e) {
                $con->rollback();
                error_log("MoMo IPN Error: " . $e->getMessage());
            }
        }
    }
    
    $stmtCheck->close();
}

// Return success response to MoMo
http_response_code(200);
echo json_encode(['status' => 'success']);
?>
