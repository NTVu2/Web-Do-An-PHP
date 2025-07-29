<?php
session_start();
include 'db_connect.php'; // Kết nối tới CSDL

// Lấy số hiệu hóa đơn từ URL
$sohieuHD = isset($_GET['sohieuHD']) ? $_GET['sohieuHD'] : '';

// Nếu không có số hiệu hóa đơn, chuyển hướng về danh sách đơn hàng
if (empty($sohieuHD)) {
    header('Location: theodoi.php');
    exit;
}

// Lấy thông tin hóa đơn
$sql_hoadon = "SELECT h.*, k.Tenkhach, k.Diachi, k.Dienthoai 
               FROM hoadon h
               JOIN khach k ON h.id = k.id
               WHERE h.SohieuHD = '$sohieuHD'";
$result_hoadon = $con->query($sql_hoadon);

// Nếu không tìm thấy hóa đơn, thông báo lỗi
if ($result_hoadon->num_rows == 0) {
    echo "Không tìm thấy hóa đơn.";
    exit;
}
$hoadon = $result_hoadon->fetch_assoc();

// Lấy chi tiết sản phẩm trong hóa đơn (đã bao gồm phương thức thanh toán)
$sql_chitiethd = "SELECT c.*, h.Tenhang, c.PTthanhtoan
                  FROM chitiethd c
                  JOIN hang h ON c.Mahang = h.Mahang
                  WHERE c.SohieuHD = '$sohieuHD'";
$result_chitiethd = $con->query($sql_chitiethd);

$con->close(); // Đóng kết nối CSDL
?>

<html lang="en">
<head>
    <title>Chi Tiết Đơn Hàng</title>
    <link rel="stylesheet" href="css/camon.css">
</head>
<body>
<div class="container">
    <h1>Chi Tiết Đơn Hàng</h1>
    
    <!-- Thông tin hóa đơn -->
    <div class="order-info">
        <p><strong>Số hóa đơn:</strong> <?= htmlspecialchars($hoadon['SohieuHD']) ?></p>
        <p><strong>Ngày đặt hàng:</strong> <?= htmlspecialchars($hoadon['NgayBH']) ?></p>
        <p><strong>Tổng tiền:</strong> <?= number_format($hoadon['Tongtien'], 0, '.', ',') ?>₫</p>
        <p><strong>Trạng thái:</strong> <?= htmlspecialchars($hoadon['Trangthai']) ?></p>
    </div>

    <!-- Danh sách sản phẩm -->
    <table class="order-details">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Số lượng</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
                <th>Phương thức thanh toán</th> 
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result_chitiethd->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['Tenhang']) ?></td>
                <td><?= htmlspecialchars($row['Soluong']) ?></td>
                <td><?= number_format($row['Thanhtien'] / $row['Soluong'], 0, '.', ',') ?>₫</td>
                <td><?= number_format($row['Thanhtien'], 0, '.', ',') ?>₫</td>
                <td><?= htmlspecialchars($row['PTthanhtoan']) ?></td> 
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="footer">
        <a href="theodoi.php">Quay lại danh sách đơn hàng</a>
    </div>
</div>
</body>
</html>