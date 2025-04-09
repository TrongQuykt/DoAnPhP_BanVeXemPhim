<?php
session_start();
require_once("../config/function.php");
require_once("../config/sendmail.php");
require_once __DIR__ . '/../vendor/autoload.php';

// Ghi log dữ liệu nhận được từ VNPAY
file_put_contents('vnpay_callback.log', date('Y-m-d H:i:s') . " - Received VNPAY callback: " . print_r($_GET, true) . "\n", FILE_APPEND);

$vnp_HashSecret = "BFRBXJCBLCYXBAYZDJDUSXWABIYXWONM"; // Chuỗi bí mật của VNPay

// Kiểm tra các tham số bắt buộc
$requiredParams = ['vnp_TxnRef', 'vnp_Amount', 'vnp_ResponseCode', 'vnp_SecureHash'];
$missingParams = [];
foreach ($requiredParams as $param) {
    if (!isset($_GET[$param]) || empty($_GET[$param])) {
        $missingParams[] = $param;
    }
}

if (!empty($missingParams)) {
    $errorMessage = "Missing required parameters in callback: " . implode(', ', $missingParams);
    file_put_contents('vnpay-error.log', date('Y-m-d H:i:s') . " - " . $errorMessage . "\n", FILE_APPEND);
    redirect('views/payment.php', 'error', $errorMessage);
    exit();
}

// Lấy dữ liệu từ VNPay
$vnp_SecureHash = $_GET['vnp_SecureHash'];
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

// Xóa tham số vnp_SecureHash để tính hash
unset($inputData['vnp_SecureHash']);
ksort($inputData);
$hashData = "";
foreach ($inputData as $key => $value) {
    if ($hashData == "") {
        $hashData .= urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData .= "&" . urlencode($key) . "=" . urlencode($value);
    }
}

$secureHash = hash_hmac("sha512", $hashData, $vnp_HashSecret);

// Xác minh chữ ký
if ($secureHash === $vnp_SecureHash) {
    if ($_GET['vnp_ResponseCode'] == '00') {
        // Kiểm tra session
        if (!isset($_SESSION['bookingData']) || !isset($_SESSION['NDId'])) {
            $errorMessage = "Session data missing";
            file_put_contents('vnpay-error.log', date('Y-m-d H:i:s') . " - " . $errorMessage . "\n", FILE_APPEND);
            redirect('views/payment.php', 'error', $errorMessage);
            exit();
        }

        // Thanh toán thành công
        $data = $_SESSION['bookingData'];
        $NDId = $_SESSION['NDId'];
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
        $tongTien = $_SESSION['finalPrice'] ?? $totalPrice; // Sử dụng tổng tiền sau giảm

        // Thêm hóa đơn
        $phuongThucThanhToan = 'VNPay';
        $query = "INSERT INTO HoaDon(MaND, NgayLapHD, TongTien, NguoiTao, NgayTao, NguoiCapNhat, NgayCapNhat, TrangThai, PhuongThucThanhToan)
                  VALUES ('$NDId', CURRENT_TIMESTAMP, '$tongTien', '$NDId', CURRENT_TIMESTAMP, '$NDId', CURRENT_TIMESTAMP, '1', '$phuongThucThanhToan')";
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

        // Cập nhật số lượng mã giảm giá đã sử dụng
        if (!empty($_SESSION['discountCode'])) {
            $queryUpdateDiscount = "UPDATE discount SET SoLuongDaDung = SoLuongDaDung + 1 WHERE MaDiscount = ?";
            $stmtUpdateDiscount = $conn->prepare($queryUpdateDiscount);
            $stmtUpdateDiscount->bind_param("s", $_SESSION['discountCode']);
            $stmtUpdateDiscount->execute();
            $stmtUpdateDiscount->close();
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
        $errorMessage = 'Thanh toán thất bại: ' . $_GET['vnp_ResponseCode'];
        file_put_contents('vnpay-error.log', date('Y-m-d H:i:s') . " - " . $errorMessage . "\n", FILE_APPEND);
        redirect('views/payment.php', 'error', $errorMessage);
    }
} else {
    $errorMessage = 'Chữ ký không hợp lệ';
    file_put_contents('vnpay-error.log', date('Y-m-d H:i:s') . " - " . $errorMessage . "\n", FILE_APPEND);
    redirect('views/payment.php', 'error', $errorMessage);
}
?>