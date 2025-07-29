<?php
session_start();

$loggedIn = isset($_SESSION['user_id']);
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Kiểm tra xem dữ liệu được gửi từ nút "Mua ngay" hay không
if (isset($_POST['mahang'])) {
    // Dữ liệu từ nút "Mua ngay"
    $selectedProducts = [
        [
            'mahang' => $_POST['mahang'],
            'name' => $_POST['tenhang'],
            'price' => $_POST['dongia'],
            'quantity' => $_POST['soluong'],
            'soluongton' => $_POST['soluongton'],
            'image' => $_POST['anh']
        ]
    ];
    $totalPrice = $_POST['dongia'] * $_POST['soluong'];
} else {
    // Dữ liệu từ giỏ hàng
    $selectedProducts = isset($_POST['selectedProducts']) ? json_decode($_POST['selectedProducts'], true) : [];
    $totalPrice = isset($_POST['totalPrice']) ? $_POST['totalPrice'] : 0;

    if (empty($selectedProducts)) {
        header("Location: giohang1.php");
        exit;
    }
}
include 'db_connect.php';





$id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

if (empty($id)) {
    header("Location: giohang1.php");
    exit;
}

// Retrieve user details from the database
$sql = "SELECT * FROM khach WHERE id = '$id'";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    $khach = $result->fetch_assoc();
    // Handle NULL values, fallback to empty string
    $name = isset($khach['Tenkhach']) ? $khach['Tenkhach'] : '';
    $phone = isset($khach['Dienthoai']) ? $khach['Dienthoai'] : '';
    $address = isset($khach['Diachi']) ? $khach['Diachi'] : '';
} else {
    echo "<p>Không tìm thấy thông tin khách hàng.</p>";
    exit;
}

$con->close();
?>

<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Order Summary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/thanhtoan.css">
    <script>
    function delaySubmission(event) {
        event.preventDefault(); // Ngăn form gửi ngay lập tức
        var submitButton = event.target.querySelector('button[type="submit"]'); // Chọn đúng nút submit của form hiện tại
        submitButton.disabled = true; // Vô hiệu hóa nút
        submitButton.textContent = "Đang xử lý..."; // Thay đổi nội dung nút

        setTimeout(function() {
            event.target.submit(); // Gửi form sau 5 giây
        }, 4000); // Chờ 5 giây (5000ms)
    }
</script>
</head>
<body>
<nav class="navbar">
    <a href="index.php" style="margin-left: 80px;text-decoration: none;">
        <div class="logo">
            <img src="img/sản_phẩm/logo.png" alt="Logo" style="border-radius: 50%;">
            <span class="shop-name">Vũ Tuấn Shop</span>
        </div>
    </a>
    <div class="nav-links" style="margin-right: 80px;">
    <?php if ($loggedIn): ?>
        
    <?php else: ?>
        
    <?php endif; ?>
    </div>
</nav>

<div class="container">
    <div class="header">
        <div class="title">Địa Chỉ Nhận Hàng</div>
        <a href="#" class="change-link" onclick="showAddressForm()">Thay Đổi</a>
    </div>
    <div class="address">
        <div class="name"><?= htmlspecialchars($name) ?> (+84) <?= htmlspecialchars($phone) ?></div>
        <div class="phone"><?= htmlspecialchars($address) ?></div>
    </div>

    <div id="address-form" style="display: none;">
        <form method="POST" action="update_address.php" onsubmit="return validateForm()">
            <input type="hidden" name="selectedProducts" value='<?= htmlspecialchars(json_encode($selectedProducts)) ?>'>
            <input type="hidden" name="totalPrice" value='<?= htmlspecialchars($totalPrice) ?>'>
            <label for="name">Tên:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
            <label for="phone">Số điện thoại:</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" required>
            <label for="new_address">Địa chỉ nhận hàng:</label>
            <input type="text" id="new_address" name="new_address" value="<?= htmlspecialchars($address) ?>" required>
            <button type="submit">Lưu</button>
            <button type="button" onclick="hideAddressForm()">Hủy</button>
        </form>
    </div>

    <div class="order-details">
        <h2>Chi tiết đơn hàng</h2>
        <table>
            <thead>
                <tr>
                    <th>Sản Phẩm</th>
                    <th>Số Lượng</th>
                    <th>Đơn Giá</th>
                    <th>Thành Tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($selectedProducts as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['quantity']) ?></td>
                    <td><?= number_format($product['price'], 0, '.', ',') ?>₫</td>
                    <td><?= number_format($product['price'] * $product['quantity'], 0, '.', ',') ?>₫</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total">
            <p>Tổng tiền: <strong><?= number_format($totalPrice, 0, '.', ',') ?>₫</strong></p>
        </div>
    </div>

    <div class="payment-options">
        <h2>Phương thức thanh toán</h2>
        <form action="hoantatdonhang.php" method="POST" onsubmit="delaySubmission(event)">
            <input type="hidden" name="selectedProducts" value='<?= htmlspecialchars(json_encode($selectedProducts)) ?>'>
            <input type="hidden" name="totalPrice" value='<?= htmlspecialchars($totalPrice) ?>'>
            <input type="hidden" name="customerId" value="<?= htmlspecialchars($id) ?>'">

            <div>
                <input type="radio" id="cod" name="paymentMethod" value="cod" required>
                <label for="cod">Thanh toán khi nhận hàng (COD)</label>
            </div>
            <div>
                <input type="radio" id="momo" name="paymentMethod" value="momo" required>
                <label for="momo">Thanh toán online (cart)</label>
            </div>
                          
            <button type="submit">Đặt Hàng</button>
        </form>
    </div>

    <div class="footer">
        Nhấn "Đặt hàng" đồng nghĩa với việc bạn đồng ý tuân theo <a href="#"><b>Điều khoản của Shop</b></a>
    </div>
</div>

<script>
    function showAddressForm() {
        document.getElementById('address-form').style.display = 'block';
    }

    function hideAddressForm() {
        document.getElementById('address-form').style.display = 'none';
    }

    function validateForm() {
        var name = document.getElementById('name').value.trim();
        var phone = document.getElementById('phone').value.trim();
        var address = document.getElementById('new_address').value.trim();

        if (name === '' || phone === '' || address === '') {
            alert('Vui lòng nhập đầy đủ thông tin: Tên, Số điện thoại và Địa chỉ nhận hàng.');
            return false;
        }
        return true;
    }
</script>
</body>
</html>
