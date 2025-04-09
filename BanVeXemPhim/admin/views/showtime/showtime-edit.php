<?php
require '../../../config/function.php';
include('../../includes/header.php');
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    redirect('sign-in.php', 'error', 'Vui lòng đăng nhập');
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
        <h2>Cập nhật suất chiếu</h2>
        <div class="text-end mb-4">
            <a class="btn btn-secondary" href="../../showtime.php">Quay lại</a>
        </div>

        <form id="editShowtimeForm" action="../../controllers/showtime-controller.php" method="post">
            <?php
            $id_result = check_valid_ID('id');
            if (!is_numeric($id_result)) {
                echo '<h5>' . $id_result . '</h5>';
                return false;
            }
            $sc = getByID('SuatChieu', 'MaSuatChieu', check_valid_ID('id'));
            if ($sc['status'] == 200) {
                // Lấy thông tin rạp và khu vực của suất chiếu
                $phong = getByID('Phong', 'MaPhong', $sc['data']['MaPhong']);
                $rap = getByID('rapchieuphim', 'MaRap', $sc['data']['MaRap']);
                $khuVuc = getByID('khuvuc', 'MaKhuVuc', $rap['data']['MaKhuVuc']);

                // Kiểm tra dữ liệu rạp và khu vực
                if ($rap['status'] != 200) {
                    echo '<h5>Lỗi: Không tìm thấy rạp với MaRap = ' . $sc['data']['MaRap'] . '</h5>';
                    return false;
                }
                if ($khuVuc['status'] != 200) {
                    echo '<h5>Lỗi: Không tìm thấy khu vực với MaKhuVuc = ' . $rap['data']['MaKhuVuc'] . '</h5>';
                    return false;
                }
            ?>
                <input type="hidden" name="masc" value="<?= $sc['data']['MaSuatChieu'] ?>">
                <div class="row">
                    <div class="col-md-4 m-auto">
                        <!-- Giờ chiếu -->
                        <div class="form-group mb-3">
                            <label for="giochieu">Giờ chiếu</label>
                            <input type="datetime-local" class="form-control" id="giochieu" name="giochieu"
                                value="<?php echo isset($formData['giochieu']) ? htmlspecialchars($formData['giochieu']) : $sc['data']['GioChieu']; ?>">
                            <?php if (isset($messages['giochieu'])): ?>
                                <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['giochieu']) ?></small>
                            <?php endif; ?>
                        </div>

                        <!-- Chọn khu vực -->
                        <div class="form-group mb-3">
                            <label for="makhuvuc">Khu vực</label>
                            <select class="form-control" id="makhuvuc" name="makhuvuc">
                                <option value="">Chọn khu vực</option>
                                <?php
                                $khuVucs = getAll('khuvuc');
                                foreach ($khuVucs as $khuVucItem): ?>
                                    <option value="<?php echo htmlspecialchars($khuVucItem['MaKhuVuc']); ?>"
                                        <?php echo (isset($formData['makhuvuc']) && $formData['makhuvuc'] == $khuVucItem['MaKhuVuc']) ||
                                            (!isset($formData['makhuvuc']) && $khuVuc['data']['MaKhuVuc'] == $khuVucItem['MaKhuVuc']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($khuVucItem['TenKhuVuc']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($messages['makhuvuc'])): ?>
                                <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['makhuvuc']) ?></small>
                            <?php endif; ?>
                        </div>

                        <!-- Chọn rạp chiếu phim -->
                        <div class="form-group mb-3">
                            <label for="marap">Rạp chiếu phim</label>
                            <select class="form-control" id="marap" name="marap">
                                <option value="">Chọn rạp chiếu phim</option>
                                <?php
                                $raps = getAllByCondition('rapchieuphim', 'MaKhuVuc', $khuVuc['data']['MaKhuVuc']);
                                if (!empty($raps)) {
                                    foreach ($raps as $rapItem): ?>
                                        <option value="<?php echo htmlspecialchars($rapItem['MaRap']); ?>"
                                            <?php echo (isset($formData['marap']) && $formData['marap'] == $rapItem['MaRap']) ||
                                                (!isset($formData['marap']) && $rap['data']['MaRap'] == $rapItem['MaRap']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($rapItem['TenRap']); ?>
                                        </option>
                                    <?php endforeach;
                                } else {
                                    echo '<option value="">Không có rạp nào</option>';
                                }
                                ?>
                            </select>
                            <?php if (isset($messages['marap'])): ?>
                                <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['marap']) ?></small>
                            <?php endif; ?>
                        </div>

                        <!-- Chọn phòng -->
                        <div class="form-group mb-3">
                            <label for="maphong">Tên phòng (<span class="text-danger">*</span>)</label>
                            <select class="form-control" id="maphong" name="maphong">
                                <option value="">Chọn phòng</option>
                                <?php
                                $rooms = getAllByCondition('Phong', 'MaRap', $rap['data']['MaRap']);
                                if (!empty($rooms)) {
                                    foreach ($rooms as $room): ?>
                                        <option value="<?php echo htmlspecialchars($room['MaPhong']); ?>"
                                            <?php echo (isset($formData['maphong']) && $formData['maphong'] == $room['MaPhong']) ||
                                                (!isset($formData['maphong']) && $sc['data']['MaPhong'] == $room['MaPhong']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($room['TenPhong']); ?>
                                        </option>
                                    <?php endforeach;
                                } else {
                                    echo '<option value="">Không có phòng nào</option>';
                                }
                                ?>
                            </select>
                            <?php if (isset($messages['maphong'])): ?>
                                <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['maphong']) ?></small>
                            <?php endif; ?>
                        </div>

                        <!-- Chọn phim -->
                        <div class="form-group mb-3">
                            <label for="maphim">Tên phim</label>
                            <select class="form-control" id="maphim" name="maphim">
                                <option value="">Chọn tên phim</option>
                                <?php
                                $films = getAll('Phim');
                                foreach ($films as $film): ?>
                                    <option value="<?php echo htmlspecialchars($film['MaPhim']); ?>"
                                        <?php echo (isset($formData['maphim']) && $formData['maphim'] == $film['MaPhim']) ||
                                            (!isset($formData['maphim']) && $sc['data']['MaPhim'] == $film['MaPhim']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($film['TenPhim']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($messages['maphim'])): ?>
                                <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['maphim']) ?></small>
                            <?php endif; ?>
                        </div>

                        <!-- Trạng thái -->
                        <div class="form-group mb-3">
                            <label for="status">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="1" <?= $sc['data']['TrangThai'] == 1 ? 'selected' : ''; ?>>Online</option>
                                <option value="0" <?= $sc['data']['TrangThai'] == 0 ? 'selected' : ''; ?>>Offline</option>
                            </select>
                        </div>

                        <button type="submit" name="editsc" class="btn bg-gradient-info px-5 mt-3">Lưu</button>
                    </div>
                </div>
            <?php
            } else {
                echo '<h5>' . $sc['message'] . '</h5>';
            }
            ?>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const khuVucSelect = document.getElementById('makhuvuc');
    const rapSelect = document.getElementById('marap');
    const phongSelect = document.getElementById('maphong');

    khuVucSelect.addEventListener('change', function() {
        const maKhuVuc = this.value;
        rapSelect.innerHTML = '<option value="">Chọn rạp chiếu phim</option>';
        rapSelect.disabled = true;
        phongSelect.innerHTML = '<option value="">Chọn phòng</option>';
        phongSelect.disabled = true;

        if (maKhuVuc) {
            fetch(`../../config/get_rapchieuphim.php?khuVuc=${maKhuVuc}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200 && data.data.length > 0) {
                        data.data.forEach(rap => {
                            const option = document.createElement('option');
                            option.value = rap.MaRap;
                            option.textContent = rap.TenRap;
                            rapSelect.appendChild(option);
                        });
                        rapSelect.disabled = false;
                    }
                })
                .catch(error => console.error('Lỗi khi lấy danh sách rạp:', error));
        }
    });

    rapSelect.addEventListener('change', function() {
        const maRap = this.value;
        phongSelect.innerHTML = '<option value="">Chọn phòng</option>';
        phongSelect.disabled = true;

        if (maRap) {
            fetch(`../../config/get_phong_by_rap.php?maRap=${maRap}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200 && data.data.length > 0) {
                        data.data.forEach(phong => {
                            const option = document.createElement('option');
                            option.value = phong.MaPhong;
                            option.textContent = phong.TenPhong;
                            phongSelect.appendChild(option);
                        });
                        phongSelect.disabled = false;
                    }
                })
                .catch(error => console.error('Lỗi khi lấy danh sách phòng:', error));
        }
    });
});
</script>

<?php include('../../includes/footer.php'); ?>