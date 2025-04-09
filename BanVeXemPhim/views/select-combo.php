<?php
require_once("../config/function.php");
$title = 'Chọn combo bắp nước';
include('../includes/header.php');

ob_start();
$isLoggedIn = isset($_SESSION['NDloggedIn']) && $_SESSION['NDloggedIn'] == TRUE;

if (!$isLoggedIn) {
    redirect('../login.php', 'error', 'Vui lòng đăng nhập để tiếp tục');
}

if (!isset($_POST['seatsInput'])) {
    redirect('../index.php', 'error', 'Dữ liệu không hợp lệ');
}

$selectedSeats = $_POST['seatsInput'] ?? '';
$data = json_decode($selectedSeats, true);
$_SESSION['bookingData'] = $data; // Lưu thông tin ghế vào session

// Lấy danh sách combo
global $conn;
$queryCombos = "SELECT * FROM combo WHERE TrangThai = 1";
$combos = mysqli_query($conn, $queryCombos);
?>

<div class="container my-5">
    <h4 class="text-center mb-4 text-uppercase fw-bold text-primary">Chọn Combo Bắp Nước</h4>
    <form id="comboForm" action="payment.php" method="POST">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
            <?php while ($combo = mysqli_fetch_assoc($combos)): ?>
                <div class="col">
                    <div class="card shadow-sm border-0 rounded-lg h-100 text-center">
                        <div class="position-relative d-flex align-items-center justify-content-center" style="height: 150px; overflow: hidden;">
                            <img src="../uploads/combo-imgs/<?= htmlspecialchars($combo['Anh']) ?>" 
                                class="card-img-top" 
                                alt="<?= htmlspecialchars($combo['TenCombo']) ?>" 
                                style="max-height: 100%; width: auto; max-width: 100%; object-fit: cover;">
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2 px-2 py-1 fs-6">
                                <?= number_format($combo['GiaCombo'], 0, ',', '.') ?> VNĐ
                            </span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title fw-bold text-dark"> <?= htmlspecialchars($combo['TenCombo']) ?> </h6>
                            <p class="card-text text-muted flex-grow-1 small"> <?= htmlspecialchars($combo['MoTa']) ?> </p>
                            <div class="input-group justify-content-center mt-auto">
                                <button type="button" class="btn btn-outline-danger px-2 py-1" onclick="updateQuantity(this, -1)">-</button>
                                <input type="number" name="combo[<?= $combo['MaCombo'] ?>]" class="form-control text-center combo-quantity mx-1" value="0" min="0" readonly>
                                <button type="button" class="btn btn-outline-success px-2 py-1" onclick="updateQuantity(this, 1)">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-4">
            <button type="button" class="btn btn-outline-secondary px-4 py-2 me-2" onclick="skipCombo()">Bỏ qua</button>
            <button type="submit" class="btn btn-primary px-5 py-2 me-2">Tiếp tục</button>
        </div>
    </form>
</div>

<style>
    .card:hover {
        transform: translateY(-3px);
        transition: all 0.3s ease-in-out;
    }
    .combo-quantity {
        width: 50px;
        font-size: 14px;
    }
    .badge {
        font-size: 12px;
    }
</style>

<!-- <script>
function updateQuantity(button, change) {
    const input = button.parentElement.querySelector('.combo-quantity');
    let value = parseInt(input.value);
    value = Math.max(0, value + change);
    input.value = value;
}

function skipCombo() {
    document.getElementById('comboForm').submit();
}
</script> -->

<script>
function updateQuantity(button, change) {
    const input = button.parentElement.querySelector('.combo-quantity');
    let value = parseInt(input.value);
    value = Math.max(0, value + change);
    input.value = value;
}

function skipCombo() {
    document.getElementById('comboForm').submit();
}
</script>

<?php include('../includes/footer.php'); ?>