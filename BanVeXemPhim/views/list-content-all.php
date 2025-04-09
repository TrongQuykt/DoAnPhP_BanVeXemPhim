<?php
$title = 'Góc điện ảnh';
include('../includes/header.php');
?>

<style>
    /* Nền tổng thể */
    body {
        background-color: #0d0d0d;
        color: #fff;
    }

    /* Container chính */
    .blog-container {
        margin-top: 3rem;
        margin-bottom: 3rem;
    }

    /* Tiêu đề phần */
    .section-title {
        font-family: 'Orbitron', sans-serif;
        font-weight: 700;
        color: #00ffff;
        text-transform: uppercase;
        border-left: 4px solid #15036c;
        padding-left: 1rem;
        margin-bottom: 2rem;
    }

    /* Card bài viết */
    .blog-card {
        background: #1a1a1a;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        margin-bottom: 2rem;
    }

    .blog-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 255, 255, 0.2);
    }

    /* Hình ảnh bài viết */
    .blog-image {
        width: 100%;
        height: 400px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .blog-card:hover .blog-image {
        transform: scale(1.05);
    }

    /* Nội dung bài viết */
    .blog-content {
        padding: 1.5rem;
    }

    .blog-title {
        font-family: 'Orbitron', sans-serif;
        font-size: 1.5rem;
        font-weight: 600;
        color: #00ffff;
        margin-bottom: 0.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .blog-meta {
        font-size: 0.9rem;
        color: #888;
        margin-bottom: 0.75rem;
    }

    .blog-meta i {
        color: #ff00ff;
        margin-right: 0.3rem;
    }

    .blog-excerpt {
        font-size: 1rem;
        color: #ccc;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Nút đọc thêm */
    .read-more {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: linear-gradient(90deg, #ff00ff, #00ffff);
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        font-family: 'Orbitron', sans-serif;
        font-weight: 600;
        transition: background 0.3s ease, transform 0.3s ease;
    }

    .read-more:hover {
        background: linear-gradient(90deg, #00ffff, #ff00ff);
        transform: translateY(-2px);
    }

    /* Cột bên phải (Phim đang chiếu) */
    .sidebar-title {
        font-family: 'Orbitron', sans-serif;
        font-weight: 700;
        color: #00ffff;
        text-transform: uppercase;
        border-left: 4px solid #000;
        padding-left: 1rem;
        margin-bottom: 2rem;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        .blog-card {
            margin-bottom: 1.5rem;
        }

        .blog-image {
            height: 150px;
        }

        .blog-title {
            font-size: 1.25rem;
        }

        .blog-excerpt {
            font-size: 0.9rem;
            -webkit-line-clamp: 2;
        }
    }

    @media (max-width: 767.98px) {
        .blog-image {
            height: 200px;
        }

        .blog-content {
            padding: 1rem;
        }

        .blog-title {
            font-size: 1.1rem;
        }

        .blog-excerpt {
            font-size: 0.85rem;
        }

        .read-more {
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
        }
    }
</style>

<div class="container blog-container">
    <div class="row g-4">
        <!-- Cột chính: Danh sách bài viết -->
        <div class="col-12 col-lg-9">
            <h4 class="section-title">Góc điện ảnh</h4>
            <div class="row">
                <?php
                $items = getAll('BaiViet');
                if (!empty($items)) {
                    foreach ($items as $item):
                ?>
                    <div class="col-12">
                        <a href="detail-content.php?id=<?= $item['Id'] ?>" class="text-decoration-none">
                            <div class="blog-card">
                                <?php
                                $anhArray = explode(',', $item['Anh']);
                                if (!empty($anhArray[0])) {
                                    $anh = trim($anhArray[0]);
                                    echo '<img src="../uploads/content-imgs/' . htmlspecialchars($anh) . '" alt="' . htmlspecialchars($item['TenBV']) . '" class="blog-image">';
                                }
                                ?>
                                <div class="blog-content">
                                    <h5 class="blog-title"><?= htmlspecialchars($item['TenBV']) ?></h5>
                                    <div class="blog-meta">
                                        <i class="bi bi-calendar"></i>
                                        <span><?= isset($item['NgayTao']) ? date('d/m/Y', strtotime($item['NgayTao'])) : 'N/A' ?></span>
                                    </div>
                                    <p class="blog-excerpt"><?= htmlspecialchars($item['MoTa']) ?></p>
                                    <span class="read-more">Đọc thêm</span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php
                    endforeach;
                } else {
                    echo '<p class="text-center text-muted">Chưa có bài viết nào.</p>';
                }
                ?>
            </div>
        </div>

        <!-- Cột bên phải: Phim đang chiếu -->
        <div class="col-12 col-lg-3">
            <h4 class="sidebar-title">Phim đang chiếu</h4>
            <?php include("currently-showing.php"); ?>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>