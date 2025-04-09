<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php
$title = "Đăng ký";
include('../includes/header.php');
$messages = isset($_SESSION['messages']) ? $_SESSION['messages'] : []; // Lấy lỗi từ session
$formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['messages']); // Xóa lỗi khỏi session sau khi hiển thị
unset($_SESSION['form_data']);
?>

<style>
    /* Nền tổng thể */
    body {
        background: linear-gradient(135deg, #0d0d0d 0%, #1a1a1a 100%);
        margin: 0;
        padding: 0;
    }

    /* Container chính */
    .register-container {
        max-width: 500px;
        width: 100%;
        min-height: 80vh; /* Giữ khoảng cách trên và dưới */
        display: flex;
        align-items: center;
        justify-content: center;
        margin: auto; /* Đảm bảo căn giữa */
        padding: 20px 0; /* Cách trên và dưới */

    }

    /* Form container */
    .form-container {
        background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%);
        border-radius: 15px;
        border: 2px solid transparent;
        background-clip: padding-box;
        position: relative;
        box-shadow: 0 0 20px rgba(0, 255, 255, 0.2);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .form-container::before {
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

    .form-container:hover {
        transform: translateY(-5px);
        box-shadow: 0 0 30px rgba(0, 255, 255, 0.5);
    }

    /* Tiêu đề */
    .form-container .fw-bolder {
        color: #00ffff;
        font-family: 'Orbitron', sans-serif;
        text-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
    }

    /* Input */
    .form-container .form-control {
        /* background-color: #FFFFFFFF; */
        border: 1px solid #ff00ff;
        color: #000;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .form-container .form-control:focus {
        background-color: #333;
        border-color: #00ffff;
        box-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
        outline: none;
    }

    .form-container .input-group-text {
        background: linear-gradient(90deg, #ff00ff, #00ffff);
        border: none;
        color: #fff;
        border-radius: 8px 0 0 8px;
    }

    /* Icon Eye */
    .form-container .iconEye {
        background-color: #2a2a2a;
        border: 1px solid #ff00ff;
        border-left: none;
        color: #ff00ff;
        border-radius: 0 8px 8px 0;
        transition: all 0.3s ease;
    }

    .form-container .iconEye:hover {
        background-color: #333;
        color: #00ffff;
        border-color: #00ffff;
    }

    /* reCAPTCHA */
    .form-container .g-recaptcha {
        transform: scale(0.77);
        transform-origin: 0 0;
        width: 100%;
    }

    /* Nút Đăng ký */
    .form-container .btn-primary {
        background: linear-gradient(90deg, #ff00ff, #00ffff);
        border: none;
        border-radius: 8px;
        font-family: 'Orbitron', sans-serif;
        font-weight: 600;
        padding: 0.75rem;
        transition: all 0.3s ease;
    }

    .form-container .btn-primary:hover {
        background: linear-gradient(90deg, #00ffff, #ff00ff);
        box-shadow: 0 0 15px rgba(0, 255, 255, 0.5);
        transform: translateY(-2px);
    }

    /* Liên kết */
    .form-container a {
        color: #00ffff;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .form-container a:hover {
        color: #ff00ff;
        text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .register-container {
            margin: 1rem;
        }

        .form-container {
            padding: 1.5rem;
        }

        .form-container .fw-bolder {
            font-size: 1.5rem;
        }

        .form-container .btn-primary {
            padding: 0.5rem;
            font-size: 0.9rem;
        }
    }
</style>

<div id="toast"></div>

<?php alertMessage() ?>

<div class="container register-container">
    <div class="form-container sign-up">
        <form class="p-4" action="../controllers/user-controller.php" method="post">
            <div class="text-center mb-4">
                <span class="fw-bolder fs-3">Đăng Ký Tài Khoản</span>
            </div>

            <!-- Họ và tên -->
            <div class="mb-3">
                <div class="input-group mb-1">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" name="tennd"
                        value="<?= isset($formData['tennd']) ? htmlspecialchars($formData['tennd']) : ''; ?>"
                        placeholder="Họ và tên" required>
                </div>
                <?php if (isset($messages['tennd'])): ?>
                <small class="text-danger"><?= htmlspecialchars($messages['tennd']) ?></small>
                <?php endif; ?>
            </div>

            <!-- Tên đăng nhập -->
            <div class="mb-3">
                <div class="input-group mb-1">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" name="tendn"
                        value="<?= isset($formData['tendn']) ? htmlspecialchars($formData['tendn']) : ''; ?>"
                        placeholder="Tên đăng nhập" required>
                </div>
                <?php if (isset($messages['tendn'])): ?>
                <small class="text-danger"><?= htmlspecialchars($messages['tendn']) ?></small>
                <?php endif; ?>
            </div>

            <!-- Gmail -->
            <div class="mb-3">
                <div class="input-group mb-1">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" name="email"
                        value="<?= isset($formData['email']) ? htmlspecialchars($formData['email']) : ''; ?>"
                        placeholder="Nhập Gmail" required>
                </div>
                <?php if (isset($messages['email'])): ?>
                <small class="text-danger"><?= htmlspecialchars($messages['email']) ?></small>
                <?php endif; ?>
            </div>

            <!-- Mật khẩu -->
            <div class="mb-3">
                <div class="input-group mb-1">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Nhập mật khẩu" required>
                    <span class="input-group-text iconEye" style="cursor: pointer;"
                        onclick="togglePassword('password', 'togglePassword')">
                        <i class="fas fa-eye-slash" id="togglePassword"></i>
                    </span>
                </div>
                <?php if (isset($messages['password'])): ?>
                <small class="text-danger"><?= htmlspecialchars($messages['password']) ?></small>
                <?php endif; ?>
            </div>

            <!-- Nhập lại mật khẩu -->
            <div class="mb-3">
                <div class="input-group mb-1">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="re_password" name="re_password"
                        placeholder="Nhập lại mật khẩu" required>
                    <span class="input-group-text iconEye" style="cursor: pointer;"
                        onclick="togglePassword('re_password', 'toggleRePassword')">
                        <i class="fas fa-eye-slash" id="toggleRePassword"></i>
                    </span>
                </div>
                <?php if (isset($messages['re_password'])): ?>
                <small class="text-danger"><?= htmlspecialchars($messages['re_password']) ?></small>
                <?php endif; ?>
            </div>

            <!-- reCAPTCHA -->
            <!-- <div class="mb-3">
                <div class="g-recaptcha" data-sitekey="6LddNHoqAAAAADttUJjLEihMpDd-UL1xA0a75ZeB"
                    style="transform:scale(0.77); transform-origin:0 0; width: 100%;"></div>
                <?php if (isset($messages['captcha'])): ?>
                <small class="text-danger"><?= htmlspecialchars($messages['captcha']) ?></small>
                <?php endif; ?>
            </div> -->

            <!-- Nút Đăng ký -->
            <button type="submit" class="btn btn-primary w-100 mb-3" id="login-sigin" name="signup">Đăng ký</button>

            <!-- Liên kết đến trang đăng nhập -->
            <div class="text-center">
                <span>Đã có tài khoản? <a href="login.php">Đăng nhập</a></span>
            </div>
        </form>
    </div>
</div>

<?php include('../includes/footer.php'); ?>