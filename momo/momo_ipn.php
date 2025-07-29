<?php
// MoMo will send payment notifications to this URL
$data = json_decode(file_get_contents("php://input"), true);

if ($data['resultCode'] == '0') {
    // Payment successful, update your order status in database
    $orderId = $data['orderId'];
    // Update order in your database
}
?>
