<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$loggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
$quyen = isset($_SESSION['quyen']) ? $_SESSION['quyen'] : [];  // Lấy quyền từ session, mặc định là mảng trống nếu không có
if (!in_array('hoadon', $quyen)) {
    echo "Bạn không có quyền truy cập trang này.";
    header("Location: loginAdmin.php");
    exit;
}
include '../db_connect.php';

// Truy vấn lấy danh sách hóa đơn (không phải chi tiết)
$query_hoadon = "SELECT DISTINCT hd.SohieuHD, hd.NgayBH, hd.Trangthai, hd.Tongtien, k.Tenkhach, k.Dienthoai, k.Diachi
                 FROM hoadon hd
                 JOIN khach k ON hd.id = k.id
                 ORDER BY hd.SohieuHD DESC";
$result_hoadon = $con->query($query_hoadon);

// Xử lý tìm kiếm hóa đơn nếu có yêu cầu GET
$searchTerm = '';
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $query_hoadon = "SELECT DISTINCT hd.SohieuHD, hd.NgayBH, hd.Trangthai, hd.Tongtien, k.Tenkhach, k.Dienthoai, k.Diachi
                     FROM hoadon hd
                     JOIN khach k ON hd.id = k.id
                     WHERE hd.SohieuHD LIKE ? OR k.Tenkhach LIKE ?
                     ORDER BY hd.SohieuHD DESC";
    $stmt = $con->prepare($query_hoadon);
    $searchTermWithWildcard = "%$searchTerm%";
    $stmt->bind_param("ss", $searchTermWithWildcard, $searchTermWithWildcard);
    $stmt->execute();
    $result_hoadon = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/xemhoadon.css">
    <title>Danh sách hóa đơn</title>
    <style>
        .ten{
            width: 15%;
        }
        .btn-detail {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-detail:hover {
            background-color: #0056b3;
        }
        .status-success {
            color: #28a745;
            font-weight: bold;
        }
        .status-processing {
            color: #ffc107;
            font-weight: bold;
        }
        .status-cancelled {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<?php include 'navbar.php'; ?>
<body style="font-family: Arial, sans-serif;">
<main>
<div style="display: flex; align-items: flex-end;">
    <form action="xemhoadon.php" method="GET" style="margin-right: 20px;">
        <input type="text" name="search" placeholder="Tìm kiếm hóa đơn hoặc tên khách..." style="padding: 5px; font-size: 14px;">
        <button type="submit" style="padding: 5px 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Tìm kiếm</button>
    </form>
    <h1 style="margin-left: 22%;">Quản lý Hóa Đơn</h1>
</div> 

    <?php if ($result_hoadon->num_rows > 0): ?>
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Số Hiệu Hóa Đơn</th>
                <th>Ngày Bán Hàng</th>
                <th class="ten">Tên Khách Hàng</th>
                <th>Trạng Thái</th>
                <th>Tổng Tiền</th>
                <th>Chi Tiết Hóa Đơn</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result_hoadon->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['SohieuHD']; ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['NgayBH'])); ?></td>
                <td class="ten"><?php echo $row['Tenkhach']; ?></td>
                <td>
                    <?php 
                    $statusClass = '';
                    switch($row['Trangthai']) {
                        case 'Giao hàng thành công':
                            $statusClass = 'status-success';
                            break;
                        case 'Đang xử lý':
                            $statusClass = 'status-processing';
                            break;
                        case 'Đã hủy':
                            $statusClass = 'status-cancelled';
                            break;
                    }
                    ?>
                    <span class="<?php echo $statusClass; ?>"><?php echo $row['Trangthai']; ?></span>
                </td>
                <td><?php echo number_format($row['Tongtien'], 0, ',', '.'); ?> VND</td>
                <td>
                    <a href="chitiethoadon.php?sohieu=<?php echo $row['SohieuHD']; ?>" class="btn-detail">
                        <i class="fas fa-eye"></i> Xem Chi Tiết
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Không có hóa đơn nào.</p>
    <?php endif; ?>

</main>
</body>
</html>

<?php
// Đóng kết nối
$con->close();
?>
