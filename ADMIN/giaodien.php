<!DOCTYPE html>
<html lang="vi">
    <?php include '../db_connect.php';
    ?>
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống Kê</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f9;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
            width: 300px;
        }
        select, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Thống Kê Doanh Thu</h2>
        <label for="month">Chọn Tháng:</label>
        <select id="month">
            <option value="01">Tháng 1</option>
            <option value="02">Tháng 2</option>
            <option value="03">Tháng 3</option>
            <option value="04">Tháng 4</option>
            <option value="05">Tháng 5</option>
            <option value="06">Tháng 6</option>
            <option value="07">Tháng 7</option>
            <option value="08">Tháng 8</option>
            <option value="09">Tháng 9</option>
            <option value="10">Tháng 10</option>
            <option value="11">Tháng 11</option>
            <option value="12">Tháng 12</option>
        </select>
        
        <label for="year">Chọn Năm:</label>
        <select id="year">
            <option value="2023">2023</option>
            <option value="2024">2024</option>
            <option value="2025">2025</option>
        </select>
        
        <button onclick="fetchStatistics()">Xem Thống Kê</button>
        
        <div id="statistics"></div>
    </div>

    <script>
        function fetchStatistics() {
            const month = document.getElementById('month').value;
            const year = document.getElementById('year').value;

            fetch(`thongke.php?month=${month}&year=${year}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('statistics').innerHTML = `
                        <p><strong>Tổng doanh thu:</strong> ${data.total_revenue} VNĐ</p>
                        <p><strong>Số đơn hàng:</strong> ${data.total_orders}</p>
                    `;
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                    document.getElementById('statistics').innerHTML = "<p>Có lỗi xảy ra khi tải dữ liệu.</p>";
                });
        }
    </script>
</body>
</html>
