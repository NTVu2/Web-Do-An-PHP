<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Bắt đầu session để lưu thông báo
session_start();

// Kết nối cơ sở dữ liệu
include '../db_connect.php';

// Kiểm tra kết nối
if (!$con) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
// Lấy mã hàng mới nhất để tạo mã hàng tự động
$sql_last_code = "SELECT Mahang FROM hang ORDER BY Mahang DESC LIMIT 1";
$result_last_code = mysqli_query($con, $sql_last_code);
$last_code = mysqli_fetch_assoc($result_last_code)['Mahang'];

// Tăng giá trị mã hàng lên
if ($last_code) {
    $number_part = (int)substr($last_code, 1); // Lấy phần số từ mã hàng
    $number_part++; // Tăng phần số lên 1
    $new_Mahang = 'H' . str_pad($number_part, 3, '0', STR_PAD_LEFT); // Tạo mã mới
} else {
    $new_Mahang = 'H001'; // Nếu không có mã hàng nào, khởi tạo mã đầu tiên
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy thông tin từ form
    $Mahang = isset($_POST['Mahang']) && !empty($_POST['Mahang']) ? $_POST['Mahang'] : $new_Mahang;
    $Tenhang = $_POST['Tenhang'];
    $Donvido = $_POST['Donvido'];
    $Mota = $_POST['Mota'];
    $Maloaihang = $_POST['Maloaihang'];
    $Soluongton = $_POST['Soluongton'];
    $Dongia = $_POST['Dongia'];
    $giagoc = $_POST['giagoc'];
    $baohanh = isset($_POST['baohanh']) ? $_POST['baohanh'] : "";
    $thongso = $_POST['thongso'];

    // Kiểm tra xem mã hàng có tồn tại không
    if (empty($Mahang)) {
        $_SESSION['thongbao'] = array(
            'type' => 'error',
            'message' => "Mã hàng không được để trống."
        );
        header("Location: Nhập_SP.php");
        exit();
    }

    // Tính toán voucher dựa trên giá gốc và đơn giá
    $voicher = 0;
    if ($giagoc > 0) {
        $voicher = round((($giagoc - $Dongia) / $giagoc) * 100); // Tính % và làm tròn
    }

    // Xử lý upload ảnh
    $anh = "";
    if (isset($_FILES['anh']) && $_FILES['anh']['error'] == 0) {
        $target_dir = "../img/sản_phẩm/";
        $target_file = $target_dir . basename($_FILES["anh"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["anh"]["tmp_name"], $target_file)) {
                $anh = basename($_FILES["anh"]["name"]);
            } else {
                $_SESSION['thongbao'] = array(
                    'type' => 'error',
                    'message' => "Lỗi khi upload file ảnh."
                );
                header("Location: Nhập_SP.php");
                exit();
            }
        } else {
            $_SESSION['thongbao'] = array(
                'type' => 'error',
                'message' => "Chỉ chấp nhận các định dạng ảnh JPG, JPEG, PNG, GIF."
            );
            header("Location: Nhập_SP.php");
            exit();
        }
    }

    // Kiểm tra nếu đang chỉnh sửa sản phẩm
if (isset($_GET['action']) && $_GET['action'] == 'edit') {
    // Cập nhật sản phẩm trong bảng hang
    $sql_update = "UPDATE hang SET Tenhang = ?, Donvido = ?, Mota = ?, Maloaihang = ?, Soluongton = ?, Dongia = ?, anh = ? WHERE Mahang = ?";
    $stmt_update = mysqli_prepare($con, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "ssssisss", $Tenhang, $Donvido, $Mota, $Maloaihang, $Soluongton, $Dongia, $anh, $Mahang);

    if (mysqli_stmt_execute($stmt_update)) {
        // Kiểm tra nếu chi tiết sản phẩm đã tồn tại trong bảng chitiet_sanpham
        $sql_check = "SELECT Mahang FROM chitiet_sanpham WHERE Mahang = ?";
        $stmt_check = mysqli_prepare($con, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "s", $Mahang);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            // Nếu đã có chi tiết, thực hiện cập nhật
            $sql_detail_update = "UPDATE chitiet_sanpham SET Thongso = ?, baohanh = ?, giagoc = ?, voicher = ? WHERE Mahang = ?";
            $stmt_detail_update = mysqli_prepare($con, $sql_detail_update);
            mysqli_stmt_bind_param($stmt_detail_update, "ssdss", $thongso, $baohanh, $giagoc, $voicher, $Mahang);

            if (mysqli_stmt_execute($stmt_detail_update)) {
                $_SESSION['thongbao'] = array(
                    'type' => 'success',
                    'message' => "Sản phẩm và chi tiết sản phẩm đã được cập nhật thành công!"
                );
            } else {
                $_SESSION['thongbao'] = array(
                    'type' => 'error',
                    'message' => "Lỗi khi cập nhật chi tiết sản phẩm: " . mysqli_stmt_error($stmt_detail_update)
                );
            }
        } else {
            // Nếu chưa có chi tiết, thực hiện chèn mới
            $sql_detail_insert = "INSERT INTO chitiet_sanpham (Mahang, Thongso, baohanh, giagoc, voicher) VALUES (?, ?, ?, ?, ?)";
            $stmt_detail_insert = mysqli_prepare($con, $sql_detail_insert);
            mysqli_stmt_bind_param($stmt_detail_insert, "ssssd", $Mahang, $thongso, $baohanh, $giagoc, $voicher);

            if (mysqli_stmt_execute($stmt_detail_insert)) {
                $_SESSION['thongbao'] = array(
                    'type' => 'success',
                    'message' => "Sản phẩm và chi tiết sản phẩm đã được thêm thành công!"
                );
            } else {
                $_SESSION['thongbao'] = array(
                    'type' => 'error',
                    'message' => "Lỗi khi thêm chi tiết sản phẩm: " . mysqli_stmt_error($stmt_detail_insert)
                );
            }
        }
    } else {
        $_SESSION['thongbao'] = array(
            'type' => 'error',
            'message' => "Lỗi: " . mysqli_stmt_error($stmt_update)
        );
    }
}}
?>
