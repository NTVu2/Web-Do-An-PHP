<?php
include 'db_connect.php';

echo "<h2>Kiểm tra cấu trúc Database</h2>";

// Kiểm tra bảng hang
echo "<h3>1. Cấu trúc bảng 'hang':</h3>";
$result = $con->query("DESCRIBE hang");
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Kiểm tra dữ liệu mẫu
echo "<h3>2. Dữ liệu mẫu trong bảng 'hang':</h3>";
$result = $con->query("SELECT Mahang, Tenhang, Soluongton FROM hang LIMIT 5");
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Mahang</th><th>Tenhang</th><th>Soluongton</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Mahang'] . "</td>";
    echo "<td>" . $row['Tenhang'] . "</td>";
    echo "<td>" . $row['Soluongton'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Kiểm tra bảng chitiethd
echo "<h3>3. Cấu trúc bảng 'chitiethd':</h3>";
$result = $con->query("DESCRIBE chitiethd");
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Kiểm tra foreign key constraints
echo "<h3>4. Kiểm tra ràng buộc khóa ngoại:</h3>";
$result = $con->query("SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_SCHEMA = DATABASE() 
AND (TABLE_NAME = 'chitiethd' OR REFERENCED_TABLE_NAME = 'hang')");

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Table</th><th>Column</th><th>Constraint</th><th>Referenced Table</th><th>Referenced Column</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['TABLE_NAME'] . "</td>";
    echo "<td>" . $row['COLUMN_NAME'] . "</td>";
    echo "<td>" . $row['CONSTRAINT_NAME'] . "</td>";
    echo "<td>" . $row['REFERENCED_TABLE_NAME'] . "</td>";
    echo "<td>" . $row['REFERENCED_COLUMN_NAME'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test một câu update đơn giản
echo "<h3>5. Test UPDATE command:</h3>";
$test_result = $con->query("SELECT Mahang, Soluongton FROM hang LIMIT 1");
if ($test_row = $test_result->fetch_assoc()) {
    $test_mahang = $test_row['Mahang'];
    $current_stock = $test_row['Soluongton'];
    
    echo "Testing với sản phẩm: $test_mahang (Tồn kho hiện tại: $current_stock)<br>";
    
    // Test update +1
    $update_sql = "UPDATE hang SET Soluongton = Soluongton + 1 WHERE Mahang = ?";
    $stmt = $con->prepare($update_sql);
    $stmt->bind_param("s", $test_mahang);
    
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        echo "✅ Update +1 thành công. Affected rows: $affected<br>";
        
        // Kiểm tra kết quả
        $check_result = $con->query("SELECT Soluongton FROM hang WHERE Mahang = '$test_mahang'");
        $new_stock = $check_result->fetch_assoc()['Soluongton'];
        echo "Tồn kho sau update: $new_stock<br>";
        
        // Rollback
        $rollback_sql = "UPDATE hang SET Soluongton = Soluongton - 1 WHERE Mahang = ?";
        $stmt_rollback = $con->prepare($rollback_sql);
        $stmt_rollback->bind_param("s", $test_mahang);
        $stmt_rollback->execute();
        echo "✅ Rollback thành công<br>";
        
    } else {
        echo "❌ Update thất bại: " . $con->error . "<br>";
    }
}

$con->close();
?>