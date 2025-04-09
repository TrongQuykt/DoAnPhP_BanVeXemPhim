<?php
session_start();
require_once("../config/function.php");
require_once("../config/sendmail.php");
// Thông tin cấu hình MOMO
$momo_partnerCode = "MOMOBKUN20180529"; // Cập nhật đúng partnerCode
$momo_accessKey = "klm05TvNBzhg7h7j";
$momo_secretKey = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa"; // Thay bằng secretKey của MOMOBKUN20180529

// Lấy dữ liệu từ query string
$partnerCode = isset($_GET['partnerCode']) ? $_GET['partnerCode'] : '';
$orderId = isset($_GET['orderId']) ? $_GET['orderId'] : '';
$requestId = isset($_GET['requestId']) ? $_GET['requestId'] : '';
$amount = isset($_GET['amount']) ? $_GET['amount'] : '';
$orderInfo = isset($_GET['orderInfo']) ? $_GET['orderInfo'] : '';
$orderType = isset($_GET['orderType']) ? $_GET['orderType'] : '';
$transId = isset($_GET['transId']) ? $_GET['transId'] : '';
$resultCode = isset($_GET['resultCode']) ? $_GET['resultCode'] : '';
$message = isset($_GET['message']) ? $_GET['message'] : '';
$payType = isset($_GET['payType']) ? $_GET['payType'] : '';
$responseTime = isset($_GET['responseTime']) ? $_GET['responseTime'] : '';
$extraData = isset($_GET['extraData']) ? $_GET['extraData'] : '';
$signature = isset($_GET['signature']) ? $_GET['signature'] : '';

// Ghi log dữ liệu nhận được
file_put_contents('momo_return.log', date('Y-m-d H:i:s') . " - Return Data: " . print_r($_GET, true) . "\n", FILE_APPEND);

// Tạo mảng dữ liệu để sắp xếp theo thứ tự bảng chữ cái
$dataForSignature = [
    'accessKey' => $momo_accessKey,
    'amount' => $amount,
    'extraData' => $extraData,
    'message' => $message,
    'orderId' => $orderId,
    'orderInfo' => $orderInfo,
    'orderType' => $orderType,
    'partnerCode' => $partnerCode,
    'payType' => $payType,
    'requestId' => $requestId,
    'responseTime' => $responseTime,
    'resultCode' => $resultCode,
    'transId' => $transId
];

// Sắp xếp mảng theo key
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

// Ghi log chuỗi dữ liệu gốc
file_put_contents('momo_return.log', date('Y-m-d H:i:s') . " - Raw Hash for Signature: " . $rawHash . "\n", FILE_APPEND);

// Tạo chữ ký
$computedSignature = hash_hmac("sha256", $rawHash, $momo_secretKey);

// Ghi log chữ ký
file_put_contents('momo_return.log', date('Y-m-d H:i:s') . " - Computed Signature: " . $computedSignature . "\n", FILE_APPEND);
file_put_contents('momo_return.log', date('Y-m-d H:i:s') . " - Received Signature: " . $signature . "\n", FILE_APPEND);

// Kiểm tra chữ ký
if ($computedSignature !== $signature) {
    redirect('views/payment.php', 'error', "Chữ ký không hợp lệ.");
    exit();
}

// Kiểm tra resultCode
if ($resultCode == '0') {
    // Thanh toán thành công
    $data = $_SESSION['bookingData'];
    $maGhe = explode(',', $data['MaGhe'] ?? '');
    $seatNames = [];
    $seatDetails = [];
    $totalPrice = 0;

    // Tính tổng tiền ghế
    foreach ($maGhe as $seatId) {
        $querySeat = "SELECT TenGhe, GiaGhe, LoaiGhe FROM GHE WHERE MaGhe = '$seatId'";
        $resultSeat = mysqli_query($conn, $querySeat);
        $seatData = mysqli_fetch_assoc($resultSeat);
        if ($seatData) {
            $seatNames[] = $seatData['TenGhe'];
            $seatDetails[] = [
                'TenGhe' => $seatData['TenGhe'],
                'GiaGhe' => $seatData['GiaGhe'],
                'LoaiGhe' => $seatData['LoaiGhe']
            ];
            $totalPrice += $seatData['GiaGhe'];
        }
    }

    // Tính tiền combo
    $comboData = $_SESSION['comboData'] ?? [];
    $totalComboPrice = 0;
    $selectedCombos = [];

    foreach ($comboData as $maCombo => $quantity) {
        if ($quantity > 0) {
            $queryCombo = "SELECT GiaCombo, TenCombo, Anh FROM combo WHERE MaCombo = '$maCombo'";
            $resultCombo = mysqli_query($conn, $queryCombo);
            $combo = mysqli_fetch_assoc($resultCombo);
            if ($combo) {
                $totalComboPrice += $combo['GiaCombo'] * $quantity;
                $selectedCombos[] = [
                    'TenCombo' => $combo['TenCombo'],
                    'SoLuong' => $quantity,
                    'GiaCombo' => $combo['GiaCombo'],
                    'Anh' => $combo['Anh']
                ];
            }
        }
    }

    $totalPrice += $totalComboPrice;
    $tongTien = $totalPrice;

    // Thêm hóa đơn
    getUser();
    $query = "INSERT INTO HoaDon(MaND, NgayLapHD, TongTien, NguoiTao, NgayTao, NguoiCapNhat, NgayCapNhat, TrangThai, PhuongThucThanhToan)
              VALUES ('$NDId', CURRENT_TIMESTAMP, '$tongTien', '$NDId', CURRENT_TIMESTAMP, '$NDId', CURRENT_TIMESTAMP, '1', 'MOMO')";
    mysqli_query($conn, $query);

    $maHD = mysqli_insert_id($conn);

    // Thêm chi tiết hóa đơn (vé)
    $seatIds = explode(',', $data['MaGhe']);
    foreach ($seatIds as $ghe) {
        $queryDetail = "INSERT INTO ChiTietHoaDon(MaHD, MaSuatChieu, MaGhe, NguoiTao, NgayTao, NguoiCapNhat, NgayCapNhat, TrangThai)
                        VALUES ('$maHD', '{$data['MaSuatChieu']}', '$ghe', '$NDId', CURRENT_TIMESTAMP, '$NDId', CURRENT_TIMESTAMP, '1')";
        mysqli_query($conn, $queryDetail);
    }

    // Thêm chi tiết combo
    foreach ($comboData as $maCombo => $quantity) {
        if ($quantity > 0) {
            $queryComboDetail = "INSERT INTO ChiTietCombo(MaHD, MaCombo, SoLuong, NguoiTao, NgayTao, NguoiCapNhat, NgayCapNhat, TrangThai)
                                 VALUES ('$maHD', '$maCombo', '$quantity', '$NDId', CURRENT_TIMESTAMP, '$NDId', CURRENT_TIMESTAMP, '1')";
            mysqli_query($conn, $queryComboDetail);
        }
    }

    // Lấy thông tin suất chiếu và phim
    $maSuatChieu = $data['MaSuatChieu'];
    $querySuatChieu = "SELECT DATE(SC.GioChieu) AS NgayChieu, TIME(SC.GioChieu) AS GioChieu, P.TenPhim 
                       FROM SuatChieu SC 
                       JOIN Phim P ON SC.MaPhim = P.MaPhim 
                       WHERE SC.MaSuatChieu = '$maSuatChieu'";
    $resultSuatChieu = mysqli_query($conn, $querySuatChieu);
    $suatChieuData = mysqli_fetch_assoc($resultSuatChieu);

    // Lưu thông tin vé vào session để sử dụng trong ticket.php
    $_SESSION['ticketData'] = [
        'MaHD' => $maHD,
        'NgayLapHD' => date('Y-m-d H:i:s'),
        'TongTien' => $tongTien,
        'MaSuatChieu' => $maSuatChieu,
        'TenPhim' => $suatChieuData['TenPhim'],
        'NgayChieu' => $suatChieuData['NgayChieu'],
        'GioChieu' => $suatChieuData['GioChieu'],
        'Seats' => $seatDetails,
        'Combos' => $selectedCombos,
        'MaND' => $NDId
    ];

    // Gửi email thông tin vé
    $user = getByID('NguoiDung', 'MaND', $NDId);
    $recipientEmail = $user['status'] == 200 && !empty($user['data']['Email']) ? $user['data']['Email'] : null;

    if ($recipientEmail) {
        error_log("Attempting to send ticket email for MaHD: $maHD to $recipientEmail");
        if (!sendTicketEmail($conn, $maHD, $recipientEmail)) {
            error_log("Failed to send ticket email for MaHD: $maHD to $recipientEmail");
            redirect('views/ticket.php', 'success', 'Thanh toán thành công! Tuy nhiên, không thể gửi email thông tin vé.');
        } else {
            error_log("Ticket email sent successfully for MaHD: $maHD to $recipientEmail");
            redirect('views/ticket.php', 'success', 'Thanh toán thành công! Thông tin vé đã được gửi đến email của bạn.');
        }
    } else {
        error_log("User with MaND: $NDId does not have an email address.");
        redirect('views/ticket.php', 'success', 'Thanh toán thành công! Vui lòng thêm email vào hồ sơ để nhận thông tin vé.');
    }
} else {
    redirect('views/payment.php', 'error', 'Thanh toán thất bại: ' . $resultCode);
}
?>