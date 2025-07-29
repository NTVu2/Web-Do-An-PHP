<?php
// Bật hiển thị lỗi (dành cho quá trình phát triển)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối cơ sở dữ liệu
include '../db.php';
session_start(); // Bắt đầu session

// Kiểm tra nếu form đã được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['usernameOrEmail']; // Lấy giá trị từ form
    $password = $_POST['password']; // Lấy mật khẩu từ form

    // Kiểm tra xem tài khoản có trong cơ sở dữ liệu không
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Kiểm tra tài khoản và mật khẩu
    if ($user && $user['password'] === $password) {
        // Đăng nhập thành công, lưu thông tin người dùng vào session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Chuyển hướng đến index.php
        header("Location: ../index.php");
        exit();
    } else {
        // Nếu không tìm thấy tài khoản hoặc mật khẩu sai
        $_SESSION['error'] = "Tài khoản không tồn tại hoặc mật khẩu không đúng!";
        header("Location: login.php"); // Chuyển về trang đăng nhập
        exit();
    }
}
?>
