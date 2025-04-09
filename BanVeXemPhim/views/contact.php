<?php
$title = 'Liên hệ';
include('../includes/header.php');
$messages = isset($_SESSION['messages']) ? $_SESSION['messages'] : []; // Lấy lỗi từ session
$formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['messages']); // Xóa lỗi khỏi session sau khi hiển thị
unset($_SESSION['form_data']);

$isLoggedIn = isset($_SESSION['NDloggedIn']) && $_SESSION['NDloggedIn'] == TRUE;
?>

<style>
    /* Nền tổng thể */
    section {
        background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.3)), url('https://wallpaperaccess.com/full/8406708.gif');
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
        min-height: 100vh; /* Đảm bảo section chiếm toàn bộ chiều cao màn hình */
        display: flex;
        align-items: center; /* Căn giữa nội dung theo chiều dọc */
        padding: 40px 0; /* Thêm padding trên/dưới */
    }

    /* Container chính */
    .container {
        max-width: 1200px; /* Giới hạn chiều rộng */
        margin: 0 auto;
    }

    /* Hình ảnh bên trái */
    .contact-image {
        width: 70%;
        height: 70%;
        object-fit: cover; /* Ảnh lấp đầy khung */
        border-radius: 15px; /* Bo góc mềm mại */
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5); /* Thêm bóng đổ */
        transition: transform 0.3s ease; /* Hiệu ứng mượt mà */
    }

    .contact-image:hover {
        transform: scale(1.03); /* Phóng to nhẹ khi hover */
    }

    /* Form container */
    .form-container {
        background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%); /* Gradient nền */
        border-radius: 15px; /* Bo góc mềm mại */
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5); /* Thêm bóng đổ */
        border: 2px solid transparent; /* Viền gradient */
        background-clip: padding-box;
        position: relative;
        padding: 30px; /* Tăng padding cho thoải mái */
    }

    /* Viền gradient cho form container */
    .form-container::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(90deg, #ff00ff, #00ffff); /* Gradient hồng-cyan */
        z-index: -1;
        border-radius: 15px;
    }

    /* Label */
    .form-label {
        color: #00ffff; /* Màu cyan */
        font-family: 'Orbitron', sans-serif; /* Font hiện đại */
        font-weight: 600;
        margin-bottom: 8px; /* Khoảng cách với input */
    }

    .text-danger {
        color: #ff3333 !important; /* Màu đỏ nổi bật cho dấu * */
    }

    /* Input và textarea */
    .form-control {
        background-color: #2a2a2a; /* Nền tối */
        border: 1px solid #ff00ff; /* Viền hồng */
        color: #fff; /* Chữ trắng */
        border-radius: 8px; /* Bo góc nhẹ */
        padding: 12px; /* Tăng padding cho thoải mái */
        transition: all 0.3s ease; /* Hiệu ứng mượt mà */
    }

    .form-control:focus {
        background-color: #333; /* Nền sáng hơn khi focus */
        border-color: #00ffff; /* Viền cyan khi focus */
        box-shadow: 0 0 10px rgba(0, 255, 255, 0.5); /* Bóng sáng cyan */
        outline: none; /* Xóa viền mặc định */
    }

    .form-control::placeholder {
        color: #888; /* Màu placeholder xám nhạt */
    }

    /* Nút gửi */
    .btn-primary {
        background: linear-gradient(90deg, #ff00ff, #00ffff); /* Gradient hồng-cyan */
        border: none;
        border-radius: 8px;
        font-family: 'Orbitron', sans-serif;
        font-weight: 600;
        padding: 12px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: linear-gradient(90deg, #00ffff, #ff00ff); /* Đảo gradient khi hover */
        box-shadow: 0 0 15px rgba(0, 255, 255, 0.5); /* Bóng sáng */
        transform: translateY(-2px); /* Nâng nhẹ khi hover */
    }

    /* Nút disabled */
    .btn-secondary {
        background: #444; /* Nền xám tối */
        border: none;
        border-radius: 8px;
        font-family: 'Orbitron', sans-serif;
        font-weight: 600;
        padding: 12px;
        color: #888; /* Chữ xám nhạt */
        cursor: not-allowed;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        .contact-image {
            display: none; /* Ẩn hình ảnh trên màn hình nhỏ hơn lg */
        }

        .form-container {
            padding: 20px; /* Giảm padding trên màn hình nhỏ */
        }

        .form-control {
            padding: 10px; /* Giảm padding input */
        }

        .btn-primary,
        .btn-secondary {
            padding: 10px; /* Giảm padding nút */
            font-size: 0.9rem; /* Giảm kích thước chữ */
        }
    }

    @media (max-width: 576px) {
        section {
            padding: 20px 0; /* Giảm padding trên/dưới */
        }

        .form-container {
            padding: 15px; /* Giảm padding thêm */
        }

        .form-label {
            font-size: 0.9rem; /* Giảm kích thước chữ label */
        }

        .form-control {
            padding: 8px; /* Giảm padding input */
            font-size: 0.9rem; /* Giảm kích thước chữ */
        }
    }
</style>

<div id="toast"></div>

<?php alertMessage() ?>

<section>
    <div class="container">
        <div class="row gy-3 gy-md-4 gy-lg-0 align-items-xl-center">
            <div class="col-12 col-lg-6 d-none d-sm-none d-md-none d-lg-block">
                <img class="img-fluid rounded contact-image" loading="lazy"
                    src="https://photo2.tinhte.vn/data/avatars/l/3037/3037189.jpg?1723275753" alt="Get in Touch">
            </div>
            <div class="col-12 col-lg-6">
                <div class="row justify-content-xl-center">
                    <div class="col-12 col-xl-11">
                        <div class="form-container">
                            <form action="/BanVeXemPhim/config/sendmail.php" method="POST">
                                <div class="row gy-2 gy-xl-3 p-2 p-xl-3">
                                    <div class="col-12">
                                        <label for="fullname" class="form-label fw-bold">Họ và tên <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="fullname" name="fullname"
                                            value="<?= isset($formData['fullname']) ? htmlspecialchars($formData['fullname']) : '' ?>" style="color:#fff;">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="email" class="form-label fw-bold">Email <span
                                                class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?= isset($formData['email']) ? htmlspecialchars($formData['email']) : '' ?>" style="color:#fff;">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="phone" class="form-label fw-bold">Số điện thoại</label>
                                        <input type="tel" class="form-control" id="phone" name="phone"
                                            value="<?= isset($formData['phone']) ? htmlspecialchars($formData['phone']) : '' ?>" style="color:#fff;">
                                    </div>
                                    <div class="col-12">
                                        <label for="subject" class="form-label fw-bold">Tiêu đề <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="subject" name="subject"
                                            value="<?= isset($formData['subject']) ? htmlspecialchars($formData['subject']) : '' ?>" style="color:#fff;">
                                    </div>
                                    <div class="col-12">
                                        <label for="message" class="form-label fw-bold">Tin nhắn <span
                                                class="text-danger">*</span></label>
                                        <textarea class="form-control" id="message" name="message" rows="3" style="color:#fff;"><?= isset($formData['message']) ? htmlspecialchars($formData['message']) : '' ?></textarea>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-grid">
                                            <?php
                                            if (!$isLoggedIn) {
                                                echo '<button class="btn btn-secondary btn-lg" id="login" type="button" disabled>Vui lòng đăng nhập để gửi tin nhắn</button>';
                                            } else {
                                                echo '<button class="btn btn-primary btn-lg" id="login" name="lienhe" type="submit">Gửi tin nhắn</button>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        let fullname = document.getElementById('fullname').value.trim();
        let email = document.getElementById('email').value.trim();
        let subject = document.getElementById('subject').value.trim();
        let message = document.getElementById('message').value.trim();
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!fullname || !email || !subject || !message) {
            alert('Vui lòng điền đầy đủ các trường bắt buộc.');
            e.preventDefault();
            return;
        }

        if (!emailPattern.test(email)) {
            alert('Email không hợp lệ.');
            e.preventDefault();
            return;
        }
    });
</script>


<?php include('../includes/footer.php'); ?>