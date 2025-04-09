<?php
require '../../../config/function.php';
require_once '../../../vendor/autoload.php'; // Bao gồm autoload của Composer
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Picqer\Barcode\BarcodeGeneratorPNG;

include('../../includes/header.php');

// Kiểm tra xem người dùng đã đăng nhập chưa, nếu chưa thì chuyển hướng đến trang đăng nhập
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    redirect('sign-in.php', 'error', 'Vui lòng đăng nhập');
}
if (isset($_SESSION['EmployedIn']) && $_SESSION['EmployedIn'] === true) {
    redirect('index.php', 'error', 'Bạn không phải admin!', 'admin');
}

// Kiểm tra ID hợp lệ của hóa đơn từ URL
$id_result = check_valid_ID('id');
if (!is_numeric($id_result)) {
    echo '<h5>' . $id_result . '</h5>';
    return false;
}

// Lấy thông tin hóa đơn từ cơ sở dữ liệu dựa trên ID
$invoice = getByID('hoadon', 'MaHD', $id_result);
if ($invoice['status'] != 200) {
    echo '<h5>' . htmlspecialchars($invoice['message']) . '</h5>';
    return false;
}

$maHD = $invoice['data']['MaHD'];

// Lấy thông tin chi tiết hóa đơn
$query = "SELECT h.*, sc.*, p.TenPhim, p.Anh
          FROM hoadon h
          JOIN chitiethoadon cthd ON h.MaHD = cthd.MaHD
          JOIN suatchieu sc ON cthd.MaSuatChieu = sc.MaSuatChieu
          JOIN phim p ON sc.MaPhim = p.MaPhim
          WHERE h.MaHD = '$maHD'
          LIMIT 1";
$result = $conn->query($query);
if (!$result || $result->num_rows == 0) {
    echo '<h5>Không tìm thấy thông tin hóa đơn</h5>';
    return false;
}
$hoaDon = $result->fetch_assoc();

// Lấy thông tin ghế
$querySeats = "SELECT G.MaGhe, G.TenGhe, G.GiaGhe, G.LoaiGhe
               FROM chitiethoadon CTHD
               JOIN GHE G ON CTHD.MaGhe = G.MaGhe
               WHERE CTHD.MaHD = '$maHD'";
$resultSeats = $conn->query($querySeats);
$seatDetails = [];
while ($seat = $resultSeats->fetch_assoc()) {
    $seatDetails[] = [
        'TenGhe' => $seat['TenGhe'],
        'GiaGhe' => $seat['GiaGhe'],
        'LoaiGhe' => $seat['LoaiGhe']
    ];
}

// Lấy thông tin combo
$queryCombos = "SELECT C.MaCombo, C.TenCombo, C.GiaCombo, C.Anh, CTC.SoLuong
                FROM chitietcombo CTC
                JOIN combo C ON CTC.MaCombo = C.MaCombo
                WHERE CTC.MaHD = '$maHD'";
$resultCombos = $conn->query($queryCombos);
$selectedCombos = [];
while ($combo = $resultCombos->fetch_assoc()) {
    $selectedCombos[] = [
        'TenCombo' => $combo['TenCombo'],
        'SoLuong' => $combo['SoLuong'],
        'GiaCombo' => $combo['GiaCombo'],
        'Anh' => $combo['Anh']
    ];
}

// Lấy thông tin rạp
$maRap = $hoaDon['MaRap'];
$rap = getByID('RapChieuPhim', 'MaRap', $maRap);
$rapName = ($rap['status'] == 200) ? htmlspecialchars($rap['data']['TenRap']) : "Không xác định";

// Lấy thông tin khu vực
$khuVuc = ($rap['status'] == 200) ? getByID('KhuVuc', 'MaKhuVuc', $rap['data']['MaKhuVuc']) : ['status' => 404];
$khuVucName = ($khuVuc['status'] == 200) ? htmlspecialchars($khuVuc['data']['TenKhuVuc']) : "Không xác định";

// Lấy thông tin phòng
$maPhong = $hoaDon['MaPhong'];
$queryRoom = "SELECT TenPhong FROM Phong WHERE MaPhong = '$maPhong'";
$resultRoom = $conn->query($queryRoom);
$room = $resultRoom->fetch_assoc();

// Lấy thông tin khách hàng
$queryCustomer = "SELECT TenND, Email
                  FROM nguoidung
                  WHERE MaND = '{$hoaDon['MaND']}'";
$resultCustomer = $conn->query($queryCustomer);
$customer = $resultCustomer->fetch_assoc();
$customerName = $customer ? htmlspecialchars($customer['TenND']) : "Không tìm thấy người dùng";
$customerEmail = $customer ? htmlspecialchars($customer['Email']) : "Không có email";

// Định dạng ngày giờ
$purchaseDate = new DateTime($hoaDon['NgayLapHD']);
$showDateTime = new DateTime($hoaDon['GioChieu']);
$showDate = $showDateTime->format('Y-m-d');
$showTime = $showDateTime->format('H:i');

// Tạo mã QR
$ticketJson = $maHD;
$qrCode = QrCode::create($ticketJson)
    ->setSize(150)
    ->setMargin(10);
$writer = new PngWriter();
$qrCodeResult = $writer->write($qrCode);
$qrCodeBase64 = base64_encode($qrCodeResult->getString());

// Tạo Barcode
$barcodeGenerator = new BarcodeGeneratorPNG();
$barcodeBase64 = base64_encode($barcodeGenerator->getBarcode($ticketJson, $barcodeGenerator::TYPE_CODE_128, 2, 50));
?>
<style>
        body{
            background: linear-gradient(135deg, #1e3a8a, #6b21a8);
        }
        </style>
<div class="container my-5">
    <div class="ticket-card mx-auto shadow-lg" style="max-width: 1000px; border-radius: 15px; overflow: hidden; border: none;">
        <!-- Phần header của vé -->
        <div class="card-header text-center py-4" style="background: linear-gradient(135deg, #ff6f61, #ffeb3b); color: white; position: relative;">
            <h4 class="m-0 fw-bold">CHI TIẾT HÓA ĐƠN</h4>
            <div class="ticket-pattern" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.1; background: url('https://www.transparenttextures.com/patterns/cinema.png');"></div>
        </div>

        <div class="card-body p-0">
            <div class="row g-0">
                <!-- Phần hình ảnh phim -->
                <div class="col-md-4 d-flex align-items-center justify-content-center p-3" style="background: linear-gradient(135deg, #f5f5f5, #e0e0e0);">
                    <div class="position-relative" style="width: 100%; max-height: 350px; overflow: hidden; border-radius: 10px;">
                        <img src="../../../uploads/film-imgs/<?= $hoaDon['Anh']; ?>" alt="Poster" 
                            class="img-fluid shadow" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
                        <div class="overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.3); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s;">
                            <span class="text-white fw-bold">Xem Trailer</span>
                        </div>
                    </div>
                </div>

                <!-- Phần thông tin hóa đơn -->
                <div class="col-md-8">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <h3 class="card-title fw-bold text-primary mb-0"><?= htmlspecialchars($hoaDon['TenPhim']); ?></h3>
                            <div class="date-badge py-2 px-4 rounded-pill" style="background: linear-gradient(45deg, #ff6f61, #ff8a65); color: white; font-weight: bold;">
                                <?= $showDate ?>
                            </div>
                        </div>

                        <div class="ticket-details mb-4">
                            <div class="row g-0 mb-3">
                                <div class="col-5 text-muted">Mã hóa đơn:</div>
                                <div class="col-7 fw-bold text-dark"><?= $maHD; ?></div>
                            </div>
                            <div class="row g-0 mb-3">
                                <div class="col-5 text-muted">Khách hàng:</div>
                                <div class="col-7 fw-bold text-dark"><?= $customerName; ?></div>
                            </div>
                            <div class="row g-0 mb-3">
                                <div class="col-5 text-muted">Email:</div>
                                <div class="col-7 fw-bold text-dark"><?= $customerEmail; ?></div>
                            </div>
                            <div class="row g-0 mb-3">
                                <div class="col-5 text-muted">Phương thức thanh toán:</div>
                                <div class="col-7 fw-bold text-dark"><?= htmlspecialchars($hoaDon['PhuongThucThanhToan'] ?? 'Chưa xác định'); ?></div>
                            </div>
                            <div class="row g-0 mb-3">
                                <div class="col-5 text-muted">Ngày mua:</div>
                                <div class="col-7 fw-bold text-dark"><?= $purchaseDate->format('Y-m-d H:i'); ?></div>
                            </div>
                            <div class="row g-0 mb-3">
                                <div class="col-5 text-muted">Khu vực:</div>
                                <div class="col-7 fw-bold text-dark"><?= $khuVucName; ?></div>
                            </div>
                            <div class="row g-0 mb-3">
                                <div class="col-5 text-muted">Rạp:</div>
                                <div class="col-7 fw-bold text-dark"><?= $rapName; ?></div>
                            </div>
                            <div class="row g-0 mb-3">
                                <div class="col-5 text-muted">Phòng chiếu:</div>
                                <div class="col-7 fw-bold text-dark"><?= htmlspecialchars($room['TenPhong']); ?></div>
                            </div>
                            <div class="row g-0 mb-3">
                                <div class="col-5 text-muted">Giờ chiếu:</div>
                                <div class="col-7 fw-bold text-dark"><?= $showTime; ?></div>
                            </div>
                        </div>

                        <!-- Phần ghế -->
                        <div class="seats-section mb-4">
                            <h5 class="section-title fw-bold">
                                <i class="fas fa-chair me-2" style="color: #2196f3;"></i>Ghế
                            </h5>
                            <div class="seat-list">
                                <?php foreach ($seatDetails as $seat): ?>
                                    <div class="seat-item d-flex justify-content-between align-items-center p-2 rounded mb-2" style="background: #e8f0fe;">
                                        <div class="seat-info">
                                            <span class="fw-bold"><?= $seat['TenGhe']; ?></span>
                                            <small class="text-muted"> (<?= $seat['LoaiGhe']; ?>)</small>
                                        </div>
                                        <div class="seat-price fw-bold text-primary">
                                            <?= number_format($seat['GiaGhe'], 0, ',', '.'); ?> VNĐ
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Phần combo -->
                        <?php if (!empty($selectedCombos)): ?>
                        <div class="combo-section mb-4">
                            <h5 class="section-title fw-bold">
                                <i class="fas fa-utensils me-2" style="color: #4caf50;"></i>Combo
                            </h5>
                            <div class="combo-list">
                                <?php foreach ($selectedCombos as $combo): ?>
                                    <div class="combo-item d-flex justify-content-between align-items-center p-2 rounded mb-2" style="background: #e6f4ea;">
                                        <div class="d-flex align-items-center">
                                            <img src="../../../uploads/combo-imgs/<?= $combo['Anh']; ?>" alt="<?= $combo['TenCombo']; ?>" class="me-3 rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                            <span class="fw-bold"><?= $combo['TenCombo']; ?> x <?= $combo['SoLuong']; ?></span>
                                        </div>
                                        <span class="fw-bold text-success"><?= number_format($combo['GiaCombo'] * $combo['SoLuong'], 0, ',', '.'); ?> VNĐ</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Tổng tiền -->
                        <div class="total-section mt-4 p-3 rounded" style="background: linear-gradient(45deg, #ff6f61, #ff8a65); color: white;">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-0">TỔNG CỘNG:</h5>
                                <h5 class="fw-bold mb-0"><?= number_format($hoaDon['TongTien'], 0, ',', '.'); ?> VNĐ</h5>
                            </div>
                        </div>

                        <!-- Phần mã QR và Barcode -->
                        <div class="codes-section mt-4 p-3 text-center rounded">
                            <h5 class="section-title fw-bold mb-3">
                                <i class="fas fa-qrcode me-2" style="color: #2196f3;"></i>Mã QR & Barcode
                            </h5>
                            <div class="d-flex justify-content-around align-items-center">
                                <div>
                                    <img src="data:image/png;base64,<?= $qrCodeBase64; ?>" alt="QR Code" style="width: 150px; height: 150px; border-radius: 5px;">
                                    <p class="mt-2 mb-0 text-muted">Mã QR</p>
                                </div>
                                <div>
                                    <img src="data:image/png;base64,<?= $barcodeBase64; ?>" alt="Barcode" style="width: 200px; height: 50px; border-radius: 5px;">
                                    <p class="mt-2 mb-0 text-muted">Barcode</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phần footer -->
        <div class="card-footer text-center py-4" style="background: linear-gradient(135deg, #f5f5f5, #e0e0e0);">
            <div class="mb-3">
                <small class="text-muted">Vui lòng đến trước giờ chiếu 15 phút để nhận vé và combo (nếu có).</small>
            </div>
            <a href="../../invoice.php" 
                class="btn px-5 py-2 rounded-pill" style="background: linear-gradient(45deg, #1a237e, #3949ab); color: white; font-weight: bold;">
                <i class="fas fa-arrow-left me-2"></i>Quay Lại
            </a>
        </div>
    </div>
</div>

<style>
.ticket-card {
    background: #fff;
    border: 2px solid transparent;
    border-image: linear-gradient(45deg, #ff6f61, #ffeb3b, #4caf50, #2196f3) 1;
    border-image-slice: 1;
    animation: gradientBorder 8s linear infinite;
    position: relative;
    overflow: hidden;
}

@keyframes gradientBorder {
    0% {
        border-image-source: linear-gradient(45deg, #ff6f61, #ffeb3b, #4caf50, #2196f3, #ff6f61);
    }
    25% {
        border-image-source: linear-gradient(45deg, #ffeb3b, #4caf50, #2196f3, #ff6f61, #ffeb3b);
    }
    50% {
        border-image-source: linear-gradient(45deg, #4caf50, #2196f3, #ff6f61, #ffeb3b, #4caf50);
    }
    75% {
        border-image-source: linear-gradient(45deg, #2196f3, #ff6f61, #ffeb3b, #4caf50, #2196f3);
    }
    100% {
        border-image-source: linear-gradient(45deg, #ff6f61, #ffeb3b, #4caf50, #2196f3, #ff6f61);
    }
}

.card-header {
    position: relative;
    overflow: hidden;
}

.card-header h4 {
    position: relative;
    z-index: 1;
}

.ticket-details .row {
    padding: 5px 0;
    border-bottom: 1px dashed #e0e0e0;
}

.ticket-details .row:last-child {
    border-bottom: none;
}

.seat-item, .combo-item {
    transition: transform 0.3s, box-shadow 0.3s;
}

.seat-item:hover, .combo-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.combo-item img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 5px;
    transition: transform 0.3s;
}

.combo-item img:hover {
    transform: scale(1.1);
}

.total-section {
    position: relative;
    overflow: hidden;
}

.total-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('https://www.transparenttextures.com/patterns/stardust.png');
    opacity: 0.2;
}

.codes-section {
    background: #f5f5f5;
    border: 2px solid transparent;
    border-image: linear-gradient(45deg, #ff6f61, #ffeb3b, #4caf50, #2196f3) 1;
    border-image-slice: 1;
    animation: gradientBorder 8s linear infinite;
}

.position-relative:hover .overlay {
    opacity: 1;
}

.position-relative:hover img {
    transform: scale(1.05);
}

.date-badge {
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s;
}

.date-badge:hover {
    transform: scale(1.1);
}

.btn {
    transition: transform 0.3s, box-shadow 0.3s;
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}
</style>

<?php include('../../includes/footer.php'); ?>