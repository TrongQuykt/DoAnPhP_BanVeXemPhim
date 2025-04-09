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

// Lấy danh sách khu vực
$queryKhuVuc = "SELECT * FROM khuvuc WHERE TrangThai = 1";
$resultKhuVuc = $conn->query($queryKhuVuc);
$khuVucList = [];
while ($khuVuc = $resultKhuVuc->fetch_assoc()) {
    $khuVucList[] = $khuVuc;
}

// Lấy danh sách rạp chiếu phim cho từng khu vực
$rapChieuData = [];
foreach ($khuVucList as $khuVuc) {
    $khuVucId = $khuVuc['MaKhuVuc'];
    $query = "SELECT * FROM rapchieuphim WHERE MaKhuVuc = ? AND TrangThai = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $khuVucId);
    $stmt->execute();
    $result = $stmt->get_result();

    $rapChieu = [];
    while ($row = $result->fetch_assoc()) {
        $rapChieu[] = $row;
    }
    $rapChieuData[$khuVucId] = $rapChieu;
    $stmt->close();
}

// Lấy tất cả phòng (không phụ thuộc vào rạp chiếu phim)
$queryPhong = "SELECT * FROM phong WHERE TrangThai = 1";
$resultPhong = $conn->query($queryPhong);
$phongList = [];
while ($phong = $resultPhong->fetch_assoc()) {
    $phongList[] = $phong;
}
?>

<div id="toast"></div>
<?php alertMessage() ?>
<div class="row">
    <div class="col-xl-12 col-lg-12 mx-auto">
        <h2>Thêm suất chiếu</h2>
        <div class="text-end mb-4">
            <a class="btn btn-secondary" href="../../showtime.php">Quay lại</a>
        </div>

        <form id="addShowtimeForm" action="../../controllers/showtime-controller.php" method="post">
            <div class="row">
                <div class="col-md-4 m-auto">
                    <!-- Chọn khoảng thời gian -->
                    <div class="form-group mb-3">
                        <label for="ngaybatdau">Từ ngày</label>
                        <input type="text" class="form-control" id="ngaybatdau" name="ngaybatdau"
                            value="<?php echo isset($formData['ngaybatdau']) ? htmlspecialchars($formData['ngaybatdau']) : ''; ?>"
                            placeholder="Chọn ngày bắt đầu" readonly>
                        <?php if (isset($messages['ngaybatdau'])): ?>
                            <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['ngaybatdau']) ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="form-group mb-3">
                        <label for="ngayketthuc">Đến ngày</label>
                        <input type="text" class="form-control" id="ngayketthuc" name="ngayketthuc"
                            value="<?php echo isset($formData['ngayketthuc']) ? htmlspecialchars($formData['ngayketthuc']) : ''; ?>"
                            placeholder="Chọn ngày kết thúc" readonly>
                        <?php if (isset($messages['ngayketthuc'])): ?>
                            <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['ngayketthuc']) ?></small>
                        <?php endif; ?>
                    </div>

                    <!-- Chọn khung giờ chiếu -->
                    <div class="form-group mb-3">
                        <label for="khunggio">Khung giờ chiếu</label>
                        <select class="form-control" id="khunggio" name="khunggio[]" multiple>
                            <option value="08:00">08:00</option>
                            <option value="09:00">09:00</option>
                            <option value="10:00">10:00</option>
                            <option value="11:00">11:00</option>
                            <option value="12:00">12:00</option>
                            <option value="13:00">13:00</option>
                            <option value="14:00">14:00</option>
                            <option value="15:00">15:00</option>
                            <option value="16:00">16:00</option>
                            <option value="17:00">17:00</option>
                            <option value="18:00">18:00</option>
                            <option value="19:00">19:00</option>
                            <option value="20:00">20:00</option>
                            <option value="21:00">21:00</option>
                            <option value="22:00">22:00</option>
                        </select>
                        <?php if (isset($messages['khunggio'])): ?>
                            <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['khunggio']) ?></small>
                        <?php endif; ?>
                    </div>

                    <!-- Chọn khu vực -->
                    <div class="form-group mb-3">
                        <label for="makhuvuc">Khu vực</label>
                        <select class="form-control" id="makhuvuc" name="makhuvuc">
                            <option value="">Chọn khu vực</option>
                            <?php
                            foreach ($khuVucList as $khuVuc) {
                                echo "<option value='{$khuVuc['MaKhuVuc']}' " . (isset($formData['makhuvuc']) && $formData['makhuvuc'] == $khuVuc['MaKhuVuc'] ? 'selected' : '') . ">{$khuVuc['TenKhuVuc']}</option>";
                            }
                            ?>
                        </select>
                        <?php if (isset($messages['makhuvuc'])): ?>
                            <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['makhuvuc']) ?></small>
                        <?php endif; ?>
                    </div>

                    <!-- Chọn rạp chiếu phim -->
                    <div class="form-group mb-3">
                        <label for="marap">Rạp chiếu phim</label>
                        <select class="form-control" id="marap" name="marap" disabled>
                            <option value="">Chọn rạp chiếu phim</option>
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
                            foreach ($phongList as $phong) {
                                echo "<option value='{$phong['MaPhong']}' " . (isset($formData['maphong']) && $formData['maphong'] == $phong['MaPhong'] ? 'selected' : '') . ">{$phong['TenPhong']}</option>";
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
                                    <?php echo (isset($formData['maphim']) && $formData['maphim'] == $film['MaPhim']) ? 'selected' : ''; ?>>
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
                            <option value="1"
                                <?php echo (isset($formData['status']) && $formData['status'] == '1') ? 'selected' : ''; ?>>
                                Online</option>
                            <option value="0"
                                <?php echo (isset($formData['status']) && $formData['status'] == '0') ? 'selected' : ''; ?>>
                                Offline</option>
                        </select>
                    </div>

                    <button type="submit" name="savesc" class="btn bg-gradient-info px-5 mt-3">Lưu</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo flatpickr cho trường ngày bắt đầu và ngày kết thúc
    flatpickr("#ngaybatdau", {
        dateFormat: "Y-m-d",
        minDate: "today",
        allowInput: true,
    });

    flatpickr("#ngayketthuc", {
        dateFormat: "Y-m-d",
        minDate: "today",
        allowInput: true,
    });

    // Truyền dữ liệu từ PHP sang JavaScript
    const rapChieuData = <?php echo json_encode($rapChieuData); ?>;

    // Lấy danh sách rạp dựa trên khu vực
    const khuVucSelect = document.getElementById('makhuvuc');
    const rapSelect = document.getElementById('marap');

    khuVucSelect.addEventListener('change', function() {
        const maKhuVuc = this.value;
        rapSelect.innerHTML = '<option value="">Chọn rạp chiếu phim</option>';
        rapSelect.disabled = true;

        if (maKhuVuc && rapChieuData[maKhuVuc]) {
            const rapChieuList = rapChieuData[maKhuVuc];
            rapChieuList.forEach(rap => {
                const option = document.createElement('option');
                option.value = rap.MaRap;
                option.textContent = rap.TenRap;
                rapSelect.appendChild(option);
            });
            rapSelect.disabled = false;
        } else {
            rapSelect.innerHTML = '<option value="">Không có rạp nào</option>';
        }
    });
});
</script>

<?php include('../../includes/footer.php'); ?>