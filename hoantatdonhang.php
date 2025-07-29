<?php
session_start();
include 'db_connect.php'; // Kết nối cơ sở dữ liệu

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Lấy dữ liệu từ form
    $selectedProducts = isset($_POST['selectedProducts']) ? json_decode($_POST['selectedProducts'], true) : [];
    $totalPrice = isset($_POST['totalPrice']) ? floatval($_POST['totalPrice']) : 0;
    $customerId = isset($_POST['customerId']) ? $con->real_escape_string($_POST['customerId']) : '';
    $paymentMethod = isset($_POST['paymentMethod']) ? $con->real_escape_string($_POST['paymentMethod']) : '';

    // Kiểm tra dữ liệu hợp lệ
    if (empty($selectedProducts) || empty($totalPrice) || empty($customerId) || empty($paymentMethod)) {
        echo "Dữ liệu không hợp lệ. Vui lòng kiểm tra thông tin đơn hàng.";
        exit;
    }

    // Tạo số hóa đơn duy nhất và ngày bán hàng
    $sohieuHD = 'HD' . uniqid();
    $ngayBH = date('Y-m-d');

    // Lưu dữ liệu cần thiết vào session
    $_SESSION['sohieuHD'] = $sohieuHD;
    
    $_SESSION['customerId'] = $customerId;
    $_SESSION['selectedProducts'] = $selectedProducts;
    $_SESSION['totalPrice'] = $totalPrice;
    $_SESSION['paymentMethod'] = $paymentMethod;

    // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
    $con->begin_transaction();

    try {
        // Nếu phương thức thanh toán là MoMo, thực hiện thanh toán trước
        if ($paymentMethod == 'momo') {
            $orderId = time() . "" . $sohieuHD;
            $result = execPostRequest($orderId, $totalPrice);
            if ($result && isset($result['payUrl'])) {
                // Lưu orderId vào session để xác thực sau này nếu cần
                $_SESSION['momo_orderId'] = $orderId;

                // Commit transaction tạm thời trước khi chuyển hướng đến trang thanh toán MoMo
                $con->commit();

                // Chuyển hướng người dùng đến trang thanh toán MoMo
                header('Location: ' . $result['payUrl']);
                exit;
            } else {
                throw new Exception("Lỗi khi thực hiện thanh toán qua MoMo.");
            }
        }

        // Nếu không phải MoMo, tiếp tục lưu hóa đơn và chi tiết đơn hàng ngay lập tức

        // Chèn hóa đơn vào bảng 'hoadon'
        $stmt_hoadon = $con->prepare("INSERT INTO hoadon (SohieuHD, id, NgayBH, Tongtien, Trangthai) VALUES (?, ?, ?, ?, ?)");
        $trangthai = 'Đang xử lý';
        $stmt_hoadon->bind_param('sssds', $sohieuHD, $customerId, $ngayBH, $totalPrice, $trangthai);
        if (!$stmt_hoadon->execute()) {
            throw new Exception("Không thể tạo hóa đơn.");
        }

        // Chuẩn bị truy vấn cho bảng 'chitiethd' và 'chitietdh'
        $stmt_chitiethd = $con->prepare("INSERT INTO chitiethd (SohieuHD, Mahang, Soluong, Thanhtien, PTthanhtoan,id) VALUES (?,?, ?, ?, ?, ?)");
        $stmt_chitietdh = $con->prepare("INSERT INTO chitietdh (Madonhang, Mahang, Soluong, Thanhtien) VALUES (?, ?, ?, ?)");

        // Lặp qua các sản phẩm đã chọn và chèn vào cơ sở dữ liệu
        foreach ($selectedProducts as $product) {
            if (!isset($product['mahang'])) {
                throw new Exception("Sản phẩm không có mã hàng. Vui lòng kiểm tra lại.");
            }

            $Mahang = $product['mahang'];
            $quantity = isset($product['quantity']) ? $product['quantity'] : 0;
            $price = isset($product['price']) ? $product['price'] : 0;
            $Thanhtien = $price * $quantity;

            // Chèn dữ liệu vào bảng 'chitiethd'
            $stmt_chitiethd->bind_param('ssidsi', $sohieuHD, $Mahang, $quantity, $Thanhtien, $paymentMethod,$customerId);
            if (!$stmt_chitiethd->execute()) {
                throw new Exception("Lỗi khi thêm chi tiết hóa đơn.");
            }

            // Chèn dữ liệu vào bảng 'chitietdh'
            $stmt_chitietdh->bind_param('ssis', $sohieuHD, $Mahang, $quantity, $Thanhtien);
            if (!$stmt_chitietdh->execute()) {
                throw new Exception("Lỗi khi thêm chi tiết đơn hàng.");
            }
        }

        // Commit transaction sau khi tất cả các thao tác thành công
        $con->commit();

        // Chuyển hướng đến trang thành công
        header('Location: thankes.php');
        exit;

    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $con->rollback();
        echo "Đã xảy ra lỗi: " . $e->getMessage();
        exit;
    }

} else {
    echo "Yêu cầu không hợp lệ.";
}

// Function to initiate the MoMo payment
function execPostRequest($orderId, $totalPrice)
{
    $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
    $partnerCode = 'MOMOBKUN20180529';
    $accessKey = 'klm05TvNBzhg7h7j';
    $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
    $orderInfo = "Thanh toán qua MoMo";
    $amount = strval($totalPrice);
    $redirectUrl = "http://localhost:3000/thankes.php";
    $ipnUrl = "http://localhost:3000/ipn.php"; // Sử dụng IPN để xử lý thanh toán
    $extraData = "";

    $requestId = time() . "";
    $requestType = "payWithATM";

    // Tạo chữ ký
    $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
    $signature = hash_hmac("sha256", $rawHash, $secretKey);

    // Dữ liệu gửi trong yêu cầu
    $data = array(
        'partnerCode' => $partnerCode,
        'partnerName' => "Test",
        'storeId' => "MomoTestStore",
        'requestId' => $requestId,
        'amount' => $amount,
        'orderId' => $orderId,
        'orderInfo' => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'lang' => 'vi',
        'extraData' => $extraData,
        'requestType' => $requestType,
        'signature' => $signature
    );

    // Gửi yêu cầu qua cURL
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    // Thực hiện POST request và nhận kết quả
    $result = curl_exec($ch);
    curl_close($ch);

    // Trả về kết quả dưới dạng mảng JSON
    return json_decode($result, true);
}
?>
