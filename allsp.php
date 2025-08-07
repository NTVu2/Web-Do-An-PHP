<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vũ Tuấn Shop - LEGO Store</title>
    <link rel="shortcut icon" href="img/sản_phẩm/logo.png" type="image/x-icon">
    
    <!-- External CSS -->
    <link rel="stylesheet" href="css/trangview.css">
    <link rel="stylesheet" href="css/banner.css">
    <link rel="stylesheet" href="css/danhmuc.css">
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

        /* Header Styles - LEGO Style */
        .lego-header {
            background: linear-gradient(135deg, var(--lego-red) 0%, #B71C1C 100%);
            box-shadow: var(--shadow-brick);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 6px solid var(--lego-yellow);
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
        }

        .lego-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: var(--lego-white);
            font-family: 'Fredoka One', cursive;
            font-size: 2rem;
            font-weight: 400;
            transition: transform 0.3s ease;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .lego-logo:hover {
            transform: scale(1.05) rotate(-2deg);
        }

        .lego-logo img {
            transition: all 0.3s ease;
        }

        .lego-logo:hover img {
            transform: rotate(10deg);
            box-shadow: var(--shadow-hover);
        }

        /* Search Section - LEGO Style */
        .lego-search {
            flex: 1;
            max-width: 600px;
            position: relative;
        }

        .search-brick {
            position: relative;
            background: var(--lego-white);
            border-radius: 25px;
            box-shadow: var(--shadow-brick);
            overflow: hidden;
            border: 4px solid var(--lego-blue);
        }

        .search-input {
            width: 100%;
            padding: 1rem 4rem 1rem 2rem;
            border: none;
            background: transparent;
            color: var(--lego-black);
            font-size: 1.1rem;
            font-weight: 600;
            font-family: 'Nunito', sans-serif;
        }

        .search-input:focus {
            outline: none;
        }

        .search-input::placeholder {
            color: var(--lego-gray);
            font-weight: 500;
        }

        .search-btn {
            position: absolute;
            right: 4px;
            top: 4px;
            bottom: 4px;
            background: var(--lego-green);
            border: none;
            border-radius: 20px;
            width: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--lego-white);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.2rem;
        }

        .search-btn:hover {
            background: #00C853;
            transform: scale(1.1);
        }

        /* Navigation Menu - LEGO Style */
        .lego-nav {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .nav-brick {
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

        .nav-brick:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            border-color: var(--lego-white);
        }

        .nav-brick.red { background: var(--lego-red); color: var(--lego-white); }
        .nav-brick.blue { background: var(--lego-blue); color: var(--lego-white); }
        .nav-brick.green { background: var(--lego-green); color: var(--lego-white); }
        .nav-brick.orange { background: var(--lego-orange); color: var(--lego-white); }

        .nav-icon {
            width: 20px;
            height: 20px;
        }

        .user-avatar {
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

        /* Product Section - LEGO Style */
        .lego-products {
            padding: 4rem 0;
            background: linear-gradient(135deg, var(--lego-light-gray) 0%, #E8F5E8 100%);
        }

        .section-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);/* 4 or 5 sản phẩm trên 1 hàng */
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .product-brick {
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

        .product-brick:nth-child(5n+1) { border-color: var(--lego-red); }
        .product-brick:nth-child(5n+2) { border-color: var(--lego-yellow); }
        .product-brick:nth-child(5n+3) { border-color: var(--lego-blue); }
        .product-brick:nth-child(5n+4) { border-color: var(--lego-green); }
        .product-brick:nth-child(5n+5) { border-color: var(--lego-orange); }

        .product-brick:hover {
            transform: translateY(-15px) rotate(-1deg);
            box-shadow: var(--shadow-hover);
        }

        .product-image-container {
            position: relative;
            overflow: hidden;
            background: var(--lego-light-gray);
        }

        .product-brick img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .product-brick:hover img {
            transform: scale(1.1);
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

        .product-info {
            padding: 2rem 1.5rem;
            background: var(--lego-white);
        }

        .product-title {
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
            line-height: 1.3;
            color: var(--lego-black);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .product-description {
            color: var(--lego-gray);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--lego-red);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Pagination - LEGO Style */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            padding: 2rem 0;
        }

.pagination-btn {
            padding: 1rem 1.5rem;
            border-radius: 15px;
            background: var(--lego-white);
            color: var(--lego-black);
            font-weight: 700;
            text-decoration: none;
            box-shadow: var(--shadow-brick);
            border: 3px solid var(--lego-blue);
    transition: all 0.3s ease;
            font-size: 1.1rem;
}

.pagination-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            border-color: var(--lego-yellow);
}

.pagination-btn.active {
            background: var(--lego-yellow);
            border-color: var(--lego-orange);
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        /* Footer - LEGO Style */
        .lego-footer {
            background: linear-gradient(135deg, var(--lego-black) 0%, #424242 100%);
            color: var(--lego-white);
            padding: 3rem 0 1rem;
            position: relative;
            overflow: hidden;
        }

        .lego-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, 
                var(--lego-red) 0%, 
                var(--lego-yellow) 20%, 
                var(--lego-blue) 40%, 
                var(--lego-green) 60%, 
                var(--lego-orange) 80%, 
                var(--lego-red) 100%);
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            text-align: center;
        }

        .footer-text {
            font-size: 1.2rem;
            font-weight: 600;
            opacity: 0.9;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .header-container {
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .lego-search {
                order: 3;
                flex-basis: 100%;
            }
        }

        @media (max-width: 768px) {
            .lego-logo {
                font-size: 1.5rem;
            }
            
            .lego-logo img {
                width: 50px;
                height: 50px;
            }
            
            .product-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .sort-links {
                flex-wrap: wrap;
                gap: 0.75rem;
            }

            .pagination {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .header-container {
                padding: 1rem;
            }
            
            .lego-nav {
                gap: 0.5rem;
            }
            
            .nav-brick {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }
        }

        /* Fun Animations */
        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% {
                transform: translate3d(0,0,0);
            }
            40%, 43% {
                transform: translate3d(0, -30px, 0);
            }
            70% {
                transform: translate3d(0, -15px, 0);
            }
            90% {
                transform: translate3d(0, -4px, 0);
            }
        }

        .bounce-animation {
            animation: bounce 2s ease infinite;
        }

        /* Loading Animation */
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

        /* Sparkle Animation */
        @keyframes sparkle {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .product-brick::before {
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

        .product-brick:hover::before {
            opacity: 1;
}
    </style>
</head>
<body>
    <!-- Header -->
    <header class="lego-header brick-pattern">
        <div class="header-container">
            <!-- Logo Section -->
            <a href="index.php" class="lego-logo">
                <img src="img/sản_phẩm/logo.png" alt="Logo" style="width: 60px; height: 60px; border-radius: 50%; box-shadow: var(--shadow-brick);">
                <span>Vũ Tuấn Shop</span>
            </a>

            <!-- Search Section -->
            <div class="lego-search">
                <form action="allsp.php" method="GET" class="search-brick">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Tìm kiếm đồ chơi tuyệt vời..." 
                        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" 
                        class="search-input"
                    />
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                    </form>
                </div>

            <!-- Navigation Menu -->
            <nav class="lego-nav">
                <?php if ($loggedIn): ?>
                    <a href="giohang1.php" class="nav-brick yellow">
                        <img src="img/giohang.png" alt="Giỏ hàng" class="nav-icon">
                        <span>Giỏ Hàng</span>
                    </a>
                    <a href="theodoi.php" class="nav-brick blue">
                        <img src="img/icon.png" alt="Tài khoản" class="user-avatar">
                        <span><?= htmlspecialchars($username) ?></span>
                    </a>
                    <a href="logout.php" class="nav-brick red">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Thoát</span>
                    </a>
                <?php else: ?>
                    <a href="giohang1.php" class="nav-brick yellow">
                        <img src="img/giohang.png" alt="Giỏ hàng" class="nav-icon">
                        <span>Giỏ Hàng</span>
                    </a>
                    <a href="Login_singup/login.php" class="nav-brick green">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Đăng Nhập</span>
                    </a>
                    <a href="Login_singup/singup.php" class="nav-brick orange">
                        <i class="fas fa-user-plus"></i>
                        <span>Đăng Ký</span>
                    </a>
                <?php endif; ?>
            </nav>
            </div>
    </header>

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
                    echo "<a href='?category=$maloaihang" . (isset($_GET['search']) ? "&search=" . $_GET['search'] : "") . "' class='category-btn $activeClass'>$tenloaihang</a>";
                }
            }

            mysqli_close($con);
            ?>
        </div>
    </div>

    <!-- Product Section -->
    <section class="lego-products" id="product-section">
        <div class="section-container">
            <div class="product-grid">
<?php
                // Include database connection
                include 'db_connect.php';

                // Check for connection error
                if (!$con) {
                    die("Connection failed: " . mysqli_connect_error());
                }

                // Lấy giá trị category từ URL nếu có
                $category = isset($_GET['category']) ? $_GET['category'] : '';

                // Lấy giá trị tìm kiếm từ URL nếu có
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                
                $limit = 20;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $limit;
            

                // SQL query để lấy các sản phẩm
                $sql = "SELECT hang.Mahang, hang.Tenhang, hang.Mota, loaihang.Tenloaihang, hang.anh, hang.Dongia, hang.Soluongton 
                FROM hang 
                INNER JOIN loaihang ON hang.Maloaihang = loaihang.Maloaihang";
        
        // Nếu có danh mục được chọn, thêm điều kiện lọc
        if (!empty($category)) {
            $sql .= " WHERE hang.Maloaihang = '$category'";
        }
        
        // Nếu có từ khóa tìm kiếm, thêm điều kiện lọc
        if (!empty($search)) {
            if (!empty($category)) {
                $sql .= " AND";
            } else {
                $sql .= " WHERE";
            }
            $sql .= " hang.Tenhang LIKE '%$search%'";
        }
        
        // Giới hạn sản phẩm theo phân trang
        $sql .= " LIMIT $limit OFFSET $offset";
        
        $result = mysqli_query($con, $sql);

                // Kiểm tra và hiển thị các sản phẩm
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $Tenhang = $row['Tenhang'];
                        $Mota = $row['Mota'];
                        $anh = $row['anh'];
                        $don_gia = number_format($row['Dongia'], 0, '.', '.');
                        $soluongton = $row['Soluongton'];
                        $Mahang = $row['Mahang'];

                        // Đường dẫn đến hình ảnh "hết hàng"
                        $outOfStockImage = 'img/sold_out.png';

                        echo "
                        <a href='sanpham.php?id=$Mahang' class='product-brick slide-in'>
                            <div class='product-image-container'>
                                <img src='../img/sản_phẩm/$anh' alt='$Tenhang' />
                                ";

                        // Hiển thị hình ảnh "hết hàng" nếu số lượng tồn bằng 0
                        if ($soluongton == 0) {
                            echo "<img src='$outOfStockImage' alt='Hết hàng' class='out-of-stock' />";
                        }

                        echo "
                            </div>
                            <div class='product-info'>
                                <h3 class='product-title'>$Tenhang</h3>
                                <p class='product-description'>$Mota</p>
                                <div class='product-price'>$don_gia VNĐ</div>
                            </div>
                        </a>";
                    }
                } else {
                    echo "<div style='grid-column: 1 / -1; text-align: center; padding: 4rem;'>
                            <div style='font-size: 4rem; margin-bottom: 1rem;'>🔍</div>
                            <p style='font-size: 1.5rem; color: var(--lego-gray); font-weight: 600;'>Không tìm thấy sản phẩm phù hợp!</p>
                            <p style='color: var(--lego-gray);'>Hãy thử tìm kiếm với từ khóa khác nhé!</p>
                          </div>";
                }
// Tính tổng số trang cho phân trang
$count_sql = "SELECT COUNT(*) AS total FROM hang";

// Thêm điều kiện lọc danh mục hoặc tìm kiếm
if (!empty($category)) {
    $count_sql .= " WHERE Maloaihang = '$category'";
}

if (!empty($search)) {
    if (!empty($category)) {
        $count_sql .= " AND";
    } else {
        $count_sql .= " WHERE";
    }
    $count_sql .= " Tenhang LIKE '%$search%'";
}

$count_result = mysqli_query($con, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$total_products = $count_row['total'];

// Tính tổng số trang
$total_pages = ceil($total_products / $limit);

// Hiển thị nút phân trang
echo "</div>";
echo "<div class='pagination'>";
for ($i = 1; $i <= $total_pages; $i++) {
    $active = ($i == $page) ? 'active' : '';
    echo "<a class='pagination-btn $active' href='?page=$i&category=$category&search=$search'>$i</a>";
}
echo "</div>";
                // Đóng kết nối
                mysqli_close($con);
            ?>
</div>
    </section>

        <!-- Footer -->
    <footer class="lego-footer">
        <div class="footer-content">
            <p class="footer-text">
                🧱 2024 Vũ Tuấn Shop - Nơi ước mơ trở thành hiện thực! 🌟
            </p>
        </div>
        </footer>

    <!-- Scripts -->
    <script src="script/trangchu.js"></script>
    <script src="script/banner.js"></script>
    <script>
    // Hàm cuộn mượt với thời gian tùy chỉnh
    function smoothScrollTo(element, duration) {
        const targetPosition = element.getBoundingClientRect().top + window.pageYOffset;
        const startPosition = window.pageYOffset;
        const distance = targetPosition - startPosition;
        let startTime = null;

        function animationScroll(currentTime) {
            if (startTime === null) startTime = currentTime;
            const timeElapsed = currentTime - startTime;
            const run = easeInOutQuad(timeElapsed, startPosition, distance, duration);
            window.scrollTo(0, run);
            if (timeElapsed < duration) requestAnimationFrame(animationScroll);
        }

        function easeInOutQuad(t, b, c, d) {
            t /= d / 2;
            if (t < 1) return c / 2 * t * t + b;
            t--;
            return -c / 2 * (t * (t - 2) - 1) + b;
        }

        requestAnimationFrame(animationScroll);
    }

        // Add staggered animation delays
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.slide-in');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });

            // Add bounce animation to logo on scroll
            window.addEventListener('scroll', function() {
                const logo = document.querySelector('.lego-logo');
                if (logo) {
                    if (window.scrollY > 100) {
                        logo.style.transform = 'scale(0.9)';
                    } else {
                        logo.style.transform = 'scale(1)';
                    }
                }
            });
        });

        // Fun click effects
        document.querySelectorAll('.product-brick').forEach(brick => {
            brick.addEventListener('click', function(e) {
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

    // Xử lý khi nhấn vào nút phân trang
    document.querySelectorAll('.pagination-btn').forEach(button => {
        button.addEventListener('click', function(event) {
            // Ngăn chặn hành vi mặc định của nút liên kết
            event.preventDefault();

            // Lưu trạng thái cuộn vào localStorage
            localStorage.setItem('scrollToProducts', 'true');

            // Chuyển hướng đến URL của trang được chọn
            const url = this.getAttribute('href');
            window.location.href = url;
        });
            });

        // Category buttons functionality - no JavaScript needed for simple navigation

        // Smooth scroll to products on page load if needed
        window.addEventListener('load', function() {
            if (localStorage.getItem('scrollToProducts') === 'true') {
                localStorage.removeItem('scrollToProducts');
                const productSection = document.getElementById('product-section');
                if (productSection) {
                    smoothScrollTo(productSection, 800);
                }
            }
        });
</script>
</body>
</html>
