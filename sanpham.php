
    <?php
    include 'db_connect.php'; // File kết nối cơ sở dữ liệu

    // Lấy id của sản phẩm từ URL
    $id = isset($_GET['id']) ? $_GET['id'] : 0;

    $sql = "SELECT h.*, cs.Thongso , cs.baohanh , cs.voicher , cs.giagoc
            FROM hang h 
            LEFT JOIN chitiet_sanpham cs ON h.Mahang = cs.Mahang 
            WHERE h.Mahang = '$id'";
    $result = $con->query($sql);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Không tìm thấy sản phẩm"; 
        exit;
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
        <title>Chi tiết Sản phẩm</title>
        <link rel="stylesheet" href="css/sanpham.css">
        <link rel="stylesheet" href="css/trangview.css">
        <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
        <script src="https://unpkg.com/unlazy@0.11.3/dist/unlazy.with-hashing.iife.js" defer init></script>
        <link rel="shortcut icon" href="img/sản_phẩm/logo.png" type="image/x-icon">
        <style>
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
            <a href="giohang1.php"><img src="../img/login/gio.png" alt="giohang" class="giohang" ></a>
            <a href="theodoi.php"><li class="info"><img class="icon"src="img/icon.png" alt="icon" style="width: 30px; height: 30px;"> <span class="ten"><?= htmlspecialchars($username) ?></span></li></a>
            <a href="logout.php" class="dangxuat text-lg">Đăng Xuất</a>
            <?php else: ?>
                <a href="giohang1.php"><img src="img/giohang.png" alt="giohang" class="giohang" ></a>
            <a href="Login_singup/login.php">Đăng Nhập</a>
            <a href="Login_singup/singup.php">Đăng Ký</a>
            <?php endif; ?>
        </div>
    </nav>

        <!-- Nội dung sản phẩm -->
        <div class="sanpham-container">
            <div class="container">
                <div class="product">
                <div class="product-images zoom-container">
        <img id="mainImage" src="img/sản_phẩm/<?php echo $product['anh']; ?>" alt="Ảnh sản phẩm" class="zoom-image">
    </div>
                    <div class="product-details">
                        <h1><?php echo $product['Tenhang']; ?></h1>
                        <div class="rating">5.0 ★★★★★ | Đánh giá: 0</div>
                        <div class="price">
                            <?php echo number_format($product['Dongia'], 0, ',', '.'); ?>đ
                            <span style="text-decoration: line-through; color: #888; margin-left: 10px;font-size: 20px;">
                            <?php echo number_format($product['giagoc'], 0, ',', '.'); ?>đ</span>
                            <span class="discount"><?php echo $product['voicher']; ?>% giảm</span>
                        </div>
                        <div class="ghichu">
                            <li>Tình Trạng: <?php echo $product['Soluongton'] > 0 ? 'Còn hàng' : 'Hết hàng'; ?></li>
                            <li>Bảo hành: <?php echo $product['baohanh']; ?></li>
                            <li>Combo mua 4 được giảm 0%</li>
                        </div>
                        <!-- Phần chọn số lượng -->
                        <div class="quantity-selector">
                            <label for="quantity">Số Lượng</label>
                            <div class="quantity-input">
                                <button class="quantity-btn" onclick="decreaseQuantity()">-</button>
                                <input type="text" id="quantity" value="1" min="1">
                                <button class="quantity-btn" onclick="increaseQuantity()">+</button>
                            </div>
                        </div>
                        <div style="display: flex;" class="buttons">
                        <button style="border: 1px solid black;" 
        onclick="orderNow('<?php echo $product['Mahang']; ?>', '<?php echo addslashes($product['Tenhang']); ?>', <?php echo $product['Dongia']; ?>, '<?php echo addslashes($product['anh']); ?>',<?php echo $product['Soluongton']; ?>)" 
        class="bg-primary text-primary-foreground hover:bg-primary/80 px-4 py-2 rounded-lg block mt-4">
        Mua ngay
    </button>

    <button style="border: 1px solid black;" 
        onclick="addToCart('<?php echo $product['Mahang']; ?>', '<?php echo addslashes($product['Tenhang']); ?>', <?php echo $product['Dongia']; ?>, 'img/sản_phẩm/<?php echo addslashes($product['anh']); ?>', <?php echo $product['Soluongton']; ?>)"
        class="bg-accent text-accent-foreground hover:bg-accent/80 px-4 py-2 rounded-lg block mt-4">
        Thêm vào giỏ hàng
    </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="notification" class="notification hidden">
        <p id="notification-text"></p>
    </div>

            <!-- Thông tin chi tiết sản phẩm -->
            <div class="container">
                <div style="background-color: #fafafa; padding: 10px 20px;">
                    <h1 style="font-size: 24px; margin-bottom: 10px;">Chi tiết sản phẩm</h1>
                </div>
                <p>Tên sản phẩm: <?php echo $product['Tenhang']; ?></p>
                <p><?php echo nl2br(str_replace(',', '<br>', $product['Thongso'])); ?></p>
            </div>
        </div>
        <?php
    // Truy vấn sản phẩm ngẫu nhiên, ngoại trừ sản phẩm hiện tại và không hiển thị sản phẩm hết hàng
    $randomProductsSql = "SELECT * FROM hang WHERE Mahang != '$id' AND Soluongton > 0 ORDER BY RAND() LIMIT 10";
    $randomProductsResult = $con->query($randomProductsSql);
    ?>


    <!-- Hiển thị sản phẩm khác -->
    <div class="container" style="margin-top: 30px;">
        <div style="background-color: #fafafa; padding: 10px 20px;">
            <h1 style="font-size: 24px; margin-bottom: 10px;">Sản phẩm khác</h1>
        </div>
        <div class="grid grid-cols-5 gap-4"> <!-- Sử dụng grid layout cho các sản phẩm -->
            <?php if ($randomProductsResult->num_rows > 0): ?>
                <?php while ($row = $randomProductsResult->fetch_assoc()): ?>
                    <?php
                    $Tenhang = $row['Tenhang'];
                    $Mota = $row['Mota'];
                    $anh = $row['anh'];
                    $don_gia = number_format($row['Dongia'], 0, '.', '.');
                    $soluongton = $row['Soluongton'];
                    $Mahang = $row['Mahang'];

                    // Đường dẫn đến hình ảnh "hết hàng"
                    $outOfStockImage = 'img/sold_out.png';
                    ?>

                    <a href="sanpham.php?id=<?php echo $Mahang; ?>">
                        <div class="bg-card text-card-foreground p-4 rounded-lg shadow-md relative">
                            <img src="img/sản_phẩm/<?php echo $anh; ?>" alt="<?php echo $Tenhang; ?>" class="w-full h-81 object-cover rounded-lg mb-4" />

                            <!-- Hiển thị hình ảnh "hết hàng" nếu hết hàng -->
                            <?php if ($soluongton == 0): ?>
                                <img src="<?php echo $outOfStockImage; ?>" alt="Hết hàng" class="out-of-stock opacity-90" />
                            <?php endif; ?>

                            <h2 class="text-lg font-bold mb-2"><?php echo $Tenhang; ?></h2>
                            <p class="text-sm text-muted-foreground mb-4"><?php echo $Mota; ?></p>
                            <span class="text-lg font-bold text-black"><?php echo $don_gia; ?> VNĐ</span>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Không có sản phẩm nào để hiển thị.</p>
            <?php endif; ?>
        </div>
    </div>
        <!-- JavaScript -->
        <script >
            // Hàm giảm số lượng
    function decreaseQuantity() {
        var quantityInput = document.getElementById('quantity');
        var currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    }

    // Hàm tăng số lượng
    function increaseQuantity() {
        var quantityInput = document.getElementById('quantity');
        var currentValue = parseInt(quantityInput.value);
        quantityInput.value = currentValue + 1;
    }
    var loggedIn = <?php echo json_encode($loggedIn); ?>;  // Lấy giá trị từ PHP


    // Hàm xử lý khi nhấn nút "Mua ngay"
function orderNow(mahang, tenhang, dongia, anh, soluongton) {
    if (!loggedIn) {
        alert("Bạn cần đăng nhập trước khi mua hàng!");
        window.location.href = 'Login_singup/login.php';
        return;
    }

    var quantity = document.getElementById('quantity').value;
    
    // Kiểm tra nếu sản phẩm tạm thời hết hàng (soluongton = 0)
    if (soluongton == 0) {
        showNotification("Sản phẩm tạm thời hết hàng!", false);
        return; // Không thực hiện tiếp nếu sản phẩm hết hàng
    }

    // Biểu thức chính quy để chỉ cho phép số nguyên dương
    var soHopLe = /^[1-9]\d*$/;

    // Kiểm tra số lượng hợp lệ
    if (!soHopLe.test(quantity)) {
        showNotification("Vui lòng nhập số lượng hợp lệ (chỉ số nguyên dương).", false);
        return; // Ngăn chặn nếu số lượng không hợp lệ
    }

    // Kiểm tra số lượng sản phẩm người dùng nhập có vượt quá số lượng tồn kho hay không
    if (quantity > soluongton) {
        showNotification("Số lượng không đủ, sản phẩm tồn kho chỉ còn " + soluongton + " sản phẩm.", false);
        return; // Không thực hiện tiếp nếu số lượng vượt quá tồn kho
    }

    // Tạo form ẩn
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = 'thanhtoan1.php';

    // Thêm các input ẩn vào form
    function addInput(name, value) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    }

    addInput('mahang', mahang);
    addInput('tenhang', tenhang);
    addInput('dongia', dongia);
    addInput('anh', anh); // Thêm trường ảnh
    addInput('soluong', quantity);
    addInput('soluongton', soluongton);

    // Thêm form vào body và submit
    document.body.appendChild(form);
    form.submit();
}

    // Hàm xử lý khi nhấn nút "Thêm vào giỏ hàng"
    function addToCart(mahang, tenhang, dongia, anh, soluongton) {
        var quantity = document.getElementById('quantity').value;

        // Kiểm tra nếu sản phẩm tạm thời hết hàng (soluongton = 0)
        if (soluongton == 0) {
            showNotification("Sản phẩm tạm thời hết hàng!", false);
            return; // Không thực hiện tiếp nếu sản phẩm hết hàng
        }

        // Kiểm tra số lượng sản phẩm người dùng nhập có vượt quá số lượng tồn kho hay không
        if (quantity > soluongton) {
            showNotification("Số lượng không đủ, sản phẩm tồn kho chỉ còn " + soluongton + " sản phẩm.", false);
            return; // Không thực hiện tiếp nếu số lượng vượt quá tồn kho
        }

        // Lấy giỏ hàng từ localStorage
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        let found = false;

        // Kiểm tra xem sản phẩm đã tồn tại trong giỏ hàng chưa
        cart.forEach(function(item) {
            if (item.mahang === mahang) {
                item.quantity += parseInt(quantity); // Tăng số lượng nếu sản phẩm đã có trong giỏ hàng
                found = true;
            }
        });

        // Nếu sản phẩm chưa tồn tại trong giỏ hàng thì thêm mới
        if (!found) {
            cart.push({
                mahang: mahang,
                name: tenhang,
                price: dongia,
                quantity: parseInt(quantity),
                image: anh,
                soluongton: soluongton
            });
        }

        // Cập nhật lại giỏ hàng trong localStorage
        localStorage.setItem('cart', JSON.stringify(cart));
        
        // Hiển thị thông báo thành công
        showNotification(" Thêm vào giỏ hàng thành công!", true);
    }

    // Hàm để hiển thị thông báo
    function showNotification(message, success) {
        var notification = document.getElementById('notification');
        var notificationText = document.getElementById('notification-text');

        notificationText.textContent = message;
        notification.style.backgroundColor = success ? '#4CAF50' : '#f44336'; // Màu xanh cho thành công, đỏ cho lỗi
        notification.classList.remove('hidden');
        notification.classList.add('show');

        // Sau 3 giây, ẩn thông báo
        setTimeout(function() {
            notification.classList.remove('show');
            notification.classList.add('hidden');
        }, 3000);
    }
    // Lấy phần tử ảnh và container
    const zoomImage = document.querySelector('.zoom-image');
    let isZoomed = false; // Trạng thái ảnh có đang phóng to hay không

    // Thêm sự kiện khi nhấn vào ảnh
    zoomImage.addEventListener('click', function(e) {
        if (!isZoomed) {
            // Nếu chưa phóng to, thực hiện phóng to ảnh
            zoomImage.classList.add('zoomed');
            isZoomed = true; // Cập nhật trạng thái phóng to
            moveImage(e); // Di chuyển ảnh theo vị trí chuột khi phóng to
        } else {
            // Nếu đang phóng to, thu nhỏ lại ảnh về trạng thái ban đầu
            zoomImage.classList.remove('zoomed');
            isZoomed = false; // Cập nhật trạng thái không phóng to
            zoomImage.style.transformOrigin = 'center'; // Đặt lại vị trí trung tâm khi ảnh thu nhỏ
        }
    });

    // Hàm xử lý việc di chuyển ảnh theo vị trí chuột khi phóng to
    function moveImage(e) {
        const containerRect = zoomImage.getBoundingClientRect();
        const x = e.clientX - containerRect.left;
        const y = e.clientY - containerRect.top;

        // Tính toán tỷ lệ phần trăm vị trí chuột so với ảnh
        const xPercent = (x / containerRect.width) * 100;
        const yPercent = (y / containerRect.height) * 100;

        // Cập nhật vị trí phóng to dựa trên tỷ lệ
        zoomImage.style.transformOrigin = `${xPercent}% ${yPercent}%`;
    }

    // Thêm sự kiện di chuột để di chuyển ảnh theo vị trí chuột (chỉ khi ảnh đã phóng to)
    zoomImage.addEventListener('mousemove', function(e) {
        if (isZoomed) {
            moveImage(e);
        }
    });
    // Gọi đến server để thực hiện thanh toán
    fetch('thanhtoan1.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ mahang, tenhang, dongia, quantity })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification("Đặt hàng thành công!", true);
                // Chuyển hướng tới trang thành công hoặc giỏ hàng
                window.location.href = 'giohang1.php';
            } else {
                showNotification("Đặt hàng không thành công!", false);
            }
        })
        .catch(error => {
            showNotification("Có lỗi xảy ra, vui lòng thử lại!", false);
        });

    </script>

    </body>
    </html>
