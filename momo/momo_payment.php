<?php
function momoPayment($orderId, $requestId, $amount, $orderInfo, $redirectUrl, $ipnUrl) {
    // MoMo API credentials
    $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
    $partnerCode = 'your_partner_code';
    $accessKey = 'your_access_key';
    $secretKey = 'your_secret_key';

    // Prepare the data for MoMo payment
    $rawData = "accessKey=$accessKey&amount=$amount&extraData=&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=captureMoMoWallet";

    $signature = hash_hmac("sha256", $rawData, $secretKey);

    $data = array(
        'partnerCode' => $partnerCode,
        'accessKey' => $accessKey,
        'requestId' => $requestId,
        'amount' => $amount,
        'orderId' => $orderId,
        'orderInfo' => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'extraData' => "",
        'requestType' => 'captureMoMoWallet',
        'signature' => $signature
    );

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $result = curl_exec($ch);
    curl_close($ch);

    $jsonResult = json_decode($result, true);

    // Return payment URL from MoMo response
    return $jsonResult['payUrl'];
}
?>
