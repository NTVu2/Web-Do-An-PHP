<?php
session_start();
include 'db_connect.php'; // Kết nối tới CSDL

$customerId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

// Nếu không có ID khách hàng, chuyển hướng về trang đăng nhập
if (empty($customerId)) {
    header('Location: login.php');
    exit;
}

// Xử lý hủy đơn (nếu có yêu cầu từ người dùng)
if (isset($_GET['huy']) && isset($_GET['SohieuHD'])) {
    $SohieuHD_huy = $_GET['SohieuHD'];

    // Kiểm tra xem đơn hàng có tồn tại và thuộc về người dùng hiện tại không
    $sql_kiemtra = "SELECT Trangthai FROM hoadon WHERE SohieuHD = ? AND id = ?";
    $stmt_kiemtra = $con->prepare($sql_kiemtra);
    $stmt_kiemtra->bind_param("si", $SohieuHD_huy, $customerId);
    $stmt_kiemtra->execute();
    $result_kiemtra = $stmt_kiemtra->get_result();

    if ($result_kiemtra->num_rows > 0) {
        $row_kiemtra = $result_kiemtra->fetch_assoc();
        $Trangthai = $row_kiemtra['Trangthai'];

        // Chỉ cho phép hủy khi trạng thái là "Đang xử lý"
        if ($Trangthai == "Đang xử lý") {
            $sql_huy = "UPDATE hoadon SET Trangthai = 'Đã hủy' WHERE SohieuHD = ? AND id = ?";
            $stmt_huy = $con->prepare($sql_huy);
            $stmt_huy->bind_param("si", $SohieuHD_huy, $customerId);

            if ($stmt_huy->execute()) {
                $_SESSION['thongbao'] = "Đơn hàng $SohieuHD_huy đã được hủy thành công!";
            } else {
                $_SESSION['thongbao'] = "Lỗi khi hủy đơn hàng $SohieuHD_huy: " . $stmt_huy->error;
            }
        } else {
            $_SESSION['thongbao'] = "Bạn chỉ có thể hủy đơn hàng khi trạng thái là 'Đang xử lý'.";
        }
    } else {
        $_SESSION['thongbao'] = "Đơn hàng $SohieuHD_huy không tồn tại hoặc bạn không có quyền hủy đơn này.";
    }

    header("Location: theodoi.php");
    exit;
}


// Truy vấn danh sách đơn hàng của khách hàng
$sql = "SELECT * FROM hoadon WHERE id = ? ORDER BY NgayBH DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param('i', $customerId);
$stmt->execute();
$result = $stmt->get_result();

// Kiểm tra nếu khách hàng chưa có đơn hàng
if ($result->num_rows == 0) {
    echo "Bạn chưa có đơn hàng nào.";
    exit;
}
?>

<html lang="en">
<head>
    <title>Theo Dõi Đơn Hàng</title>
    <link rel="stylesheet" href="css/theodoi.css">
    <style>
.chitiet{
    width: 12%;
}   
    </style>
</head>
<body>
<nav class="navbar">
    <a href="index.php" style="margin-left: 80px; text-decoration: none;">
        <div class="logo">
            <img src="img/sản_phẩm/logo.png" alt="Logo" style="border-radius: 50%;">
            <span class="shop-name">Vũ Tuấn Shop</span>
        </div>
    </a>

</nav>
<div class="container">
    <h1>Đơn Hàng Của Bạn</h1>

    <!-- Hiển thị thông báo nếu có -->
    <?php if (isset($_SESSION['thongbao'])): ?>
        <div class="alert <?= strpos($_SESSION['thongbao'], 'lỗi') !== false ? 'error' : '' ?>">
            <?= $_SESSION['thongbao']; unset($_SESSION['thongbao']); ?>
        </div>
    <?php endif; ?>

    <table class="order-list">
        <thead>
            <tr>
                <th>Số Hóa Đơn</th>
                <th>Ngày Đặt Hàng</th>
                <th>Tổng Tiền</th>
                <th>Trạng Thái</th>
                <th class="chitiet">Chi Tiết</th>
                <th>Hủy Đơn</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($order['SohieuHD']) ?></td>
                <td><?= htmlspecialchars($order['NgayBH']) ?></td>
                <td><?= number_format($order['Tongtien'], 0, '.', ',') ?>₫</td>
                <td><?= htmlspecialchars($order['Trangthai']) ?></td>
                <td class="chitiet"><a href="camon.php?SohieuHD=<?= htmlspecialchars($order['SohieuHD']) ?>">Chi Tiết</a></td>
                <td>
                <?php if ($order['Trangthai'] == 'Đang xử lý'): ?>
                            <a class="huy-don-btn" href="?huy=1&SohieuHD=<?= htmlspecialchars($order['SohieuHD']) ?>" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?')">Hủy Đơn</a>
                        <?php else: ?>
                            <span class="khong-huy-duoc">Không thể hủy</span>
                        <?php endif; ?>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </table>

<!-- Quay lại trang chủ (nằm ngoài vòng lặp) -->
<div style="margin-top: 20px; text-align: center;">
    <a href="index.php" class="dquaylaitrangchu" style="text-decoration: none; color: #007bff;">⬅ Quay lại Trang Chủ</a>
</div>
</div>
</body>
</html>