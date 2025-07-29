<?php
header('Content-Type: application/json');

// Kết nối tới database
include 'db_connect.php';

// Kiểm tra kết nối
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Lấy giá trị category từ query string
$category = isset($_GET['category']) ? $_GET['category'] : '';

// SQL query để lấy các sản phẩm
$sql = "SELECT hang.Mahang, hang.Tenhang, hang.Mota, hang.anh, hang.Dongia 
        FROM hang 
        INNER JOIN loaihang ON hang.Maloaihang = loaihang.Maloaihang";

if (!empty($category)) {
    $sql .= " WHERE hang.Maloaihang = '$category'";
}

$result = mysqli_query($con, $sql);

$products = array();
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['Dongia'] = number_format($row['Dongia'], 0, '.', '.'); // Định dạng giá tiền
        $products[] = $row;
    }
}

// Trả về dữ liệu JSON
echo json_encode($products);

// Đóng kết nối
mysqli_close($con);

?>
