<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Picqer\Barcode\BarcodeGeneratorPNG;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

require_once 'function.php';

// Tạo một instance; passing `true` enables exceptions
$mail = new PHPMailer(true);
function sendEmail($to, $subject, $body = '')
{
    $mail = new PHPMailer(true);

    try {
        // Cấu hình máy chủ
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Địa chỉ máy chủ SMTP
        $mail->SMTPAuth = true;
        $mail->Username   = 'vyquy633@gmail.com';  // Tên người dùng SMTP (admin)
        $mail->Password   = 'tmbwwcizvetibzle'; // Mật khẩu ứng dụng
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;        // Kích hoạt mã hóa TLS
        $mail->Port       = 587;                                    // Cổng TCP để kết nối
        // Người gửi và người nhận
        $mail->setFrom('vyquy633@gmail.com', 'Vy Trọng Qúy'); // Đặt địa chỉ email của admin làm người gửi
        $mail->addAddress($to); // Địa chỉ email của người nhận

        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        // Gửi email
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// function generateRandomPassword($length = 6)
// {
//     // Các ký tự cho mật khẩu
//     $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
//     $numbers = '0123456789';
//     $specialChars = '!@#$%^&*()_+-=[]{}|;:,.<>?';

//     // Đảm bảo mật khẩu có ít nhất một ký tự viết hoa, một số và một ký tự đặc biệt
//     $password = '';
//     $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
//     $password .= $numbers[random_int(0, strlen($numbers) - 1)];
//     $password .= $specialChars[random_int(0, strlen($specialChars) - 1)];

//     // Tạo phần còn lại của mật khẩu
//     $allChars = $uppercase . $numbers . $specialChars;
//     for ($i = 3; $i < $length; $i++) {
//         $password .= $allChars[random_int(0, strlen($allChars) - 1)];
//     }

//     // Trộn mật khẩu để không có thứ tự cố định
//     return str_shuffle($password);
// }
function generateRandomPassword($length = 6)
{
    return 'CGV12!';
}

// Kiểm tra nếu có dữ liệu POST từ biểu mẫu
if (isset($_POST['lienhe'])) {
    $recipientEmail = $_POST['email'];
    $fullname = trim($_POST['fullname']); // Tên người gửi
    $subject = trim($_POST['subject']); // Tiêu đề
    $sdt = trim($_POST['phone']); // Số điện thoại (không bắt buộc)
    $message = trim($_POST['message']); // Nội dung

    // Mảng lưu trữ lỗi
    $errors = [];

    // Kiểm tra các trường bắt buộc
    if (empty($fullname)) {
        $errors[] = "Họ và tên là bắt buộc.";
    }
    if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email là bắt buộc và phải hợp lệ.";
    }
    if (empty($subject)) {
        $errors[] = "Tiêu đề là bắt buộc.";
    }
    if (empty($message)) {
        $errors[] = "Tin nhắn là bắt buộc.";
    }

    // Nếu có lỗi, lưu lỗi và dữ liệu vào session, sau đó chuyển hướng về trang liên hệ
    if (!empty($errors)) {
        $_SESSION['messages'] = $errors; // Lưu thông báo lỗi
        $_SESSION['form_data'] = $_POST; // Lưu dữ liệu đã nhập để hiển thị lại
        header("Location: views/contact.php");
        exit();
    }

    // Nếu không có lỗi, tiến hành gửi email
    $body = '
    <h2>Tin nhắn liên hệ từ ' . htmlspecialchars($fullname) . ' - ' . htmlspecialchars($sdt) . ':</h2><br>
    <p>' . nl2br(htmlspecialchars($message)) . '</p>';

    $email = 'vyquy633@gmail.com';
    if (sendEmail($email, $subject, $body)) {
        redirect('views/contact.php', 'success', 'Gửi email thành công.');
    } else {
        redirect('views/contact.php', 'error', 'Gửi email không thành công.');
    }
}

function sendTicketEmail($conn, $maHD, $recipientEmail) {
    error_log("Starting sendTicketEmail for MaHD: $maHD to $recipientEmail");

    if (!isset($_SESSION['NDId'])) {
        error_log("Session NDId not set. Cannot send ticket email for MaHD: $maHD");
        return false;
    }

    $NDId = $_SESSION['NDId'];
    error_log("NDId: $NDId");

    // Bước 1: Kiểm tra hóa đơn
    $queryCheckHD = "SELECT * FROM HoaDon WHERE MaHD = ? AND MaND = ?";
    $stmtCheckHD = $conn->prepare($queryCheckHD);
    if (!$stmtCheckHD) {
        error_log("Failed to prepare query for checking invoice: " . $conn->error);
        return false;
    }
    $stmtCheckHD->bind_param("ss", $maHD, $NDId);
    $stmtCheckHD->execute();
    $resultCheckHD = $stmtCheckHD->get_result();

    if (mysqli_num_rows($resultCheckHD) == 0) {
        error_log("Invoice not found for MaHD: $maHD and MaND: $NDId");
        return false;
    }

    $hd = mysqli_fetch_assoc($resultCheckHD);
    error_log("Invoice data: " . json_encode($hd));
    $stmtCheckHD->close();

    // Lấy thông tin khách hàng (tên, email) từ bảng NguoiDung
    $queryUser = "SELECT TenND, Email FROM NguoiDung WHERE MaND = ?";
    $stmtUser = $conn->prepare($queryUser);
    if (!$stmtUser) {
        error_log("Failed to prepare query for user: " . $conn->error);
        return false;
    }
    $stmtUser->bind_param("s", $NDId);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();
    $userData = mysqli_fetch_assoc($resultUser);
    $customerName = $userData['TenND'] ?? 'Không xác định';
    $customerEmail = $userData['Email'] ?? 'Không có email';
    $stmtUser->close();

    // Lấy phương thức thanh toán từ hóa đơn
    $paymentMethod = $hd['PhuongThucThanhToan'] ?? 'Không xác định';

    // Bước 2: Lấy thông tin suất chiếu
    $queryHD = "SELECT SC.MaSuatChieu, SC.MaPhim, SC.MaPhong, SC.MaRap, SC.GioChieu 
                FROM ChiTietHoaDon CTHD 
                JOIN SuatChieu SC ON CTHD.MaSuatChieu = SC.MaSuatChieu 
                WHERE CTHD.MaHD = ?
                LIMIT 1";
    $stmtHD = $conn->prepare($queryHD);
    if (!$stmtHD) {
        error_log("Failed to prepare query for showtime: " . $conn->error);
        return false;
    }
    $stmtHD->bind_param("s", $maHD);
    $stmtHD->execute();
    $resultHD = $stmtHD->get_result();

    if (mysqli_num_rows($resultHD) == 0) {
        error_log("Showtime not found for MaHD: $maHD");
        $maPhim = null;
        $maPhong = null;
        $maSuatChieu = null;
        $maRap = null;
        $showDate = null;
        $showTime = null;
    } else {
        $row = mysqli_fetch_assoc($resultHD);
        $maPhim = $row['MaPhim'];
        $maPhong = $row['MaPhong'];
        $maSuatChieu = $row['MaSuatChieu'];
        $maRap = $row['MaRap'];
        $showDate = date('Y-m-d', strtotime($row['GioChieu']));
        $showTime = date('H:i:s', strtotime($row['GioChieu']));
        error_log("Showtime data: " . json_encode($row));
    }
    $stmtHD->close();

    // Bước 3: Lấy thông tin phim
    $movie = null;
    if ($maPhim) {
        $queryMovie = "SELECT TenPhim, Anh FROM Phim WHERE MaPhim = ?";
        $stmtMovie = $conn->prepare($queryMovie);
        if (!$stmtMovie) {
            error_log("Failed to prepare query for movie: " . $conn->error);
            return false;
        }
        $stmtMovie->bind_param("s", $maPhim);
        $stmtMovie->execute();
        $resultMovie = $stmtMovie->get_result();
        $movie = mysqli_fetch_assoc($resultMovie);
        error_log("Movie data: " . json_encode($movie));
        $stmtMovie->close();
    }

    // Bước 4: Lấy thông tin phòng
    $room = null;
    if ($maPhong) {
        $queryRoom = "SELECT TenPhong FROM Phong WHERE MaPhong = ?";
        $stmtRoom = $conn->prepare($queryRoom);
        if (!$stmtRoom) {
            error_log("Failed to prepare query for room: " . $conn->error);
            return false;
        }
        $stmtRoom->bind_param("s", $maPhong);
        $stmtRoom->execute();
        $resultRoom = $stmtRoom->get_result();
        $room = mysqli_fetch_assoc($resultRoom);
        error_log("Room data: " . json_encode($room));
        $stmtRoom->close();
    }

    // Bước 5: Lấy thông tin rạp và khu vực
    $rapName = "Không xác định";
    $khuVucName = "Không xác định";
    if ($maRap) {
        $rap = getByID('RapChieuPhim', 'MaRap', $maRap);
        $rapName = ($rap['status'] == 200) ? htmlspecialchars($rap['data']['TenRap']) : "Không xác định";
        error_log("Rap data: " . json_encode($rap));

        $khuVuc = ($rap['status'] == 200) ? getByID('KhuVuc', 'MaKhuVuc', $rap['data']['MaKhuVuc']) : ['status' => 404];
        $khuVucName = ($khuVuc['status'] == 200) ? htmlspecialchars($khuVuc['data']['TenKhuVuc']) : "Không xác định";
        error_log("KhuVuc data: " . json_encode($khuVuc));
    }

    // Bước 6: Lấy danh sách ghế
    $seatDetails = [];
    $querySeats = "SELECT G.MaGhe, G.TenGhe, G.GiaGhe, G.LoaiGhe 
                   FROM ChiTietHoaDon CTHD 
                   JOIN GHE G ON CTHD.MaGhe = G.MaGhe 
                   WHERE CTHD.MaHD = ?";
    $stmtSeats = $conn->prepare($querySeats);
    if (!$stmtSeats) {
        error_log("Failed to prepare query for seats: " . $conn->error);
        return false;
    }
    $stmtSeats->bind_param("s", $maHD);
    $stmtSeats->execute();
    $resultSeats = $stmtSeats->get_result();
    while ($seat = mysqli_fetch_assoc($resultSeats)) {
        $seatDetails[] = [
            'TenGhe' => $seat['TenGhe'],
            'GiaGhe' => $seat['GiaGhe'],
            'LoaiGhe' => $seat['LoaiGhe']
        ];
    }
    error_log("Seat data: " . json_encode($seatDetails));
    $stmtSeats->close();

    // Bước 7: Lấy danh sách combo
    $selectedCombos = [];
    $queryCombos = "SELECT C.MaCombo, C.TenCombo, C.GiaCombo, C.Anh, CTC.SoLuong 
                    FROM ChiTietCombo CTC 
                    JOIN Combo C ON CTC.MaCombo = C.MaCombo 
                    WHERE CTC.MaHD = ?";
    $stmtCombos = $conn->prepare($queryCombos);
    if (!$stmtCombos) {
        error_log("Failed to prepare query for combos: " . $conn->error);
        return false;
    }
    $stmtCombos->bind_param("s", $maHD);
    $stmtCombos->execute();
    $resultCombos = $stmtCombos->get_result();
    while ($combo = mysqli_fetch_assoc($resultCombos)) {
        $selectedCombos[] = [
            'TenCombo' => $combo['TenCombo'],
            'SoLuong' => $combo['SoLuong'],
            'GiaCombo' => $combo['GiaCombo'],
            'Anh' => $combo['Anh']
        ];
    }
    error_log("Combo data: " . json_encode($selectedCombos));
    $stmtCombos->close();

    // Bước 8: Tạo mã QR và Barcode
    try {
        $ticketJson = $maHD;
        $qrCode = QrCode::create($ticketJson)
            ->setSize(150)
            ->setMargin(10);
        $writer = new PngWriter();
        $qrCodeResult = $writer->write($qrCode);
        $qrCodeBase64 = base64_encode($qrCodeResult->getString());

        $barcodeGenerator = new BarcodeGeneratorPNG();
        $barcodeBase64 = base64_encode($barcodeGenerator->getBarcode($ticketJson, $barcodeGenerator::TYPE_CODE_128, 2, 50));
        error_log("QR Code and Barcode generated successfully for MaHD: $maHD");
    } catch (Exception $e) {
        error_log("Failed to generate QR Code or Barcode for MaHD: $maHD: " . $e->getMessage());
        return false;
    }

    // Tạo nội dung email dưới dạng HTML
    $body = '
    <div style="max-width: 1000px; margin: 0 auto; font-family: Arial, sans-serif; border: 2px solid transparent; border-image: linear-gradient(45deg, #ff6f61, #ffeb3b, #4caf50, #2196f3) 1; border-image-slice: 1;">
        <!-- Phần header của vé -->
        <div style="background: linear-gradient(135deg, #ff6f61, #ffeb3b); color: white; text-align: center; padding: 20px; position: relative;">
            <h2 style="margin: 0; font-weight: bold;">VÉ XEM PHIM</h2>
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.1; background: url(\'https://www.transparenttextures.com/patterns/cinema.png\');"></div>
        </div>

        <div style="display: flex; flex-wrap: wrap;">
            <!-- Phần hình ảnh phim -->
            <div style="flex: 1; min-width: 300px; background: linear-gradient(135deg, #f5f5f5, #e0e0e0); padding: 15px; text-align: center;">
                <div style="width: 100%; max-height: 350px; overflow: hidden; border-radius: 10px;">
                    ' . ($movie && !empty($movie['Anh']) ? 
                        '<img src="http://localhost:3000/BanVeXemPhim/uploads/film-imgs/' . $movie['Anh'] . '" alt="Poster" style="width: 100%; height: 100%; object-fit: cover;">' : 
                        '<div style="text-align: center; color: #888;">Không có hình ảnh</div>') . '
                </div>
            </div>

            <!-- Phần thông tin vé -->
            <div style="flex: 2; min-width: 400px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                    <h3 style="margin: 0; color: #007bff; font-weight: bold;">
                        ' . ($movie ? htmlspecialchars($movie['TenPhim']) : "Không xác định") . '
                    </h3>
                    <div style="background: linear-gradient(45deg, #ff6f61, #ff8a65); color: white; padding: 8px 20px; border-radius: 20px; font-weight: bold;">
                        ' . ($showDate ?? "Không xác định") . '
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <div style="display: flex; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px dashed #e0e0e0;">
                        <div style="width: 150px; color: #666;">Mã hóa đơn:</div>
                        <div style="font-weight: bold; color: #333;">' . $maHD . '</div>
                    </div>
                    <div style="display: flex; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px dashed #e0e0e0;">
                        <div style="width: 150px; color: #666;">Khách hàng:</div>
                        <div style="font-weight: bold; color: #333;">' . htmlspecialchars($customerName) . '</div>
                    </div>
                    <div style="display: flex; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px dashed #e0e0e0;">
                        <div style="width: 150px; color: #666;">Email:</div>
                        <div style="font-weight: bold; color: #333;">' . htmlspecialchars($customerEmail) . '</div>
                    </div>
                    <div style="display: flex; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px dashed #e0e0e0;">
                        <div style="width: 150px; color: #666;">Phương thức thanh toán:</div>
                        <div style="font-weight: bold; color: #333;">' . htmlspecialchars($paymentMethod) . '</div>
                    </div>
                    <div style="display: flex; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px dashed #e0e0e0;">
                        <div style="width: 150px; color: #666;">Ngày mua:</div>
                        <div style="font-weight: bold; color: #333;">' . date('d-m-Y H:i', strtotime($hd['NgayLapHD'])) . '</div>
                    </div>
                    <div style="display: flex; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px dashed #e0e0e0;">
                        <div style="width: 150px; color: #666;">Khu vực:</div>
                        <div style="font-weight: bold; color: #333;">' . $khuVucName . '</div>
                    </div>
                    <div style="display: flex; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px dashed #e0e0e0;">
                        <div style="width: 150px; color: #666;">Rạp:</div>
                        <div style="font-weight: bold; color: #333;">' . $rapName . '</div>
                    </div>
                    <div style="display: flex; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px dashed #e0e0e0;">
                        <div style="width: 150px; color: #666;">Phòng chiếu:</div>
                        <div style="font-weight: bold; color: #333;">' . ($room ? htmlspecialchars($room['TenPhong']) : "Không xác định") . '</div>
                    </div>
                    <div style="display: flex; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px dashed #e0e0e0;">
                        <div style="width: 150px; color: #666;">Giờ chiếu:</div>
                        <div style="font-weight: bold; color: #333;">' . ($showTime ?? "Không xác định") . '</div>
                    </div>
                </div>

                <!-- Phần ghế -->
                <div style="margin-bottom: 20px;">
                    <h5 style="font-weight: bold;">
                        <span style="color: #2196f3;">💺</span> Ghế
                    </h5>
                    <div>
                        ' . (!empty($seatDetails) ? 
                            implode('', array_map(function($seat) {
                                return '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; padding: 10px; background: #e8f0fe; border-radius: 5px;">
                                            <div>
                                                <span style="font-weight: bold;">' . $seat['TenGhe'] . '</span>
                                                <small style="color: #666;"> (' . $seat['LoaiGhe'] . ')</small>
                                            </div>
                                            <div style="font-weight: bold; color: #2196f3;">' . number_format($seat['GiaGhe'], 0, ',', '.') . ' VNĐ</div>
                                        </div>';
                            }, $seatDetails)) : 
                            '<div style="color: #666;">Không có ghế nào được chọn.</div>') . '
                    </div>
                </div>

                <!-- Phần combo -->
                ' . (!empty($selectedCombos) ? '
                <div style="margin-bottom: 20px;">
                    <h5 style="font-weight: bold;">
                        <span style="color: #4caf50;">🍴</span> Combo
                    </h5>
                    <div>
                        ' . implode('', array_map(function($combo) {
                            return '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; padding: 10px; background: #e6f4ea; border-radius: 5px;">
                                        <div style="display: flex; align-items: center;">
                                            <img src="http://localhost:3000/BanVeXemPhim/uploads/combo-imgs/' . $combo['Anh'] . '" alt="' . $combo['TenCombo'] . '" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px; margin-right: 10px;">
                                            <span style="font-weight: bold;">' . $combo['TenCombo'] . ' x ' . $combo['SoLuong'] . '</span>
                                        </div>
                                        <span style="font-weight: bold; color: #4caf50;">' . number_format($combo['GiaCombo'] * $combo['SoLuong'], 0, ',', '.') . ' VNĐ</span>
                                    </div>';
                        }, $selectedCombos)) . '
                    </div>
                </div>' : '') . '

                <!-- Tổng tiền -->
                <div style="background: linear-gradient(45deg, #ff6f61, #ff8a65); color: white; border-radius: 10px; padding: 15px; margin-top: 20px; position: relative;">
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.2; background: url(\'https://www.transparenttextures.com/patterns/stardust.png\');"></div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h5 style="font-weight: bold; margin: 0;">TỔNG CỘNG:</h5>
                        <h5 style="font-weight: bold; margin: 0;">' . number_format($hd['TongTien'], 0, ',', '.') . ' VNĐ</h5>
                    </div>
                </div>

                <!-- Phần mã QR và Barcode -->
                <div style="background: #f5f5f5; border: 2px solid transparent; border-image: linear-gradient(45deg, #ff6f61, #ffeb3b, #4caf50, #2196f3) 1; border-image-slice: 1; border-radius: 10px; padding: 15px; text-align: center; margin-top: 20px;">
                    <h5 style="font-weight: bold; margin-bottom: 15px;">
                        <span style="color: #2196f3;">📛</span> Mã QR & Barcode
                    </h5>
                    <div style="display: flex; justify-content: space-around; align-items: center; flex-wrap: wrap;">
                        <div>
                            <img src="data:image/png;base64,' . $qrCodeBase64 . '" alt="QR Code" style="width: 150px; height: 150px; border-radius: 5px;">
                            <p style="margin-top: 10px; margin-bottom: 0; color: #666;">Mã QR</p>
                        </div>
                        <div>
                            <img src="data:image/png;base64,' . $barcodeBase64 . '" alt="Barcode" style="width: 200px; height: 50px; border-radius: 5px;">
                            <p style="margin-top: 10px; margin-bottom: 0; color: #666;">Barcode</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phần footer -->
        <div style="background: linear-gradient(135deg, #f5f5f5, #e0e0e0); text-align: center; padding: 20px;">
            <div style="margin-bottom: 15px;">
                <small style="color: #666;">Vui lòng đến trước giờ chiếu 15 phút để nhận vé và combo (nếu có).</small>
            </div>
            <a href="http://localhost:3000/BanVeXemPhim/index.php" 
                style="display: inline-block; padding: 10px 30px; background: linear-gradient(45deg, #1a237e, #3949ab); color: white; font-weight: bold; text-decoration: none; border-radius: 20px;">
                Về Trang Chủ
            </a>
        </div>
    </div>';
    
    // Gửi email
    $subject = "Thông tin vé xem phim - Mã hóa đơn: $maHD";
    error_log("Sending email to $recipientEmail with subject: $subject");
    $result = sendEmail($recipientEmail, $subject, $body);
    if (!$result) {
        error_log("Failed to send ticket email to $recipientEmail for MaHD: $maHD");
    } else {
        error_log("Email sent successfully to $recipientEmail for MaHD: $maHD");
    }
    return $result;
}
?>