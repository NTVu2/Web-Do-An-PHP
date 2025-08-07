
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
        <title>Chi tiết Sản phẩm - Vũ Tuấn Shop - LEGO Store</title>
        <link rel="shortcut icon" href="img/sản_phẩm/logo.png" type="image/x-icon">
        
        <!-- External CSS -->
        <link rel="stylesheet" href="css/sanpham.css">
        <link rel="stylesheet" href="css/trangview.css">
        <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
        <script src="https://unpkg.com/unlazy@0.11.3/dist/unlazy.with-hashing.iife.js" defer init></script>
        
        <!-- Font Awesome for icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        <!-- Google Fonts - LEGO style fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Fredoka+One:wght@400&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
        
        <style>
            :root {
                --lego-red: #D50000;
                --lego-yellow: #FFD600;
                --lego-blue: #0066CC;
                --lego-green: #00A651;
                --lego-orange: #FF6900;
                --lego-purple: #7B68EE;
                --lego-white: #FFFFFF;
                --lego-black: #1A1A1A;
                --lego-gray: #9E9E9E;
                --lego-light-gray: #F5F5F5;
                --shadow-brick: 0 8px 16px rgba(0, 0, 0, 0.2);
                --shadow-hover: 0 12px 24px rgba(0, 0, 0, 0.3);
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Nunito', sans-serif;
                background: linear-gradient(45deg, var(--lego-light-gray) 25%, transparent 25%), 
                            linear-gradient(-45deg, var(--lego-light-gray) 25%, transparent 25%), 
                            linear-gradient(45deg, transparent 75%, var(--lego-light-gray) 75%), 
                            linear-gradient(-45deg, transparent 75%, var(--lego-light-gray) 75%);
                background-size: 20px 20px;
                background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
                background-color: var(--lego-white);
                color: var(--lego-black);
                line-height: 1.6;
            }

            /* LEGO Brick Pattern Background */
            .brick-pattern {
                position: relative;
                overflow: hidden;
            }

            .brick-pattern::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-image: 
                    radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.3) 2px, transparent 2px),
                    radial-gradient(circle at 75% 25%, rgba(255, 255, 255, 0.3) 2px, transparent 2px),
                    radial-gradient(circle at 25% 75%, rgba(255, 255, 255, 0.3) 2px, transparent 2px),
                    radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.3) 2px, transparent 2px);
                background-size: 20px 20px;
                pointer-events: none;
            }

            /* Navigation - LEGO Style */
            .navbar {
                background: linear-gradient(135deg, var(--lego-red) 0%, #B71C1C 100%);
                box-shadow: var(--shadow-brick);
                padding: 1rem 0;
                border-bottom: 6px solid var(--lego-yellow);
                position: sticky;
                top: 0;
                z-index: 1000;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .logo {
                display: flex;
                align-items: center;
                gap: 1rem;
                text-decoration: none;
                color: var(--lego-white);
                transition: transform 0.3s ease;
            }

            .logo:hover {
                transform: scale(1.05) rotate(-2deg);
            }

            .logo img {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                box-shadow: var(--shadow-brick);
                transition: all 0.3s ease;
            }

            .logo:hover img {
                box-shadow: var(--shadow-hover);
                transform: rotate(10deg);
            }

            .shop-name {
                font-family: 'Fredoka One', cursive;
                font-size: 2rem;
                font-weight: 400;
                color: var(--lego-white);
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            }

            .nav-links {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .nav-links a {
                background: var(--lego-yellow);
                color: var(--lego-black);
                text-decoration: none;
                padding: 0.75rem 1.5rem;
                border-radius: 15px;
                font-weight: 700;
                font-size: 0.9rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                transition: all 0.3s ease;
                box-shadow: var(--shadow-brick);
                border: 3px solid transparent;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .nav-links a:hover {
                transform: translateY(-3px);
                box-shadow: var(--shadow-hover);
                border-color: var(--lego-white);
            }

            .nav-links a:nth-child(1) { background: var(--lego-yellow); }
            .nav-links a:nth-child(2) { background: var(--lego-blue); color: var(--lego-white); }
            .nav-links a:nth-child(3) { background: var(--lego-red); color: var(--lego-white); }

            .giohang {
                width: 20px;
                height: 20px;
            }

                    .icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid var(--lego-white);
        }

        /* Category Dropdown - LEGO Style */
        #sort-bar {
            background: linear-gradient(135deg, var(--lego-blue) 0%, #1976D2 100%);
            padding: 1.5rem 2rem;
            margin-top: 0;
            border-bottom: 6px solid var(--lego-yellow);
            box-shadow: var(--shadow-brick);
        }

        .sort-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        /* Category Buttons - LEGO Style */
        .category-btn {
            background: var(--lego-green);
            color: var(--lego-white);
            padding: 0.75rem 1.25rem;
            border-radius: 15px;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: var(--shadow-brick);
            border: 3px solid transparent;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 0 0.5rem;
        }

        .category-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            border-color: var(--lego-white);
            background: var(--lego-yellow);
            color: var(--lego-black);
        }

        .category-btn.active {
            background: var(--lego-orange);
            color: var(--lego-white);
            border-color: var(--lego-white);
        }

        /* Product Container - LEGO Style */
            .sanpham-container {
                max-width: 1400px;
                margin: 2rem auto;
                padding: 0 2rem;
            }

            .container {
                background: var(--lego-white);
                border-radius: 20px;
                box-shadow: var(--shadow-brick);
                margin-bottom: 2rem;
                overflow: hidden;
                border: 5px solid var(--lego-blue);
            }

            .product {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
                padding: 2rem;
            }

            /* Product Images */
            .product-images {
                position: relative;
                background: var(--lego-light-gray);
                border-radius: 15px;
                overflow: hidden;
                box-shadow: var(--shadow-brick);
                border: 4px solid var(--lego-yellow);
            }

            .zoom-image {
                width: 100%;
                height: 400px;
                object-fit: cover;
                transition: transform 0.4s ease;
                cursor: zoom-in;
            }

            .zoom-image.zoomed {
                transform: scale(2);
                cursor: zoom-out;
            }

            /* Product Details */
            .product-details {
                padding: 1rem;
            }

            .product-details h1 {
                font-family: 'Fredoka One', cursive;
                font-size: 2.5rem;
                color: var(--lego-red);
                margin-bottom: 1rem;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            }

            .rating {
                background: var(--lego-yellow);
                color: var(--lego-black);
                padding: 0.5rem 1rem;
                border-radius: 10px;
                font-weight: 700;
                margin-bottom: 1rem;
                display: inline-block;
                box-shadow: var(--shadow-brick);
            }

            .price {
                font-size: 2rem;
                font-weight: 800;
                color: var(--lego-red);
                margin-bottom: 1rem;
                text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            }

            .discount {
                background: linear-gradient(135deg, var(--lego-orange) 0%, #E65100 100%);
                color: var(--lego-white);
                padding: 0.4rem 0.8rem;
                border-radius: 10px;
                font-size: 0.9rem;
                font-weight: 700;
                margin-left: 1rem;
                display: inline-block;
                min-width: auto;
                width: auto;
                text-align: center;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                box-shadow: var(--shadow-brick);
                transition: all 0.3s ease;
                border: 2px solid var(--lego-yellow);
                white-space: nowrap;
            }

            .discount:hover {
                background: linear-gradient(135deg, var(--lego-red) 0%, #C62828 100%);
                transform: translateY(-2px) scale(1.05);
                box-shadow: var(--shadow-hover);
                border-color: var(--lego-white);
            }

            .ghichu {
                background: var(--lego-light-gray);
                padding: 1rem;
                border-radius: 10px;
                margin-bottom: 1.5rem;
                border-left: 5px solid var(--lego-green);
            }

            .ghichu li {
                list-style: none;
                padding: 0.25rem 0;
                font-weight: 600;
                position: relative;
                padding-left: 1.5rem;
            }

            .ghichu li::before {
                content: '🧱';
                position: absolute;
                left: 0;
                top: 0.25rem;
            }

            /* Quantity Selector */
            .quantity-selector {
                margin-bottom: 1.5rem;
            }

            .quantity-selector label {
                font-weight: 700;
                font-size: 1.1rem;
                color: var(--lego-black);
                display: block;
                margin-bottom: 0.5rem;
            }

            .quantity-input {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                background: var(--lego-white);
                border: 3px solid var(--lego-blue);
                border-radius: 15px;
                padding: 0.5rem;
                box-shadow: var(--shadow-brick);
                width: fit-content;
            }

            .quantity-btn {
                background: var(--lego-yellow);
                color: var(--lego-black);
                border: none;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                font-size: 1.5rem;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: var(--shadow-brick);
            }

            .quantity-btn:hover {
                background: var(--lego-orange);
                transform: scale(1.1);
            }

            #quantity {
                border: none;
                background: transparent;
                text-align: center;
                font-size: 1.2rem;
                font-weight: 700;
                width: 60px;
                color: var(--lego-black);
            }

            #quantity:focus {
                outline: none;
            }

            /* Buttons */
            .buttons {
                display: flex;
                gap: 1rem;
                margin-top: 1rem;
            }

            .buttons button {
                flex: 1;
                padding: 1rem 2rem;
                border-radius: 15px;
                font-weight: 700;
                font-size: 1.1rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                transition: all 0.3s ease;
                box-shadow: var(--shadow-brick);
                border: 3px solid transparent;
                cursor: pointer;
            }

            .buttons button:first-child {
                background: var(--lego-green);
                color: var(--lego-white);
                border-color: var(--lego-green);
            }

            .buttons button:last-child {
                background: var(--lego-blue);
                color: var(--lego-white);
                border-color: var(--lego-blue);
            }

            .buttons button:hover {
                transform: translateY(-3px);
                box-shadow: var(--shadow-hover);
                border-color: var(--lego-yellow);
            }

            /* Notification */
            .notification {
                position: fixed;
                top: 2rem;
                right: 2rem;
                background: var(--lego-green);
                color: var(--lego-white);
                padding: 1rem 2rem;
                border-radius: 15px;
                box-shadow: var(--shadow-hover);
                font-weight: 700;
                z-index: 9999;
                transition: all 0.3s ease;
                border: 3px solid var(--lego-yellow);
            }

            .notification.hidden {
                opacity: 0;
                transform: translateX(100%);
            }

            .notification.show {
                opacity: 1;
                transform: translateX(0);
            }

            /* Section Headers */
            .container > div[style*="background-color: #fafafa"] {
                background: linear-gradient(135deg, var(--lego-blue) 0%, #1976D2 100%) !important;
                color: var(--lego-white) !important;
                padding: 1.5rem 2rem !important;
                border-radius: 0 !important;
            }

            .container > div[style*="background-color: #fafafa"] h1 {
                color: var(--lego-white) !important;
                font-family: 'Fredoka One', cursive !important;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3) !important;
            }

            /* Product Grid - LEGO Style */
            .grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 1.5rem;
                padding: 2rem;
            }

            .bg-card {
                background: var(--lego-white);
                border-radius: 20px;
                overflow: hidden;
                box-shadow: var(--shadow-brick);
                transition: all 0.4s ease;
                text-decoration: none;
                color: var(--lego-black);
                position: relative;
                border: 5px solid var(--lego-blue);
            }

            .bg-card:nth-child(5n+1) { border-color: var(--lego-red); }
            .bg-card:nth-child(5n+2) { border-color: var(--lego-yellow); }
            .bg-card:nth-child(5n+3) { border-color: var(--lego-blue); }
            .bg-card:nth-child(5n+4) { border-color: var(--lego-green); }
            .bg-card:nth-child(5n+5) { border-color: var(--lego-orange); }

            .bg-card:hover {
                transform: translateY(-15px) rotate(-1deg);
                box-shadow: var(--shadow-hover);
            }

            .bg-card img {
                width: 100%;
                height: 200px;
                object-fit: cover;
                transition: transform 0.4s ease;
            }

            .bg-card:hover img {
                transform: scale(1.1);
            }

            .bg-card h2 {
                font-size: 1.2rem;
                font-weight: 800;
                margin-bottom: 0.5rem;
                color: var(--lego-black);
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .bg-card p {
                color: var(--lego-gray);
                font-size: 0.9rem;
                margin-bottom: 1rem;
                line-height: 1.4;
            }

            .bg-card span {
                font-size: 1.3rem;
                font-weight: 800;
                color: var(--lego-red);
                text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            }

            .out-of-stock {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
                pointer-events: none !important;
                z-index: 10 !important;
                opacity: 0.95 !important;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .product {
                    grid-template-columns: 1fr;
                    gap: 1rem;
                }

                .product-details h1 {
                    font-size: 2rem;
                }

                .buttons {
                    flex-direction: column;
                }

                .navbar {
                    flex-direction: column;
                    gap: 1rem;
                    padding: 1rem;
                }

                .nav-links {
                    gap: 0.5rem;
                }

                .grid {
                    grid-template-columns: 1fr;
                    padding: 1rem;
                }
            }

            /* Fun Animations */
            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(50px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .slide-in {
                animation: slideInUp 0.6s ease-out;
            }

            @keyframes sparkle {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            .bg-card::before {
                content: '';
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
                background-size: 15px 15px;
                animation: sparkle 3s linear infinite;
                opacity: 0;
                transition: opacity 0.3s ease;
                z-index: 1;
            }

            .bg-card:hover::before {
                opacity: 1;
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

    <!-- Category Navigation -->
    <div id="sort-bar">
        <div class="sort-links">
            <?php
            include 'db_connect.php';
            $sql_loaihang = "SELECT * FROM loaihang";
            $result_loaihang = mysqli_query($con, $sql_loaihang);

            if (mysqli_num_rows($result_loaihang) > 0) {
                while ($row_loaihang = mysqli_fetch_assoc($result_loaihang)) {
                    $tenloaihang = $row_loaihang['Tenloaihang'];
                    $maloaihang = $row_loaihang['Maloaihang'];
                    // Thêm class 'active' nếu danh mục đang được chọn
                    $activeClass = (isset($_GET['category']) && $_GET['category'] == $maloaihang) ? 'active' : '';
                    echo "<a href='allsp.php?category=$maloaihang' class='category-btn $activeClass'>$tenloaihang</a>";
                }
            }

            mysqli_close($con);
            ?>
        </div>
    </div>

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

                    <a href="sanpham.php?id=<?php echo $Mahang; ?>" class="bg-card slide-in">
                        <div class="p-4 relative">
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
        <script>
            // Add staggered animation delays for products
            document.addEventListener('DOMContentLoaded', function() {
                const cards = document.querySelectorAll('.slide-in');
                cards.forEach((card, index) => {
                    card.style.animationDelay = `${index * 0.1}s`;
                });

                // Add bounce animation to logo on scroll
                window.addEventListener('scroll', function() {
                    const logo = document.querySelector('.logo');
                    if (logo) {
                        if (window.scrollY > 100) {
                            logo.style.transform = 'scale(0.9) rotate(-1deg)';
                        } else {
                            logo.style.transform = 'scale(1) rotate(0deg)';
                        }
                    }
                });
            });

            // Fun click effects for product cards
            document.querySelectorAll('.bg-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    // Create sparkle effect
                    const sparkle = document.createElement('div');
                    sparkle.style.position = 'absolute';
                    sparkle.style.left = e.clientX + 'px';
                    sparkle.style.top = e.clientY + 'px';
                    sparkle.style.width = '10px';
                    sparkle.style.height = '10px';
                    sparkle.style.background = 'var(--lego-yellow)';
                    sparkle.style.borderRadius = '50%';
                    sparkle.style.pointerEvents = 'none';
                    sparkle.style.zIndex = '9999';
                    sparkle.style.animation = 'sparkle 0.6s ease-out forwards';
                    document.body.appendChild(sparkle);
                    
                    setTimeout(() => {
                        sparkle.remove();
                    }, 600);
                });
            });
        
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

        // Category buttons functionality - no JavaScript needed for simple navigation

    </script>

    </body>
    </html>
