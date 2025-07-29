<?php
session_start();
// Kiểm tra xem session đã có thông tin về sản phẩm được chọn hay chưa
if (isset($_SESSION['selectedProducts']) && is_array($_SESSION['selectedProducts'])) {
    $selectedProducts = $_SESSION['selectedProducts']; // Lấy danh sách sản phẩm từ session
} else {
    // Nếu không có sản phẩm trong session, gán biến $selectedProducts là mảng rỗng để tránh lỗi
    $selectedProducts = [];
}

// Kiểm tra nếu danh sách sản phẩm không trống thì lấy mã sản phẩm
if (!empty($selectedProducts)) {
    // Lấy danh sách mã sản phẩm đã thanh toán
    $paidProducts = array_column($selectedProducts, 'mahang'); // Mảng chứa mã sản phẩm đã thanh toán
} else {
    $paidProducts = []; // Nếu không có sản phẩm, gán là mảng rỗng
}
include 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra xem thanh toán MoMo có trả về hay không
if (isset($_GET['orderId']) && isset($_GET['resultCode'])) {
    $orderId = $_GET['orderId'];
    $resultCode = $_GET['resultCode']; // 0 là thành công

    // Kiểm tra nếu thanh toán thành công
    if ($resultCode == 0) {
        // Lấy số hóa đơn từ session
        if (isset($_SESSION['sohieuHD'])) {
            $sohieuHD = $_SESSION['sohieuHD'];
            unset($_SESSION['sohieuHD']); // Xóa session sau khi sử dụng

            // Lấy các dữ liệu cần thiết khác từ session
            $customerId = isset($_SESSION['customerId']) ? $_SESSION['customerId'] : '';
            $selectedProducts = isset($_SESSION['selectedProducts']) ? $_SESSION['selectedProducts'] : [];
            $totalPrice = isset($_SESSION['totalPrice']) ? $_SESSION['totalPrice'] : 0;
            $paymentMethod = isset($_SESSION['paymentMethod']) ? $_SESSION['paymentMethod'] : 'momo';

            // Kiểm tra dữ liệu cần thiết
            if (!empty($sohieuHD) && !empty($customerId) && !empty($selectedProducts) && !empty($totalPrice)) {

                // Bắt đầu transaction
                $con->begin_transaction();
                try {
                    // Lưu hóa đơn vào bảng 'hoadon'
                  // Tạo SohieuHD tự động dạng HD001, HD002, ...
// Lấy 2 số cuối của năm hiện tại, ví dụ: 2025 → "25"
$year = date('y');

// Tạo SohieuHD tự động dạng H25001, H25002, ...
// Truy vấn hóa đơn mới nhất có cùng năm (bắt đầu bằng H25)
$result = $con->query("SELECT SohieuHD FROM hoadon WHERE SohieuHD LIKE 'H$year%' ORDER BY SohieuHD DESC LIMIT 1");

if ($result && $row = $result->fetch_assoc()) {
    // Tách phần số phía sau, ví dụ H25007 → 007
    $lastNumber = (int)substr($row['SohieuHD'], 3); // bỏ "H" + 2 số năm
    $nextNumber = $lastNumber + 1;
} else {
    $nextNumber = 1;
}

// Ghép lại thành mã mới, ví dụ: H25008
$sohieuHD = 'HD' . $year . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

// Ngày và trạng thái
$ngayBH = date('Y-m-d');
$trangthai = 'Đang xử lý';

// Thêm hóa đơn mới
$stmt_hoadon = $con->prepare("INSERT INTO hoadon (SohieuHD, id, NgayBH, Tongtien, Trangthai) VALUES (?, ?, ?, ?, ?)");
$stmt_hoadon->bind_param('sssds', $sohieuHD, $customerId, $ngayBH, $totalPrice, $trangthai);

if (!$stmt_hoadon->execute()) {
    throw new Exception("Không thể tạo hóa đơn.");
}



                    // Lưu chi tiết hóa đơn
                    $stmt_chitiethd = $con->prepare("INSERT INTO chitiethd (SohieuHD, Mahang, Soluong, Thanhtien, PTthanhtoan,id) VALUES (?,?, ?, ?, ?, ?)");
                    $stmt_chitietdh = $con->prepare("INSERT INTO chitietdh (Madonhang, Mahang, Soluong, Thanhtien,id) VALUES (?,?, ?, ?, ?)");

                    foreach ($selectedProducts as $product) {
                        $Mahang = $product['mahang'];
                        $quantity = $product['quantity'];
                        $price = $product['price'];
                        $Thanhtien = $price * $quantity;

                        // Chèn dữ liệu vào bảng 'chitiethd'
                        $stmt_chitiethd->bind_param('ssidsi', $sohieuHD, $Mahang, $quantity, $Thanhtien, $paymentMethod,$customerId);
                        if (!$stmt_chitiethd->execute()) {
                            throw new Exception("Lỗi khi thêm chi tiết hóa đơn.");
                        }

                        // Chèn dữ liệu vào bảng 'chitietdh'
                        $stmt_chitietdh->bind_param('ssisi', $sohieuHD, $Mahang, $quantity, $Thanhtien,$customerId);
                        if (!$stmt_chitietdh->execute()) {
                            throw new Exception("Lỗi khi thêm chi tiết đơn hàng.");
                        }
                    }

                    // Commit transaction sau khi tất cả các thao tác thành công
                    $con->commit();

                    // Thông báo thành công và chuyển hướng
                   
                    header('Location: thankes.php');
                    exit;

                } catch (Exception $e) {
                    $con->rollback();
                    echo "Đã xảy ra lỗi: " . $e->getMessage();
                    exit;
                }

            } else {
                echo "Dữ liệu hóa đơn không hợp lệ.";
            }

        } else {
            echo "Không tìm thấy hóa đơn trong session.";
        }
    } else {
        header('Location: thatbai.php');
        exit;
    }
}
?>

<html>
<head>
    <title>Đặt Hàng Thành Công</title>
    <link rel="stylesheet" href="css/end.css">
    <style>
       
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">✔️</div>
        <div class="message">Đặt hàng thành công</div>
        <div class="sub-message">Cảm ơn bạn đã tin tưởng chúng tôi. Đơn hàng của bạn đang được giao</div>
        <div class="buttons">
            <a href="index.php" class="button">Trang chủ</a>
            <a href="theodoi.php" class="button">Đơn Hàng</a>
        </div>
    </div>
    <script>
        // Mã sản phẩm đã thanh toán từ PHP (truyền qua JSON)
        const paidProducts = <?php echo json_encode($paidProducts); ?>;

        // Lấy giỏ hàng từ localStorage
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        // Lọc và giữ lại các sản phẩm chưa thanh toán
        cart = cart.filter(product => !paidProducts.includes(product.mahang));

        // Cập nhật lại giỏ hàng vào localStorage
        localStorage.setItem('cart', JSON.stringify(cart));

        console.log("Đã xóa các sản phẩm đã thanh toán khỏi giỏ hàng.");
    </script>
</body>
</html>