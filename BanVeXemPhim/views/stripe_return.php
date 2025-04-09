<?php
session_start();
require_once("../config/function.php");
require_once("../config/sendmail.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Stripe\Stripe;
use Stripe\Checkout\Session;

$stripe_secret_key = "sk_test_51RB7vJFaa2zcokyWNaVblbusuXmg88GDzqh9Z7YporkVhwWSMaieWxjI2Ofd07Gz6BKwjkSPI5SOnwhSHiNArG7d00AxSLNrsJ"; // Thay bằng Secret Key Sandbox của bạn
Stripe::setApiKey($stripe_secret_key);

// Ghi log dữ liệu nhận được
file_put_contents('stripe_return.log', date('Y-m-d H:i:s') . " - Received Stripe callback: " . print_r($_GET, true) . "\n", FILE_APPEND);

// Kiểm tra dữ liệu trả về
if (!isset($_GET['session_id'])) {
    file_put_contents('stripe_return.log', date('Y-m-d H:i:s') . " - Missing session_id\n", FILE_APPEND);
    redirect('views/payment.php', 'error', 'Dữ liệu Stripe không hợp lệ');
    exit();
}

$session_id = $_GET['session_id'];

try {
    // Lấy thông tin Checkout Session
    $session = Session::retrieve($session_id);
    $orderId = $session->metadata->order_id;

    if ($session->payment_status === 'paid') {
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
                  VALUES ('$NDId', CURRENT_TIMESTAMP, '$tongTien', '$NDId', CURRENT_TIMESTAMP, '$NDId', CURRENT_TIMESTAMP, '1', 'Stripe')";
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
        redirect('views/payment.php', 'error', 'Thanh toán Stripe thất bại');
    }
} catch (Exception $e) {
    file_put_contents('stripe_return.log', date('Y-m-d H:i:s') . " - Stripe Error: " . $e->getMessage() . "\n", FILE_APPEND);
    redirect('views/payment.php', 'error', 'Lỗi xử lý Stripe: ' . $e->getMessage());
}
?>