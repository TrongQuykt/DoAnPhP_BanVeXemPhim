<div class="px-4">
    <div class="d-none d-md-none d-lg-flex flex-column align-items-center w-100">
        <?php
        // Lấy id của phim hiện tại từ URL
        $currentFilmId = isset($_GET['id']) ? $_GET['id'] : null;

        // Lấy danh sách phim đang chiếu
        $items = getFilm('1');

        // Loại bỏ phim hiện tại (phim đang xem) khỏi danh sách
        if ($currentFilmId) {
            $items = array_filter($items, function($item) use ($currentFilmId) {
                return $item['MaPhim'] != $currentFilmId;
            });
        }

        // Chuyển mảng về dạng chỉ số liên tục sau khi lọc
        $items = array_values($items);

        // Lấy ngẫu nhiên các phim
        if (!empty($items)) {
            shuffle($items); // Xáo trộn danh sách phim
            $items = array_slice($items, 0, 3); // Lấy tối đa 3 phim
        }

        // Hiển thị danh sách phim
        foreach ($items as $value => $item): ?>
            <div class="col-11 mb-4 movie-item">
                <div class="movie-card card">
                    <img class="img-fluid" src="/BanVeXemPhim/uploads/film-imgs/<?= $item['Anh'] ?>"
                        alt="<?= $item['TenPhim'] ?>">
                    <span class="movie-age"><?= $item['PhanLoai'] ?></span>
                    <a href="/BanVeXemPhim/views/detail-film.php?id=<?= $item['MaPhim'] ?>" class="buy-ticket">
                        <i class="bi bi-ticket-perforated"></i> Mua Vé
                    </a>
                </div>
                <div class="movie-info">
                    <div class="movie-title"><?= $item['TenPhim'] ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<style>
    .movie-card img {
        height: 300px;
    }
    .buy-ticket {
        padding: 6px 6px;
    }
</style>