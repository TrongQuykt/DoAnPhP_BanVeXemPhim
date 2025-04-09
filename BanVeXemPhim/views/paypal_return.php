<?php
session_start();
require_once("../config/function.php");
require_once("../config/sendmail.php");
require_once __DIR__ . '/../vendor/autoload.php';

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

$paypal_client_id = "AdCwnycsGVNOqwLq9ZKlUjPveVicpkVDwlZ2kzRJlqS5cqG0BXlFef3XoWaK6Rn0PjrEtKM65FUIwjzP"; // Thay bằng Client ID Sandbox
$paypal_secret = "EIXPli_JdNPg3lcyDttjuusvg7V5zMo_BGnskQc96Poa395WXp9e6vKh6-uJ9gW4YvlMM0iYyuVSQrRF"; // Thay bằng Secret Sandbox

// Ghi log dữ liệu nhận được
file_put_contents('paypal_return.log', date('Y-m-d H:i:s') . " - Received PayPal callback: " . print_r($_GET, true) . "\n", FILE_APPEND);

// Thiết lập API Context
$apiContext = new ApiContext(
    new OAuthTokenCredential($paypal_client_id, $paypal_secret)
);
$apiContext->setConfig([
    'mode' => 'sandbox',
    'log.LogEnabled' => true,
    'log.FileName' => '../PayPal.log',
    'log.LogLevel' => 'DEBUG'
]);

// Kiểm tra dữ liệu trả về
if (!isset($_GET['paymentId']) || !isset($_GET['PayerID'])) {
    file_put_contents('paypal_return.log', date('Y-m-d H:i:s') . " - Missing paymentId or PayerID\n", FILE_APPEND);
    redirect('views/payment.php', 'error', 'Dữ liệu PayPal không hợp lệ');
    exit();
}

$paymentId = $_GET['paymentId'];
$payerId = $_GET['PayerID'];

try {
    $payment = Payment::get($paymentId, $apiContext);
    $execution = new PaymentExecution();
    $execution->setPayerId($payerId);

    // Thực hiện thanh toán
    $result = $payment->execute($execution, $apiContext);
    $transaction = $result->getTransactions()[0];
    $orderId = $transaction->getInvoiceNumber();

    if ($result->getState() === 'approved') {
        // Thanh toán thành công
        $data = $_SESSION['bookingData'];
        $NDId = $_SESSION['NDId'] ?? getUser()['MaND'];
        $maGhe = explode(',', $data['MaGhe'] ?? '');
        $seatDetails = [];
        $totalPrice = 0;

        foreach ($maGhe as $seatId) {
            $querySeat = "SELECT TenGhe, GiaGhe, LoaiGhe FROM GHE WHERE MaGhe = '$seatId'";
            $resultSeat = mysqli_query($conn, $querySeat);
            $seatData = mysqli_fetch_assoc($resultSeat);
            if ($seatData) {
                $seatDetails[] = [
                    'TenGhe' => $seatData['TenGhe'],
                    'GiaGhe' => $seatData['GiaGhe'],
                    'LoaiGhe' => $seatData['LoaiGhe']
                ];
                $totalPrice += $seatData['GiaGhe'];
            }
        }

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
        $tongTien = $_SESSION['finalPrice'] ?? $totalPrice;

        // Thêm hóa đơn
        $query = "INSERT INTO HoaDon(MaND, NgayLapHD, TongTien, NguoiTao, NgayTao, NguoiCapNhat, NgayCapNhat, TrangThai, PhuongThucThanhToan)
                  VALUES ('$NDId', CURRENT_TIMESTAMP, '$tongTien', '$NDId', CURRENT_TIMESTAMP, '$NDId', CURRENT_TIMESTAMP, '1', 'PayPal')";
        mysqli_query($conn, $query);

        $maHD = mysqli_insert_id($conn);

        // Thêm chi tiết hóa đơn (vé)
        foreach ($maGhe as $ghe) {
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

        // Lưu thông tin vé vào session
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

        // Gửi email
        $user = getByID('NguoiDung', 'MaND', $NDId);
        $recipientEmail = $user['status'] == 200 && !empty($user['data']['Email']) ? $user['data']['Email'] : null;

        if ($recipientEmail) {
            if (!sendTicketEmail($conn, $maHD, $recipientEmail)) {
                redirect('views/ticket.php', 'success', 'Thanh toán thành công! Tuy nhiên, không thể gửi email thông tin vé.');
            } else {
                redirect('views/ticket.php', 'success', 'Thanh toán thành công! Thông tin vé đã được gửi đến email của bạn.');
            }
        } else {
            redirect('views/ticket.php', 'success', 'Thanh toán thành công! Vui lòng thêm email vào hồ sơ để nhận thông tin vé.');
        }
    } else {
        redirect('views/payment.php', 'error', 'Thanh toán PayPal thất bại');
    }
} catch (Exception $e) {
    file_put_contents('paypal_return.log', date('Y-m-d H:i:s') . " - PayPal Error: " . $e->getMessage() . "\n", FILE_APPEND);
    redirect('views/payment.php', 'error', 'Lỗi xử lý PayPal: ' . $e->getMessage());
}
?>