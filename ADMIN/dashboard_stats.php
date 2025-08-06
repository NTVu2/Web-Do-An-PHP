<?php
// Không cần include database connection vì đã có trong trangchuadmin.php

// Lấy thống kê tổng quan cho dashboard
$sqlStats = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN Trangthai = 'Đang xử lý' THEN 1 ELSE 0 END) as processing_orders,
    SUM(CASE WHEN Trangthai = 'Đã hủy' THEN 1 ELSE 0 END) as cancelled_orders,
    SUM(CASE WHEN Trangthai = 'Giao hàng thành công' THEN 1 ELSE 0 END) as completed_orders,
    SUM(Tongtien) as total_revenue
FROM hoadon";
$resultStats = $con->query($sqlStats);
$stats = $resultStats->fetch_assoc();

// Lấy thống kê sản phẩm bán chạy cho dashboard
$sqlTopProducts = "SELECT 
    h.Tenhang,
    SUM(ct.Soluong) as total_sold,
    SUM(ct.Thanhtien) as total_revenue
FROM chitiethd ct
JOIN hang h ON ct.Mahang = h.Mahang
JOIN hoadon hd ON ct.SohieuHD = hd.SohieuHD
WHERE hd.Trangthai = 'Giao hàng thành công'
GROUP BY h.Mahang, h.Tenhang
ORDER BY total_sold DESC
LIMIT 5";
$resultTopProducts = $con->query($sqlTopProducts);
if (!$resultTopProducts) {
    error_log("SQL Error in dashboard_stats.php: " . $con->error);
}

// Lấy thống kê chi tiết theo tháng
$currentYear = date('Y');
$currentMonth = date('m');

$sqlMonthlyStats = "SELECT 
    MONTH(NgayBH) as month,
    COUNT(*) as order_count,
    SUM(Tongtien) as monthly_revenue
FROM hoadon 
WHERE YEAR(NgayBH) = $currentYear 
GROUP BY MONTH(NgayBH)
ORDER BY month";

$resultMonthlyStats = $con->query($sqlMonthlyStats);

// Lấy thống kê khách hàng mua nhiều nhất
$sqlTopCustomers = "SELECT 
    k.Tenkhach,
    COUNT(hd.SohieuHD) as order_count,
    SUM(hd.Tongtien) as total_spent
FROM hoadon hd
JOIN khach k ON hd.id = k.id
WHERE hd.Trangthai = 'Giao hàng thành công'
GROUP BY hd.id, k.Tenkhach
ORDER BY total_spent DESC
LIMIT 5";

$resultTopCustomers = $con->query($sqlTopCustomers);

// Lấy thống kê theo danh mục sản phẩm
$sqlCategoryStats = "SELECT 
    dm.Tendanhmuc,
    COUNT(DISTINCT hd.SohieuHD) as order_count,
    SUM(ct.Soluong) as total_quantity,
    SUM(ct.Thanhtien) as total_revenue
FROM chitiethd ct
JOIN hang h ON ct.Mahang = h.Mahang
JOIN danhmuc dm ON h.Maloaihang = dm.Maloaihang
JOIN hoadon hd ON ct.SohieuHD = hd.SohieuHD
WHERE hd.Trangthai = 'Giao hàng thành công'
GROUP BY dm.Maloaihang, dm.Tendanhmuc
ORDER BY total_revenue DESC";

$resultCategoryStats = $con->query($sqlCategoryStats);
?>



    <!-- Thống kê tổng quan -->
    <div class="stats-container">
        <div class="row">
            <div class="col-md-2">
                <div class="stats-card">
                    <h4><i class="fa fa-shopping-cart"></i> Tổng đơn hàng</h4>
                    <div class="number"><?php echo $stats['total_orders']; ?></div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <h4><i class="fa fa-clock"></i> Đang xử lý</h4>
                    <div class="number"><?php echo $stats['processing_orders']; ?></div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <h4><i class="fa fa-times-circle"></i> Đã hủy</h4>
                    <div class="number"><?php echo $stats['cancelled_orders']; ?></div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card">
                    <h4><i class="fa fa-check-circle"></i> Hoàn thành</h4>
                    <div class="number"><?php echo $stats['completed_orders']; ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <h4><i class="fa fa-money-bill-wave"></i> Tổng doanh thu</h4>
                    <div class="number revenue"><?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?> VND</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thống kê theo tháng -->
    <div class="stats-container">
        <h3><i class="fas fa-calendar-alt"></i> Thống Kê Theo Tháng (Năm <?php echo $currentYear; ?>)</h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Tháng</th>
                        <th>Số Đơn Hàng</th>
                        <th>Doanh Thu (VNĐ)</th>
                        <th>Trung Bình/Đơn (VNĐ)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $monthNames = [
                        1 => 'Tháng 1', 2 => 'Tháng 2', 3 => 'Tháng 3', 4 => 'Tháng 4',
                        5 => 'Tháng 5', 6 => 'Tháng 6', 7 => 'Tháng 7', 8 => 'Tháng 8',
                        9 => 'Tháng 9', 10 => 'Tháng 10', 11 => 'Tháng 11', 12 => 'Tháng 12'
                    ];
                    
                    if ($resultMonthlyStats && $resultMonthlyStats->num_rows > 0):
                        while ($row = $resultMonthlyStats->fetch_assoc()): 
                            $avgOrder = $row['order_count'] > 0 ? $row['monthly_revenue'] / $row['order_count'] : 0;
                    ?>
                        <tr>
                            <td><strong><?php echo $monthNames[$row['month']]; ?></strong></td>
                            <td><?php echo $row['order_count']; ?> đơn</td>
                            <td><?php echo number_format($row['monthly_revenue'], 0, ',', '.'); ?></td>
                            <td><?php echo number_format($avgOrder, 0, ',', '.'); ?></td>
                        </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                <i class="fas fa-info-circle"></i> Chưa có dữ liệu thống kê theo tháng
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top 5 khách hàng mua nhiều nhất -->
    <div class="stats-container">
        <h3><i class="fas fa-users"></i> Top 5 Khách Hàng Mua Nhiều Nhất</h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>STT</th>
                        <th>Tên Khách Hàng</th>
                        <th>Số Đơn Hàng</th>
                        <th>Tổng Chi Tiêu (VNĐ)</th>
                        <th>Trung Bình/Đơn (VNĐ)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    if ($resultTopCustomers && $resultTopCustomers->num_rows > 0):
                        while ($row = $resultTopCustomers->fetch_assoc()): 
                            $avgOrder = $row['order_count'] > 0 ? $row['total_spent'] / $row['order_count'] : 0;
                    ?>
                        <tr>
                            <td><?php echo $rank; ?></td>
                            <td><?php echo htmlspecialchars($row['Tenkhach']); ?></td>
                            <td><?php echo $row['order_count']; ?> đơn</td>
                            <td><?php echo number_format($row['total_spent'], 0, ',', '.'); ?></td>
                            <td><?php echo number_format($avgOrder, 0, ',', '.'); ?></td>
                        </tr>
                    <?php 
                    $rank++;
                    endwhile; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                <i class="fas fa-info-circle"></i> Chưa có dữ liệu khách hàng
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Thống kê theo danh mục sản phẩm -->
    <div class="stats-container">
        <h3><i class="fas fa-tags"></i> Thống Kê Theo Danh Mục Sản Phẩm</h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>STT</th>
                        <th>Danh Mục</th>
                        <th>Số Đơn Hàng</th>
                        <th>Tổng Số Lượng</th>
                        <th>Doanh Thu (VNĐ)</th>
                        <th>Trung Bình/Đơn (VNĐ)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    if ($resultCategoryStats && $resultCategoryStats->num_rows > 0):
                        while ($row = $resultCategoryStats->fetch_assoc()): 
                            $avgOrder = $row['order_count'] > 0 ? $row['total_revenue'] / $row['order_count'] : 0;
                    ?>
                        <tr>
                            <td><?php echo $rank; ?></td>
                            <td><?php echo htmlspecialchars($row['Tendanhmuc']); ?></td>
                            <td><?php echo $row['order_count']; ?> đơn</td>
                            <td><?php echo $row['total_quantity']; ?> sản phẩm</td>
                            <td><?php echo number_format($row['total_revenue'], 0, ',', '.'); ?></td>
                            <td><?php echo number_format($avgOrder, 0, ',', '.'); ?></td>
                        </tr>
                    <?php 
                    $rank++;
                    endwhile; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                <i class="fas fa-info-circle"></i> Chưa có dữ liệu thống kê theo danh mục
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top 5 sản phẩm bán chạy -->
    <div class="stats-container">
        <h3><i class="fas fa-chart-line"></i> Top 5 Sản Phẩm Bán Chạy</h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>STT</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Số Lượng Đã Bán</th>
                        <th>Tổng Doanh Thu (VNĐ)</th>
                        <th>Xếp Hạng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    if ($resultTopProducts && $resultTopProducts->num_rows > 0):
                        while ($row = $resultTopProducts->fetch_assoc()): 
                    ?>
                        <tr>
                            <td><?php echo $rank; ?></td>
                            <td><?php echo htmlspecialchars($row['Tenhang']); ?></td>
                            <td><?php echo $row['total_sold']; ?> sản phẩm</td>
                            <td><?php echo number_format($row['total_revenue'], 0, ',', '.'); ?> VND</td>
                            <td>
                                <?php if ($rank <= 3): ?>
                                    <span class="badge bg-warning">Top <?php echo $rank; ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?php echo $rank; ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php 
                    $rank++;
                    endwhile; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                <i class="fas fa-info-circle"></i> Chưa có dữ liệu sản phẩm bán chạy
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>