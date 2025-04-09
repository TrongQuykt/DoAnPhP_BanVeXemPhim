<?php
include('../includes/header.php');
include_once('../config/function.php');

$id_result = check_valid_ID('id');
if (!is_numeric($id_result)) {
    echo '<h5 class="text-danger text-center">' . htmlspecialchars($id_result) . '</h5>';
    return false;
}

$item = getByID('BaiViet', 'Id', $id_result);
?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Orbitron', sans-serif;
    }

    body {
        background: #0d0d0d;
        color: #fff;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }

    /* Nút Trở về */
    .btn-back {
        background: transparent;
        border: 2px solid #ff00ff;
        color: #ff00ff;
        padding: 10px 20px;
        border-radius: 5px;
        font-weight: 600;
        text-transform: uppercase;
        transition: all 0.3s ease;
        margin: 30px 0;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-back:hover {
        background: #ff00ff;
        color: #000;
        box-shadow: 0 0 20px #ff00ff;
    }

    /* Tiêu đề bài viết */
    .article-title {
        font-size: 2.0rem;
        font-weight: 700;
        color: #ff00ff;
        text-shadow: 0 0 10px #ff00ff;
        text-transform: uppercase;
        margin-bottom: 30px;
        text-align: center;
    }

    /* Hình ảnh chính */
    .main-image {
        width: 100%;
        max-width: 600px;
        border-radius: 15px;
        border: 2px solid #00ffff;
        box-shadow: 0 0 15px rgba(0, 255, 255, 0.3);
        transition: all 0.3s ease;
        display: block;
        margin: 0 auto 30px;
    }

    .main-image:hover {
        box-shadow: 0 0 25px rgba(0, 255, 255, 0.5);
        transform: scale(1.02);
    }

    /* Nội dung bài viết */
    .content-area {
        background: rgba(20, 20, 20, 0.9);
        padding: 30px;
        border-radius: 15px;
        border: 1px solid #ff00ff;
        box-shadow: 0 0 20px rgba(255, 0, 255, 0.2);
        margin-bottom: 40px;
    }

    .content-area h2 {
        font-size: 1.8rem;
        font-weight: 600;
        color: #00ffff;
        border-bottom: 2px solid #00ffff;
        padding-bottom: 10px;
        margin-bottom: 20px;
        text-transform: uppercase;
    }

    .content-area p {
        font-size: 1rem;
        line-height: 1.8;
        color: #ddd;
        text-align: justify;
        margin-bottom: 20px;
    }

    /* Sidebar */
    .currently-showing h4 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #fff;
        border-left: 5px solid #ff00ff;
        padding-left: 15px;
        margin-bottom: 20px;
        text-transform: uppercase;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .article-title {
            font-size: 2rem;
        }

        .main-image {
            max-width: 100%;
        }
    }

    @media (max-width: 576px) {
        .article-title {
            font-size: 1.5rem;
        }

        .content-area {
            padding: 20px;
        }

        .content-area h2 {
            font-size: 1.5rem;
        }

        .content-area p {
            font-size: 0.9rem;
        }
    }
</style>

<div class="container">
    <?php if ($item['status'] == 200) { ?>
        <!-- <a href="http://localhost/BanVeXemPhim/views/list-content-all.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Trở về
        </a> -->
        <div class="row flex-nowrap" style="padding-top: 20px;">
            <div class="col-lg-9">
                <!-- Tiêu đề bài viết -->
                <h4 class="article-title"><?= htmlspecialchars($item['data']['TenBV']) ?></h4>

                <!-- Hình ảnh chính -->
                <div class="text-center">
                    <?php
                    $anhArray = explode(',', $item['data']['Anh']);
                    if (!empty($anhArray[0])) {
                        $anh = trim($anhArray[0]);
                        echo '<img src="/BanVeXemPhim/uploads/content-imgs/' . htmlspecialchars($anh) . '" alt="Ảnh xem trước" class="main-image"/>';
                    }
                    ?>
                </div>

                <!-- Nội dung bài viết -->
                <div class="content-area">
                    <h2>Nội dung bài viết</h2>
                    <?php
                    $paragraphs = explode(PHP_EOL, $item['data']['ChiTiet']);
                    foreach ($paragraphs as $paragraph) {
                        $trimmedParagraph = trim($paragraph);
                        if (!empty($trimmedParagraph)) {
                            echo '<p>' . nl2br(htmlspecialchars($trimmedParagraph)) . '</p>';
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-3 currently-showing">
                <h4>Phim Đang Chiếu</h4>
                <?php include("currently-showing.php"); ?>
            </div>
        </div>
    <?php } else { ?>
        <h5 class="text-danger text-center"><?= htmlspecialchars($item['message']) ?></h5>
    <?php } ?>
</div>

<?php include('../includes/footer.php'); ?>