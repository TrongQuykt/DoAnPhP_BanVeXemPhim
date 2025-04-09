<?php
require_once("config/function.php");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Phim</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <style>
        body {
            background: #0d0d0d;
            color: #fff;
            font-family: 'Orbitron', sans-serif;
        }

        .film-section {
            padding: 20px;
            background: rgba(20, 20, 20, 0.9);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.2);
        }

        .section-title {
            text-transform: uppercase;
            font-size: 2rem;
            color: #00ffff;
            text-shadow: 0 0 10px #00ffff;
            border-left: 5px solid #ff00ff;
            padding-left: 15px;
        }

        .film-tabs .nav-link {
            color: #fff;
            font-weight: bold;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .film-tabs .nav-link.active {
            color: #ff00ff;
            text-shadow: 0 0 10px #ff00ff;
            border-bottom: 2px solid #ff00ff;
        }

        .swiper-slide {
            width: 220px !important;
        }

        .film-card {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .film-card:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(255, 0, 255, 0.5);
        }

        .card-inner {
            position: relative;
        }

        .card-inner img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px;
        }

        .badge-status {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 8px 15px;
            font-size: 12px;
            border-radius: 20px;
            animation: pulse 2s infinite;
        }

        .now-showing {
            background: linear-gradient(45deg, #00ff00, #00ffff);
            color: #000;
        }

        .coming-soon {
            background: linear-gradient(45deg, #ff00ff, #ffff00);
            color: #000;
        }

        .badge-age {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff3333;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .buy-ticket-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 10px 10px;
            background: #ff00ff;
            color: #fff;
            text-decoration: none;
            border-radius: 25px;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .film-card:hover .buy-ticket-btn {
            opacity: 1;
        }

        .buy-ticket-btn:hover {
            background: #00ffff;
            color: #000;
            box-shadow: 0 0 15px #00ffff;
        }

        .film-title {
            text-align: center;
            margin-top: 10px;
            color: #fff;
            font-size: 1.1rem;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .tab-pane {
            animation: fadeIn 0.5s ease-in;
        }

        /* Styling for the View More button */
        .view-more-btn {
    display: block;
    margin: 20px auto; /* Giữ nút nằm giữa theo chiều ngang */
    padding: 8px 16px; /* Thu nhỏ kích thước nút (giảm padding) */
    width: 120px; /* Đặt chiều rộng cố định để nút nhỏ hơn */
    background: #ff00ff;
    color: #fff;
    text-align: center;
    text-decoration: none;
    border-radius: 25px;
    transition: all 0.3s ease;
    font-size: 0.9rem; /* Giảm kích thước chữ nếu cần */
}

        .view-more-btn:hover {
            background: #00ffff;
            color: #000;
            box-shadow: 0 0 15px #00ffff;
        }

        /* Ensure the button stays below the full list */
        .full-film-list.active + .view-more-btn {
            margin-top: 20px;
        }

        /* Styling for the full list */
        .full-film-list {
            display: none;
            margin-top: 20px;
        }

        .full-film-list.active {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        /* Ensure the full list items inherit the same styles */
        .full-film-list .film-card {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .full-film-list .film-card:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(255, 0, 255, 0.5);
        }

        .full-film-list .card-inner {
            position: relative;
        }

        .full-film-list .card-inner img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px;
        }

        .full-film-list .badge-status {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 8px 15px;
            font-size: 12px;
            border-radius: 20px;
            animation: pulse 2s infinite;
        }

        .full-film-list .badge-age {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff3333;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .full-film-list .buy-ticket-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 10px 10px;
            background: #ff00ff;
            color: #fff;
            text-decoration: none;
            border-radius: 25px;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .full-film-list .film-card:hover .buy-ticket-btn {
            opacity: 1;
        }

        .full-film-list .film-title {
            text-align: center;
            margin-top: 10px;
            color: #fff;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="container mt-5 w-75 film-section">
        <div class="d-flex align-items-center mb-4">
            <h4 class="section-title">Phim</h4>
            <ul class="nav ms-5 film-tabs" id="filmTabs">
                <li class="nav-item">
                    <a class="nav-link active" id="currently-showing-tab" href="javascript:void(0);" onclick="showTab('currently-showing')">Đang Chiếu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="coming-soon-tab" href="javascript:void(0);" onclick="showTab('coming-soon')">Sắp Chiếu</a>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <!-- Currently Showing Tab -->
            <div id="currently-showing" class="tab-pane active">
                <div class="swiper film-swiper" id="swiper-currently-showing">
                    <div class="swiper-wrapper">
                        <?php
                        $items = getFilm('1');
                        foreach ($items as $item): ?>
                        <div class="swiper-slide">
                            <div class="film-card">
                                <div class="card-inner">
                                    <img src="uploads/film-imgs/<?= $item['Anh'] ?>" alt="<?= $item['TenPhim'] ?>">
                                    <span class="badge-status now-showing">Đang Chiếu</span>
                                    <span class="badge-age"><?= $item['PhanLoai'] ?></span>
                                    <a href="views/detail-film.php?id=<?= $item['MaPhim'] ?>" class="buy-ticket-btn bi bi-ticket-perforated"> Mua Vé</a>
                                </div>
                                <div class="film-title"><?= $item['TenPhim'] ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
                <!-- Full List for Currently Showing -->
                <div class="full-film-list" id="full-list-currently-showing">
                    <?php
                    $items = getFilm('1');
                    foreach ($items as $item): ?>
                    <div class="film-card">
                        <div class="card-inner">
                            <img src="uploads/film-imgs/<?= $item['Anh'] ?>" alt="<?= $item['TenPhim'] ?>">
                            <span class="badge-status now-showing">Đang Chiếu</span>
                            <span class="badge-age"><?= $item['PhanLoai'] ?></span>
                            <a href="views/detail-film.php?id=<?= $item['MaPhim'] ?>" class="buy-ticket-btn bi bi-ticket-perforated"> Mua Vé</a>
                        </div>
                        <div class="film-title"><?= $item['TenPhim'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <!-- View More Button for Currently Showing -->
                <a href="javascript:void(0);" class="view-more-btn" id="view-more-currently-showing" onclick="toggleFullList('currently-showing')">Xem Thêm</a>
            </div>

            <!-- Coming Soon Tab -->
            <div id="coming-soon" class="tab-pane">
                <div class="swiper film-swiper" id="swiper-coming-soon">
                    <div class="swiper-wrapper">
                        <?php
                        $items = getFilm('2');
                        foreach ($items as $item): ?>
                        <div class="swiper-slide">
                            <div class="film-card">
                                <div class="card-inner">
                                    <img src="uploads/film-imgs/<?= $item['Anh'] ?>" alt="<?= $item['TenPhim'] ?>">
                                    <span class="badge-status coming-soon">Sắp Chiếu</span>
                                    <span class="badge-age"><?= $item['PhanLoai'] ?></span>
                                    <a href="views/detail-film.php?id=<?= $item['MaPhim'] ?>" class="buy-ticket-btn bi bi-ticket-perforated"> Mua Vé</a>
                                </div>
                                <div class="film-title"><?= $item['TenPhim'] ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
                <!-- Full List for Coming Soon -->
                <div class="full-film-list" id="full-list-coming-soon">
                    <?php
                    $items = getFilm('2');
                    foreach ($items as $item): ?>
                    <div class="film-card">
                        <div class="card-inner">
                            <img src="uploads/film-imgs/<?= $item['Anh'] ?>" alt="<?= $item['TenPhim'] ?>">
                            <span class="badge-status coming-soon">Sắp Chiếu</span>
                            <span class="badge-age"><?= $item['PhanLoai'] ?></span>
                            <a href="views/detail-film.php?id=<?= $item['MaPhim'] ?>" class="buy-ticket-btn bi bi-ticket-perforated"> Mua Vé</a>
                        </div>
                        <div class="film-title"><?= $item['TenPhim'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <!-- View More Button for Coming Soon -->
                <a href="javascript:void(0);" class="view-more-btn" id="view-more-coming-soon" onclick="toggleFullList('coming-soon')">Xem Thêm</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        // Khởi tạo Swiper
        let swiperInstances = {};
        const initSwiper = (tabId) => {
            const swiper = new Swiper(`#swiper-${tabId}`, {
                slidesPerView: 'auto',
                spaceBetween: 20,
                // pagination: {
                //     el: '.swiper-pagination',
                //     clickable: true,
                // },
                pagination: false, // Thay thế phần cấu hình pagination với false
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
            });
            swiperInstances[tabId] = swiper;
        };

        // Khởi tạo Swiper cho cả hai tab
        initSwiper('currently-showing');
        initSwiper('coming-soon');

        // Chuyển tab
        function showTab(tabId) {
            // Ẩn tất cả các tab và danh sách đầy đủ
            document.querySelectorAll('.tab-pane').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.full-film-list').forEach(list => list.classList.remove('active'));
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));

            // Hiển thị tab được chọn
            document.querySelector(`#${tabId}`).classList.add('active');
            document.querySelector(`#${tabId}-tab`).classList.add('active');

            // Khôi phục Swiper và ẩn danh sách đầy đủ
            document.querySelector(`#swiper-${tabId}`).style.display = 'block';
            document.querySelector(`#full-list-${tabId}`).classList.remove('active');
            document.querySelector(`#full-list-${tabId}`).style.display = 'none';
            document.querySelector(`#view-more-${tabId}`).textContent = 'Xem Thêm'; // Đặt lại nút thành "Xem Thêm"
        }

        // Hiển thị/Ẩn danh sách đầy đủ
        function toggleFullList(tabId) {
            const swiperElement = document.querySelector(`#swiper-${tabId}`);
            const fullListElement = document.querySelector(`#full-list-${tabId}`);
            const viewMoreBtn = document.querySelector(`#view-more-${tabId}`);

            if (fullListElement.classList.contains('active')) {
                // Nếu danh sách đầy đủ đang hiển thị, ẩn nó và hiện lại Swiper
                fullListElement.classList.remove('active');
                fullListElement.style.display = 'none';
                swiperElement.style.display = 'block';
                viewMoreBtn.textContent = 'Xem Thêm'; // Đặt lại thành "Xem Thêm"
            } else {
                // Nếu danh sách đầy đủ đang ẩn, hiển thị nó và ẩn Swiper
                fullListElement.classList.add('active');
                fullListElement.style.display = 'grid';
                swiperElement.style.display = 'none';
                viewMoreBtn.textContent = 'Thu Gọn'; // Chuyển thành "Thu Gọn"
            }
        }
    </script>
</body>
</html>