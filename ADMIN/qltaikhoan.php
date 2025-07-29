<?php
session_start();
$loggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
$quyen = isset($_SESSION['quyen']) ? $_SESSION['quyen'] : [];  // Lấy quyền từ session, mặc định là mảng trống nếu không có
if (!in_array('taikhoan', $quyen)) {
    echo "Bạn không có quyền truy cập trang này.";
    header("Location: loginAdmin.php");
    exit;
}
// Kết nối cơ sở dữ liệu
include '../db_connect.php';

// Khởi tạo biến để lưu các thông báo
$thongbao = '';

// Kiểm tra hành động: thêm, sửa, xóa hoặc hiển thị danh sách người dùng
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        // Thêm người dùng
        $username = $_POST['username'];
        $password = $_POST['password'];
        $name = $_POST['name'];
        $ngaytao = date('Y-m-d'); // Lấy ngày hiện tại

        // Mã hóa mật khẩu
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, password, Tên, `Ngày tạo tk`) VALUES ('$username', '$password_hashed', '$name', '$ngaytao')";

        if (mysqli_query($con, $sql)) {
            $thongbao = "Thêm người dùng thành công!";
        } else {
            $thongbao = "Lỗi khi thêm người dùng: " . mysqli_error($con);
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'edit') {
        // Sửa người dùng
        $id = $_POST['id'];
        $username = $_POST['username'];
        $name = $_POST['name'];
        $password = $_POST['password'];

        // Mã hóa mật khẩu nếu có thay đổi
        $password_hashed = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

        // Cập nhật tài khoản
        if ($password_hashed) {
            $sql = "UPDATE users SET username='$username', password='$password_hashed', Tên='$name' WHERE id = $id";
        } else {
            $sql = "UPDATE users SET username='$username', Tên='$name' WHERE id = $id";
        }

        if (mysqli_query($con, $sql)) {
            $thongbao = "Cập nhật người dùng thành công!";
        } else {
            $thongbao = "Lỗi khi cập nhật người dùng: " . mysqli_error($con);
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    // Xóa người dùng
    $id = $_GET['id'];
    $sql = "DELETE FROM users WHERE id = $id";

    if (mysqli_query($con, $sql)) {
        $thongbao = "Xóa người dùng thành công!";
    } else {
        $thongbao = "Lỗi khi xóa người dùng: " . mysqli_error($con);
    }
}

// Lấy danh sách người dùng để hiển thị
$sql = "SELECT * FROM users";
$result = mysqli_query($con, $sql);

// Xử lý khi nhấn sửa
$isEdit = isset($_GET['action']) && $_GET['action'] == 'edit';
$editUser = null;
if ($isEdit) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM users WHERE id = $id";
    $editResult = mysqli_query($con, $sql);
    $editUser = mysqli_fetch_assoc($editResult);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tài khoản</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/qltaikhoan.css">
    <style>

    </style>
</head>
<body style="font-family: Arial, sans-serif ; margin: 0;">
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
            <a href="Nhap_SP.php" class="tab-button"><i class="fa fa-product-hunt"></i> Sản phẩm</a>
        <?php endif; ?>
        <?php if (in_array('danhmuc', $_SESSION['quyen'])): ?>
            <a href="Nhap_DM.php" class="tab-button"><i class="fa fa-list"></i> Danh mục</a>
        <?php endif; ?>

        <?php if (in_array('banner', $_SESSION['quyen'])): ?>
            <a href="Nhap_Banner.php" class="tab-button"><i class="fa fa-image"></i> Banner</a>
        <?php endif; ?>

        <?php if (in_array('taikhoan', $_SESSION['quyen'])): ?>
            <a href="qltaikhaon.php" class="tab-button"  style="background-color: #858382 ;"><i class="fa fa-user"></i> Tài khoản</a>
        <?php endif; ?>

        <?php if (in_array('donhang', $_SESSION['quyen'])): ?>
            <a href="quanlydonhang.php" class="tab-button"><i class="fa fa-credit-card"></i> Đơn hàng</a>
        <?php endif; ?>

        <?php if (in_array('hoadon', $_SESSION['quyen'])): ?>
            <a href="xemhoadon.php" class="tab-button"><i class="fa fa-clipboard-list"></i> Hóa đơn</a>
        <?php endif; ?>
        <?php if (in_array('nhanvien', $_SESSION['quyen'])): ?>
            <a href="qlnhanvien.php" class="tab-button"><i class="fa fa-user-tie"></i> Nhân viên</a>
        <?php endif; ?>
    </div>
</nav>
<body>
    

<main>

<h2>Quản lý tài khoản người dùng</h2>

<!-- Thông báo -->
<?php if ($thongbao): ?>
    <p><strong><?php echo $thongbao; ?></strong></p>
<?php endif; ?>

<!-- Bố cục chứa cả form và danh sách tài khoản -->
<div class="container">
    <!-- Form thêm hoặc sửa người dùng -->
    <div class="form-container">
        <h3><?php echo $isEdit ? 'Sửa tài khoản' : 'Thêm tài khoản mới'; ?></h3>
        <form method="POST">
            <input type="hidden" name="action" value="<?php echo $isEdit ? 'edit' : 'add'; ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">
            <?php endif; ?>
            <label>Tên đăng nhập:</label>
            <input type="text" name="username" value="<?php echo $isEdit ? $editUser['username'] : ''; ?>" required>
            <label>Mật khẩu <?php echo $isEdit ? '(để trống nếu không muốn thay đổi)' : ''; ?>:</label>
            <input type="password" name="password">
            <label>Tên đầy đủ:</label>
            <input type="text" name="name" value="<?php echo $isEdit ? $editUser['Tên'] : ''; ?>" required>
            <input type="submit" value="<?php echo $isEdit ? 'Cập nhật' : 'Thêm tài khoản'; ?>">
            
        </form>
    </div>

    <!-- Danh sách người dùng -->
    <div class="table-container">
        <h3>Danh sách tài khoản</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên đăng nhập</th>
                    <th>Mật khẩu</th>
                    <th>Tên đầy đủ</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tmain>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['username']; ?></td>
                    <td><?php echo $row['password']; ?></td> <!-- Hiển thị mật khẩu -->
                    <td><?php echo $row['Tên']; ?></td>
                    <td><?php echo $row['Ngày tạo tk']; ?></td>
                    <td>
                        <!-- Sửa tài khoản -->
                        <a href="?action=edit&id=<?php echo $row['id']; ?>">Sửa</a> | 
                        <!-- Xóa tài khoản -->
                        <a href="?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này không?')">Xóa</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tmain>
        </table>
    </div>
</div>

</main>
</body>
</html>
