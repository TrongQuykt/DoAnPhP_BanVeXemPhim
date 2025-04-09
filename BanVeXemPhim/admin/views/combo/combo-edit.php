<?php
require '../../../config/function.php';
include('../../includes/header.php');
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    redirect('sign-in.php', 'error', 'Vui lòng đăng nhập để quản lý combo.');
}

$maCombo = check_valid_ID('id');
$combo = getByID('combo', 'MaCombo', $maCombo);
if ($combo['status'] !== 200) {
    redirect('admin/combo.php', 'error', 'Combo không tồn tại.');
}

$messages = isset($_SESSION['messages']) ? $_SESSION['messages'] : [];
$formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['messages']);
unset($_SESSION['form_data']);
?>

<div id="toast"></div>
<?php alertMessage() ?>
<div class="row">
    <div class="col-xl-12 col-lg-12 mx-auto">
        <h2>Chỉnh sửa combo</h2>
        <div class="text-end mb-4">
            <a class="btn btn-secondary" href="../../combo.php">Quay lại</a>
        </div>

        <form id="editComboForm" action="../../controllers/combo-controller.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $maCombo ?>">
            <input type="hidden" name="old_anh" value="<?= htmlspecialchars($combo['data']['Anh']) ?>">
            <div class="row">
                <div class="col-md-6 m-auto">
                    <!-- Tên combo -->
                    <div class="form-group mb-3">
                        <label for="tencombo">Tên combo (<span class="text-danger">*</span>)</label>
                        <input type="text" class="form-control" id="tencombo" name="tencombo"
                            value="<?php echo isset($formData['tencombo']) ? htmlspecialchars($formData['tencombo']) : htmlspecialchars($combo['data']['TenCombo']); ?>">
                        <?php if (isset($messages['tencombo'])): ?>
                            <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['tencombo']) ?></small>
                        <?php endif; ?>
                    </div>

                    <!-- Mô tả -->
                    <div class="form-group mb-3">
                        <label for="mota">Mô tả</label>
                        <textarea class="form-control" id="mota" name="mota" rows="3"><?php echo isset($formData['mota']) ? htmlspecialchars($formData['mota']) : htmlspecialchars($combo['data']['MoTa']); ?></textarea>
                    </div>

                    <!-- Giá -->
                    <div class="form-group mb-3">
                        <label for="giacombo">Giá (<span class="text-danger">*</span>)</label>
                        <input type="number" class="form-control" id="giacombo" name="giacombo" step="0.01"
                            value="<?php echo isset($formData['giacombo']) ? htmlspecialchars($formData['giacombo']) : htmlspecialchars($combo['data']['GiaCombo']); ?>">
                        <?php if (isset($messages['giacombo'])): ?>
                            <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['giacombo']) ?></small>
                        <?php endif; ?>
                    </div>

                    <!-- Ảnh -->
                    <div class="form-group mb-3">
                        <label for="anh">Ảnh</label>
                        <input type="file" class="form-control" id="anh" name="anh" accept="image/*">
                        <?php if (!empty($combo['data']['Anh'])): ?>
                            <img src="../uploads/combo-imgs/<?= htmlspecialchars($combo['data']['Anh']) ?>" alt="Combo Image" width="100" class="mt-2">
                        <?php endif; ?>
                    </div>

                    <!-- Trạng thái -->
                    <div class="form-group mb-3">
                        <label for="status">Trạng thái</label>
                        <select class="form-select" id="status" name="status">
                            <option value="1" <?php echo ($combo['data']['TrangThai'] == 1) ? 'selected' : ''; ?>>Online</option>
                            <option value="0" <?php echo ($combo['data']['TrangThai'] == 0) ? 'selected' : ''; ?>>Offline</option>
                        </select>
                    </div>

                    <button type="submit" name="updateCombo" class="btn bg-gradient-info px-5 mt-3">Cập nhật</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>