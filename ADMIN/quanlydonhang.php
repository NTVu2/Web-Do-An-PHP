<?php
session_start();
$loggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
$quyen = isset($_SESSION['quyen']) ? $_SESSION['quyen'] : [];  // Lấy quyền từ session, mặc định là mảng trống nếu không có
if (!in_array('donhang', $quyen)) {
    echo "Bạn không có quyền truy cập trang này.";
    header("Location: loginADMIN.php");
    exit;
}
include '../db_connect.php'; // Kết nối database

// Xử lý yêu cầu POST để cập nhật trạng thái hoặc xóa hóa đơn
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kiểm tra nếu có yêu cầu xóa hóa đơn
    if (isset($_POST['SohieuHD_xoa'])) {
        $SohieuHD_xoa = $_POST['SohieuHD_xoa'];

        // Xóa bản ghi trong bảng hoadon, giữ lại chitiethd
        $sqlDeleteHD = "DELETE FROM hoadon WHERE SohieuHD = ?";
        $stmtHD = $con->prepare($sqlDeleteHD);
        $stmtHD->bind_param("s", $SohieuHD_xoa);

        if ($stmtHD->execute()) {
            $_SESSION['message'] = "Hóa đơn đã được xóa thành công.";
        } else {
            $_SESSION['message'] = "Lỗi khi xóa hóa đơn: " . $con->error;
        }

        // Chuyển hướng về trang quản lý đơn hàng sau khi xóa
        header("Location: quanlydonhang.php");
        exit();
    }

    // Xử lý cập nhật trạng thái đơn hàng
    $SohieuHD = $_POST['SohieuHD'];
    $Trangthai = $_POST['Trangthai'];

     // Kiểm tra trạng thái và cập nhật ngày giao hàng dự kiến
     if ($Trangthai == "1-2 ngày") {
        $currentDate = new DateTime();
        $currentDate->modify('+2 day');
        $ngayGiaoDuKien = $currentDate->format('d-m-Y');
        $Trangthai = "Ngày giao hàng dự kiến: " . $ngayGiaoDuKien;
    } elseif ($Trangthai == "3-4 ngày") {
        $currentDate = new DateTime();
        $currentDate->modify('+4 day');
        $ngayGiaoDuKien = $currentDate->format('d-m-Y');
        $Trangthai = "Ngày giao hàng dự kiến: " . $ngayGiaoDuKien;
    } elseif ($Trangthai == "5-6 ngày") {
        $currentDate = new DateTime();
        $currentDate->modify('+6 day');
        $ngayGiaoDuKien = $currentDate->format('d-m-Y');
        $Trangthai = "Ngày giao hàng dự kiến: " . $ngayGiaoDuKien;
    } elseif ($Trangthai == "7-8 ngày") {
        $currentDate = new DateTime();
        $currentDate->modify('+8 day');
        $ngayGiaoDuKien = $currentDate->format('d-m-Y');
        $Trangthai = "Ngày giao hàng dự kiến: " . $ngayGiaoDuKien;
    }
    

    // Cập nhật trạng thái đơn hàng
    $sql = "UPDATE hoadon SET Trangthai = ? WHERE SohieuHD = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ss", $Trangthai, $SohieuHD);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Trạng thái đơn hàng đã được cập nhật.";

        // Kiểm tra nếu trạng thái là 'Giao hàng thành công' và cập nhật số lượng tồn kho
        if ($Trangthai == "Giao hàng thành công") {
            $sqlDetails = "SELECT Mahang, Soluong FROM chitiethd WHERE SohieuHD = ?";
            $stmtDetails = $con->prepare($sqlDetails);
            $stmtDetails->bind_param("s", $SohieuHD);
            $stmtDetails->execute();
            $resultDetails = $stmtDetails->get_result();

            while ($row = $resultDetails->fetch_assoc()) {
                $Mahang = $row['Mahang'];
                $SoluongBan = $row['Soluong'];

                // Trừ số lượng bán vào số lượng tồn
                $updateHang = "UPDATE hang SET Soluongton = Soluongton - ? WHERE Mahang = ?";
                $stmtUpdate = $con->prepare($updateHang);
                $stmtUpdate->bind_param("is", $SoluongBan, $Mahang);
                $stmtUpdate->execute();
            }
            $stmtDetails->close();
        }
    } else {
        $_SESSION['message'] = "Lỗi: " . $con->error;
    }

    $stmt->close();
}

// Truy vấn để lấy danh sách đơn hàng cùng với số điện thoại
$sql = "SELECT hd.SohieuHD, k.Tenkhach, k.Dienthoai, hd.NgayBH, hd.Tongtien, hd.Trangthai 
        FROM hoadon hd
        JOIN khach k ON hd.id = k.id
        ORDER BY hd.NgayBH DESC";  // Sắp xếp theo NgàyBH giảm dần
        
$result = $con->query($sql);


// Xử lý tìm kiếm hóa đơn nếu có yêu cầu GET
if (isset($_GET['search'])) {
    $searchTerm =  $_GET['search'] ; // Thêm ký tự % để tìm kiếm tương tự
    $sql = "SELECT hd.SohieuHD, k.Tenkhach, k.Dienthoai, hd.NgayBH, hd.Tongtien, hd.Trangthai 
            FROM hoadon hd
            JOIN khach k ON hd.id = k.id
            WHERE hd.SohieuHD LIKE ? 
            OR k.Tenkhach LIKE ? 
            OR k.Dienthoai LIKE ? 
            OR hd.Trangthai LIKE ?"; // Thêm điều kiện tìm kiếm theo trạng thái
    $stmtSearch = $con->prepare($sql);
    $stmtSearch->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmtSearch->execute();
    $result = $stmtSearch->get_result();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/qldonhang.css">
    <style>
    .col-du-kien {
        width: 15%; /* Đặt độ rộng mong muốn */
    }
    .ten{
        width: 10%;
    }
    .ngay{
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
                <li><a href="loginADMIN.php" class="dangnhap">Đăng Nhập</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>

<nav>
    <div class="tabs">   
    <a href="trangchuadmin.php" class="tab-button"><i class="fa fa-home"></i> Trang chủ</a>   
        <!-- Sử dụng in_array để kiểm tra quyền trong mảng -->
        <?php if (in_array('sanpham', $_SESSION['quyen'])): ?>
            <a href="Nhập_SP.php" class="tab-button" ><i class="fa fa-product-hunt"></i> Sản phẩm</a>
        <?php endif; ?>
        <?php if (in_array('danhmuc', $_SESSION['quyen'])): ?>
            <a href="Nhập_DM.php" class="tab-button"><i class="fa fa-list"></i> Danh mục</a>
        <?php endif; ?>

        <?php if (in_array('banner', $_SESSION['quyen'])): ?>
            <a href="Nhập_Banner.php" class="tab-button"><i class="fa fa-image"></i> Banner</a>
        <?php endif; ?>

        <?php if (in_array('taikhoan', $_SESSION['quyen'])): ?>
            <a href="qltaikhaon.php" class="tab-button"><i class="fa fa-user"></i> Tài khoản</a>
        <?php endif; ?>

        <?php if (in_array('donhang', $_SESSION['quyen'])): ?>
            <a href="quanlydonhang.php" class="tab-button" style="background-color: #858382 ;"><i class="fa fa-credit-card"></i> Đơn hàng</a>
        <?php endif; ?>

        <?php if (in_array('hoadon', $_SESSION['quyen'])): ?>
            <a href="xemhoadon.php" class="tab-button"><i class="fa fa-clipboard-list"></i> Hóa đơn</a>
        <?php endif; ?>
        <?php if (in_array('nhanvien', $_SESSION['quyen'])): ?>
            <a href="qlnhanvien.php" class="tab-button"><i class="fa fa-user-tie"></i> Nhân viên</a>
        <?php endif; ?>
    </div>
</nav>
<body style=" font-family: Arial, sans-serif;">
    
</bodys>

<main>
    <div style="display: flex; align-items: flex-end; ">
<form action="quanlydonhang.php" method="GET" style="margin-right: 20px;">
            <input type="text" name="search" placeholder="Tìm kiếm hóa đơn..." style="padding: 5px; font-size: 14px;">
            <button type="submit" style="padding: 5px 10px; background-color: #3399ff; color: white; border: none; cursor: pointer;">Tìm kiếm</button>
        </form>
    <h1 style="margin-left: 22%;">Quản lý đơn hàng</h1>
 </div>   
    <?php
// Hiển thị thông báo nếu có trong session
if (isset($_SESSION['message'])) {
    echo '<div id="message" class="message">' . $_SESSION['message'] . '</div>';
    // Xóa thông báo sau khi hiển thị
    unset($_SESSION['message']);
}

?>
    <table border="1">
    <thead>
        <tr>
            <th>Số hóa đơn</th>
            <th class="ten">Tên khách hàng</th>
            <th>Số điện thoại</th>
            <th class="ngay">Ngày bán hàng</th>
            <th class="col-du-kien" >Dự kiến</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tmain>
        <?php while ($row = $result->fetch_assoc()) { 
            // Bỏ qua các đơn hàng có trạng thái "Đã hủy"
            if ($row['Trangthai'] == 'Đã hủy') continue;
        ?>
        <tr>
            <td><?php echo $row['SohieuHD']; ?></td>
            <td class="ten"><?php echo $row['Tenkhach']; ?></td>
            <td>(+84)<?php echo $row['Dienthoai']; ?></td>
            <td class="ngay"><?php echo $row['NgayBH']; ?></td>
            <td class="col-du-kien"><?php echo $row['Trangthai']; ?></td>
            <td><?php echo number_format($row['Tongtien'], 0, ',', '.') . ' VND'; ?></td>
            <td>
                <form action="quanlydonhang.php" method="POST">
                    <input type="hidden" name="SohieuHD" value="<?php echo $row['SohieuHD']; ?>">
                    <select name="Trangthai">
                        <option value="Đang xử lý" <?php if ($row['Trangthai'] == 'Đang xử lý') echo 'selected'; ?>>Đang xử lý</option>
                        <option value="1-2 ngày" <?php if (strpos($row['Trangthai'], '1-2 ngày') !== false) echo 'selected'; ?>>1-2 ngày</option>
                        <option value="3-4 ngày" <?php if (strpos($row['Trangthai'], '3-4 ngày') !== false) echo 'selected'; ?>>3-4 ngày</option>
                        <option value="5-6 ngày" <?php if (strpos($row['Trangthai'], '5-6 ngày') !== false) echo 'selected'; ?>>5-6 ngày</option>
                        <option value="7-8 ngày" <?php if (strpos($row['Trangthai'], '7-8 ngày') !== false) echo 'selected'; ?>>7-8 ngày</option>
                        <option value="Giao hàng thành công" <?php if ($row['Trangthai'] == 'Giao hàng thành công') echo 'selected'; ?>>Giao hàng thành công</option>
                        <option value="Đã hủy" <?php if ($row['Trangthai'] == 'Đã hủy') echo 'selected'; ?>>Đã hủy</option>
                    </select>
                    <button type="submit" <?php if ($row['Trangthai'] == 'Giao hàng thành công' || $row['Trangthai'] == 'Đã hủy') echo 'disabled'; ?>>Cập nhật</button>
                </form>
            </td>
            <td>
                <a class="link-action" href="indonhang.php?SohieuHD=<?php echo $row['SohieuHD']; ?>">In hóa đơn</a>
                <form action="quanlydonhang.php" method="POST" style="display:inline;">
                    <input type="hidden" name="SohieuHD_xoa" value="<?php echo $row['SohieuHD']; ?>">
                    <button type="submit" onclick="return confirm('Bạn có chắc chắn muốn xóa hóa đơn này?');" style="background-color: #f15050; color: white; height: 28px;">Xóa </button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </tmain>
</table>

    
        <script>
    // Tự động ẩn thông báo sau 3 giây (3000 milliseconds)
    setTimeout(function() {
        var messageDiv = document.getElementById("message");
        if (messageDiv) {
            messageDiv.style.display = "none";
        }
    }, 3000); // 3 giây
</script>

    
</main>
</html>
