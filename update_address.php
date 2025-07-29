<?php
session_start();
include 'db_connect.php';

// Retrieve user ID from session
$id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($id)) {
    // Sanitize user input and set default values if fields are empty
    $name = !empty($_POST['name']) ? $_POST['name'] : ''; // Default to empty string if NULL
    $phone = !empty($_POST['phone']) ? $_POST['phone'] : ''; // Default to empty string if NULL
    $new_address = !empty($_POST['new_address']) ? $_POST['new_address'] : ''; // Default to empty string if NULL

    // Update customer details in the database
    $sql = "UPDATE khach SET Tenkhach = ?, Dienthoai = ?, Diachi = ? WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sssi", $name, $phone, $new_address, $id);

    if ($stmt->execute()) {
        // Successfully updated, redirect back to the order summary
        header("Location: thanhtoan1.php");
        exit;
    } else {
        echo "Có lỗi xảy ra. Vui lòng thử lại.";
    }

    $stmt->close();
    $con->close();
} else {
    echo "Dữ liệu không hợp lệ.";
}
?>
