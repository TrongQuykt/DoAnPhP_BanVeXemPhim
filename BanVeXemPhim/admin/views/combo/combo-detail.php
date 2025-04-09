<?php
require '../../../config/function.php';
include('../../includes/header.php');
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    redirect('../sign-in.php', 'error', 'Vui lòng đăng nhập để quản lý combo.');
}

$maCombo = check_valid_ID('id');
$combo = getByID('combo', 'MaCombo', $maCombo);
if ($combo['status'] !== 200) {
    redirect('combo.php', 'error', 'Combo không tồn tại.');
}
?>

<div id="toast"></div>
<?php alertMessage() ?>
<div class="row">
    <div class="col-xl-12 col-lg-12 mx-auto">
        <h2>Chi tiết combo</h2>
        <div class="text-end mb-4">
            <a class="btn btn-secondary" href="combo.php">Quay lại</a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Tên combo:</strong> <?= htmlspecialchars($combo['data']['TenCombo']) ?></p>
                        <p><strong>Mô tả:</strong> <?= htmlspecialchars($combo['data']['MoTa']) ?: 'Không có mô tả' ?></p>
                        <p><strong>Giá:</strong> <?= number_format($combo['data']['GiaCombo'], 0, ',', '.') ?> VNĐ</p>
                        <p><strong>Trạng thái:</strong> <?= $combo['data']['TrangThai'] == 1 ? 'Online' : 'Offline' ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Ảnh:</strong></p>
                        <?php if (!empty($combo['data']['Anh'])): ?>
                            <img src="../uploads/combo-imgs/<?= htmlspecialchars($combo['data']['Anh']) ?>" alt="Combo Image" width="200">
                        <?php else: ?>
                            <p>Không có ảnh</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>