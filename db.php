<?php
// Cấu hình kết nối cơ sở dữ liệu
$host = 'localhost'; // Địa chỉ host (ví dụ: '127.0.0.1' nếu dùng IP)
$db   = 'lego'; // Tên database của bạn
$user = 'root'; // Tên người dùng database
$pass = ''; // Mật khẩu người dùng database (nếu có)
$charset = 'utf8mb4'; // Charset để hỗ trợ đầy đủ UTF-8

// Cấu hình DSN (Data Source Name) cho PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Các tùy chọn cho PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Ném ngoại lệ khi có lỗi
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Thiết lập chế độ fetch mặc định là associative array
    PDO::ATTR_EMULATE_PREPARES   => false, // Tắt chế độ giả lập prepares, dùng prepares thật sự
];

try {
    // Kết nối đến cơ sở dữ liệu với PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Xử lý lỗi kết nối và hiển thị thông báo lỗi
    die('Kết nối cơ sở dữ liệu thất bại: ' . $e->getMessage());
}
