<?php
// Bắt đầu session
session_start();

// Kết nối cơ sở dữ liệu
include '../db_connect.php';

// Kiểm tra xem phương thức yêu cầu có phải là POST không
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten = $_POST['ten']; 
    $username = $_POST['username']; 
    $matkhau = $_POST['matkhau']; 
    $matkhau_xacnhan = $_POST['matkhau_xacnhan'];

    // Lấy thêm dữ liệu từ các ô mới
    $dienthoai = $_POST['dienthoai'];
    $diachi = $_POST['diachi'];

    $thongbao = ""; // Biến để lưu thông báo

    // Kiểm tra nếu mật khẩu và nhập lại mật khẩu khớp nhau
    if ($matkhau != $matkhau_xacnhan) {
        $thongbao = "Mật khẩu không khớp!";
    } else {
        // Kiểm tra xem tên đăng nhập đã tồn tại chưa
        $stmt = $con->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username); // sử dụng bind_param để tránh SQL injection
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $thongbao = "Tên đăng nhập đã tồn tại!";
        } else {
            // Bắt đầu một giao dịch (transaction)
            mysqli_begin_transaction($con); // Bắt đầu giao dịch

            try {
                // Thêm tài khoản mới vào bảng users
                $sql = "INSERT INTO users (Tên, username, password, `Ngày tạo tk`) VALUES (?, ?, ?, CURDATE())";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("sss", $ten, $username, $matkhau); // sử dụng bind_param
                $stmt->execute();

                // Lấy ID của người dùng vừa được thêm vào
                $user_id = $con->insert_id;

                // Thêm bản ghi tương ứng vào bảng khach với thông tin mới
                $sql_khach = "INSERT INTO khach (id, Tenkhach, Dienthoai, Diachi) VALUES (?, ?, ?, ?)";
                $stmt_khach = $con->prepare($sql_khach);
                $stmt_khach->bind_param("isss", $user_id, $ten, $dienthoai, $diachi); // sử dụng bind_param
                $stmt_khach->execute();

                // Xác nhận giao dịch thành công
                mysqli_commit($con);

                $thongbao = "Đăng ký thành công!";
            } catch (Exception $e) {
                // Nếu có lỗi, hủy bỏ giao dịch
                mysqli_rollback($con);
                $thongbao = "Đã xảy ra lỗi trong quá trình đăng ký: " . $e->getMessage();
            }
        }
    }

    // Gửi thông báo trở lại trang đăng ký
    $_SESSION['thongbao'] = $thongbao;

    // Chuyển hướng lại file đăng ký
    header("Location: singup.php");
    exit();
}
?>
