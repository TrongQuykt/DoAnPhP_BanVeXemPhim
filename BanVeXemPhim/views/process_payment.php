<?php
session_start();
require_once("../config/function.php");
require_once '../vendor/autoload.php'; // Đảm bảo autoload PayPal SDK, Stripe SDK

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

use Stripe\Stripe;
use Stripe\Checkout\Session;

date_default_timezone_set('Asia/Ho_Chi_Minh');
// Đảm bảo không có đầu ra trước header()
ob_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('views/payment.php', 'error', 'Yêu cầu không hợp lệ');
    exit();
}

$totalPrice = $_SESSION['finalPrice'] ?? 0; // Sử dụng tổng tiền sau giảm từ session
$orderId = $_POST['orderId'] ?? '';
$paymentMethod = $_POST['payment_method'] ?? '';

// Ghi log dữ liệu nhận được
file_put_contents('process_payment.log', date('Y-m-d H:i:s') . " - Received POST Data: " . print_r($_POST, true) . "\n", FILE_APPEND);
file_put_contents('process_payment.log', date('Y-m-d H:i:s') . " - Total Price: $totalPrice\n", FILE_APPEND);

// Kiểm tra dữ liệu
if ($totalPrice <= 0 || empty($orderId) || empty($paymentMethod)) {
    $errorMessage = 'Dữ liệu không hợp lệ: ' . ($totalPrice <= 0 ? 'Tổng tiền không hợp lệ' : '') . (empty($orderId) ? ' Thiếu orderId' : '') . (empty($paymentMethod) ? ' Thiếu paymentMethod' : '');
    file_put_contents('process_payment.log', date('Y-m-d H:i:s') . " - Error: $errorMessage\n", FILE_APPEND);
    redirect('views/payment.php', 'error', $errorMessage);
    exit();
}

// Cấu hình VNPAY
$vnp_TmnCode = "2VK9KT2C"; // Mã website của bạn tại VNPAY
$vnp_HashSecret = "BFRBXJCBLCYXBAYZDJDUSXWABIYXWONM"; // Chuỗi bí mật
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // URL thanh toán của VNPAY (sandbox)
$vnp_Returnurl = "http://localhost:3000/BanVeXemPhim/views/vnpay_return.php"; // URL callback sau khi thanh toán

if ($paymentMethod === 'vnpay') {
    $vnp_TxnRef = $orderId; // Mã giao dịch
    $vnp_OrderInfo = "Thanh toan ve xem phim - Ma giao dich: " . $orderId;
    $vnp_OrderType = 'billpayment';
    $vnp_Amount = $totalPrice * 100; // Số tiền (VND, nhân 100 theo yêu cầu của VNPAY)
    $vnp_Locale = 'vn';
    $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
    $vnp_CreateDate = date('YmdHis'); // Thời gian tạo giao dịch
    $vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes', strtotime($vnp_CreateDate))); // Thời gian hết hạn (15 phút)

    $inputData = array(
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => $vnp_CreateDate,
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => $vnp_IpAddr,
        "vnp_Locale" => $vnp_Locale,
        "vnp_OrderInfo" => $vnp_OrderInfo,
        "vnp_OrderType" => $vnp_OrderType,
        "vnp_ReturnUrl" => $vnp_Returnurl,
        "vnp_TxnRef" => $vnp_TxnRef,
        "vnp_ExpireDate" => date('YmdHis', strtotime('+30 minutes'))
    );

    ksort($inputData);
    $query = "";
    $i = 0;
    $hashdata = "";
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
        $query .= urlencode($key) . "=" . urlencode($value) . '&';
    }

    $vnp_SecureHash = hash_hmac("sha512", $hashdata, $vnp_HashSecret);
    $vnp_Url .= "?" . $query . "vnp_SecureHash=" . $vnp_SecureHash;

    // Ghi log URL thanh toán
    file_put_contents('process_payment.log', date('Y-m-d H:i:s') . " - VNPAY Redirect URL: " . $vnp_Url . "\n", FILE_APPEND);

    // Đảm bảo không có đầu ra trước header
    ob_end_clean();

    // Chuyển hướng đến VNPAY
    header('Location: ' . $vnp_Url);
    exit();
}

// Cấu hình MOMO
$momo_endpoint = "https://test-payment.momo.vn/v2/gateway/api/create"; // URL sandbox của MOMO
$momo_partnerCode = "MOMOBKUN20180529";
$momo_accessKey = "klm05TvNBzhg7h7j";
$momo_secretKey = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa";   
$momo_returnUrl = "http://localhost:3000/BanVeXemPhim/views/momo_return.php";
$momo_notifyUrl = "http://localhost:3000/BanVeXemPhim/views/momo_notify.php";

    if (strpos($paymentMethod, 'momo') === 0) { // Kiểm tra nếu phương thức là MOMO
        $orderInfo = "Thanh toan ve xem phim - Ma giao dich: " . $orderId;
        $amount = $totalPrice;
        $requestId = time() . "-" . uniqid(); // Đảm bảo requestId duy nhất
        $extraData = "";
    
        // Xác định requestType dựa trên phương thức thanh toán
        if ($paymentMethod === 'momo_qr') {
            $requestType = "captureWallet"; // Quét mã QR
        } elseif ($paymentMethod === 'momo_app') {
            $requestType = "payWithApp"; // Thanh toán qua ứng dụng MoMo
        } elseif ($paymentMethod === 'momo_card') {
            $requestType = "payWithMethod"; // Thanh toán qua thẻ ngân hàng
        } else {
            $requestType = "payWithMethod"; // Mặc định hiển thị tất cả phương thức
        }
    
        // Tạo mảng dữ liệu để sắp xếp theo thứ tự bảng chữ cái
        $dataForSignature = [
            'accessKey' => $momo_accessKey,
            'amount' => $amount,
            'extraData' => $extraData,
            'ipnUrl' => $momo_notifyUrl,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'partnerCode' => $momo_partnerCode,
            'redirectUrl' => $momo_returnUrl,
            'requestId' => $requestId,
            'requestType' => $requestType
        ];
    
        // Sắp xếp mảng theo key (theo thứ tự bảng chữ cái)
        ksort($dataForSignature);
    
        // Tạo chuỗi dữ liệu gốc từ mảng đã sắp xếp
        $rawHash = "";
        $first = true;
        foreach ($dataForSignature as $key => $value) {
            if ($first) {
                $rawHash .= "$key=$value";
                $first = false;
            } else {
                $rawHash .= "&$key=$value";
            }
        }
    
        // Tạo chữ ký
        $signature = hash_hmac("sha256", $rawHash, $momo_secretKey);
    
        // Dữ liệu gửi đi
        $data = array(
            'partnerCode' => $momo_partnerCode,
            'partnerName' => "Test",
            'storeId' => "MomoTestStore",
            'requestId' => $requestId,
            'amount' => (int)$amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $momo_returnUrl,
            'ipnUrl' => $momo_notifyUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        );
    
        // Ghi log dữ liệu gửi đi
        file_put_contents('momo_request.log', date('Y-m-d H:i:s') . " - Request Data: " . print_r($data, true) . "\n", FILE_APPEND);
        file_put_contents('momo_request.log', date('Y-m-d H:i:s') . " - Raw Hash for Signature: " . $rawHash . "\n", FILE_APPEND);
    
        // Sử dụng cURL để gửi yêu cầu
        $ch = curl_init($momo_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
    
        // Ghi log phản hồi từ MOMO
        file_put_contents('momo_response.log', date('Y-m-d H:i:s') . " - HTTP Code: $httpCode - Response Data: " . $response . "\n", FILE_APPEND);
    
        if ($response === false) {
            redirect('views/payment.php', 'error', "Không thể kết nối đến MOMO: " . $curlError);
        } else {
            $result = json_decode($response, true);
            if (isset($result['payUrl'])) {
                header('Location: ' . $result['payUrl']);
                exit();
            } else {
                $errorMessage = isset($result['message']) ? $result['message'] : 'Lỗi không xác định';
                $errorCode = isset($result['resultCode']) ? $result['resultCode'] : 'Không có mã lỗi';
                redirect('views/payment.php', 'error', "Không thể tạo giao dịch MOMO: $errorMessage (Mã lỗi: $errorCode)");
            }
        }
    }

// Cấu hình PayPal
$paypal_client_id = "AdCwnycsGVNOqwLq9ZKlUjPveVicpkVDwlZ2kzRJlqS5cqG0BXlFef3XoWaK6Rn0PjrEtKM65FUIwjzP"; // Thay bằng Client ID Sandbox của bạn
$paypal_secret = "EIXPli_JdNPg3lcyDttjuusvg7V5zMo_BGnskQc96Poa395WXp9e6vKh6-uJ9gW4YvlMM0iYyuVSQrRF"; // Thay bằng Secret Sandbox của bạn
$paypal_return_url = "http://localhost:3000/BanVeXemPhim/views/paypal_return.php";
$paypal_cancel_url = "http://localhost:3000/BanVeXemPhim/views/payment.php";

if ($paymentMethod === 'paypal') {
    // Thiết lập API Context
    $apiContext = new ApiContext(
        new OAuthTokenCredential($paypal_client_id, $paypal_secret)
    );
    $apiContext->setConfig([
        'mode' => 'sandbox', // Chế độ Sandbox
        'log.LogEnabled' => true,
        'log.FileName' => '../PayPal.log',
        'log.LogLevel' => 'DEBUG'
    ]);

    // Thiết lập thông tin thanh toán
    $payer = new Payer();
    $payer->setPaymentMethod("paypal");

    $amount = new Amount();
    $amount->setCurrency("USD") // PayPal yêu cầu USD, bạn có thể quy đổi từ VND
           ->setTotal(number_format($totalPrice / 23000, 2, '.', '')); // Quy đổi VND sang USD (tỷ giá tạm thời 1 USD = 23,000 VND)

    $transaction = new Transaction();
    $transaction->setAmount($amount)
                ->setDescription("Thanh toán vé xem phim - Mã giao dịch: " . $orderId)
                ->setInvoiceNumber($orderId);

    $redirectUrls = new RedirectUrls();
    $redirectUrls->setReturnUrl($paypal_return_url)
                 ->setCancelUrl($paypal_cancel_url);

    $payment = new Payment();
    $payment->setIntent("sale")
            ->setPayer($payer)
            ->setTransactions([$transaction])
            ->setRedirectUrls($redirectUrls);

    try {
        $payment->create($apiContext);
        $approvalUrl = $payment->getApprovalLink();

        file_put_contents('process_payment.log', date('Y-m-d H:i:s') . " - PayPal Redirect URL: " . $approvalUrl . "\n", FILE_APPEND);
        ob_end_clean();
        header("Location: " . $approvalUrl);
        exit();
    } catch (Exception $e) {
        file_put_contents('process_payment.log', date('Y-m-d H:i:s') . " - PayPal Error: " . $e->getMessage() . "\n", FILE_APPEND);
        redirect('views/payment.php', 'error', 'Không thể tạo giao dịch PayPal: ' . $e->getMessage());
        exit();
    }
}

// Cấu hình Stripe
$stripe_secret_key = "sk_test_51RB7vJFaa2zcokyWNaVblbusuXmg88GDzqh9Z7YporkVhwWSMaieWxjI2Ofd07Gz6BKwjkSPI5SOnwhSHiNArG7d00AxSLNrsJ"; // Thay bằng Secret Key Sandbox của bạn
$stripe_return_url = "http://localhost:3000/BanVeXemPhim/views/stripe_return.php";
$stripe_cancel_url = "http://localhost:3000/BanVeXemPhim/views/payment.php";

if ($paymentMethod === 'stripe') {
    // Thiết lập Stripe API Key
    Stripe::setApiKey($stripe_secret_key);

    try {
        // Tạo Checkout Session
        $session = Session::create([
            'payment_method_types' => ['card'], // Chỉ chấp nhận thanh toán bằng thẻ
            'line_items' => [[
                'price_data' => [
                    'currency' => 'vnd', // Sử dụng VND trực tiếp
                    'product_data' => [
                        'name' => 'Thanh toán vé xem phim - Mã giao dịch: ' . $orderId,
                    ],
                    'unit_amount' => $totalPrice, // Sử dụng trực tiếp $totalPrice (VND)
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $stripe_return_url . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $stripe_cancel_url,
            'metadata' => [
                'order_id' => $orderId,
            ],
        ]);

        file_put_contents('process_payment.log', date('Y-m-d H:i:s') . " - Stripe Redirect URL: " . $session->url . "\n", FILE_APPEND);
        ob_end_clean();
        header("Location: " . $session->url);
        exit();
    } catch (Exception $e) {
        file_put_contents('process_payment.log', date('Y-m-d H:i:s') . " - Stripe Error: " . $e->getMessage() . "\n", FILE_APPEND);
        redirect('views/payment.php', 'error', 'Không thể tạo giao dịch Stripe: ' . $e->getMessage());
        exit();
    }
}

// Nếu không vào nhánh nào, ghi log lỗi
file_put_contents('process_payment.log', date('Y-m-d H:i:s') . " - Error: Invalid payment method: $paymentMethod\n", FILE_APPEND);
redirect('views/payment.php', 'error', 'Phương thức thanh toán không hợp lệ');
exit();
?>