<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$loggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
$quyen = isset($_SESSION['quyen']) ? $_SESSION['quyen'] : [];  // Lấy quyền từ session, mặc định là mảng trống nếu không có
if (!in_array('danhmuc', $quyen)) {
    echo "Bạn không có quyền truy cập trang này.";
    header("Location: loginAdmin.php");
    exit;
}
// Kết nối cơ sở dữ liệu
include '../db_connect.php'      ;

// Biến để lưu thông báo
$successMessage = '';
$deleteSuccessMessage = ''; // Biến cho thông báo xóa

// Kiểm tra khi form được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $categoryName = $_POST['categoryName'];  // Tenloaihang
    $supplierName = $_POST['supplierName'];  // Nhacungcap

    // Tạo Maloaihang tự động
    // $sqlId = "SELECT MAX(Maloaihang) as maxId FROM loaihang";
    // $resultId = mysqli_query($con, $sqlId);
    // $rowId = mysqli_fetch_assoc($resultId);
    // $categoryId = $rowId['maxId'] ? $rowId['maxId'] + 1 : 1;  // Nếu không có danh mục nào, gán Maloaihang là 1
// Tạo Maloaihang tự động dạng LH01, LH02...
$sqlId = "SELECT MAX(Maloaihang) as maxId FROM loaihang";
$resultId = mysqli_query($con, $sqlId);
$rowId = mysqli_fetch_assoc($resultId);
if ($rowId['maxId']) {
    // Tách phần số từ mã hiện tại
    $number = (int)substr($rowId['maxId'], 2); // Bỏ 2 ký tự 'LH' đầu
    $number++; // Tăng mã
    $categoryId = 'LH' . str_pad($number, 2, '0', STR_PAD_LEFT); // Định dạng lại
} else {
    $categoryId = 'LH01'; // Nếu chưa có dữ liệu
}

    // Xử lý việc tải ảnh lên
    if (isset($_FILES['categoryImage']) && $_FILES['categoryImage']['error'] == 0) {
        $imageName = $_FILES['categoryImage']['name'];
        $imageTmpName = $_FILES['categoryImage']['tmp_name'];

        // Thư mục để lưu trữ ảnh
        $targetDirectory = "../img/danh_mục/";
        if (!is_dir($targetDirectory)) {
            if (!mkdir($targetDirectory, 0777, true)) {
                die('Lỗi: Không thể tạo thư mục lưu trữ hình ảnh.');
            }
        }
        $targetFilePath = $targetDirectory . basename($imageName);

        // Kiểm tra loại file (chỉ cho phép các file ảnh jpg, png, jpeg, gif)
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($fileType, $allowedTypes)) {
            // Di chuyển file tải lên vào thư mục uploads
            if (move_uploaded_file($imageTmpName, $targetFilePath)) {
                // Thêm danh mục mới vào cơ sở dữ liệu
                $sql = "INSERT INTO loaihang (Maloaihang, Tenloaihang, Nhacungcap, anh) 
                        VALUES ('$categoryId', '$categoryName', '$supplierName', '$imageName')";
                if (mysqli_query($con, $sql)) {
                    $successMessage = "Thêm danh mục thành công!";
                } else {
                    echo "Lỗi: " . mysqli_error($con);
                }
            } else {
                echo "Lỗi: Không thể tải file lên.";
            }
        } else {
            echo "Chỉ chấp nhận các file ảnh có định dạng JPG, JPEG, PNG, GIF.";
        }
    } 
        
    
}

// Xử lý xóa danh mục
if (isset($_GET['delete'])) {
    $categoryId = $_GET['delete'];
    $deleteSql = "DELETE FROM loaihang WHERE Maloaihang = '$categoryId'";
    if (mysqli_query($con, $deleteSql)) {
        $deleteSuccessMessage = "Xóa danh mục thành công!";
    } else {
        echo "Lỗi: " . mysqli_error($con);
    }
}
// Xử lý cập nhật danh mục
if (isset($_GET['edit'])) {
    $categoryId = $_GET['edit'];
    // Lấy thông tin danh mục từ cơ sở dữ liệu
    $editSql = "SELECT * FROM loaihang WHERE Maloaihang = '$categoryId'";
    $editResult = mysqli_query($con, $editSql);
    $editRow = mysqli_fetch_assoc($editResult);

    $editCategoryName = $editRow['Tenloaihang'];
    $editSupplierName = $editRow['Nhacungcap'];
    $editImage = $editRow['anh'];
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $categoryId = $_POST['categoryId'];
    $categoryName = $_POST['categoryName'];
    $supplierName = $_POST['supplierName'];

    // Nếu người dùng chọn ảnh mới, xử lý ảnh
    if (isset($_FILES['categoryImage']) && $_FILES['categoryImage']['error'] == 0) {
        $imageName = $_FILES['categoryImage']['name'];
        $imageTmpName = $_FILES['categoryImage']['tmp_name'];
        $targetDirectory = "../img/danh_mục/";
        $targetFilePath = $targetDirectory . basename($imageName);
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($imageTmpName, $targetFilePath)) {
                // Cập nhật danh mục với ảnh mới
                $sql = "UPDATE loaihang SET Tenloaihang = '$categoryName', Nhacungcap = '$supplierName', anh = '$imageName' WHERE Maloaihang = '$categoryId'";
                if (mysqli_query($con, $sql)) {
                    $successMessage = "Cập nhật danh mục thành công!";
                } else {
                    echo "Lỗi: " . mysqli_error($con);
                }
            } else {
                echo "Lỗi: Không thể tải file lên.";
            }
        } else {
            echo "Chỉ chấp nhận các file ảnh có định dạng JPG, JPEG, PNG, GIF.";
        }
    } else {
      
    // Xử lý việc tải ảnh lên
    if (isset($_FILES['categoryImage']) && $_FILES['categoryImage']['error'] == 0) {
        $imageName = $_FILES['categoryImage']['name'];
        $imageTmpName = $_FILES['categoryImage']['tmp_name'];

        // Thư mục để lưu trữ ảnh
        $targetDirectory = "../img/danh_mục/";
        if (!is_dir($targetDirectory)) {
            if (!mkdir($targetDirectory, 0777, true)) {
                die('Lỗi: Không thể tạo thư mục lưu trữ hình ảnh.');
            }
        }
        $targetFilePath = $targetDirectory . basename($imageName);

        // Kiểm tra loại file (chỉ cho phép các file ảnh jpg, png, jpeg, gif)
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($fileType, $allowedTypes)) {
            // Di chuyển file tải lên vào thư mục uploads
            if (move_uploaded_file($imageTmpName, $targetFilePath)) {
                // Thêm danh mục mới vào cơ sở dữ liệu
                $sql = "INSERT INTO loaihang (Maloaihang, Tenloaihang, Nhacungcap, anh) 
                        VALUES ('$categoryId', '$categoryName', '$supplierName', '$imageName')";
                if (mysqli_query($con, $sql)) {
                    $successMessage = "Thêm danh mục thành công!";
                } else {
                    echo "Lỗi: " . mysqli_error($con);
                }
            } else {
                echo "Lỗi: Không thể tải file lên.";
            }
        } else {
            echo "Chỉ chấp nhận các file ảnh có định dạng JPG, JPEG, PNG, GIF.";
        }
    }
}
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/nhập_dm.css">
    <style>
#successMessage {
    display: <?= !empty($successMessage) ? 'block' : 'none'; ?>;
}

/* Hiển thị thông báo xóa thành công */
#deleteSuccessMessage {
    display: <?= !empty($deleteSuccessMessage) ? 'block' : 'none'; ?>;
}
.sua1{
    margin-left: auto;
    padding: 10px;
}
.sua{
    margin-left: 10px;
}
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <h1>Quản lý danh mục</h1>
    <div id="successMessage" style="display: <?= !empty($successMessage) ? 'block' : 'none'; ?>;">
                <?= $successMessage; ?>
            </div>
            <div id="deleteSuccessMessage" style="display: <?= !empty($deleteSuccessMessage) ? 'block' : 'none'; ?>;">
                <?= $deleteSuccessMessage; ?>
            </div>
    <!-- Container cho form và danh sách -->
    <div class="container">
        <!-- Form để thêm danh mục -->
        <div class="form-container">
            <!-- Thông báo thành công -->
            
            <h2>Danh mục</h2>
            <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="categoryId" value="<?= isset($categoryId) ? $categoryId : ''; ?>">
        
        <label for="categoryName">Tên danh mục:</label>
        <input type="text" id="categoryName" name="categoryName" value="<?= isset($editCategoryName) ? $editCategoryName : ''; ?>" required>

        <label for="supplierName">Nhà cung cấp:</label>
        <input type="text" id="supplierName" name="supplierName" value="<?= isset($editSupplierName) ? $editSupplierName : ''; ?>" required>

        <label for="categoryImage">Ảnh danh mục:</label>
        <?php if (isset($editImage)): ?>
            <img src="../img/danh_mục/<?= $editImage; ?>" alt="Category Image" width="100">
        <?php endif; ?>
        <input type="file" id="categoryImage" name="categoryImage" accept="image/*">
        
        <button type="submit" name="<?= isset($categoryId) ? 'update' : 'submit'; ?>">
            <?= isset($categoryId) ? 'Cập nhật danh mục' : 'Thêm danh mục'; ?>
        </button>
    </form>
        </div>

        <!-- Danh sách danh mục -->
        <div class="list-container">
            <h2>Danh sách danh mục</h2>
            <ul class="category-list">
                <?php
        // Lấy danh sách danh mục
        $sql = "SELECT * FROM loaihang";
        $result = mysqli_query($con, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<li class="gach">' . $row['Tenloaihang'] . ' - ' . $row['Nhacungcap'] . 
                 ' <a  class="sua1"href="?edit=' . $row['Maloaihang'] . '">Sửa</a> | <a class="sua" href="?delete=' . $row['Maloaihang'] . '" onclick="return confirm(\'Bạn có chắc chắn muốn xóa danh mục này không?\');">Xóa</a></li>'; 
                 
        }
        ?>
            </ul>
        </div>
    </div>
</body>
<script>
    // Tự động ẩn thông báo sau 3 giây (3000 milliseconds)
    setTimeout(function() {
        var successMessageDiv = document.getElementById("successMessage");
        if (successMessageDiv) {
            successMessageDiv.style.display = "none";
        }
    }, 3000); // 3 giây
        // Tự động ẩn thông báo sau 3 giây (3000 milliseconds)
        setTimeout(function() {
        var successMessageDiv = document.getElementById("deleteSuccessMessage");
        if (successMessageDiv) {
            successMessageDiv.style.display = "none";
        }
    }, 3000); // 3 giây
</script>
</html>