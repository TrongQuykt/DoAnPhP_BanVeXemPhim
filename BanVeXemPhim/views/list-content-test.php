<?php
require_once("config/function.php");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Bài Viết</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Orbitron -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #0d0d0d;
            color: #fff;
            font-family: 'Orbitron', sans-serif;
        }

        .content-section {
            background: rgba(20, 20, 20, 0.9);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(255, 0, 255, 0.2);
        }

        .section-title {
            text-transform: uppercase;
            font-size: 2rem;
            color: #ff00ff;
            text-shadow: 0 0 10px #ff00ff;
            border-left: 5px solid #00ffff;
            padding-left: 15px;
            margin-bottom: 30px;
        }

        .main-article .article-card {
            position: relative;
            overflow: hidden;
            height: 400px;
            border-radius: 10px;
        }

        .main-article img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .main-article:hover img {
            transform: scale(1.1);
        }

        .article-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
            padding: 20px;
            color: #fff;
        }

        .sub-articles .sub-article-card {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .sub-articles .sub-article-card:hover {
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.5);
        }

        .sub-articles .sub-article-card img {
            width: 50%;
            height: 120px;
            object-fit: cover;
            border-radius: 10px 0 0 10px;
        }

        .sub-article-overlay {
            padding: 10px;
            color: #fff;
            flex: 1;
        }

        .btn-neon {
            padding: 10px 30px;
            background: transparent;
            border: 2px solid #ff00ff;
            color: #ff00ff;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }

        .btn-neon:hover {
            background: #ff00ff;
            color: #000;
            box-shadow: 0 0 20px #ff00ff;
        }
    </style>
</head>
<body>
    <div class="container mt-5 w-75 content-section">
        <h4 class="section-title">Góc Điện Ảnh</h4>
        <div class="row">
            <?php
            // Lấy tất cả các bài viết
            $result = getAll('BaiViet');

            // Kiểm tra nếu có ít nhất một bài viết
            if ($result && $result->num_rows > 0) {
                // Lấy bài viết đầu tiên
                $firstItem = $result->fetch_assoc();
            ?>

            <!-- Bài viết lớn bên trái -->
            <div class="col-lg-7 main-article">
                <a href="views/detail-content.php?id=<?= $firstItem['Id'] ?>" class="card-link">
                    <div class="article-card">
                        <?php
                        $anhArray = explode(',', $firstItem['Anh']);
                        if (!empty($anhArray[0])) {
                            $anh = trim($anhArray[0]);
                            echo '<img id="img-content-top" src="/BanVeXemPhim/uploads/content-imgs/' . htmlspecialchars($anh) . '" class="rounded" alt="Article image">';
                        }
                        ?>
                        <div class="article-overlay">
                            <h5 class="card-title"><?= htmlspecialchars($firstItem['TenBV']) ?></h5>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Các bài viết nhỏ bên phải -->
            <div class="col-lg-5 sub-articles">
                <?php
                // Đặt bộ đếm cho bài viết
                $count = 0;

                // Duyệt qua các bài viết còn lại và chỉ hiển thị tối đa 3 bài viết
                while ($item = $result->fetch_assoc()) {
                    if ($count >= 3) break; // Ngừng vòng lặp sau khi hiển thị 3 bài viết

                    // Kiểm tra xem $item có hợp lệ không trước khi truy cập vào các phần tử
                    if ($item) {
                ?>
                <a href="views/detail-content.php?id=<?= $item['Id'] ?>" class="card-link">
                    <div class="sub-article-card">
                        <?php
                        $anhArray = explode(',', $item['Anh']);
                        if (!empty($anhArray[0])) {
                            $anh = trim($anhArray[0]);
                            echo '<img src="/BanVeXemPhim/uploads/content-imgs/' . htmlspecialchars($anh) . '" alt="Article image">';
                        }
                        ?>
                        <div class="sub-article-overlay">
                            <h5 class="card-title"><?= htmlspecialchars($item['TenBV']) ?></h5>
                        </div>
                    </div>
                </a>
                <?php
                        $count++; // Tăng bộ đếm sau mỗi bài viết
                    }
                }
                ?>
            </div>
            <?php } ?>
        </div>

        <!-- Nút xem thêm -->
        <div class="text-center mt-4">
            <a href="views/list-content-all.php" class="btn-neon">Xem Thêm</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>