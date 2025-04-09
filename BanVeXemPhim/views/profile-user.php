<?php
$title = 'Trang người dùng';
include('../includes/header.php');
require_once '../config/function.php';
$messages = isset($_SESSION['messages']) ? $_SESSION['messages'] : []; // Lấy lỗi từ session
$formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['messages']); // Xóa lỗi khỏi session sau khi hiển thị
unset($_SESSION['form_data']);
getUser();
?>

<style>
    /* Nền tổng thể cho phần nội dung chính */
    .main-content {
        background-color: #0d0d0d;
        min-height: 100vh;
        padding: 0;
        margin-top: 0;
    }

    /* Container chính */
    .profile-page .container {
        padding: 2rem 0;
    }

    /* Profile Card */
    .profile-page .profile-card {
        background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%);
        border-radius: 15px;
        border: 2px solid transparent;
        background-clip: padding-box;
        position: relative;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .profile-page .profile-card::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(90deg, #ff00ff, #00ffff);
        z-index: -1;
        border-radius: 15px;
    }

    .profile-page .profile-card:hover {
        transform: scale(1.05);
        box-shadow: 0 0 20px rgba(0, 255, 255, 0.5);
    }

    /* Ảnh đại diện */
    .profile-page .profile-picture-container {
        position: relative;
    }

    .profile-page .profile-picture-container img {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        width: 100%;
    }

    .profile-page .profile-picture-container:hover img {
        transform: scale(1.1);
        box-shadow: 0 0 15px rgba(0, 255, 255, 0.7) !important;
    }

    .profile-page .profile-picture-container:hover #camera {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    .profile-page #camera {
        transition: background 0.3s ease;
    }

    .profile-page #camera:hover {
        background: rgba(0, 255, 255, 0.6) !important;
    }

    /* Thanh tiến trình */
    .profile-page .progress {
        background: #2a2a2a;
        border-radius: 5px;
        overflow: visible;
        position: relative;
        height: 20px;
    }

    .profile-page .progress-bar {
        background: linear-gradient(90deg, #ff00ff, #00ffff);
        position: relative;
        overflow: hidden;
        animation: progressAnimation 2s ease-in-out infinite;
    }

    @keyframes progressAnimation {
        0% {
            background-position: 0% 50%;
        }

        100% {
            background-position: 200% 50%;
        }
    }

    .profile-page .progress-marks {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .profile-page .progress-mark {
        background-color: #fff;
        width: 2px;
        height: 100%;
        position: absolute;
    }

    /* Form thông tin cá nhân */
    .profile-page .profile-form .card {
        background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%);
        border-radius: 15px;
        border: 2px solid transparent;
        background-clip: padding-box;
        position: relative;
    }

    .profile-page .profile-form .card::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(90deg, #ff00ff, #00ffff);
        z-index: -1;
        border-radius: 15px;
    }

    /* Tabs */
    .profile-page .nav-link {
        color: #888;
        font-family: 'Orbitron', sans-serif;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .profile-page .nav-link:hover,
    .profile-page .nav-link.active {
        color: #00ffff;
        border-bottom: 2px solid #00ffff;
    }

    /* Input */
    .profile-page .form-control,
    .profile-page .form-check-input {
        background-color: #2a2a2a;
        border: 1px solid #ff00ff;
        color: #fff;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .profile-page .form-control:focus,
    .profile-page .form-check-input:focus {
        background-color: #333;
        border-color: #00ffff;
        box-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
        outline: none;
    }

    .profile-page .input-group-text {
        background-color: #2a2a2a;
        border: 1px solid #ff00ff;
        color: #ff00ff;
    }

    /* Label */
    .profile-page .form-label {
        color: #00ffff;
        font-family: 'Orbitron', sans-serif;
        font-weight: 600;
    }

    /* Nút cập nhật */
    .profile-page #update {
        background: linear-gradient(90deg, #ff00ff, #00ffff);
        border: none;
        border-radius: 8px;
        font-family: 'Orbitron', sans-serif;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
    }

    .profile-page #update:hover {
        background: linear-gradient(90deg, #00ffff, #ff00ff);
        box-shadow: 0 0 15px rgba(0, 255, 255, 0.5);
        transform: translateY(-2px);
    }

    /* Modal Thay đổi mật khẩu */
    .modal-content {
        background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%);
        border: 2px solid #ff00ff;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(255, 0, 255, 0.5);
    }

    .modal-header {
        border-bottom: 1px solid #ff00ff;
    }

    .modal-title {
        color: #00ffff;
        font-family: 'Orbitron', sans-serif;
        font-weight: 600;
    }

    .modal-body .form-group label {
        color: #00ffff;
        font-family: 'Orbitron', sans-serif;
        font-weight: 500;
    }

    .modal-body .form-control {
        background-color: #2a2a2a;
        border: 1px solid #ff00ff;
        color: #fff;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .modal-body .form-control:focus {
        background-color: #333;
        border-color: #00ffff;
        box-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
        outline: none;
    }

    .modal-footer .btn-success {
        background: linear-gradient(90deg, #28a745, #00ff00);
        border: none;
        border-radius: 8px;
        font-family: 'Orbitron', sans-serif;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .modal-footer .btn-success:hover {
        background: linear-gradient(90deg, #00ff00, #28a745);
        box-shadow: 0 0 15px rgba(0, 255, 0, 0.5);
        transform: translateY(-2px);
    }

    .modal-footer .btn-danger {
        background: linear-gradient(90deg, #dc3545, #ff0000);
        border: none;
        border-radius: 8px;
        font-family: 'Orbitron', sans-serif;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .modal-footer .btn-danger:hover {
        background: linear-gradient(90deg, #ff0000, #dc3545);
        box-shadow: 0 0 15px rgba(255, 0, 0, 0.5);
        transform: translateY(-2px);
    }

    /* Responsive */
    @media (max-width: 991.98px) {

        .profile-page .profile-card,
        .profile-page .profile-form .card {
            padding: 1.5rem;
        }

        .profile-page .form-control {
            font-size: 0.9rem;
        }

        .profile-page #update {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
    }

    @media (max-width: 767.98px) {

        .profile-page .profile-card,
        .profile-page .profile-form .card {
            padding: 1rem;
        }

        .profile-page .form-control {
            font-size: 0.85rem;
        }

        .profile-page #update {
            width: 100%;
        }
    }
</style>

<div id="toast"></div>
<?php alertMessage() ?>

<div class="main-content profile-page">
    <form id="avatarForm" action="/BanVeXemPhim/controllers/user-controller.php" method="post"
        enctype="multipart/form-data">
        <input type="hidden" name="mand" value="<?= $user['data']['MaND'] ?>">
        <input type="hidden" name="tend" value="<?= $user['data']['TenND'] ?>">

        <div class="container p-5">
            <div class="row">
                <?php
                $client_revenue = client_revenue2($NDId);

                $silverValue = 1;
                $goldValue = 1;
                $platinumValue = 1;

                $list_param = getAll('thamso');
                if (!empty($list_param)) {
                    foreach ($list_param as $param) {
                        if ($param['TenThamSo'] == 'Silver') {
                            $silverValue = $param['GiaTri'];
                            $silverName = $param['TenThamSo'];
                        }
                        if ($param['TenThamSo'] == 'Gold') {
                            $goldValue = $param['GiaTri'];
                            $goldName = $param['TenThamSo'];
                        }
                        if ($param['TenThamSo'] == 'Platinum') {
                            $platinumValue = $param['GiaTri'];
                            $platinumName = $param['TenThamSo'];
                        }
                    }
                }

                $mucTieu = $platinumValue;
                $percentage = ($client_revenue / $mucTieu) * 100;

                if ($client_revenue < $silverValue) {
                    $level = "Silver";
                    $color = "#d6d6e7";
                    $diff = $silverValue - $client_revenue;
                } elseif ($client_revenue < $goldValue) {
                    $level = "Gold";
                    $color = "#6c757d";
                    $diff = $goldValue - $client_revenue;
                } elseif ($client_revenue < $platinumValue) {
                    $level = "Platinum";
                    $color = "#ffc107";
                    $diff = $platinumValue - $client_revenue;
                } else {
                    $level = "Đã max";
                    $color = "#1948ff";
                    $diff = 0;
                }

                $silverPercentage = ($silverValue / $mucTieu) * 100;
                $goldPercentage = ($goldValue / $mucTieu) * 100;
                $platinumPercentage = ($platinumValue / $mucTieu) * 100;
                ?>

                <div class="col-md-4 col-lg-5 mb-4">
                    <div class="card profile-card">
                        <div class="card-body text-center p-4">
                            <div class="profile-picture-container position-relative d-inline-block">
                                <input type="file" class="form-control d-none" id="avatar" name="avatar" accept="image/*"
                                    onchange="submitAvatarForm();">
                                <img id="preview" style="box-shadow: 0 0 10px 5px <?= $color; ?>;"
                                    src="<?= $baseUrl . 'uploads/avatars/' . (!empty($user['data']['Anh']) ? $user['data']['Anh'] : 'user-icon.png') ?>"
                                    class="rounded-circle border border-2 border-light mb-3" alt="Profile Picture"
                                    width="120" height="120">
                                <button id="camera" type="button"
                                    class="position-absolute top-50 start-50 translate-middle text-white d-none"
                                    style="border: none; background: rgba(0, 0, 0, 0.6); width: 40px; height: 40px; border-radius: 50%;">
                                    <i class="bi bi-camera" style="font-size: 20px;"></i>
                                </button>
                            </div>

                            <script>
                                document.getElementById('camera').addEventListener('click', function(event) {
                                    event.preventDefault();
                                    document.getElementById('avatar').click();
                                });

                                function submitAvatarForm() {
                                    var submitButton = document.createElement('input');
                                    submitButton.type = 'hidden';
                                    submitButton.name = 'updateAvt';
                                    document.getElementById('avatarForm').appendChild(submitButton);
                                    document.getElementById('avatarForm').submit();
                                }
                            </script>

                            <h4 class="mt-2 mb-2" style="font-family: 'Orbitron', sans-serif; color: #00ffff;">
                                <i class="<?= $icon ?> me-2"></i>
                                <?= $user['data']['TenND'] ?>
                            </h4>
                            <small class="mt-3" style="color:#fff;">Tổng chi tiêu <span
                                    class="text-info fw-bolder fs-5 text-decoration-underline"><?= $current_year = date('Y'); ?></span></small>
                            <p class="fw-bold fs-4" style="color:#fff;"><?= number_format($client_revenue, 0, ',', '.') ?> ₫</p>

                            <div class="progress my-3">
                                <div class="progress-bar" role="progressbar" style="width: <?= min($percentage, 100); ?>%;"
                                    aria-valuenow="<?= $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                <div class="progress-marks">
                                    <div class="progress-mark" style="left: <?= $silverPercentage; ?>%;"></div>
                                    <div class="progress-mark" style="left: <?= $goldPercentage; ?>%;"></div>
                                    <div class="progress-mark" style="left: <?= $platinumPercentage; ?>%;"></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between fw-bold">
                                <small class="text-secondary">0</small>
                                <small class="text-secondary"><?= $silverName ?></small>
                                <small class="text-warning"><?= $goldName ?></small>
                                <small class="text-primary"><?= $platinumName ?></small>
                            </div>
                            <div class="container mt-3">
                                <small class="text-sm text-white">Số tiền còn thiếu để đạt <br> <span
                                        class="fw-bold"><?= $level ?></span></small>
                                <p class="fw-bold fs-6 mb-0" style="color:#fff;">
                                    +<?= number_format(max(0, $diff), 0, ',', '.') ?> ₫</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8 col-lg-7 profile-form">
                    <div class="border-bottom mb-2">
                        <ul class="nav d-flex justify-content-center" id="filmTabs">
                            <li class="nav-item">
                                <a class="nav-link" id="transition-history-tab" href="javascript:void(0);"
                                    onclick="showTab('transition-history')">Lịch sử giao dịch</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" id="personal-information-tab" href="javascript:void(0);"
                                    onclick="showTab('personal-infomation')">Thông tin cá nhân</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content">
                        <div id="personal-infomation" class="tab-pane fade show active">
                            <div class="card d-flex justify-content-center w-100 shadow border-0">
                                <div class="card-body p-2">
                                    <div class="row justify-content-center">
                                        <div class="col-6">
                                            <div class="mb-4">
                                                <label for="fullName" class="form-label">Họ và tên</label>
                                                <?php if (isset($messages['tennd'])): ?>
                                                    <br>
                                                    <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['tennd']) ?></small>
                                                <?php endif; ?>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                    <input type="text" class="form-control form-control-lg" id="fullName"
                                                        name="tennd" value="<?= $user['data']['TenND'] ?>">
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label for="email" class="form-label">Email</label>
                                                <?php if (isset($messages['email'])): ?>
                                                    <br>
                                                    <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['email']) ?></small>
                                                <?php endif; ?>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                    <input type="email" class="form-control form-control-lg" name="email"
                                                        id="email" value="<?= $user['data']['Email'] ?>">
                                                </div>
                                            </div>
                                            <div class="mb-4 text-center">
                                                <label class="form-label">Giới tính</label>
                                                <div class="d-flex justify-content-center">
                                                    <div class="form-check me-5">
                                                        <input class="form-check-input" type="radio" name="gioi_tinh"
                                                            id="male" value="1"
                                                            <?php echo ($user['data']['GioiTinh'] == '1') ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="male" style="color:#fff;">Nam</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="gioi_tinh"
                                                            id="female" value="0"
                                                            <?php echo ($user['data']['GioiTinh'] == '0') ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="female" style="color:#fff;">Nữ</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="mb-4">
                                                <label for="dob" class="form-label">Ngày sinh</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                    <input type="date" class="form-control form-control-lg" id="dob"
                                                        name="ngay_sinh"
                                                        max="<?php echo date('Y-m-d', strtotime('-5 years')); ?>"
                                                        value="<?= isset($user['data']['NgaySinh']) ? htmlspecialchars($user['data']['NgaySinh']) : ''; ?>">
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label for="phone" class="form-label">Số điện thoại</label>
                                                <?php if (isset($messages['sdt'])): ?>
                                                    <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['sdt']) ?></small>
                                                <?php endif; ?>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                    <?php
                                                    $phoneNumber = $user['data']['SDT'];
                                                    $displayPhone = $phoneNumber; // Hiển thị nguyên số điện thoại

                                                    ?>
                                                    <input type="text" class="form-control form-control-lg" id="phone"
                                                        name="sdt" value="<?= htmlspecialchars($displayPhone) ?>">
                                                </div>

                                            </div>
                                            <div class="mb-4">
                                                <label for="pwd" class="form-label">Mật khẩu</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                    <a href="#" data-bs-toggle="modal"
                                                        data-url="/controllers/user-controller.php"
                                                        data-bs-target="#change-pwd" style="text-decoration: none; padding-left: 20px;">Thay đổi mật khẩu</a>
                                                </div>
                                            </div>
                                            <!-- Modal Thay Đổi Mật Khẩu -->
                                            <div class="modal fade" id="change-pwd" tabindex="-1"
                                                aria-labelledby="changePwdModalLabel" aria-hidden="true">
                                                <div class="modal-dialog mt-5">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="changePwdModalLabel">Thay đổi mật khẩu</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label for="old-password">Mật khẩu cũ:</label>
                                                                <input type="password" class="form-control mt-2"
                                                                    id="old-password" name="old-password">
                                                                <?php if (isset($messages['old-password'])): ?>
                                                                    <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['old-password']) ?></small>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="new-password">Mật khẩu mới:</label>
                                                                <input type="password" class="form-control mt-2"
                                                                    id="new-password" name="new-password">
                                                                <?php if (isset($messages['new-password'])): ?>
                                                                    <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['new-password']) ?></small>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="new-repassword">Nhập lại mật khẩu mới:</label>
                                                                <input type="password" class="form-control mt-2"
                                                                    id="new-repassword" name="new-repassword">
                                                                <?php if (isset($messages['new-repassword'])): ?>
                                                                    <small class="text-danger m-2 text-xs"><?= htmlspecialchars($messages['new-repassword']) ?></small>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer d-flex justify-content-center">
                                                            <button type="submit" name="change-password-form"
                                                                id="change-password-btn"
                                                                class="btn btn-sm btn-success px-3">Thay đổi</button>
                                                            <button type="button" class="btn btn-sm btn-danger me-2"
                                                                data-bs-dismiss="modal">Không</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" name="updateInf" class="btn w-25" id="update">Cập nhật</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="transition-history" class="tab-pane fade">
                            <div class="card d-flex justify-content-center w-100 shadow border-0">
                                <div class="card-body p-2">
                                    <?php include('../views/transition-history.php'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include('../includes/footer.php'); ?>