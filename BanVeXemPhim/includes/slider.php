<?php
require_once 'config/function.php';
?>

<style>
    /* Container chính của slider */
    .carousel-container {
        max-width: 1400px; /* Giữ nguyên kích thước tối đa */
        margin: 20px auto; /* Căn giữa và thêm khoảng cách trên/dưới */
        background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%); /* Gradient nền */
        border-radius: 15px; /* Bo góc mềm hơn */
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5); /* Thêm bóng đổ */
        position: relative;
        border: 2px solid transparent; /* Viền gradient */
        background-clip: padding-box;
    }

    /* Viền gradient cho container */
    .carousel-container::before {
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

    /* Ảnh trong slider */
    .carousel-item img {
        width: 100%;
        height: 480px;
        object-fit: cover; /* Đổi sang cover để ảnh lấp đầy khung, không có viền trống */
        border-radius: 15px; /* Đồng bộ bo góc với container */
        transition: transform 0.5s ease, opacity 0.5s ease; /* Hiệu ứng mượt mà */
        background-color: black; /* Giữ màu nền đen cho an toàn */
    }

    /* Hiệu ứng khi hover vào ảnh */
    .carousel-item img:hover {
        transform: scale(1.05); /* Phóng to nhẹ khi hover */
        opacity: 0.9; /* Giảm độ sáng nhẹ */
    }

    /* Hiệu ứng chuyển slide */
    .carousel-item {
        transition: opacity 0.5s ease-in-out; /* Chuyển slide mượt mà */
    }

    /* Indicators (chấm tròn bên dưới) */
    .carousel-indicators {
        bottom: 15px; /* Đưa indicators lên cao hơn một chút */
        margin-bottom: 0; /* Loại bỏ margin mặc định */
    }

    .carousel-indicators button {
        width: 12px; /* Tăng kích thước chấm */
        height: 12px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.5); /* Màu trắng mờ */
        border: 1px solid #ff00ff; /* Viền hồng */
        margin: 0 6px; /* Khoảng cách giữa các chấm */
        transition: all 0.3s ease; /* Hiệu ứng mượt mà */
    }

    .carousel-indicators .active {
        background-color: #00ffff; /* Màu cyan khi active */
        border-color: #00ffff; /* Viền cyan */
        transform: scale(1.3); /* Phóng to chấm active */
    }

    /* Nút điều hướng (prev/next) */
    .carousel-control-prev,
    .carousel-control-next {
        width: 60px; /* Tăng kích thước vùng click */
        opacity: 0.7; /* Độ mờ mặc định */
        transition: opacity 0.3s ease, transform 0.3s ease; /* Hiệu ứng mượt mà */
    }

    .carousel-control-prev:hover,
    .carousel-control-next:hover {
        opacity: 1; /* Tăng độ sáng khi hover */
        transform: scale(1.1); /* Phóng to nhẹ */
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        width: 30px; /* Tăng kích thước icon */
        height: 30px;
        background-size: 100%, 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Nền đen mờ */
        border-radius: 50%; /* Bo tròn icon */
        /* border: 2px solid #ff00ff; Viền hồng */
        transition: all 0.3s ease;
    }

    .carousel-control-prev:hover .carousel-control-prev-icon,
    .carousel-control-next:hover .carousel-control-next-icon {
        border-color: #00ffff; /* Viền cyan khi hover */
        background-color: rgba(0, 0, 0, 0.8); /* Nền đậm hơn */
    }

    /* Responsive */
    @media (max-width: 768px) {
        .carousel-item img {
            height: 300px; /* Giảm chiều cao ảnh trên màn hình nhỏ */
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 40px; /* Giảm kích thước vùng click */
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            width: 20px; /* Giảm kích thước icon */
            height: 20px;
        }

        .carousel-indicators button {
            width: 8px; /* Giảm kích thước chấm */
            height: 8px;
            margin: 0 4px; /* Giảm khoảng cách giữa các chấm */
        }
    }

    @media (max-width: 576px) {
        .carousel-item img {
            height: 200px; /* Giảm chiều cao ảnh trên màn hình rất nhỏ */
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 30px; /* Giảm thêm kích thước vùng click */
        }

        .carousel-indicators {
            bottom: 10px; /* Đưa indicators lên cao hơn */
        }
    }
</style>

<?php
$items = getSliders($conn, 'header'); // Lấy các slider có vị trí là header
?>

<div class="carousel-container">
    <div id="carousel" class="carousel slide" data-bs-ride="carousel" data-bs-touch="true">
        <div class="carousel-indicators">
            <?php foreach ($items as $index => $item): ?>
            <button type="button" data-bs-target="#carousel" data-bs-slide-to="<?= $index ?>"
                class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>"
                aria-label="Slide <?= $index + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php foreach ($items as $index => $item): ?>
            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>" data-bs-interval="5000">
                <a class="link" href="<?= htmlspecialchars($item['URL']) ?>" target="_blank">
                    <img src="uploads/slider-imgs/<?= htmlspecialchars($item['Anh']) ?>" alt="<?= htmlspecialchars($item['TenSlider']) ?>">
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</div>