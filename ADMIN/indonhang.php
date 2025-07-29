<?php
include '../db_connect.php'; // Kết nối database

if (isset($_GET['SohieuHD'])) {
    $SohieuHD = $_GET['SohieuHD'];

    // Lấy chi tiết hóa đơn
    $sql = "SELECT hd.SohieuHD, k.Tenkhach, k.Diachi, k.Dienthoai, hd.NgayBH, hd.Tongtien 
            FROM hoadon hd
            JOIN khach k ON hd.id = k.id
            WHERE hd.SohieuHD = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $SohieuHD);
    $stmt->execute();
    $result = $stmt->get_result();
    $hoadon = $result->fetch_assoc();
    
    // Lấy chi tiết các mặt hàng trong hóa đơn
    $sql_items = "SELECT cthd.Mahang, h.tenhang, cthd.Soluong, cthd.Thanhtien , cthd.PTthanhtoan 
                  FROM chitiethd cthd
                  JOIN hang h ON cthd.Mahang = h.Mahang
                  WHERE cthd.SohieuHD = ?";
    $stmt_items = $con->prepare($sql_items);
    $stmt_items->bind_param("s", $SohieuHD);
    $stmt_items->execute();
    $items = $stmt_items->get_result();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>In hóa đơn</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .invoice-container {
            width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #000;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .invoice-header, .invoice-footer {
            text-align: center;
            margin-bottom: 20px;
        }

        .invoice-details {
            margin-bottom: 20px;
        }

        

        h2 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 8px;
            text-align: center;
            font-size: 16px;
        }

        th {
            background-color: #f2f2f2;
        }

        .total-container {
            text-align: right;
            margin-top: 20px;
        }

        .total-container p {
            font-size: 18px;
            font-weight: bold;
        }

        button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Ẩn nút in khi in */
        @media print {
            button {
                display: none;
            }

            .invoice-container {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>

<div class="invoice-container">
    <div class="invoice-header">
        <h1>Vũ Tuấn Shop</h1>
    </div>

    <div class="invoice-details">
        <p><strong>Tên khách hàng:</strong> <?php echo $hoadon['Tenkhach']; ?></p>
        <p></p>
        <p><strong>Địa chỉ:</strong> <?php echo $hoadon['Diachi']; ?></p>
        <p></p>
        <p><strong>Số điện thoại: (+84)</strong> <?php echo $hoadon['Dienthoai']; ?></p>
        <p></p>
        
        <p><strong>Tổng tiền:</strong> <?php echo number_format($hoadon['Tongtien'], 0, ',', '.') . ' VND'; ?></p>
    </div>

    <h2>Chi tiết hóa đơn</h2>
    <table>
        <thead>
            <tr>
                
                <th>Tên hàng</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
                <th>PTTT</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items->fetch_assoc()) { ?>
            <tr>
               
                <td><?php echo $item['tenhang']; ?></td>
                <td><?php echo $item['Soluong']; ?></td>
                <td><?php echo number_format($item['Thanhtien'], 0, ',', '.') . ' VND'; ?></td>
                <td><?php echo $item['PTthanhtoan']; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="total-container">
        <p>Tổng tiền: <?php echo number_format($hoadon['Tongtien'], 0, ',', '.') . ' VND'; ?></p>
    </div>

    <!-- Nút in hóa đơn, sẽ bị ẩn khi in -->
    <button onclick="window.print()">In hóa đơn</button>
</div>

</body>
</html>
