<?php
session_start();
require_once("../config/function.php");

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['NDloggedIn']) || !isset($_SESSION['NDId'])) {
    echo '<p>Bạn cần đăng nhập để xem chi tiết vé.</p>';
    exit;
}

if (!isset($_GET['maHD'])) {
    echo '<p>Mã hóa đơn không hợp lệ.</p>';
    exit;
}

$maHD = $_GET['maHD'];
$NDId = $_SESSION['NDId'];

// Bước 1: Kiểm tra xem hóa đơn có tồn tại không
$queryCheckHD = "SELECT * FROM HoaDon WHERE MaHD = ? AND MaND = ?";
$stmtCheckHD = $conn->prepare($queryCheckHD);
$stmtCheckHD->bind_param("ss", $maHD, $NDId);
$stmtCheckHD->execute();
$resultCheckHD = $stmtCheckHD->get_result();

if (mysqli_num_rows($resultCheckHD) == 0) {
    echo '<p>Hóa đơn không tồn tại hoặc bạn không có quyền xem.</p>';
    $stmtCheckHD->close();
    exit;
}

$hd = mysqli_fetch_assoc($resultCheckHD);
$stmtCheckHD->close();

// Lấy thông tin khách hàng
$queryCustomer = "SELECT TenND, Email FROM nguoidung WHERE MaND = ?";
$stmtCustomer = $conn->prepare($queryCustomer);
$stmtCustomer->bind_param("s", $NDId);
$stmtCustomer->execute();
$resultCustomer = $stmtCustomer->get_result();
$customer = $resultCustomer->fetch_assoc();
$customerName = $customer ? htmlspecialchars($customer['TenND']) : "Không tìm thấy người dùng";
$customerEmail = $customer ? htmlspecialchars($customer['Email']) : "Không có email";
$stmtCustomer->close();

// Bước 2: Lấy thông tin suất chiếu từ ChiTietHoaDon và SuatChieu
$queryHD = "SELECT SC.MaSuatChieu, SC.MaPhim, SC.MaPhong, SC.MaRap, SC.GioChieu 
            FROM ChiTietHoaDon CTHD 
            JOIN SuatChieu SC ON CTHD.MaSuatChieu = SC.MaSuatChieu 
            WHERE CTHD.MaHD = ?
            LIMIT 1";
$stmtHD = $conn->prepare($queryHD);
$stmtHD->bind_param("s", $maHD);
$stmtHD->execute();
$resultHD = $stmtHD->get_result();

if (mysqli_num_rows($resultHD) == 0) {
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
}
$stmtHD->close();

// Bước 3: Lấy thông tin phim (nếu có MaPhim)
$movie = null;
if ($maPhim) {
    $queryMovie = "SELECT TenPhim, Anh FROM Phim WHERE MaPhim = ?";
    $stmtMovie = $conn->prepare($queryMovie);
    $stmtMovie->bind_param("s", $maPhim);
    $stmtMovie->execute();
    $resultMovie = $stmtMovie->get_result();
    $movie = mysqli_fetch_assoc($resultMovie);
    $stmtMovie->close();
}

// Bước 4: Lấy thông tin phòng (nếu có MaPhong)
$room = null;
if ($maPhong) {
    $queryRoom = "SELECT TenPhong FROM Phong WHERE MaPhong = ?";
    $stmtRoom = $conn->prepare($queryRoom);
    $stmtRoom->bind_param("s", $maPhong);
    $stmtRoom->execute();
    $resultRoom = $stmtRoom->get_result();
    $room = mysqli_fetch_assoc($resultRoom);
    $stmtRoom->close();
}

// Bước 5: Lấy thông tin rạp và khu vực (nếu có MaRap)
$rapName = "Không xác định";
$khuVucName = "Không xác định";
if ($maRap) {
    $rap = getByID('RapChieuPhim', 'MaRap', $maRap);
    $rapName = ($rap['status'] == 200) ? htmlspecialchars($rap['data']['TenRap']) : "Không xác định";

    $khuVuc = ($rap['status'] == 200) ? getByID('KhuVuc', 'MaKhuVuc', $rap['data']['MaKhuVuc']) : ['status' => 404];
    $khuVucName = ($khuVuc['status'] == 200) ? htmlspecialchars($khuVuc['data']['TenKhuVuc']) : "Không xác định";
}

// Bước 6: Lấy danh sách ghế
$seatDetails = [];
$querySeats = "SELECT G.MaGhe, G.TenGhe, G.GiaGhe, G.LoaiGhe 
               FROM ChiTietHoaDon CTHD 
               JOIN GHE G ON CTHD.MaGhe = G.MaGhe 
               WHERE CTHD.MaHD = ?";
$stmtSeats = $conn->prepare($querySeats);
$stmtSeats->bind_param("s", $maHD);
$stmtSeats->execute();
$resultSeats = $stmtSeats->get_result();
while ($seat = $resultSeats->fetch_assoc()) {
    $seatDetails[] = [
        'TenGhe' => $seat['TenGhe'],
        'GiaGhe' => $seat['GiaGhe'],
        'LoaiGhe' => $seat['LoaiGhe']
    ];
}
$stmtSeats->close();

// Bước 7: Lấy danh sách combo
$selectedCombos = [];
$queryCombos = "SELECT C.MaCombo, C.TenCombo, C.GiaCombo, C.Anh, CTC.SoLuong 
                FROM ChiTietCombo CTC 
                JOIN Combo C ON CTC.MaCombo = C.MaCombo 
                WHERE CTC.MaHD = ?";
$stmtCombos = $conn->prepare($queryCombos);
$stmtCombos->bind_param("s", $maHD);
$stmtCombos->execute();
$resultCombos = $stmtCombos->get_result();
while ($combo = $resultCombos->fetch_assoc()) {
    $selectedCombos[] = [
        'TenCombo' => $combo['TenCombo'],
        'SoLuong' => $combo['SoLuong'],
        'GiaCombo' => $combo['GiaCombo'],
        'Anh' => $combo['Anh']
    ];
}
$stmtCombos->close();

// Bước 8: Tạo mã QR và Barcode
require_once '../vendor/autoload.php';
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Picqer\Barcode\BarcodeGeneratorPNG;

$ticketJson = $maHD;
$qrCode = QrCode::create($ticketJson)
    ->setSize(120)
    ->setMargin(5);
$writer = new PngWriter();
$qrCodeResult = $writer->write($qrCode);
$qrCodeBase64 = base64_encode($qrCodeResult->getString());

$barcodeGenerator = new BarcodeGeneratorPNG();
$barcodeBase64 = base64_encode($barcodeGenerator->getBarcode($ticketJson, $barcodeGenerator::TYPE_CODE_128, 2, 40));
?>

<div class="ticket-card shadow-lg">
    <!-- Phần header của vé -->
    <div class="card-header text-center py-3">
        <h4 class="m-0 fw-bold">VÉ XEM PHIM</h4>
        <div class="ticket-pattern"></div>
    </div>

    <div class="card-body p-0">
        <div class="row g-0">
            <!-- Phần hình ảnh phim -->
            <div class="col-md-5 d-flex align-items-center justify-content-center p-3 movie-poster-section">
                <div class="position-relative">
                    <?php if ($movie && !empty($movie['Anh'])): ?>
                        <img src="../uploads/film-imgs/<?= $movie['Anh']; ?>" alt="Poster" class="img-fluid shadow">
                    <?php else: ?>
                        <div class="text-center text-muted">Không có hình ảnh</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Phần thông tin vé -->
            <div class="col-md-7">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h3 class="card-title fw-bold text-primary mb-0">
                            <?= $movie ? htmlspecialchars($movie['TenPhim']) : "Không xác định"; ?>
                        </h3>
                        <div class="date-badge py-1 px-3 rounded-pill">
                            <?= $showDate ?? "Không xác định"; ?>
                        </div>
                    </div>

                    <!-- Thông tin vé -->
                    <div class="ticket-details mb-3">
                        <div class="row g-0 mb-2">
                            <div class="col-5 text-muted">Mã hóa đơn:</div>
                            <div class="col-7 fw-bold text-dark"><?= $maHD; ?></div>
                        </div>
                        <div class="row g-0 mb-2">
                            <div class="col-5 text-muted">Khách hàng:</div>
                            <div class="col-7 fw-bold text-dark"><?= $customerName; ?></div>
                        </div>
                        <div class="row g-0 mb-2">
                            <div class="col-5 text-muted">Email:</div>
                            <div class="col-7 fw-bold text-dark"><?= $customerEmail; ?></div>
                        </div>
                        <div class="row g-0 mb-2">
                            <div class="col-5 text-muted">Phương thức thanh toán:</div>
                            <div class="col-7 fw-bold text-dark"><?= htmlspecialchars($hd['PhuongThucThanhToan'] ?? 'Chưa xác định'); ?></div>
                        </div>
                        <div class="row g-0 mb-2">
                            <div class="col-5 text-muted">Ngày mua:</div>
                            <div class="col-7 fw-bold text-dark"><?= date('d-m-Y H:i', strtotime($hd['NgayLapHD'])); ?></div>
                        </div>
                        <div class="row g-0 mb-2">
                            <div class="col-5 text-muted">Khu vực:</div>
                            <div class="col-7 fw-bold text-dark"><?= $khuVucName; ?></div>
                        </div>
                        <div class="row g-0 mb-2">
                            <div class="col-5 text-muted">Rạp:</div>
                            <div class="col-7 fw-bold text-dark"><?= $rapName; ?></div>
                        </div>
                        <div class="row g-0 mb-2">
                            <div class="col-5 text-muted">Phòng chiếu:</div>
                            <div class="col-7 fw-bold text-dark"><?= $room ? htmlspecialchars($room['TenPhong']) : "Không xác định"; ?></div>
                        </div>
                        <div class="row g-0 mb-2">
                            <div class="col-5 text-muted">Giờ chiếu:</div>
                            <div class="col-7 fw-bold text-dark"><?= $showTime ?? "Không xác định"; ?></div>
                        </div>
                    </div>

                    <!-- Phần ghế -->
                    <div class="seats-section mb-3">
                        <h5 class="section-title fw-bold">
                            <i class="fas fa-chair me-2"></i>Ghế
                        </h5>
                        <div class="seat-list">
                            <?php if (!empty($seatDetails)): ?>
                                <?php foreach ($seatDetails as $seat): ?>
                                    <div class="seat-item d-flex justify-content-between align-items-center p-2 rounded mb-2">
                                        <div class="seat-info">
                                            <span class="fw-bold"><?= $seat['TenGhe']; ?></span>
                                            <small class="text-muted"> (<?= $seat['LoaiGhe']; ?>)</small>
                                        </div>
                                        <div class="seat-price fw-bold text-primary">
                                            <?= number_format($seat['GiaGhe'], 0, ',', '.'); ?> VNĐ
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-muted">Không có ghế nào được chọn.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Phần combo -->
                    <?php if (!empty($selectedCombos)): ?>
                        <div class="combo-section mb-3">
                            <h5 class="section-title fw-bold">
                                <i class="fas fa-utensils me-2"></i>Combo
                            </h5>
                            <div class="combo-list">
                                <?php foreach ($selectedCombos as $combo): ?>
                                    <div class="combo-item d-flex justify-content-between align-items-center p-2 rounded mb-2">
                                        <div class="d-flex align-items-center">
                                            <img src="../uploads/combo-imgs/<?= $combo['Anh']; ?>" alt="<?= $combo['TenCombo']; ?>" class="me-3 rounded">
                                            <span class="fw-bold"><?= $combo['TenCombo']; ?> x <?= $combo['SoLuong']; ?></span>
                                        </div>
                                        <span class="fw-bold text-success"><?= number_format($combo['GiaCombo'] * $combo['SoLuong'], 0, ',', '.'); ?> VNĐ</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Phần tổng tiền và mã QR/Barcode -->
        <div class="row g-0 p-4">
            <!-- Tổng tiền -->
            <div class="col-md-6 total-section p-3 rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">TỔNG CỘNG:</h5>
                    <h5 class="fw-bold mb-0"><?= number_format($hd['TongTien'], 0, ',', '.'); ?> VNĐ</h5>
                </div>
            </div>

            <!-- Mã QR và Barcode -->
            <div class="col-md-6 codes-section p-3 text-center rounded">
                <h5 class="section-title fw-bold mb-3">
                    <i class="fas fa-qrcode me-2"></i>Mã QR & Barcode
                </h5>
                <div class="d-flex justify-content-around align-items-center">
                    <div>
                        <img src="data:image/png;base64,<?= $qrCodeBase64; ?>" alt="QR Code">
                        <p class="mt-2 mb-0 text-muted">Mã QR</p>
                    </div>
                    <div>
                        <img src="data:image/png;base64,<?= $barcodeBase64; ?>" alt="Barcode">
                        <p class="mt-2 mb-0 text-muted">Barcode</p>
                    </div>
                </div>
            </div>
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
    max-width: 900px;
    margin: 0 auto;
    border-radius: 15px;
}

@keyframes gradientBorder {
    0% { border-image-source: linear-gradient(45deg, #ff6f61, #ffeb3b, #4caf50, #2196f3, #ff6f61); }
    25% { border-image-source: linear-gradient(45deg, #ffeb3b, #4caf50, #2196f3, #ff6f61, #ffeb3b); }
    50% { border-image-source: linear-gradient(45deg, #4caf50, #2196f3, #ff6f61, #ffeb3b, #4caf50); }
    75% { border-image-source: linear-gradient(45deg, #2196f3, #ff6f61, #ffeb3b, #4caf50, #2196f3); }
    100% { border-image-source: linear-gradient(45deg, #ff6f61, #ffeb3b, #4caf50, #2196f3, #ff6f61); }
}

.card-header {
    background: linear-gradient(135deg, #ff6f61, #ffeb3b);
    color: white;
    position: relative;
}

.ticket-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.1;
    background: url('https://www.transparenttextures.com/patterns/cinema.png');
    border-radius: none;
}

.movie-poster-section {
    background: linear-gradient(135deg, #f5f5f5, #e0e0e0);
}

.position-relative {
    width: 100%;
    max-height: 500px;
    overflow: hidden;
    border-radius: 10px;
}

.position-relative img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.position-relative:hover .overlay {
    opacity: 1;
}

.position-relative:hover img {
    transform: scale(1.05);
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.ticket-details .row {
    padding: 4px 0;
    border-bottom: 1px dashed #e0e0e0;
}

.ticket-details .row:last-child {
    border-bottom: none;
}

.date-badge {
    background: linear-gradient(45deg, #ff6f61, #ff8a65);
    color: white;
    font-weight: bold;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s;
}

.date-badge:hover {
    transform: scale(1.1);
}

.seats-section, .combo-section {
    max-height: 150px;
    overflow-y: auto;
}

.seat-item, .combo-item {
    background: #e8f0fe;
    transition: transform 0.3s, box-shadow 0.3s;
}

.seat-item:hover, .combo-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.combo-item {
    background: #e6f4ea;
}

.combo-item img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 5px;
    transition: transform 0.3s;
}

.combo-item img:hover {
    transform: scale(1.1);
}

.total-section {
    background: linear-gradient(45deg, #ff6f61, #ff8a65);
    color: white;
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

.codes-section img {
    border-radius: 5px;
}

.section-title i {
    color: #2196f3;
}

.section-title i.fa-utensils {
    color: #4caf50;
}
</style>