<?php
session_start();
require_once("../config/function.php");
date_default_timezone_set('Asia/Ho_Chi_Minh');

$title = 'Thanh toán';
include('../includes/header.php');

ob_start();
if (!isset($_SESSION['bookingData'])) {
    redirect('../index.php', 'error', 'Dữ liệu không hợp lệ');
}

$data = $_SESSION['bookingData'];
$maGhe = explode(',', $data['MaGhe'] ?? '');
$seatNames = [];
$seatDetails = [];
$totalPrice = 0;

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

$comboData = $_POST['combo'] ?? [];
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
$_SESSION['comboData'] = $comboData;
$_SESSION['finalPrice'] = $totalPrice; // Lưu tổng tiền vào session

$maPhim = $data['MaPhim'] ?? '';
$queryMovie = "SELECT TenPhim, Anh FROM Phim WHERE MaPhim = '$maPhim'";
$resultMovie = mysqli_query($conn, $queryMovie);
$movie = mysqli_fetch_assoc($resultMovie);

$maSuatChieu = $data['MaSuatChieu'] ?? '';
$queryShowtime = "SELECT GioChieu, MaRap FROM SuatChieu WHERE MaSuatChieu = '$maSuatChieu'";
$resultShowtime = mysqli_query($conn, $queryShowtime);
$showtime = mysqli_fetch_assoc($resultShowtime);

$maPhong = $data['MaPhong'] ?? '';
$queryRoom = "SELECT TenPhong FROM Phong WHERE MaPhong = '$maPhong'";
$resultRoom = mysqli_query($conn, $queryRoom);
$room = mysqli_fetch_assoc($resultRoom);

$maRap = $showtime['MaRap'] ?? '';
$rap = getByID('RapChieuPhim', 'MaRap', $maRap);
$rapName = ($rap['status'] == 200) ? htmlspecialchars($rap['data']['TenRap']) : "Không xác định";

$khuVuc = ($rap['status'] == 200) ? getByID('KhuVuc', 'MaKhuVuc', $rap['data']['MaKhuVuc']) : ['status' => 404];
$khuVucName = ($khuVuc['status'] == 200) ? htmlspecialchars($khuVuc['data']['TenKhuVuc']) : "Không xác định";

$showtimeDate = new DateTime($showtime['GioChieu']);
$showDate = $showtimeDate->format('d-m-Y');
$showTime = $showtimeDate->format('H:i');

// Hiển thị thông báo lỗi nếu có
if (isset($_SESSION['error'])) {
    $errorMessage = htmlspecialchars($_SESSION['error']);
    echo '<div class="container my-3"><div class="alert alert-danger">' . $errorMessage . '</div></div>';
    unset($_SESSION['error']); // Xóa thông báo sau khi hiển thị
}
?>

<div class="container my-5">
    <h4 class="text-center mb-4 text-uppercase fw-bold text-primary">Thanh Toán</h4>
    <div class="row justify-content-center">
        <!-- Thông tin vé -->
        <div class="col-md-6">
            <div class="ticket">
                <div class="ticket-header">
                    <h5 class="text-center"><?= htmlspecialchars($movie['TenPhim']) ?></h5>
                </div>
                <div class="ticket-body">
                    <div class="ticket-left">
                        <div class="ticket-poster">
                            <img src="../uploads/film-imgs/<?= htmlspecialchars($movie['Anh']) ?>" alt="<?= htmlspecialchars($movie['TenPhim']) ?>" class="img-fluid">
                        </div>
                    </div>
                    <div class="ticket-right">
                        <div class="ticket-info">
                            <p><span class="label">Rạp:</span> <?= $rapName ?></p>
                            <p><span class="label">Khu vực:</span> <?= $khuVucName ?></p>
                            <p><span class="label">Phòng:</span> <?= $room['TenPhong'] ?></p>
                            <p><span class="label">Giờ chiếu:</span> <?= $showDate ?> | <?= $showTime ?></p>
                            <p><span class="label">Ghế:</span> <?= implode(', ', $seatNames) ?> (<?= number_format(count($seatNames) * $seatDetails[0]['GiaGhe'], 0, ',', '.') ?> VNĐ)</p>
                        </div>
                        <?php if (!empty($selectedCombos)): ?>
                            <div class="combo-section">
                                <p><span class="label">Combo:</span></p>
                                <ul class="combo-list">
                                    <?php foreach ($selectedCombos as $combo): ?>
                                        <li class="combo-item">
                                            <img src="../uploads/combo-imgs/<?= htmlspecialchars($combo['Anh']) ?>" alt="<?= htmlspecialchars($combo['TenCombo']) ?>" class="combo-img">
                                            <span class="combo-text"><?= $combo['TenCombo'] ?> x <?= $combo['SoLuong'] ?> (<?= number_format($combo['GiaCombo'] * $combo['SoLuong'], 0, ',', '.') ?> VNĐ)</span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <div class="total-price-box">
                            <p><span class="label">Tổng tiền:</span> <?= number_format($totalPrice, 0, ',', '.') ?> VNĐ</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phương thức thanh toán -->
        <div class="col-md-4">
            <div class="card payment-card">
                <div class="card-header">
                    <h5 class="mb-0">Chọn phương thức thanh toán</h5>
                </div>
                <div class="card-body text-center">
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-circle"></i> Vui lòng hoàn tất thanh toán trong vòng 15 phút, nếu không giao dịch sẽ tự động hủy.
                    </div>
                    <form id="paymentForm" action="process_payment.php" method="POST">
                        <input type="hidden" name="totalPrice" value="<?= $totalPrice ?>">
                        <input type="hidden" name="orderId" value="<?= uniqid() ?>">
                        <div class="mb-3">
                            <button type="submit" name="payment_method" value="vnpay" class="btn btn-primary btn-lg w-100">
                                Thanh toán qua VNPAY
                            </button>
                        </div>
                        <div class="mb-3">
                            <button type="submit" name="payment_method" value="momo" class="btn btn-danger btn-lg w-100">
                                Thanh toán qua MOMO
                            </button>
                        </div>
                        <div class="mb-3">
                            <button type="submit" name="payment_method" value="paypal" class="btn btn-warning btn-lg w-100">
                                Thanh toán qua PayPal
                            </button>
                        </div>
                        <div class="mb-3">
                            <button type="submit" name="payment_method" value="stripe" class="btn btn-success btn-lg w-100">
                                Thanh toán qua Stripe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .ticket {
        background: #fff;
        border: 2px dashed #ff0000;
        border-radius: 15px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        max-width: 600px;
        margin: 0 auto;
        padding: 25px;
        overflow: hidden;
    }

    .ticket-header h5 {
        font-size: 1.9rem;
        font-weight: 700;
        color: #d32f2f;
        margin-bottom: 20px;
        text-transform: uppercase;
        text-align: center;
        letter-spacing: 1px;
    }

    .ticket-body {
        display: flex;
        align-items: stretch;
        gap: 20px;
    }

    .ticket-left {
        flex: 0 0 30%;
    }

    .ticket-right {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .ticket-poster img {
        width: 100%;
        max-width: 150px;
        border: 3px solid #d32f2f;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .ticket-info p {
        font-size: 0.95rem;
        margin: 8px 0;
        color: #444;
        line-height: 1.5;
    }

    .ticket-info .label {
        font-weight: 600;
        color: #222;
    }

    .combo-section {
        margin-top: 15px;
    }

    .combo-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .combo-item {
        display: flex;
        align-items: center;
        background: #f5f5f5;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
        gap: 10px;
    }

    .combo-img {
        width: 50px;
        height: 50px;
        border: 2px solid #d32f2f;
        border-radius: 5px;
        object-fit: cover;
    }

    .combo-text {
        font-size: 0.9rem;
        color: #333;
        flex: 1;
    }

    .total-price-box {
        background: #ffebee;
        border-radius: 8px;
        padding: 12px;
        margin-top: 20px;
        text-align: center;
    }

    .total-price-box p {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
        color: #d32f2f;
    }

    .payment-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        background: #fff;
    }

    .payment-card .card-header {
        background: #f9f9f9;
        border-bottom: none;
        text-align: center;
        padding: 15px;
        font-weight: 600;
        color: #333;
    }

    .payment-card .btn-primary {
        background-color: #1976d2;
        border-color: #1976d2;
        font-weight: 600;
        padding: 12px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .payment-card .btn-primary:hover {
        background-color: #1565c0;
        border-color: #1565c0;
        transform: translateY(-2px);
    }

    .payment-card .btn-danger {
        background-color: #e91e63;
        border-color: #e91e63;
        font-weight: 600;
        padding: 12px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .payment-card .btn-danger:hover {
        background-color: #c2185b;
        border-color: #c2185b;
        transform: translateY(-2px);
    }

    .payment-card .btn-warning {
        background-color: #0070ba;
        border-color: #0070ba;
        color: #fff;
        font-weight: 600;
        padding: 12px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .payment-card .btn-warning:hover {
        background-color: #003087;
        border-color: #003087;
        transform: translateY(-2px);
    }
    .payment-card .btn-success {
    background-color: #6772e5;
    border-color: #6772e5;
    color: #fff;
    font-weight: 600;
    padding: 12px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.payment-card .btn-success:hover {
    background-color: #5469d4;
    border-color: #5469d4;
    transform: translateY(-2px);
}
</style>

<script>
    document.getElementById('paymentForm').addEventListener('submit', function(event) {
        console.log('Form submitted'); // Ghi log để kiểm tra sự kiện submit
    });
</script>

<?php include('../includes/footer.php'); ?>