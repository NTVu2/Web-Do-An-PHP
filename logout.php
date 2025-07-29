<?php
session_start();
session_unset(); // Xóa tất cả các biến phiên
session_destroy(); // Hủy phiên

// Đặt một đoạn script JavaScript để xóa giỏ hàng sau khi chuyển hướng
echo "
<script>
    // Xóa giỏ hàng trong localStorage hoặc sessionStorage
    localStorage.removeItem('cart'); // Xóa giỏ hàng từ localStorage (nếu bạn lưu ở đây)
    
    
    // Chuyển hướng đến trang chủ
    window.location.href = 'index.php';
</script>
";

exit(); // Dừng thực thi tập lệnh PHP
?>
