<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$loggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
$quyen = isset($_SESSION['quyen']) ? $_SESSION['quyen'] : [];  // Lấy quyền từ session, mặc định là mảng trống nếu không có
if (!in_array('donhang', $quyen)) {
    echo "Bạn không có quyền truy cập trang này.";
    header("Location: loginADMIN.php");
    exit;
}
include '../db_connect.php'; // Kết nối database

// Xử lý yêu cầu POST để cập nhật trạng thái hoặc xóa hóa đơn
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kiểm tra nếu có yêu cầu xóa hóa đơn
    if (isset($_POST['SohieuHD_xoa'])) {
        $SohieuHD_xoa = $_POST['SohieuHD_xoa'];

        // Xóa bản ghi trong bảng hoadon, giữ lại chitiethd
        $sqlDeleteHD = "DELETE FROM hoadon WHERE SohieuHD = ?";
        $stmtHD = $con->prepare($sqlDeleteHD);
        $stmtHD->bind_param("s", $SohieuHD_xoa);

        if ($stmtHD->execute()) {
            $_SESSION['message'] = "Hóa đơn đã được xóa thành công.";
        } else {
            $_SESSION['message'] = "Lỗi khi xóa hóa đơn: " . $con->error;
        }

        // Chuyển hướng về trang quản lý đơn hàng sau khi xóa
        header("Location: quanlydonhang.php");
        exit();
    }

    // Xử lý cập nhật trạng thái đơn hàng
    $SohieuHD = $_POST['SohieuHD'];
    $Trangthai = $_POST['Trangthai'];

     // Kiểm tra trạng thái và cập nhật ngày giao hàng dự kiến
     if ($Trangthai == "1-2 ngày") {
        $currentDate = new DateTime();
        $currentDate->modify('+2 day');
        $ngayGiaoDuKien = $currentDate->format('d-m-Y');
        $Trangthai = "Ngày giao hàng dự kiến: " . $ngayGiaoDuKien;
    } elseif ($Trangthai == "3-4 ngày") {
        $currentDate = new DateTime();
        $currentDate->modify('+4 day');
        $ngayGiaoDuKien = $currentDate->format('d-m-Y');
        $Trangthai = "Ngày giao hàng dự kiến: " . $ngayGiaoDuKien;
    } elseif ($Trangthai == "5-6 ngày") {
        $currentDate = new DateTime();
        $currentDate->modify('+6 day');
        $ngayGiaoDuKien = $currentDate->format('d-m-Y');
        $Trangthai = "Ngày giao hàng dự kiến: " . $ngayGiaoDuKien;
    } elseif ($Trangthai == "7-8 ngày") {
        $currentDate = new DateTime();
        $currentDate->modify('+8 day');
        $ngayGiaoDuKien = $currentDate->format('d-m-Y');
        $Trangthai = "Ngày giao hàng dự kiến: " . $ngayGiaoDuKien;
    }
    

    // Cập nhật trạng thái đơn hàng
    $sql = "UPDATE hoadon SET Trangthai = ? WHERE SohieuHD = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ss", $Trangthai, $SohieuHD);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Trạng thái đơn hàng đã được cập nhật.";

        // Kiểm tra nếu trạng thái là 'Giao hàng thành công' và cập nhật số lượng tồn kho
        if ($Trangthai == "Giao hàng thành công") {
            $sqlDetails = "SELECT Mahang, Soluong FROM chitiethd WHERE SohieuHD = ?";
            $stmtDetails = $con->prepare($sqlDetails);
            $stmtDetails->bind_param("s", $SohieuHD);
            $stmtDetails->execute();
            $resultDetails = $stmtDetails->get_result();

            while ($row = $resultDetails->fetch_assoc()) {
                $Mahang = $row['Mahang'];
                $SoluongBan = $row['Soluong'];

                // Trừ số lượng bán vào số lượng tồn
                $updateHang = "UPDATE hang SET Soluongton = Soluongton - ? WHERE Mahang = ?";
                $stmtUpdate = $con->prepare($updateHang);
                $stmtUpdate->bind_param("is", $SoluongBan, $Mahang);
                $stmtUpdate->execute();
            }
            $stmtDetails->close();
        }
    } else {
        $_SESSION['message'] = "Lỗi: " . $con->error;
    }

    $stmt->close();
}

// Lấy thống kê tổng quan
$sqlStats = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN Trangthai = 'Đang xử lý' THEN 1 ELSE 0 END) as processing_orders,
    SUM(CASE WHEN Trangthai = 'Đã hủy' THEN 1 ELSE 0 END) as cancelled_orders,
    SUM(CASE WHEN Trangthai = 'Giao hàng thành công' THEN 1 ELSE 0 END) as completed_orders,
    SUM(Tongtien) as total_revenue
FROM hoadon";
$resultStats = $con->query($sqlStats);
$stats = $resultStats->fetch_assoc();

// Lấy thống kê tổng tiền cho từng tab
$sqlProcessingRevenue = "SELECT COUNT(*) as count, SUM(Tongtien) as revenue FROM hoadon WHERE Trangthai = 'Đang xử lý'";
$resultProcessingRevenue = $con->query($sqlProcessingRevenue);
$processingStats = $resultProcessingRevenue->fetch_assoc();

$sqlCancelledRevenue = "SELECT COUNT(*) as count, SUM(Tongtien) as revenue FROM hoadon WHERE Trangthai = 'Đã hủy'";
$resultCancelledRevenue = $con->query($sqlCancelledRevenue);
$cancelledStats = $resultCancelledRevenue->fetch_assoc();

$sqlCompletedRevenue = "SELECT COUNT(*) as count, SUM(Tongtien) as revenue FROM hoadon WHERE Trangthai = 'Giao hàng thành công'";
$resultCompletedRevenue = $con->query($sqlCompletedRevenue);
$completedStats = $resultCompletedRevenue->fetch_assoc();



// Xử lý tìm kiếm và lọc
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'all';

// Truy vấn để lấy danh sách đơn hàng
$sql = "SELECT hd.SohieuHD, k.Tenkhach, k.Dienthoai, hd.NgayBH, hd.Tongtien, hd.Trangthai 
        FROM hoadon hd
        JOIN khach k ON hd.id = k.id";

// Thêm điều kiện lọc dựa trên tab hiện tại
if ($currentTab == 'processing') {
    $sql .= " WHERE hd.Trangthai = 'Đang xử lý'";
} elseif ($currentTab == 'cancelled') {
    $sql .= " WHERE hd.Trangthai = 'Đã hủy'";
} elseif ($currentTab == 'completed') {
    $sql .= " WHERE hd.Trangthai = 'Giao hàng thành công'";
}

// Thêm điều kiện tìm kiếm
if (!empty($searchTerm)) {
    $searchCondition = " WHERE hd.SohieuHD LIKE '%$searchTerm%' 
                        OR k.Tenkhach LIKE '%$searchTerm%' 
                        OR k.Dienthoai LIKE '%$searchTerm%' 
                        OR hd.Trangthai LIKE '%$searchTerm%'";
    
    if (strpos($sql, 'WHERE') !== false) {
        $sql .= " AND (" . substr($searchCondition, 7, -1) . ")";
    } else {
        $sql .= $searchCondition;
    }
}

$sql .= " ORDER BY hd.NgayBH DESC";
$result = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/qldonhang.css">
    <style>
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 14px;
        }
        
        .stat-card .number {
            font-size: 24px;
            font-weight: bold;
            color: #3399ff;
        }
        
        .stat-card .revenue {
            color: #28a745;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        
        .tab-button {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .tab-button:hover {
            color: #3399ff;
            text-decoration: none;
        }
        
        .tab-button.active {
            color: #3399ff;
            border-bottom-color: #3399ff;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        

        
        .search-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
        }
        
        .search-filter input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            flex: 1;
        }
        
        .search-filter select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .search-filter button {
            padding: 8px 16px;
            background: #3399ff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-processing {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .col-du-kien {
            width: 15%;
        }
        .ten{
            width: 10%;
        }
        .ngay{
            width: 10%;
        }
        
        .disabled-action {
            color: #999 !important;
            cursor: not-allowed !important;
            text-decoration: none !important;
            pointer-events: none;
        }
        
        .disabled-action:hover {
            color: #999 !important;
            text-decoration: none !important;
        }
        
        .tab-stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #3399ff;
        }
        
        .tab-stats h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 16px;
        }
        
        .tab-stats .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .tab-stats .stat-item {
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .tab-stats .stat-item .label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .tab-stats .stat-item .value {
            font-size: 18px;
            font-weight: bold;
            color: #3399ff;
        }
        
        .tab-stats .stat-item .revenue {
            color: #28a745;
        }
    </style>
</head>
<?php include 'navbar.php'; ?>
<body style="font-family: Arial, sans-serif;">
    <main>
        <h1 style="margin-bottom: 20px;">Quản lý đơn hàng</h1>
        
        <?php
        // Hiển thị thông báo nếu có trong session
        if (isset($_SESSION['message'])) {
            echo '<div id="message" class="message">' . $_SESSION['message'] . '</div>';
            // Xóa thông báo sau khi hiển thị
            unset($_SESSION['message']);
        }
        ?>

        <!-- Thống kê tổng quan -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3><i class="fa fa-shopping-cart"></i> Tổng đơn hàng</h3>
                <div class="number"><?php echo $stats['total_orders']; ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fa fa-clock"></i> Đang xử lý</h3>
                <div class="number"><?php echo $stats['processing_orders']; ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fa fa-times-circle"></i> Đã hủy</h3>
                <div class="number"><?php echo $stats['cancelled_orders']; ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fa fa-check-circle"></i> Hoàn thành</h3>
                <div class="number"><?php echo $stats['completed_orders']; ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fa fa-money-bill-wave"></i> Tổng doanh thu</h3>
                <div class="number revenue"><?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?> VND</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <a href="?tab=all" class="tab-button <?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'all') ? 'active' : ''; ?>">Tất cả đơn hàng</a>
            <a href="?tab=processing" class="tab-button <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'processing') ? 'active' : ''; ?>">Đang xử lý</a>
            <a href="?tab=cancelled" class="tab-button <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'cancelled') ? 'active' : ''; ?>">Đã hủy</a>
            <a href="?tab=completed" class="tab-button <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'completed') ? 'active' : ''; ?>">Hoàn thành</a>

        </div>

        <?php if ($currentTab == 'all' || $currentTab == 'processing' || $currentTab == 'cancelled' || $currentTab == 'completed'): ?>
        <!-- Tab: Đơn hàng -->
        <div class="tab-content active">
            <!-- Thống kê cho tab hiện tại -->
            <?php if ($currentTab == 'processing'): ?>
            <div class="tab-stats">
                <h4><i class="fa fa-clock"></i> Thống kê đơn hàng đang xử lý</h4>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="label">Số đơn hàng</div>
                        <div class="value"><?php echo $processingStats['count']; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="label">Tổng giá trị</div>
                        <div class="value revenue"><?php echo number_format($processingStats['revenue'], 0, ',', '.'); ?> VND</div>
                    </div>
                </div>
            </div>
            <?php elseif ($currentTab == 'cancelled'): ?>
            <div class="tab-stats">
                <h4><i class="fa fa-times-circle"></i> Thống kê đơn hàng đã hủy</h4>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="label">Số đơn hàng</div>
                        <div class="value"><?php echo $cancelledStats['count']; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="label">Tổng giá trị</div>
                        <div class="value revenue"><?php echo number_format($cancelledStats['revenue'], 0, ',', '.'); ?> VND</div>
                    </div>
                </div>
            </div>
            <?php elseif ($currentTab == 'completed'): ?>
            <div class="tab-stats">
                <h4><i class="fa fa-check-circle"></i> Thống kê đơn hàng hoàn thành</h4>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="label">Số đơn hàng</div>
                        <div class="value"><?php echo $completedStats['count']; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="label">Tổng doanh thu</div>
                        <div class="value revenue"><?php echo number_format($completedStats['revenue'], 0, ',', '.'); ?> VND</div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="tab-stats">
                <h4><i class="fa fa-shopping-cart"></i> Thống kê tổng quan</h4>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="label">Tổng đơn hàng</div>
                        <div class="value"><?php echo $stats['total_orders']; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="label">Tổng doanh thu</div>
                        <div class="value revenue"><?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?> VND</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="search-filter">
                <form action="quanlydonhang.php" method="GET" style="display: flex; gap: 10px; flex: 1;">
                    <input type="hidden" name="tab" value="<?php echo htmlspecialchars($currentTab); ?>">
                    <input type="text" name="search" placeholder="Tìm kiếm đơn hàng..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <select name="filter">
                        <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>Tất cả</option>
                        <option value="processing" <?php echo $filter == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                        <option value="cancelled" <?php echo $filter == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                        <option value="completed" <?php echo $filter == 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                    </select>
                    <button type="submit">Tìm kiếm</button>
                </form>
            </div>

            <table border="1">
                <thead>
                    <tr>
                        <th>Số hóa đơn</th>
                        <th class="ten">Tên khách hàng</th>
                        <th>Số điện thoại</th>
                        <th class="ngay">Ngày bán hàng</th>
                        <th class="col-du-kien">Dự kiến</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['SohieuHD']; ?></td>
                        <td class="ten"><?php echo $row['Tenkhach']; ?></td>
                        <td>(+84)<?php echo $row['Dienthoai']; ?></td>
                        <td class="ngay"><?php echo $row['NgayBH']; ?></td>
                        <td class="col-du-kien"><?php echo $row['Trangthai']; ?></td>
                        <td><?php echo number_format($row['Tongtien'], 0, ',', '.') . ' VND'; ?></td>
                        <td>
                            <?php
                            $statusClass = '';
                            if ($row['Trangthai'] == 'Đang xử lý') $statusClass = 'status-processing';
                            elseif ($row['Trangthai'] == 'Đã hủy') $statusClass = 'status-cancelled';
                            elseif ($row['Trangthai'] == 'Giao hàng thành công') $statusClass = 'status-completed';
                            ?>
                            <span class="status-badge <?php echo $statusClass; ?>"><?php echo $row['Trangthai']; ?></span>
                        </td>
                        <td>
                            <form action="quanlydonhang.php" method="POST">
                                <input type="hidden" name="SohieuHD" value="<?php echo $row['SohieuHD']; ?>">
                                <select name="Trangthai">
                                    <option value="Đang xử lý" <?php if ($row['Trangthai'] == 'Đang xử lý') echo 'selected'; ?>>Đang xử lý</option>
                                    <option value="1-2 ngày" <?php if (strpos($row['Trangthai'], '1-2 ngày') !== false) echo 'selected'; ?>>1-2 ngày</option>
                                    <option value="3-4 ngày" <?php if (strpos($row['Trangthai'], '3-4 ngày') !== false) echo 'selected'; ?>>3-4 ngày</option>
                                    <option value="5-6 ngày" <?php if (strpos($row['Trangthai'], '5-6 ngày') !== false) echo 'selected'; ?>>5-6 ngày</option>
                                    <option value="7-8 ngày" <?php if (strpos($row['Trangthai'], '7-8 ngày') !== false) echo 'selected'; ?>>7-8 ngày</option>
                                    <option value="Giao hàng thành công" <?php if ($row['Trangthai'] == 'Giao hàng thành công') echo 'selected'; ?>>Giao hàng thành công</option>
                                    <option value="Đã hủy" <?php if ($row['Trangthai'] == 'Đã hủy') echo 'selected'; ?>>Đã hủy</option>
                                </select>
                                <button type="submit" <?php if ($row['Trangthai'] == 'Giao hàng thành công' || $row['Trangthai'] == 'Đã hủy') echo 'disabled'; ?>>Cập nhật</button>
                            </form>
                        </td>
                        <td>
                            <?php if ($row['Trangthai'] == 'Giao hàng thành công'): ?>
                                <a class="link-action" href="indonhang.php?SohieuHD=<?php echo $row['SohieuHD']; ?>">In hóa đơn</a>
                            <?php else: ?>
                                <span class="disabled-action">In hóa đơn</span>
                            <?php endif; ?>
                            <form action="quanlydonhang.php" method="POST" style="display:inline;">
                                <input type="hidden" name="SohieuHD_xoa" value="<?php echo $row['SohieuHD']; ?>">
                                <button type="submit" onclick="return confirm('Bạn có chắc chắn muốn xóa hóa đơn này?');" style="background-color: #f15050; color: white; height: 28px;">Xóa </button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>



        <script>
            // Tự động ẩn thông báo sau 3 giây
            setTimeout(function() {
                var messageDiv = document.getElementById("message");
                if (messageDiv) {
                    messageDiv.style.display = "none";
                }
            }, 3000);
        </script>
    </main>
</body>
</html>
