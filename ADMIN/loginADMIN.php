<?php
session_start();

include '../db_connect.php';

// Xử lý đăng nhập khi form được gửi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usernameOrEmail = $_POST['usernameOrEmail'];
    $password = $_POST['password'];

    // Truy vấn thông tin từ bảng 'admin'
    $sql = "SELECT * FROM admin WHERE tendangnhap = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Kiểm tra mật khẩu (nếu mật khẩu đã mã hóa thì cần sử dụng password_verify)
        if ($password == $row['matkhau']) {
            // Nếu đăng nhập thành công, lưu thông tin admin vào session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $row['tendangnhap'];
            $quyen = explode(',', $row['quyen']); // Chuyển chuỗi quyền thành mảng

            // Lưu quyền vào session
            $_SESSION['quyen'] = $quyen;
            header("Location: trangchuadmin.php");
            exit();
        } else {
            // Mật khẩu sai, lưu thông báo lỗi vào session và chuyển đến trang chờ
            $_SESSION['error'] = "Mật khẩu không đúng.";
            header("Location: ../index.php");
            exit();
        }
    } else {
        // Không tìm thấy tài khoản, lưu thông báo lỗi vào session và chuyển đến trang chờ
        $_SESSION['error'] = "Không tìm thấy tài khoản.";
        header("Location: ../index.php");
        exit();
    }
}

// Kiểm tra nếu có lỗi trong session và gán nó vào biến $error
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']); // Xóa lỗi sau khi hiển thị để tránh hiển thị lại
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" integrity="anonymous" />
</head>
<body background="">
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-action-left">
                <div class="auth-form-outer">
                    <h2 class="auth-form-title">Đăng Nhập ADMIN</h2>

                    <form class="login-form" method="POST" action="">
                        <input type="text" name="usernameOrEmail" class="auth-form-input" placeholder="Email/Tên đăng nhập" required>
                        <div class="input-icon">
                            <input type="password" name="password" class="auth-form-input" placeholder="Mật khẩu" required>
                            <i class="fa fa-eye show-password"></i>
                        </div>

                        <div class="footer-action">
                            <input type="submit" value="Đăng nhập" class="auth-submit">
                            
                        </div>
                    </form>
                </div>
            </div>
            <div class="auth-action-right">
                <div class="auth-image">
                    <img src="../img/login/am.jpg" alt="login">
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Đóng kết nối cơ sở dữ liệu
$con->close();
?>
