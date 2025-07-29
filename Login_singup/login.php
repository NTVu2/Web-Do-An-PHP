<?php 
session_start(); 

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
    <style>
        /* CSS cho thông báo lỗi */
        .error {
            color: red;
            margin: 10px 0;
            text-align: center;
        }
    </style>
</head>
<body background="../img/login/B2.jpg">
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-action-left">
                <div class="auth-form-outer">
                    <h2 class="auth-form-title">Đăng Nhập</h2>

                    <!-- Hiển thị thông báo lỗi nếu có -->
                    <?php if (!empty($error)): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form class="login-form" method="POST" action="logic_login.php">
                        <input type="text" name="usernameOrEmail" class="auth-form-input" placeholder="Email/Tên đăng nhập" required>
                        <div class="input-icon">
                            <input type="password" name="password" class="auth-form-input" placeholder="Mật khẩu" required>
                            <i class="fa fa-eye show-password"></i>
                        </div>

                        <div class="footer-action">
                            <input type="submit" value="Đăng nhập" class="auth-submit">
                            <a href="singup.php" class="auth-btn-direct">Đăng ký</a>
                        </div>
                    </form>
                    <div class="auth-forgot-password">
                        <a href="quenmk.php">Quên Mật khẩu</a>
                    </div>
                </div>
            </div>
            <div class="auth-action-right">
                <div class="auth-image">
                    <img src="../img/login/t.jpg" alt="login">
                </div>
            </div>
        </div>
    </div> 
    <script src="../script/login.js" ></script>

</body>

</html>
