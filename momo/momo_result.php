<?php
session_start();

if (isset($_GET['resultCode']) && $_GET['resultCode'] == '0') {
    // Payment was successful
    $_SESSION['payment_success'] = true;
    header('Location: ../thankes.php');
    exit;
} else {
    // Payment failed
    $_SESSION['payment_failed'] = true;
    header('Location: ../thatbai.php');
    exit;
}
?>
