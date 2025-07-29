<?php
session_start();
include 'db_connect.php'; // Kết nối tới CSDL

// Lấy SohieuHD từ URL
$SohieuHD = isset($_GET['SohieuHD']) ? $_GET['SohieuHD'] : '';

if (!empty($SohieuHD)) {
    // Truy vấn thông tin hóa đơn
    $sql_hoadon = "SELECT h.*, k.Tenkhach, k.Diachi, k.Dienthoai 
                   FROM hoadon h
                   JOIN khach k ON h.id = k.id
                   WHERE h.SohieuHD = '$SohieuHD'";
    $result_hoadon = $con->query($sql_hoadon);

    if ($result_hoadon->num_rows > 0) {
        $hoadon = $result_hoadon->fetch_assoc();
    } else {
        echo "Không tìm thấy hóa đơn.";
        exit;
    }

    // Truy vấn chi tiết sản phẩm
    $sql_chitiethd = "SELECT c.*, h.Tenhang 
                      FROM chitiethd c 
                      JOIN hang h ON c.Mahang = h.Mahang
                      WHERE c.SohieuHD = '$SohieuHD'";
    $result_chitiethd = $con->query($sql_chitiethd);

    if ($result_chitiethd->num_rows == 0) {
        echo "Không có chi tiết hóa đơn.";
        exit;
    }

    $con->close(); // Đóng kết nối CSDL
    ?>

    <html lang="en">
    <head>
        <title>Chi Tiết Hóa Đơn</title>
        <link rel="stylesheet" href="css/camon.css">
    </head>
    <body>
        
    <div class="container">
        <h1>Chi Tiết Hóa Đơn</h1>

        <!-- Thông tin khách hàng -->
        <div class="customer-info">
            <p><strong>Tên khách hàng:</strong> <?= htmlspecialchars($hoadon['Tenkhach']) ?></p>
            <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($hoadon['Diachi']) ?></p>
            <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($hoadon['Dienthoai']) ?></p>
        </div>

        <!-- Thông tin hóa đơn -->
        <div class="order-info">
            <p><strong>Số hiệu hóa đơn:</strong> <?= htmlspecialchars($hoadon['SohieuHD']) ?></p>
            <p><strong>Ngày mua hàng:</strong> <?= htmlspecialchars($hoadon['NgayBH']) ?></p>
            <p><strong>Tổng tiền:</strong> <?= number_format($hoadon['Tongtien'], 0, '.', ',') ?>₫</p>
        </div>

        <!-- Chi tiết sản phẩm -->
        <table class="order-details">
            <thead>
                <tr>
                    <th>Sản Phẩm</th>
                    <th>Số Lượng</th>
                    <th>Đơn Giá</th>
                    <th>Thành Tiền</th>
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
            <p>Cảm ơn quý khách đã mua hàng tại Shop!</p>
            <a href="index.php">Quay về trang chủ</a>
        </div>
    </div>
    </body>
    </html>

    <?php
    // Xóa cookie SohieuHD sau khi xem chi tiết
    setcookie('SohieuHD', '', time() - 3600);
} else {
    echo "Không tìm thấy hóa đơn.";
}
?>