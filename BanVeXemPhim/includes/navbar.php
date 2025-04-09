<?php
$current_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
require_once $_SERVER['DOCUMENT_ROOT'] . "/BanVeXemPhim/config/function.php";
getUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar Thiết Kế Lại</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Orbitron -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .cgv { color: #E60012; } /* Đỏ CGV */
        .lotte { color: #DA291C; } /* Đỏ Lotte */
        .bhd { color: #A4CD39; } /* Xanh lá BHD */
        .galaxy { color: #F78F1E; } /* Cam Galaxy */
        .cinestar { color: #5B3F92; } /* Tím CineStar */

        .letter {
            display: inline-block;
            position: relative;
            transition: 0.4s;
        }

        .out {
            transform: translateY(-100%);
            opacity: 0;
        }

        .in {
            transform: translateY(0);
            opacity: 1;
        }

        .behind {
            opacity: 0;
        }

        /* Tùy chỉnh màu sắc cho nav-link */
        .nav-link {
            color: #ccc !important; /* Xám nhạt cho tất cả các mục menu */
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .nav-link i {
            margin-right: 8px;
            color: #ff00ff; /* Màu hồng cho icon */
            font-size: 1.2rem;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #00ffff !important; /* Cyan khi hover hoặc active */
            text-shadow: 0 0 5px rgba(0, 255, 255, 0.5);
        }

        .nav-link:hover i,
        .nav-link.active i {
            color: #00ffff; /* Icon chuyển thành cyan khi hover hoặc active */
        }

        /* Tùy chỉnh khoảng cách giữa các mục menu */
        .nav-item {
            margin: 0 15px; /* Tăng khoảng cách giữa các mục menu */
        }

        /* Logo */
        .navbar-brand img {
            width: 60px;
            border-radius: 50%;
            border: 2px solid #ff00ff;
            transition: all 0.3s ease;
        }

        .navbar-brand img:hover {
            border-color: #00ffff;
            box-shadow: 0 0 10px #00ffff;
        }

        /* Dropdown menu */
        .dropdown-menu {
    background: #1a1a1a;
    border: 1px solid #ff00ff;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
    padding: 15px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s linear 0.3s; /* Độ trễ 0.3s khi ẩn */
}
/* Hiển thị dropdown khi hover */
.nav-item.dropdown:hover .dropdown-menu,
.dropdown.user-dropdown:hover .dropdown-menu {
    display: block;
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
    transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s linear 0s; /* Không có độ trễ khi hiển thị */
}

        .dropdown-menu h6 {
            color: #00ffff;
            text-shadow: 0 0 5px rgba(0, 255, 255, 0.5);
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
        }

        .movie-card {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .movie-card:hover {
            transform: scale(1.05);
        }

        .movie-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .movie-age {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff3333;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .buy-ticket {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #ff00ff;
            color: #fff;
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .movie-card:hover .buy-ticket {
            opacity: 1;
        }

        .buy-ticket:hover {
            background: #00ffff;
            color: #000;
            box-shadow: 0 0 10px #00ffff;
        }

        .movie-info {
            text-align: center;
            margin-top: 10px;
        }

        .movie-title {
            color: #fff;
            font-size: 0.95rem;
            font-weight: 600;
        }

        /* User dropdown */
        .nav-item.user-dropdown {
            display: flex;
            align-items: center;
            position: relative; /* Đảm bảo dropdown được định vị tương đối với user item */
        }

        .nav-item.user-dropdown .nav-link {
            padding: 0; /* Loại bỏ padding mặc định để căn chỉnh đúng với avatar */
        }

        .nav-item.user-dropdown .dropdown-toggle {
            display: flex;
            align-items: center;
        }

        .nav-item.user-dropdown .dropdown-toggle img {
            width: 40px;
            height: 40px;
            border: 2px solid #ff00ff;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .nav-item.user-dropdown .dropdown-toggle img:hover {
            border-color: #00ffff;
            box-shadow: 0 0 10px #00ffff;
        }

        /* Điều chỉnh khi chưa đăng nhập */
        .nav-item.user-dropdown .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Điều chỉnh vị trí dropdown của user */
        .dropdown-menu.user-menu {
    background: #1a1a1a;
    border: 1px solid #ff00ff;
    border-radius: 10px;
    min-width: 200px;
    right: 0;
    left: auto;
    transform: translateX(80%) translateY(50%); /* Ban đầu lệch xuống một chút */
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s linear 0.3s;
}

.dropdown.user-dropdown:hover .dropdown-menu.user-menu {
    opacity: 1;
    visibility: visible;
    transform: translateX(80%) translateY(70%);
    transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s linear 0s;
}

        /* Đảm bảo dropdown không bị cắt trên màn hình nhỏ */
        @media (max-width: 991.98px) {
            .dropdown-menu.user-menu {
        transform: none;
        right: auto;
        left: 0;
        width: 100%;
    }
        }

        .dropdown-menu.user-menu .dropdown-item {
            color: #ccc;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .dropdown-menu.user-menu .dropdown-item i {
            margin-right: 10px;
            color: #ff00ff;
        }

        .dropdown-menu.user-menu .dropdown-item:hover {
            color: #00ffff;
            background: transparent;
        }

        .dropdown-menu.user-menu .dropdown-item:hover i {
            color: #00ffff;
        }

        .user-name {
            background: linear-gradient(to right, #30CFD0 0%, #330867 100%);
            background-clip: text;
            color: transparent;
        }

        /* Nút menu trên mobile */
        .btn-dark {
            color: #ff00ff;
            transition: all 0.3s ease;
        }

        .btn-dark:hover {
            color: #00ffff;
        }

        /* Offcanvas */
        .offcanvas {
            background: #1a1a1a;
            color: #fff;
        }

        .offcanvas-header {
            border-bottom: 1px solid #ff00ff;
        }

        .offcanvas-title {
            color: #00ffff;
            font-family: 'Orbitron', sans-serif;
        }

        .offcanvas-body .nav-link {
            color: #ccc;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
            margin: 10px 0;
        }

        .offcanvas-body .nav-link:hover,
        .offcanvas-body .nav-link.active {
            color: #00ffff;
        }

        /* Hiện dropdown khi hover trên màn hình lớn */
        .nav-item.dropdown:hover .dropdown-menu {
            display: block;
        }

        /* Đảm bảo căn giữa toàn bộ menu */
        .nav.flex-lg-row {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .nav-item.user-dropdown {
                margin: 10px 0;
            }

            .nav-item.user-dropdown .nav-link {
                justify-content: flex-start;
            }

            .nav.flex-lg-row {
                justify-content: flex-start;
            }

            .nav-item.dropdown:hover .dropdown-menu,
            .dropdown.user-dropdown:hover .dropdown-menu {
                display: none;
            }

            .dropdown-menu li a {
                font-size: 13px !important;
            }
        }

        @media (max-width: 575.98px) {
            .nav-item.dropdown:hover .dropdown-menu,
            .dropdown.user-dropdown:hover .dropdown-menu {
                display: none;
            }

            .dropdown-menu li a {
                font-size: 13px !important;
            }
        }
    </style>
    <style>
        Dropdown menu
.dropdown-menu {
    background: #1A1A1A; /* Nền đen đậm */
    border: 1px solid #333333; /* Viền xám đậm */
    border-radius: 10px;
    padding: 15px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s linear 0.3s;
}

/* Tiêu đề trong dropdown */
.dropdown-menu h6 {
    color: #FFFFFF; /* Màu trắng */
    font-family: 'Orbitron', sans-serif;
    font-size: 1rem;
    border-left: 4px solid #000000; /* Đường viền đen */
}

/* Card rạp */
.theater-card {
    background: #222222; /* Nền đen nhạt */
    border: 1px solid #333333; /* Viền xám đậm */
    border-radius: 8px;
    padding: 15px;
    height: 150px; /* Chiều cao cố định */
    display: flex;
    flex-direction: column;
    justify-content: space-between; /* Căn chỉnh nội dung */
    transition: transform 0.3s ease;
}

.theater-card:hover {
    transform: scale(1.02); /* Hiệu ứng phóng to nhẹ khi hover */
}

/* Tên rạp */
.theater-title {
    color: #FFFFFF; /* Màu trắng */
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 5px;
    white-space: nowrap; /* Không xuống dòng */
    overflow: hidden; /* Ẩn phần thừa */
    text-overflow: ellipsis; /* Thêm dấu ba chấm */
}

/* Địa chỉ */
.theater-address {
    color: #BBBBBB; /* Xám sáng */
    font-size: 0.8rem;
    margin-bottom: 8px;
    display: -webkit-box;
    -webkit-line-clamp: 2; /* Giới hạn 2 dòng */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis; /* Thêm dấu ba chấm */
}

/* Nút Xem chi tiết */
.view-details {
    background: #333333; /* Nền xám đậm */
    color: #FFFFFF; /* Text trắng */
    padding: 6px 10px;
    border-radius: 15px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
    align-self: flex-start; /* Căn nút về phía trên */
}

.view-details:hover {
    background: #555555; /* Xám sáng hơn khi hover */
    color: #FFFFFF; /* Text trắng */
}

/* Tùy chỉnh thanh cuộn */
.dropdown-menu::-webkit-scrollbar {
    width: 8px;
}

.dropdown-menu::-webkit-scrollbar-track {
    background: #1A1A1A; /* Nền thanh cuộn */
}

.dropdown-menu::-webkit-scrollbar-thumb {
    background: #555555; /* Màu thanh cuộn */
    border-radius: 4px;
}

.dropdown-menu::-webkit-scrollbar-thumb:hover {
    background: #777777; /* Màu khi hover */
}
        </style>
</head>
<body>
    <nav class="navbar sticky-top navbar-expand-lg navbar-dark bg-dark navbar-blur p-3">
        <div class="container">
            <div class="container-fluid">
                <div class="flex-lg-nowrap d-flex align-items-center justify-content-center">
                    <!-- Logo -->
                    <div class="col-6 d-lg-flex row text-center col-lg-2 justify-content-center me-lg-auto mb-md-0">
                        <div class="col-6">
                            <a href="http://localhost:3000/BanVeXemPhim/index.php" class="navbar-brand me-3">
                                <img src="/BanVeXemPhim/assets/imgs/logo1.jpeg" style="width: 60px;" class="bg-dark rounded-circle">
                            </a>
                        </div>
                        <div class="text d-none d-lg-block col-6 position-relative mt-1">
                            <p class="mb-0 position-absolute" style="left: -20px;">
                                <span class="word cgv">CGV</span>
                                <span class="word lotte">Lotte</span>
                                <span class="word bhd">BHD Star</span>
                                <span class="word galaxy">Galaxy</span>
                                <span class="word cinestar">CineStar</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-6 d-lg-none align-content-end text-end ms-4 ms-lg-0">
                        <button class="btn btn-dark" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasMenu" aria-controls="offcanvasMenu">
                            <i class="bi bi-list"></i>
                        </button>
                    </div>
                    <!-- Offcanvas sidebar cho menu trên màn hình nhỏ -->
                    <div class="offcanvas offcanvas-top w-100 h-50" tabindex="-1" id="offcanvasMenu"
                        aria-labelledby="offcanvasMenuLabel">
                        <div class="offcanvas-header">
                            <h5 class="offcanvas-title" id="offcanvasMenuLabel">Menu</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            <?php
                            $items = getMenu('Menu');
                            $baseUrl = "http://" . $_SERVER['HTTP_HOST'] . "/BanVeXemPhim/";
                            if (!empty($items) && is_array($items)) {
                            ?>
                            <ul class="nav flex-lg-row flex-column col-lg-10 col-sm-12 align-items-center justify-content-start justify-content-lg-center mb-md-0">
                            <?php foreach ($items as $item): ?>
    <?php
    $lienKet = isset($item['LienKet']) ? $item['LienKet'] : '#';
    $tenMenu = isset($item['TenMenu']) ? htmlspecialchars($item['TenMenu']) : 'Không xác định';
    ?>
    <li class="nav-item dropdown mx-2">
        <a href="<?= $baseUrl . $lienKet ?>" aria-expanded="false"
           id="<?= ($tenMenu == 'Phim') ? 'phim' : (($tenMenu == 'Rạp') ? 'rap' : '') ?>"
           class="nav-link px-2 fw-bolder text-capitalize <?= ($current_url === $baseUrl . $lienKet) ? 'active' : '' ?>">
            <i class="bi <?= ($tenMenu == 'Phim') ? 'bi-film' : ($tenMenu == 'Tin Tức' ? 'bi-newspaper' : ($tenMenu == 'Trang Chủ' ? 'bi-house' : ($tenMenu == 'Liên Hệ' ? 'bi-telephone' : ($tenMenu == 'Rạp' ? 'bi-building' : 'bi-info-circle')))) ?>"></i>
            <?= $tenMenu ?>
        </a>

        <?php if ($tenMenu == 'Phim'): ?>
            <!-- Dropdown cho Phim (giữ nguyên như cũ) -->
            <ul class="dropdown-menu shadow border-0 w-100 py-3 px-2" style="width:45rem !important; left: -50px;" aria-labelledby="phim">
                <li class="px-3 py-2">
                    <h6 class="mb-3 text-uppercase ps-3" style="border-left: 4px solid #15036c; color:#000;">
                        Phim đang chiếu
                    </h6>
                    <div class="row g-3">
                        <?php
                        $films = getFilm('1');
                        if (!empty($films) && is_array($films)) {
                            shuffle($films);
                            $films = array_slice($films, 0, 4);
                            foreach ($films as $film) {
                                $filmId = isset($film['MaPhim']) ? $film['MaPhim'] : '';
                                $filmName = isset($film['TenPhim']) ? htmlspecialchars($film['TenPhim']) : 'Không xác định';
                                $filmImage = isset($film['Anh']) ? $film['Anh'] : 'default.jpg';
                                $filmAge = isset($film['PhanLoai']) ? $film['PhanLoai'] : 'N/A';
                                ?>
                                <div class="col-12 col-sm-12 col-md-6 col-lg-3">
                                    <div class="movie-card card">
                                        <img class="img-fluid" style="height: 200px; width:280px"
                                             src="/BanVeXemPhim/uploads/film-imgs/<?= $filmImage ?>"
                                             alt="<?= $filmName ?>">
                                        <span class="movie-age"><?= $filmAge ?></span>
                                        <a style="width: 100px; font-size: 13px; padding: 10px 7px"
                                           href="/BanVeXemPhim/views/detail-film.php?id=<?= $filmId ?>"
                                           class="buy-ticket text-center align-items-center">
                                            <i class="bi bi-ticket-perforated"></i> Mua Vé
                                        </a>
                                    </div>
                                    <div class="movie-info">
                                        <small class="movie-title fs-6 fw-bold" style="color:#000;"><?= $filmName ?></small>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<p>Không có phim đang chiếu.</p>';
                        }
                        ?>
                    </div>
                </li>
                <li class="px-3 py-2 mt-2">
                    <h6 class="mb-3 text-uppercase ps-3" style="border-left: 4px solid #15036c; color:#000;">
                        Phim sắp chiếu
                    </h6>
                    <div class="row g-3">
                        <?php
                        $films = getFilm('2');
                        if (!empty($films) && is_array($films)) {
                            shuffle($films);
                            $films = array_slice($films, 0, 4);
                            foreach ($films as $film) {
                                $filmId = isset($film['MaPhim']) ? $film['MaPhim'] : '';
                                $filmName = isset($film['TenPhim']) ? htmlspecialchars($film['TenPhim']) : 'Không xác định';
                                $filmImage = isset($film['Anh']) ? $film['Anh'] : 'default.jpg';
                                $filmAge = isset($film['PhanLoai']) ? $film['PhanLoai'] : 'N/A';
                                ?>
                                <div class="col-12 col-sm-12 col-md-6 col-lg-3">
                                    <div class="movie-card card">
                                        <img class="img-fluid" style="height: 200px; width:280px"
                                             src="/BanVeXemPhim/uploads/film-imgs/<?= $filmImage ?>"
                                             alt="<?= $filmName ?>">
                                        <span class="movie-age"><?= $filmAge ?></span>
                                        <a style="width: 100px; font-size: 13px; padding: 10px 7px"
                                           href="/BanVeXemPhim/views/detail-film.php?id=<?= $filmId ?>"
                                           class="buy-ticket text-center align-items-center">
                                            <i class="bi bi-ticket-perforated"></i> Mua Vé
                                        </a>
                                    </div>
                                    <div class="movie-info">
                                        <small class="movie-title fs-6 fw-bold" style="color:#000;"><?= $filmName ?></small>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<p>Không có phim sắp chiếu.</p>';
                        }
                        ?>
                    </div>
                </li>
            </ul>
            <?php elseif ($tenMenu == 'Rạp'): ?>
    <!-- Dropdown cho Rạp -->
    <ul class="dropdown-menu shadow border-0 py-3 px-2" style="width: 50rem !important; left: -50px; max-height: 500px; overflow-y: auto;" aria-labelledby="rap">
        <li class="px-3 py-2">
            <h6 class="mb-3 text-uppercase ps-3" style="border-left: 4px solid #000000; color:#000;">
                Danh sách rạp chiếu phim
            </h6>
            <div class="row g-3">
                <?php
                $theaters = getAll('rapchieuphim'); // Lấy tất cả rạp từ bảng rapchieuphim
                if ($theaters && mysqli_num_rows($theaters) > 0) {
                    while ($theater = mysqli_fetch_assoc($theaters)) {
                        $theaterId = isset($theater['MaRap']) ? $theater['MaRap'] : '';
                        $theaterName = isset($theater['TenRap']) ? htmlspecialchars($theater['TenRap']) : 'Không xác định';
                        $theaterAddress = isset($theater['DiaChi']) ? htmlspecialchars($theater['DiaChi']) : 'Không có địa chỉ';
                        ?>
                        <div class="col-12 col-sm-12 col-md-4 col-lg-4"> <!-- 3 cột mỗi hàng -->
                            <div class="theater-card">
                                <h6 class="theater-title fs-6 fw-bold"><?= $theaterName ?></h6>
                                <p class="theater-address small mb-2"><?= $theaterAddress ?></p>
                                <!-- <a style="font-size: 12px; padding: 6px 10px;"
                                   href="/BanVeXemPhim/views/theater-details.php?id=<?= $theaterId ?>"
                                   class="view-details text-center align-items-center">
                                    <i class="bi bi-eye"></i> Xem chi tiết
                                </a> -->
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p>Không có rạp chiếu phim nào.</p>';
                }
                ?>
            </div>
        </li>
    </ul>
<?php endif; ?>
    </li>
<?php endforeach; ?>


                                <!-- Thêm user dropdown vào đây -->
                                <li class="nav-item dropdown user-dropdown mx-2">
                                    <?php if (isset($_SESSION['NDId']) && $_SESSION['NDloggedIn'] == true): ?>
                                        <a href="#" class="nav-link px-2 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <img src="<?= $baseUrl . 'uploads/avatars/' . (!empty($user['data']['Anh']) ? $user['data']['Anh'] : 'user-icon.png') ?>"
                                                alt="User Avatar" width="40" height="40" class="rounded-circle">
                                        </a>
                                        <ul class="dropdown-menu user-menu shadow border-0">
                                            <li class="dropdown-css">
                                                <a class="dropdown-item">
                                                    <img src="<?= $baseUrl ?>/assets/imgs/wave.gif" class="bg-transparent"
                                                        width="25px" height="25px" alt="">
                                                    <span class="fw-bold">Xin chào, </span>
                                                    <div class="d-flex overflow-visible">
                                                        <span class="fw-bold user-name" style="background: linear-gradient(to right, #30CFD0 0%, #330867 100%);
                                                        background-clip: text; color: transparent; white-space: nowrap;
                                                        overflow: hidden; text-overflow: ellipsis;">
                                                            <?= isset($user['data']['TenND']) ? htmlspecialchars($user['data']['TenND']) : 'Người dùng' ?>
                                                        </span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="dropdown-css">
                                                <a class="dropdown-item fw-bold" href="<?= $baseUrl ?>views/profile-user.php">
                                                    <i class="bi bi-person-video2"></i> Trang người dùng
                                                </a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li class="dropdown-css">
                                                <a class="dropdown-item fw-bold" href="<?= $baseUrl ?>views/logout.php">
                                                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                                                </a>
                                            </li>
                                        </ul>
                                    <?php else: ?>
                                        <a href="<?= $baseUrl ?>views/login.php" class="nav-link px-2 fw-bolder text-capitalize">
                                            <i class="bi bi-person-circle"></i> Đăng nhập
                                        </a>
                                    <?php endif; ?>
                                </li>
                            </ul>
                            <?php
                            } else {
                                echo '<ul class="nav flex-lg-row flex-column col-lg-10 col-sm-12 align-items-center justify-content-start justify-content-lg-center mb-md-0">';
                                echo '<li class="nav-item"><a href="#" class="nav-link px-2 fw-bolder text-capitalize text-secondary">Không có menu</a></li>';
                                echo '</ul>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var words = document.getElementsByClassName('word');
        var wordArray = [];
        var currentWord = 0;

        words[currentWord].style.opacity = 1;
        for (var i = 0; i < words.length; i++) {
            splitLetters(words[i]);
        }

        function changeWord() {
            var cw = wordArray[currentWord];
            var nw = currentWord == words.length - 1 ? wordArray[0] : wordArray[currentWord + 1];
            for (var i = 0; i < cw.length; i++) {
                animateLetterOut(cw, i);
            }

            for (var i = 0; i < nw.length; i++) {
                nw[i].className = 'letter behind';
                nw[0].parentElement.style.opacity = 1;
                animateLetterIn(nw, i);
            }

            currentWord = (currentWord == wordArray.length - 1) ? 0 : currentWord + 1;
        }

        function animateLetterOut(cw, i) {
            setTimeout(function() {
                cw[i].className = 'letter out';
            }, i * 80);
        }

        function animateLetterIn(nw, i) {
            setTimeout(function() {
                nw[i].className = 'letter in';
            }, 340 + (i * 80));
        }

        function splitLetters(word) {
            var content = word.innerHTML;
            word.innerHTML = '';
            var letters = [];
            for (var i = 0; i < content.length; i++) {
                var letter = document.createElement('span');
                letter.className = 'letter';
                letter.innerHTML = content.charAt(i);
                word.appendChild(letter);
                letters.push(letter);
            }
            wordArray.push(letters);
        }

        changeWord();
        setInterval(changeWord, 4000);
        // Xử lý độ trễ cho dropdown
document.querySelectorAll('.nav-item.dropdown, .nav-item.user-dropdown').forEach(dropdown => {
    const dropdownMenu = dropdown.querySelector('.dropdown-menu');
    let timeout;

    // Khi hover vào
    dropdown.addEventListener('mouseenter', () => {
        clearTimeout(timeout); // Hủy timeout nếu đang có
        dropdownMenu.style.display = 'block';
        dropdownMenu.style.opacity = '1';
        dropdownMenu.style.visibility = 'visible';
        dropdownMenu.style.transform = dropdown.classList.contains('user-dropdown') 
            ? 'translateX(-80%) translateY(0)' 
            : 'translateY(0)';
    });

    // Khi chuột rời khỏi
    dropdown.addEventListener('mouseleave', () => {
        timeout = setTimeout(() => {
            dropdownMenu.style.opacity = '0';
            dropdownMenu.style.visibility = 'hidden';
            dropdownMenu.style.transform = dropdown.classList.contains('user-dropdown') 
                ? 'translateX(-80%) translateY(10px)' 
                : 'translateY(10px)';
        }, 2000); // Độ trễ 2 giây (2000ms)
    });

    // Khi hover vào chính dropdown menu, giữ nó hiển thị
    dropdownMenu.addEventListener('mouseenter', () => {
        clearTimeout(timeout);
        dropdownMenu.style.display = 'block';
        dropdownMenu.style.opacity = '1';
        dropdownMenu.style.visibility = 'visible';
        dropdownMenu.style.transform = dropdown.classList.contains('user-dropdown') 
            ? 'translateX(-80%) translateY(0)' 
            : 'translateY(0)';
    });

    // Khi chuột rời khỏi dropdown menu
    dropdownMenu.addEventListener('mouseleave', () => {
        timeout = setTimeout(() => {
            dropdownMenu.style.opacity = '0';
            dropdownMenu.style.visibility = 'hidden';
            dropdownMenu.style.transform = dropdown.classList.contains('user-dropdown') 
                ? 'translateX(-80%) translateY(10px)' 
                : 'translateY(10px)';
        }, 2000); // Độ trễ 2 giây
    });
});
    </script>
</body>
</html>