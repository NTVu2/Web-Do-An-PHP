<?php
session_start();
$loggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
$quyen = isset($_SESSION['quyen']) ? $_SESSION['quyen'] : [];  // Lấy quyền từ session, mặc định là mảng trống nếu không có
if (!in_array('hoadon', $quyen)) {
    echo "Bạn không có quyền truy cập trang này.";
    header("Location: loginAdmin.php");
    exit;
}
include '../db_connect.php';

// Truy vấn lấy danh sách chi tiết hóa đơn
$query_chitiethd = "SELECT c.SohieuHD, c.Mahang, c.Soluong, c.Thanhtien, c.PTthanhtoan, h.Tenhang, k.Tenkhach, k.Diachi, k.Dienthoai
                    FROM chitiethd c
                    JOIN hang h ON c.Mahang = h.Mahang
                    JOIN khach k ON c.id = k.id
                    ORDER BY c.SohieuHD DESC";
$result_chitiethd = $con->query($query_chitiethd);

// Xử lý tìm kiếm hóa đơn nếu có yêu cầu GET
$searchTerm = '';
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $query_chitiethd = "SELECT c.SohieuHD, c.Mahang, c.Soluong, c.Thanhtien, c.PTthanhtoan, h.Tenhang, k.Tenkhach, k.Diachi, k.Dienthoai
                        FROM chitiethd c
                        JOIN hang h ON c.Mahang = h.Mahang
                        JOIN khach k ON c.id = k.id
                        WHERE c.SohieuHD LIKE ?
                        ORDER BY c.SohieuHD DESC";
    $stmt = $con->prepare($query_chitiethd);
    $searchTermWithWildcard =  $searchTerm ;
    $stmt->bind_param("s", $searchTermWithWildcard);
    $stmt->execute();
    $result_chitiethd = $stmt->get_result();
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
    <title>Danh sách chi tiết hóa đơn</title>
    <style>
.ten{
    width: 10%;
}
    </style>
</head>
<header class="admin-header">
    <div class="header-container">
        <h1 class="admin-title" style="color: #3399ff;">Bảng Điều Khiển Admin</h1>
        <ul class="user-actions">
            <?php if ($loggedIn): ?>
                
                <li><a href="logout.php"><i class="fa fa-user"></i> <?php echo $_SESSION['admin_username'];; ?></a></li>
            <?php else: ?>
                <li><a href="loginAdmin.php" class="dangnhap">Đăng Nhập</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>


<nav>
    <div class="tabs">   
    <a href="trangchuadmin.php" class="tab-button"><i class="fa fa-home"></i> Trang chủ</a>   
        <!-- Sử dụng in_array để kiểm tra quyền trong mảng -->
        <?php if (in_array('sanpham', $_SESSION['quyen'])): ?>
            <a href="Nhap_SP.php" class="tab-button" ><i class="fa fa-product-hunt"></i> Sản phẩm</a>
        <?php endif; ?>
        <?php if (in_array('danhmuc', $_SESSION['quyen'])): ?>
            <a href="Nhap_DM.php" class="tab-button"><i class="fa fa-list"></i> Danh mục</a>
        <?php endif; ?>

        <?php if (in_array('banner', $_SESSION['quyen'])): ?>
            <a href="Nhap_Banner.php" class="tab-button"><i class="fa fa-image"></i> Banner</a>
        <?php endif; ?>

        <?php if (in_array('taikhoan', $_SESSION['quyen'])): ?>
            <a href="qltaikhoan.php" class="tab-button"><i class="fa fa-user"></i> Tài khoản</a>
        <?php endif; ?>

        <?php if (in_array('donhang', $_SESSION['quyen'])): ?>
            <a href="quanlydonhang.php" class="tab-button"><i class="fa fa-credit-card"></i> Đơn hàng</a>
        <?php endif; ?>

        <?php if (in_array('hoadon', $_SESSION['quyen'])): ?>
            <a href="xemhoadon.php" class="tab-button"  style="background-color: #858382 ;"><i class="fa fa-clipboard-list"></i> Hóa đơn</a>
        <?php endif; ?>
        <?php if (in_array('nhanvien', $_SESSION['quyen'])): ?>
            <a href="qlnhanvien.php" class="tab-button"><i class="fa fa-user-tie"></i> Nhân viên</a>
        <?php endif; ?>
    </div>
</nav>
<body style=" font-family: Arial, sans-serif;"></body>
<main>
<div style="display: flex; align-items: flex-end; ">
<form action="xemhoadon.php" method="GET" style="margin-right: 20px;">
            <input type="text" name="search" placeholder="Tìm kiếm hóa đơn..." style="padding: 5px; font-size: 14px;">
            <button type="submit" style="padding: 5px 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Tìm kiếm</button>
        </form>
    <h1 style="margin-left: 22%;">Quản lý Hoá Đơn</h1>
 </div> 

    <?php if ($result_chitiethd->num_rows > 0): ?>
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Số Hiệu Hóa Đơn</th>
                <th>Mã Hàng</th>
                <th class="ten">Tên Hàng</th>
                <th>Tên Khách</th>
                <th>Địa Chỉ</th>
                <th style="min-width: 150px;">Điện Thoại</th>
                <th>Số Lượng</th>
                <th style="min-width: 150px;">Thành Tiền</th>
                <th>PT Thanh Toán</th>
            </tr>
        </thead>
        <tmain>
            <?php while ($row = $result_chitiethd->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['SohieuHD']; ?></td>
                <td><?php echo $row['Mahang']; ?></td>
                <td><?php echo $row['Tenhang']; ?></td>
                <td class="ten"><?php echo $row['Tenkhach']; ?></td>
                <td><?php echo $row['Diachi']; ?></td>
                <td>(+84)<?php echo $row['Dienthoai']; ?></td>
                <td><?php echo $row['Soluong']; ?></td>
                <td><?php echo number_format($row['Thanhtien'], 2); ?> VND</td>
                <td><?php echo $row['PTthanhtoan']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tmain>
    </table>
    <?php else: ?>
    <p>Không có chi tiết hóa đơn nào.</p>
    <?php endif; ?>

</main>
</html>

<?php
// Đóng kết nối
$con->close();
?>
