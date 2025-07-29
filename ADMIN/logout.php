<?php
session_start();
session_destroy(); // Hủy session
header("Location: loginADMIN.php"); // Chuyển hướng về trang đăng nhập
exit;
?>
