<?php
// Nhận thông tin sản phẩm từ URL (nếu có)
$mahang = isset($_GET['mahang']) ? $_GET['mahang'] : '';
$tenhang = isset($_GET['tenhang']) ? $_GET['tenhang'] : '';
$dongia = isset($_GET['dongia']) ? $_GET['dongia'] : 0;
$soluong = isset($_GET['soluong']) ? $_GET['soluong'] : 1;
$anh = isset($_GET['anh']) ? $_GET['anh'] : ''; // Nhận đường dẫn ảnh
$soluongton = isset($_GET['soluongton']) ? $_GET['soluongton'] : 0; // Nhận số lượng tồn kho
$tongtien = $dongia * $soluong;

// Nếu có thông tin sản phẩm, thêm vào giỏ hàng
if ($mahang && $tenhang && $dongia) {
    echo "
    <script>
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        let found = false;

        cart.forEach(function(item) {
            if (item.mahang === '$mahang') {
                item.quantity += $soluong; // Tăng số lượng nếu sản phẩm đã có trong giỏ hàng
                found = true;
            }
        });

        if (!found) {
            cart.push({
                mahang: '$mahang',
                name: '$tenhang',
                price: $dongia,
                quantity: $soluong,
                image: '$anh', // Cập nhật đường dẫn ảnh
                soluongton: $soluongton // Cập nhật số lượng tồn kho
            });
        }

        localStorage.setItem('cart', JSON.stringify(cart));
    </script>";
}
session_start();
$loggedIn = isset($_SESSION['user_id']);
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng</title>
    <link rel="stylesheet" href="css/giohang.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .ten {
            display: inline-block;
            max-width: 50px;
            /* Bạn có thể điều chỉnh lại kích thước phù hợp */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
        }

        li {
            list-style-type: none;
            /* Ẩn dấu chấm trong li */
        }

        .icon {
            width: 30px;
            height: 30px;
            display: block;
            /* Để icon trở thành một khối riêng, giúp tên hiển thị dưới nó */
            margin: 0 auto;
            /* Căn giữa icon nếu cần */
        }
    </style>
</head>

<body>
<nav class="navbar">
    <a href="index.php" style="margin-left: 80px;">
        <div class="logo">
            <img src="img/sản_phẩm/logo.png" alt="Logo" style="border-radius: 50%;">
            <span class="shop-name">Vũ Tuấn Shop</span>
        </div>
    </a>
    <div class="nav-links" style="margin-right: 80px;">
        <?php if ($loggedIn): ?>
            <a href="theodoi.php"><li class="info"><img class="icon" src="img/icon.png" alt="icon" style="width: 30px; height: 30px;"> <span class="ten"><?= htmlspecialchars($username) ?></span></li></a>
            <a href="logout.php" class="dangxuat text-lg">Đăng Xuất</a>
        <?php else: ?>
            <a href="Login_singup/login.php">Đăng Nhập</a>
            <a href="Login_singup/singup.php">Đăng Ký</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <h1>Giỏ Hàng</h1>
    <div class="cart-header">
        <input type="checkbox" id="select-all" onclick="toggleSelectAll()" />
        <span>Sản Phẩm</span>
        <span>Đơn Giá</span>
        <span>Số Lượng</span>
        <span>Số Tiền</span>
        <span>Thao Tác</span>
    </div>
    <div id="cart-items">
        <!-- Các sản phẩm trong giỏ hàng sẽ được hiển thị ở đây -->
        <p>Giỏ hàng của bạn hiện tại trống.</p>
    </div>
    <div class="remove-selected">
        <button onclick="removeSelected()">Xóa tất cả những sản phẩm đã tích</button>
        <div id="cart-total" class="cart-total">
            <p>Tổng tiền: 0₫</p>
        </div>
    </div>
    <button class="checkout-btn" onclick="submitCheckoutForm()">Thanh Toán</button>
    <form id="checkout-form" action="thanhtoan1.php" method="POST">
        <input type="hidden" name="selectedProducts" id="selectedProducts">
        <input type="hidden" name="totalPrice" id="totalPrice">
    </form>

</div>

<script>
// Hàm định dạng số tiền
function formatCurrency(value) {
    if (typeof value === 'undefined' || value === null) {
        return '0'; // Giá trị mặc định
    }
    return value.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Hàm tải giỏ hàng từ localStorage và hiển thị các sản phẩm
function loadCart() {
    const cartItemsDiv = document.getElementById('cart-items');
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    // Kiểm tra xem giỏ hàng có sản phẩm nào không
    if (cart.length === 0) {
        cartItemsDiv.innerHTML = "<p>Giỏ hàng của bạn hiện tại trống.</p>";
    } else {
        cartItemsDiv.innerHTML = ""; // Xóa nội dung trước đó
        cart.forEach((item, index) => {
            // Tính tổng tiền cho từng sản phẩm
            const itemTotal = item.price * item.quantity;

            // Thêm sản phẩm vào phần hiển thị giỏ hàng với hình ảnh
            cartItemsDiv.innerHTML += `
                <div class="cart-container">
                    <div class="cart-item">
                        <input type="checkbox" class="select-item" onchange="updateTotal()">
                        <div class="cart-item-info">
                            <img src="${item.image}" alt="${item.name}">
                            <span class="cart-item-name">${item.name}</span>
                        </div>
                        <span class="cart-item-price">${formatCurrency(item.price)}</span>
                        <div class="cart-controls">
                            <button onclick="decrementQuantity(${index})">-</button>
                            <input type="text" value="${item.quantity}" min="1" id="quantity-${index}" onchange="updateQuantity(${index}, this.value)">
                            <button onclick="incrementQuantity(${index})">+</button>
                        </div>
                        <span class="cart-item-total">${formatCurrency(itemTotal)}</span>
                        <button onclick="removeItem(${index})" class="cart-remove-btn">Xóa</button>
                    </div>
                </div>
            `;
        });
    }

    // Cập nhật tổng tiền
    updateTotal();
}

// Hàm cập nhật tổng tiền
function updateTotal() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let total = 0;
    const checkboxes = document.querySelectorAll('.select-item');
    checkboxes.forEach((checkbox, index) => {
        if (checkbox.checked) {
            total += cart[index].price * cart[index].quantity;
        }
    });
    const cartTotalDiv = document.getElementById('cart-total');
    cartTotalDiv.innerHTML = `<p>Tổng tiền: ${formatCurrency(total)}</p>`;
}

// Hàm tăng số lượng
function incrementQuantity(index) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    // Kiểm tra nếu số lượng trong kho còn đủ
    if (cart[index].quantity < cart[index].soluongton) {
        cart[index].quantity++;
        localStorage.setItem('cart', JSON.stringify(cart));
        document.getElementById(`quantity-${index}`).value = cart[index].quantity;
        loadCart(); // Cập nhật lại giỏ hàng hiển thị
    } else {
        alert(`Số lượng sản phẩm trong kho chỉ còn ${cart[index].soluongton}`);
    }
}

// Hàm giảm số lượng
function decrementQuantity(index) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    if (cart[index].quantity > 1) {
        cart[index].quantity--;
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    document.getElementById(`quantity-${index}`).value = cart[index].quantity;
    loadCart(); // Cập nhật lại giỏ hàng hiển thị
}

// Hàm cập nhật số lượng
function updateQuantity(index, quantity) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    if (quantity <= 0 || isNaN(quantity)) {
        alert("Số lượng không hợp lệ.");
        return;
    }

    // Kiểm tra nếu số lượng trong kho còn đủ
    if (quantity > cart[index].soluongton) {
        alert(`Số lượng sản phẩm trong kho chỉ còn ${cart[index].soluongton}`);
        return;
    }
    
    cart[index].quantity = parseInt(quantity);
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart(); // Cập nhật lại giỏ hàng hiển thị
}

// Hàm xóa một sản phẩm khỏi giỏ hàng
function removeItem(index) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart.splice(index, 1);
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart(); // Cập nhật lại giỏ hàng hiển thị
}

// Hàm xóa tất cả sản phẩm đã chọn
function removeSelected() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    const checkboxes = document.querySelectorAll('.select-item');

    checkboxes.forEach((checkbox, index) => {
        if (checkbox.checked) {
            cart.splice(index, 1);
        }
    });

    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart(); // Cập nhật lại giỏ hàng hiển thị
}

// Hàm kiểm tra và gửi biểu mẫu thanh toán
function submitCheckoutForm() {
    // Kiểm tra xem người dùng đã đăng nhập hay chưa
    const loggedIn = <?php echo $loggedIn ? 'true' : 'false'; ?>;

    if (!loggedIn) {
        alert('Vui lòng đăng nhập trước khi thanh toán.');
        window.location.href = 'Login_singup/login.php';
        return;
    }

    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let selectedProducts = [];
    let total = 0;

    const checkboxes = document.querySelectorAll('.select-item');
    checkboxes.forEach((checkbox, index) => {
        if (checkbox.checked) {
            const quantity = cart[index].quantity;
            // Kiểm tra số lượng có hợp lệ hay không
            if (isNaN(quantity) || quantity <= 0) {
                alert(`Số lượng cho sản phẩm ${cart[index].name} không hợp lệ.`);
                return; // Ngưng hàm nếu số lượng không hợp lệ
            }
            selectedProducts.push(cart[index]);
            total += cart[index].price * quantity;
        }
    });

    if (selectedProducts.length === 0) {
        alert('Vui lòng chọn sản phẩm để thanh toán.');
        return;
    }

    // Gửi các sản phẩm đã chọn và tổng tiền qua form
    document.getElementById('selectedProducts').value = JSON.stringify(selectedProducts);
    document.getElementById('totalPrice').value = total;

    document.getElementById('checkout-form').submit(); // Gửi form
}

// Hàm bật tắt chọn tất cả sản phẩm
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.select-item');
    checkboxes.forEach((checkbox) => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    updateTotal(); // Cập nhật tổng tiền sau khi thay đổi
}

// Tải giỏ hàng khi trang được tải
window.onload = loadCart;
</script>

</body>
</html>
