<?php
// Include database connection
include 'db_connect.php';

// Check for connection error
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Lấy đường dẫn hình ảnh từ cơ sở dữ liệu
$sql = "SELECT banner_type, image_path FROM banner";
$result = mysqli_query($con, $sql);

// Khởi tạo mảng banner_images
$banner_images = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $banner_images[$row['banner_type']] = $row['image_path'];
    }
}

// Đóng kết nối
mysqli_close($con);
?>
