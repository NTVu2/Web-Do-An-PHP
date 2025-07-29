<?php
// Bao gồm tệp kết nối cơ sở dữ liệu
include '../db.php';

// Khởi tạo biến để lưu thông báo
$error = "";
$success = "";

// Kiểm tra xem form có được gửi không
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $phone = $_POST['phone'];

    // Kiểm tra xem tên đăng nhập và số điện thoại có tồn tại trong bảng 'users' và 'khach' không
    $sql = "SELECT users.password 
            FROM users 
            INNER JOIN khach ON users.id = khach.id 
            WHERE users.username = :username AND khach.Dienthoai = :phone";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username, 'phone' => $phone]);
    $user = $stmt->fetch();

    if ($user) {
        // Nếu người dùng tồn tại, hiển thị mật khẩu
        $password = $user['password'];
        $success = "Mật khẩu của bạn là: " . $password;
    } else {
        // Tên đăng nhập hoặc số điện thoại không đúng
        $error = "Tên đăng nhập hoặc số điện thoại không đúng.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu</title>
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" integrity="anonymous" />
    <style>
        /* CSS cho thông báo lỗi và thành công */
        .error {
            font-size: 1.4em;
            color: red;
            margin-bottom: 30px;
            text-align: center;
        }
        .success {
            font-size: 1.4em;
            color: green;
            margin-bottom: 30px;
            text-align: center;
            
        }
    </style>
</head>
<body background="../img/login/backro.png">
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-action-left">
                <div class="auth-form-outer">
                    <h2 class="auth-form-title">Quên Mật Khẩu</h2>

                    <!-- Hiển thị thông báo lỗi hoặc thành công nếu có -->
                    <?php if (!empty($error)): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php elseif (!empty($success)): ?>
                        <div class="success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form class="forgot-password-form" method="POST" action="">
                        <input type="text" name="username" class="auth-form-input" placeholder="Tên đăng nhập" required>
                        <input type="text" name="phone" class="auth-form-input" placeholder="Số điện thoại" required>
                        
                        <div class="footer-action">
                            <input type="submit" value="Lấy lại mật khẩu" class="auth-submit">
                            <a href="login.php" class="auth-btn-direct">Trở lại Đăng nhập</a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="auth-action-right">
                <div class="auth-image">
                    <img src="../img/login/login.jpg" alt="forgot password">
                </div>
            </div>
        </div>
    </div>



</body>

<script src="../script/login.js"></script>
</html>
