<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$loggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
$quyen = isset($_SESSION['quyen']) ? $_SESSION['quyen'] : [];  // Lấy quyền từ session, mặc định là mảng trống nếu không có
if (!in_array('hoadon', $quyen)) {
    echo "Bạn không có quyền truy cập trang này.";
    header("Location: loginAdmin.php");
    exit;
}
include '../db_connect.php';

// Kiểm tra có tham số sohieu không
if (!isset($_GET['sohieu'])) {
    echo "Không tìm thấy hóa đơn.";
    exit;
}

$sohieu = $_GET['sohieu'];

// Lấy thông tin hóa đơn
$query_hoadon = "SELECT hd.*, k.Tenkhach, k.Diachi, k.Dienthoai
                 FROM hoadon hd
                 JOIN khach k ON hd.id = k.id
                 WHERE hd.SohieuHD = ?";
$stmt_hoadon = $con->prepare($query_hoadon);
$stmt_hoadon->bind_param("s", $sohieu);
$stmt_hoadon->execute();
$result_hoadon = $stmt_hoadon->get_result();

if ($result_hoadon->num_rows == 0) {
    echo "Không tìm thấy hóa đơn với số hiệu: " . $sohieu;
    exit;
}

$hoadon = $result_hoadon->fetch_assoc();

// Lấy chi tiết hóa đơn
$query_chitiet = "SELECT ct.*, h.Tenhang, h.Dongia
                  FROM chitiethd ct
                  JOIN hang h ON ct.Mahang = h.Mahang
                  WHERE ct.SohieuHD = ?
                  ORDER BY ct.Mahang";
$stmt_chitiet = $con->prepare($query_chitiet);
if ($stmt_chitiet === false) {
    die("Lỗi chuẩn bị query: " . $con->error);
}
$stmt_chitiet->bind_param("s", $sohieu);
$stmt_chitiet->execute();
$result_chitiet = $stmt_chitiet->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/xemhoadon.css">
    <title>Chi tiết hóa đơn - <?php echo $sohieu; ?></title>
    <style>
        .invoice-header {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .info-group {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
        }
        .info-value {
            color: #212529;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        .status-success {
            background-color: #d4edda;
            color: #155724;
        }
        .status-processing {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .total-section {
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: right;
        }
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<?php include 'navbar.php'; ?>
<body style="font-family: Arial, sans-serif;">
<main>
    <a href="xemhoadon.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Quay lại danh sách hóa đơn
    </a>

    <div class="invoice-header">
        <h1><i class="fas fa-receipt"></i> Chi Tiết Hóa Đơn: <?php echo $sohieu; ?></h1>
        
        <div class="invoice-info">
            <div>
                <div class="info-group">
                    <span class="info-label">Ngày bán hàng:</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($hoadon['NgayBH'])); ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Tên khách hàng:</span>
                    <span class="info-value"><?php echo $hoadon['Tenkhach']; ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Địa chỉ:</span>
                    <span class="info-value"><?php echo $hoadon['Diachi']; ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Số điện thoại:</span>
                    <span class="info-value">(+84) <?php echo $hoadon['Dienthoai']; ?></span>
                </div>
            </div>
            <div>
                <div class="info-group">
                    <span class="info-label">Trạng thái:</span>
                    <span class="info-value">
                        <?php 
                        $statusClass = '';
                        switch($hoadon['Trangthai']) {
                            case 'Giao hàng thành công':
                                $statusClass = 'status-success';
                                break;
                            case 'Đang xử lý':
                                $statusClass = 'status-processing';
                                break;
                            case 'Đã hủy':
                                $statusClass = 'status-cancelled';
                                break;
                        }
                        ?>
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <?php echo $hoadon['Trangthai']; ?>
                        </span>
                    </span>
                </div>
                <div class="info-group">
                    <span class="info-label">Phương thức thanh toán:</span>
                    <span class="info-value">
                        <?php 
                        // Lấy phương thức thanh toán từ bảng chitiethd
                        $pt_query = "SELECT DISTINCT PTthanhtoan FROM chitiethd WHERE SohieuHD = ? LIMIT 1";
                        $pt_stmt = $con->prepare($pt_query);
                        if ($pt_stmt) {
                            $pt_stmt->bind_param("s", $sohieu);
                            $pt_stmt->execute();
                            $pt_result = $pt_stmt->get_result();
                            if ($pt_result->num_rows > 0) {
                                $pt_row = $pt_result->fetch_assoc();
                                echo $pt_row['PTthanhtoan'];
                            } else {
                                echo "Không có thông tin";
                            }
                        } else {
                            echo "Không có thông tin";
                        }
                        ?>
                    </span>
                </div>
                <div class="info-group">
                    <span class="info-label">Tổng tiền:</span>
                    <span class="info-value total-amount"><?php echo number_format($hoadon['Tongtien'], 0, ',', '.'); ?> VND</span>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <h3><i class="fas fa-list"></i> Danh sách sản phẩm</h3>
        <?php if ($result_chitiet->num_rows > 0): ?>
        <table border="1" cellpadding="10" style="width: 100%;">
            <thead>
                <tr style="background-color: #f8f9fa;">
                    <th>STT</th>
                    <th>Mã Hàng</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Đơn Giá</th>
                    <th>Số Lượng</th>
                    <th>Thành Tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stt = 1;
                while ($row = $result_chitiet->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo $stt++; ?></td>
                    <td><?php echo $row['Mahang']; ?></td>
                    <td><?php echo $row['Tenhang']; ?></td>
                    <td><?php echo number_format($row['Dongia'], 0, ',', '.'); ?> VND</td>
                    <td><?php echo $row['Soluong']; ?></td>
                    <td><?php echo number_format($row['Thanhtien'], 0, ',', '.'); ?> VND</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>Không có sản phẩm nào trong hóa đơn này.</p>
        <?php endif; ?>
    </div>

    <div class="total-section">
        <div class="info-group">
            <span class="info-label">Tổng cộng:</span>
            <span class="total-amount"><?php echo number_format($hoadon['Tongtien'], 0, ',', '.'); ?> VND</span>
        </div>
    </div>

</main>
</body>
</html>

<?php
// Đóng kết nối
$con->close();
?> 