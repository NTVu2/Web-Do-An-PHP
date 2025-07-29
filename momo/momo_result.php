<?php
if (isset($_GET['resultCode']) && $_GET['resultCode'] == '0') {
    // Payment was successful
    echo "Thanh toán thành công!";
    // Process the order (store order in database, etc.)
} else {
    // Payment failed
    echo "Thanh toán thất bại, vui lòng thử lại.";
}
?>
