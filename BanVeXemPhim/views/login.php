<?php
$title = "Đăng nhập";
include('../includes/header.php');
$messages = isset($_SESSION['messages']) ? $_SESSION['messages'] : []; // Lấy lỗi từ session
$formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['messages']); // Xóa lỗi khỏi session sau khi hiển thị
unset($_SESSION['form_data']);

// Lấy lại dữ liệu từ cookie nếu có
$username = isset($_COOKIE['username']) ? $_COOKIE['username'] : '';
// Kiểm tra trạng thái của checkbox 'Remember me'
$rememberMeChecked = isset($_SESSION['rememberMe']) && $_SESSION['rememberMe'] ? 'checked' : '';
?>

<style>
    /* Nền tổng thể */
    body {
        background: linear-gradient(135deg, #0d0d0d 0%, #1a1a1a 100%);
        margin: 0;
        padding: 0;
    }

    /* Container chính */
    .login-container {
        max-width: 450px;
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
        background-color: #2a2a2a;
        border: 1px solid #ff00ff;
        color: #fff;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .form-container.no-hover:hover {
    transform: none;
    box-shadow: 0 0 20px rgba(0, 255, 255, 0.2); /* Giữ shadow ban đầu */
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

    /* Checkbox */
    .form-container .form-check-input {
        background-color: #2a2a2a;
        border: 1px solid #ff00ff;
        transition: all 0.3s ease;
    }

    .form-container .form-check-input:checked {
        background-color: #00ffff;
        border-color: #00ffff;
    }

    .form-container .form-check-label {
        color: #fff;
        font-family: 'Roboto', sans-serif;
    }

    /* Nút Đăng nhập */
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

    /* Nút Google */
    #btn-google .btn {
        background: #fff;
        color: #333;
        border-radius: 8px;
        font-family: 'Roboto', sans-serif;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    #btn-google .btn:hover {
        background: #f1f1f1;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        transform: translateY(-2px);
    }

    #btn-google .bi-google {
        color: #4285F4;
        margin-right: 8px;
    }

    /* Modal Quên mật khẩu */
    .modal-content {
        background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%);
        border: 2px solid transparent;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0, 255, 255, 0.2);
    }

    .modal-content::before {
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

    .modal-header {
        border-bottom: 1px solid #ff00ff;
    }

    .modal-title {
        color: #00ffff;
        font-family: 'Orbitron', sans-serif;
    }

    .modal-body .form-control {
        background-color: #2a2a2a;
        border: 1px solid #ff00ff;
        color: #fff;
        border-radius: 8px;
    }

    .modal-body .form-control:focus {
        border-color: #00ffff;
        box-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
    }

    .modal-body label {
        color: #00ffff;
        font-family: 'Orbitron', sans-serif;
    }

    .modal-footer .btn-success {
        background: linear-gradient(90deg, #00ff00, #00cc00);
        border: none;
        border-radius: 8px;
    }

    .modal-footer .btn-success:hover {
        background: linear-gradient(90deg, #00cc00, #00ff00);
        box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
    }

    .modal-footer .btn-danger {
        background: linear-gradient(90deg, #ff3333, #cc0000);
        border: none;
        border-radius: 8px;
    }

    .modal-footer .btn-danger:hover {
        background: linear-gradient(90deg, #cc0000, #ff3333);
        box-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
    }

    /* Responsive */
    @media (max-width: 576px) {
        .login-container {
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

<?php alertMessage(); ?>
<div class="container login-container">
    <div class="form-container sign-in">
        <form id="login-form" class="p-4" action="/BanVeXemPhim/controllers/user-controller.php" method="post">
            <div class="mb-4 text-center">
                <span class="fw-bolder fs-3">Đăng Nhập Tài Khoản</span>
            </div>
            <!-- Tên đăng nhập -->
            <div class="mb-3">
                <div class="input-group mb-1">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" name="tendn" placeholder="Tên đăng nhập"
                        autocomplete="username"
                        value="<?php echo isset($formData['tendn']) ? htmlspecialchars($formData['tendn']) : htmlspecialchars($username); ?>">
                </div>
                <?php if (isset($messages['tendn'])): ?>
                <small class="text-danger m-2"><?= htmlspecialchars($messages['tendn']) ?></small>
                <?php endif; ?>
            </div>

            <!-- Mật khẩu -->
            <div class="mb-3">
                <div class="input-group mb-1">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password_login" name="password"
                        placeholder="Nhập mật khẩu">
                    <span class="input-group-text iconEye" style="cursor: pointer;"
                        onclick="togglePassword('password_login', 'togglePasswordLogin')">
                        <i class="fas fa-eye-slash" id="togglePasswordLogin"></i>
                    </span>
                </div>
                <?php if (isset($messages['password'])): ?>
                <small class="text-danger m-2"><?= htmlspecialchars($messages['password']) ?></small>
                <?php endif; ?>
            </div>

            <!-- Ghi nhớ đăng nhập -->
            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" name="remember_me" id="remember_me"
                    <?php echo $rememberMeChecked; ?>>
                <label class="form-check-label" for="remember_me">Ghi nhớ đăng nhập</label>
            </div>

            <!-- Nút Đăng Nhập -->
            <button type="submit" class="btn btn-primary w-100 mb-3" id="login-sigin" name="login">Đăng Nhập</button>

            <!-- Liên kết Quên mật khẩu -->
            <div class="text-center mb-3">
                <a href="#" data-bs-toggle="modal" data-url="/BanVeXemPhim/controllers/user-controller.php"
                    data-bs-target="#confirmModal">Quên mật khẩu?</a>
            </div>

            <!-- Liên kết Đăng ký -->
            <div class="text-center mb-3">
                <span>Chưa có tài khoản? <a href="register.php">Đăng ký</a></span>
            </div>

            <?php
            // Tạo đường dẫn đăng nhập Google
            $client_id = '739364190972-invtld8qsu0go5i7n53gddq289n9svf4.apps.googleusercontent.com';
            $redirect_uri = 'http://localhost:3000/BanVeXemPhim/controllers/google-callback.php';
            $scope = 'email profile';

            $google_login_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
                'client_id' => $client_id,
                'redirect_uri' => $redirect_uri,
                'response_type' => 'code',
                'scope' => $scope,
                'access_type' => 'offline',
                'prompt' => 'select_account'
            ]);
            ?>

            <!-- Nút Đăng Nhập Google -->
            <div class="text-center" id="btn-google">
                <a href="<?= htmlspecialchars($google_login_url) ?>" class="btn p-2">
                    <i class="bi bi-google"></i><span> Đăng nhập bằng Google</span>
                </a>
            </div>
        </form>
    </div>
</div>
<!-- Modal Quên Mật Khẩu -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog mt-5">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Quên mật khẩu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="username-fpwd">Tên đăng nhập:</label>
                    <input type="text" class="form-control mt-2" id="username-fpwd" name="username-fpwd">
                    <small class="text-danger m-2" id="username-error"></small>
                    <br>
                    <label for="email-fpwd">Địa chỉ email:</label>
                    <input type="email" class="form-control mt-2" id="email-fpwd" name="email-fpwd">
                    <small class="text-danger m-2" id="email-error"></small>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" id="forget-password" class="btn btn-success px-3">Gửi</button>
                <button type="button" class="btn btn-danger me-2" data-bs-dismiss="modal">Không</button>
            </div>
        </div>
    </div>
</div>
<script>
    // Khi modal mở
    document.getElementById('confirmModal').addEventListener('show.bs.modal', function () {
        document.querySelector('.form-container').classList.add('no-hover');
    });

    // Khi modal đóng
    document.getElementById('confirmModal').addEventListener('hide.bs.modal', function () {
        document.querySelector('.form-container').classList.remove('no-hover');
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const forgetPasswordBtn = document.getElementById('forget-password');
    if (forgetPasswordBtn) {
        forgetPasswordBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Lấy dữ liệu từ form
            const username = document.getElementById('username-fpwd').value;
            const email = document.getElementById('email-fpwd').value;

            // Xóa thông báo lỗi cũ
            const usernameError = document.getElementById('username-error');
            const emailError = document.getElementById('email-error');
            usernameError.innerText = '';
            emailError.innerText = '';

            // Tạo FormData để gửi dữ liệu
            const formData = new FormData();
            formData.append('username-fpwd', username);
            formData.append('email-fpwd', email);
            formData.append('forget-password', true);

            // Gửi yêu cầu AJAX
            fetch('/BanVeXemPhim/controllers/user-controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Đóng modal
                    const modalElement = document.getElementById('confirmModal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                        modalElement.addEventListener('hidden.bs.modal', function () {
                            document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                            document.body.classList.remove('modal-open');
                            document.body.style.overflow = 'auto';
                        }, { once: true });
                    }

                    // Thay thế toast bằng alert (tùy chọn)
                    alert(data.message || 'Đã gửi mật khẩu mới qua Gmail thành công.');
                } else if (data.status === 'error' && data.messages) {
                    // Hiển thị thông báo lỗi
                    if (data.messages['username-fpwd']) {
                        usernameError.innerText = data.messages['username-fpwd'];
                    }
                    if (data.messages['email-fpwd']) {
                        emailError.innerText = data.messages['email-fpwd'];
                    }
                } else {
                    alert('Có lỗi xảy ra. Vui lòng thử lại.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            });
        });
    } else {
        console.error('Element with ID "forget-password" not found.');
    }
});
</script>
<?php include('../includes/footer.php'); ?>