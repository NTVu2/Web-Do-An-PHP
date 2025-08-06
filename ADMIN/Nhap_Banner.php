<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$loggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
// Include database connection
$quyen = isset($_SESSION['quyen']) ? $_SESSION['quyen'] : [];  // Lấy quyền từ session, mặc định là mảng trống nếu không có
if (!in_array('banner', $quyen)) {
    echo "Bạn không có quyền truy cập trang này.";
    header("Location: loginAdmin.php");
    exit;
}
include '../db_connect.php';

// Kiểm tra kết nối
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Xử lý khi người dùng gửi biểu mẫu
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Mảng lưu trữ thông tin các tệp ảnh
    $banner_images = [
        'main_banner_1' => 'banner_main_1',
        'main_banner_2' => 'banner_main_2',
        'main_banner_3' => 'banner_main_3',
        'main_banner_4' => 'banner_main_4',
        'side_banner_1' => 'banner_side_1',
        'side_banner_2' => 'banner_side_2'
    ];

    $target_dir = "../img/banner/";

    // Kiểm tra và tạo thư mục nếu nó chưa tồn tại
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    foreach ($banner_images as $key => $input_name) {
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
            $target_file = $target_dir . basename($_FILES[$input_name]['name']);
            
            // Di chuyển tệp tải lên đến thư mục đích
            if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $target_file)) {
                // Cập nhật hoặc thêm thông tin hình ảnh vào cơ sở dữ liệu
                $sql = "INSERT INTO banner (banner_type, image_path) 
                        VALUES ('$key', '$target_file')
                        ON DUPLICATE KEY UPDATE image_path = '$target_file'";
                if (mysqli_query($con, $sql)) {
                    echo "Banner $key đã được cập nhật thành công.<br>";
                } else {
                    echo "Lỗi: " . $sql . "<br>" . mysqli_error($con);
                }
            } else {
                echo "Có lỗi xảy ra khi tải lên tệp $input_name.<br>";
            }
        }
    }
}

// Lấy tất cả hình ảnh từ bảng banner theo từng loại
$sql = "SELECT banner_type, image_path FROM banner";
$result = mysqli_query($con, $sql);

$banner_images = [
    'main_banner_1' => '',
    'main_banner_2' => '',
    'main_banner_3' => '',
    'main_banner_4' => '',
    'side_banner_1' => '',
    'side_banner_2' => ''
];

// Gán giá trị cho các banner từ kết quả truy vấn
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $banner_images[$row['banner_type']] = $row['image_path'];
    }
}

// Đóng kết nối
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/nhapbanner.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <title>Quản Lý Banner</title>
    <style>

    </style>
</head>
<body style="font-family: Arial, sans-serif ; margin: 0;">
<?php include 'navbar.php'; ?>
</body>
<main>
    <h1>Quản Lý Banner</h1>
    <form action="" method="post" enctype="multipart/form-data" style="display: flex;justify-content: flex-start;gap: 10px; flex-direction: column;">
        <div>
            <label for="banner_main_1">Banner Chính 1:</label>
            <input type="file" name="banner_main_1" id="banner_main_1">
        </div>
        <div>
            <label for="banner_main_2">Banner Chính 2:</label>
            <input type="file" name="banner_main_2" id="banner_main_2">
        </div>
        <div>
            <label for="banner_main_3">Banner Chính 3:</label>
            <input type="file" name="banner_main_3" id="banner_main_3">
        </div>
        <div>
            <label for="banner_main_4">Banner Chính 4:</label>
            <input type="file" name="banner_main_4" id="banner_main_4">
        </div>
        <div>
            <label for="banner_side_1">Banner Phụ 1:</label>
            <input type="file" name="banner_side_1" id="banner_side_1">
        </div>
        <div>
            <label for="banner_side_2">Banner Phụ 2:</label>
            <input type="file" name="banner_side_2" id="banner_side_2">
        </div>
        <button type="submit">Cập Nhật Banner</button>
    </form>

</main>
<script src="../script/banner.js"></script>
</html>
