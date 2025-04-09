<?php
require '../../../config/function.php';
include('../../includes/header.php');
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    redirect('../sign-in.php', 'error', 'Vui lòng đăng nhập để quản lý combo.');
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
        <h2>Thêm combo</h2>
        <div class="text-end mb-4">
            <a class="btn btn-secondary" href="../../combo.php">Quay lại</a>
        </div>

        <form id="addComboForm" action="../../controllers/combo-controller.php" method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 m-auto">
                    <!-- Tên combo -->
                    <div class="form-group mb-3">
                        <label for="tencombo">Tên combo (<span class="text-danger">*</span>)</label>
                        <input type="text" class="form-control" id="tencombo" name="tencombo"
                            value="<?php echo isset($formData['tencombo']) ? htmlspecialchars($formData['tencombo']) : ''; ?>">
                        <?php if (isset($messages['tencombo'])): ?>
                            <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['tencombo']) ?></small>
                        <?php endif; ?>
                    </div>

                    <!-- Mô tả -->
                    <div class="form-group mb-3">
                        <label for="mota">Mô tả</label>
                        <textarea class="form-control" id="mota" name="mota" rows="3"><?php echo isset($formData['mota']) ? htmlspecialchars($formData['mota']) : ''; ?></textarea>
                    </div>

                    <!-- Giá -->
                    <div class="form-group mb-3">
                        <label for="giacombo">Giá (<span class="text-danger">*</span>)</label>
                        <input type="number" class="form-control" id="giacombo" name="giacombo" step="0.01"
                            value="<?php echo isset($formData['giacombo']) ? htmlspecialchars($formData['giacombo']) : ''; ?>">
                        <?php if (isset($messages['giacombo'])): ?>
                            <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['giacombo']) ?></small>
                        <?php endif; ?>
                    </div>

                    <!-- Ảnh -->
                    <div class="form-group mb-3">
                        <label for="anh">Ảnh</label>
                        <input type="file" class="form-control" id="anh" name="anh" accept="image/*">
                    </div>

                    <!-- Trạng thái -->
                    <div class="form-group mb-3">
                        <label for="status">Trạng thái</label>
                        <select class="form-select" id="status" name="status">
                            <option value="1" <?php echo (isset($formData['status']) && $formData['status'] == '1') ? 'selected' : ''; ?>>Online</option>
                            <option value="0" <?php echo (isset($formData['status']) && $formData['status'] == '0') ? 'selected' : ''; ?>>Offline</option>
                        </select>
                    </div>

                    <button type="submit" name="saveCombo" class="btn bg-gradient-info px-5 mt-3">Lưu</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>