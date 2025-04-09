<?php
include_once("../config/function.php");
$name = getByID('Phim', 'MaPhim', check_valid_ID('id'));
$title = 'Đặt vé - ' . $name['data']['TenPhim'] . '';

include('../includes/header.php');
$item = getByID('Phim', 'MaPhim', check_valid_ID('id'));

// Lấy danh sách khu vực
$queryKhuVuc = "SELECT * FROM khuvuc WHERE TrangThai = 1";
$resultKhuVuc = $conn->query($queryKhuVuc);
$khuVucList = [];
while ($khuVuc = $resultKhuVuc->fetch_assoc()) {
    $khuVucList[] = $khuVuc;
}

// Lấy danh sách rạp chiếu phim cho từng khu vực
$rapChieuData = [];
foreach ($khuVucList as $khuVuc) {
    $khuVucId = $khuVuc['MaKhuVuc'];
    $query = "SELECT * FROM rapchieuphim WHERE MaKhuVuc = ? AND TrangThai = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $khuVucId);
    $stmt->execute();
    $result = $stmt->get_result();

    $rapChieu = [];
    while ($row = $result->fetch_assoc()) {
        $rapChieu[] = $row;
    }
    $rapChieuData[$khuVucId] = $rapChieu;
    $stmt->close();
}
?>

<style>
/* Reset và thiết lập cơ bản */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    background-color: #0a0a0a;
    color: #fff;
}

/* Banner */
.banner {
    position: relative;
    height: 500px;
    overflow: hidden;
}

.banner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.9));
}

.banner-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(0.7);
}

/* Container chính */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

/* Phần thông tin phim */
.movie-header {
    display: flex;
    align-items: flex-start;
    margin-top: -100px;
    position: relative;
    z-index: 1;
}

.movie-poster {
    width: 300px;
    border-radius: 15px;
    border: 3px solid #ff00ff;
    box-shadow: 0 0 20px rgba(255, 0, 255, 0.3);
    transition: transform 0.3s ease;
}

.movie-poster:hover {
    transform: scale(1.05);
}

.movie-info {
    margin-left: 30px;
    flex: 1;
}

.movie-title-detail {
    font-size: 1.5rem;
    font-weight: 700;
    color: #fff;
    text-shadow: 0 0 10px rgba(255, 0, 255, 0.5);
}

.movie-age-detail {
    background-color: #ff4444;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 0.9rem;
    font-weight: 600;
}

.movie-meta {
    margin-top: 10px;
    display: flex;
    gap: 20px;
    font-size: 1rem;
    color: #ccc;
}

.movie-meta i {
    color: #ff00ff;
    margin-right: 5px;
}

.movie-details {
    margin-top: 20px;
    font-size: 1rem;
    color: #ddd;
}

.movie-details p {
    margin-bottom: 10px;
}

.movie-details strong {
    color: #ff00ff;
}

/* Nút Xem Trailer */
.btn-trailer {
    background: linear-gradient(135deg, #ff4b2b, #ff416c);
    border: none;
    color: white;
    font-size: 1.1rem;
    font-weight: bold;
    padding: 12px 25px;
    border-radius: 30px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-transform: uppercase;
    margin-top: 20px;
}

.btn-trailer i {
    font-size: 1.3rem;
}

.btn-trailer:hover {
    background: linear-gradient(135deg, #ff416c, #ff4b2b);
    transform: scale(1.05);
    box-shadow: 0 0 15px rgba(255, 65, 108, 0.5);
}

.btn-trailer:active {
    transform: scale(0.95);
}

/* Modal Trailer */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background-color: rgba(0, 0, 0, 0.9);
}

.modal-content {
    background-color: #000;
    margin: 6% auto;
    padding: 0;
    border: none;
    width: 95%;
    height: 85vh;
    max-width: 100%;
    position: relative;
    border-radius: 0;
    box-shadow: none;
}

.close {
    color: #fff;
    font-size: 30px;
    font-weight: bold;
    cursor: pointer;
    position: absolute;
    top: 10px;
    right: 15px;
    z-index: 1001;
}

.close:hover,
.close:focus {
    color: #ccc;
    text-decoration: none;
}

.modal-content iframe {
    width: 100%;
    height: 100%;
    border: none;
}

@media (max-width: 768px) {
    .modal-content {
        width: 100%;
        height: 60vh;
    }

    .close {
        font-size: 24px;
        top: 5px;
        right: 10px;
    }
}

/* Phần nội dung phim */
.movie-content {
    margin-top: 40px;
}

.movie-content h4 {
    font-size: 1.8rem;
    font-weight: 600;
    color: #ff00ff;
    border-bottom: 2px solid #ff00ff;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.movie-content p {
    font-size: 1rem;
    line-height: 1.6;
    color: #000;
    font-weight: 300;
}

/* Phần lịch chiếu */
.showtimes-section {
    margin-top: 50px;
}

.showtimes-section h5 {
    font-size: 2rem;
    font-weight: 700;
    color: #fff;
    border-left: 5px solid #ff00ff;
    padding-left: 15px;
    margin-bottom: 30px;
    text-transform: uppercase;
}

/* Select box */
.form-label {
    font-size: 1.1rem;
    color: #ff00ff;
    margin-bottom: 10px;
}

.form-select {
    background-color: #1a1a1a;
    color: #fff;
    border: 1px solid #ff00ff;
    border-radius: 8px;
    padding: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-select:focus {
    outline: none;
    border-color: #ff66ff;
    box-shadow: 0 0 10px rgba(255, 0, 255, 0.3);
}

/* Danh sách suất chiếu */
.showtime {
    margin-top: 20px;
}

.showtime .film-card {
    background-color: #1a1a1a;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 0 15px rgba(255, 0, 255, 0.1);
    transition: transform 0.3s ease;
}

.showtime .film-card:hover {
    transform: translateY(-5px);
}

.showtime .film-card h6 {
    font-size: 1.4rem;
    font-weight: 600;
    color: #ff00ff;
    margin-bottom: 15px;
    text-align: center;
}

.showtime .date-group {
    margin-bottom: 15px;
}

.showtime .date-group p {
    font-size: 1.1rem;
    font-weight: 500;
    color: #fff;
    margin-bottom: 10px;
}

.showtime ul {
    list-style: none;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.showtime li.time-link a.btn {
    background-color: #ff00ff;
    color: #fff;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.showtime li.time-link a.btn:hover {
    background-color: #ff66ff;
    box-shadow: 0 0 10px rgba(255, 0, 255, 0.5);
}

/* Phim đang chiếu (sidebar) */
.currently-showing {
    margin-top: 50px;
}

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
    .movie-header {
        flex-direction: column;
        align-items: center;
        margin-top: -50px;
    }

    .movie-poster {
        width: 250px;
    }

    .movie-info {
        margin-left: 0;
        margin-top: 20px;
        text-align: center;
    }

    .movie-meta {
        justify-content: center;
    }

    .btn-trailer {
        margin: 20px auto;
    }
}

@media (max-width: 576px) {
    .movie-title-detail {
        font-size: 1.8rem;
    }

    .movie-poster {
        width: 200px;
    }

    .showtimes-section h5 {
        font-size: 1.5rem;
    }
}
</style>
<style>
    .form-label {
    font-size: 1.1rem;
    color: #ff00ff;
    margin-bottom: 10px;
}

.form-select {
    background-color: #1a1a1a;
    color: #fff;
    border: 1px solid #ff00ff;
    border-radius: 8px;
    padding: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-select:focus {
    outline: none;
    border-color: #ff66ff;
    box-shadow: 0 0 10px rgba(255, 0, 255, 0.3);
}
</style>
<div class="banner">
    <div class="banner-overlay">
        <img src="../uploads/film-imgs/<?= htmlspecialchars($item['data']['Banner']) ?>"
            alt="<?= $item['data']['TenPhim'] ?>" class="banner-image">
    </div>
</div>

<?php
$id_result = check_valid_ID('id');
if (!is_numeric($id_result)) {
    echo '<h5>' . $id_result . '</h5>';
    return false;
}

if ($item['status'] == 200) {
?>

<div class="container">
    <div class="row">
        <div class="col-lg-9">
            <!-- Thông tin phim -->
            <div class="movie-header">
                <img src="<?= isset($item['data']['Anh']) ? '../uploads/film-imgs/' . htmlspecialchars($item['data']['Anh']) : '#' ?>"
                    alt="<?= $item['data']['TenPhim'] ?>" class="movie-poster">
                <div class="movie-info" style="padding-top: 120px;">
                    <div class="d-flex align-items-center mb-2">
                        <h5 class="movie-title-detail me-3"><?= htmlspecialchars($item['data']['TenPhim']) ?></h5>
                        <span class="movie-age-detail">
                            <?= htmlspecialchars($item['data']['PhanLoai'] ?? 'Chưa xác định') ?>
                        </span>
                    </div>
                    <div class="movie-meta">
                        <span><i class="bi bi-clock"></i> <?= htmlspecialchars($item['data']['ThoiLuong'] ?? 'Updating...') ?> Phút</span>
                        <span><i class="bi bi-calendar"></i> <?= htmlspecialchars($item['data']['NamPhatHanh'] ?? 'Updating...') ?></span>
                    </div>
                    <div class="movie-details">
                        <p><strong>Quốc gia:</strong> <?= htmlspecialchars($item['data']['QuocGia'] ?? 'Updating...') ?></p>
                        <p><strong>Thể loại:</strong>
                            <?php
                            global $conn;
                            $query = "SELECT GROUP_CONCAT(Theloai.TenTheLoai SEPARATOR ', ') AS TheLoai
                                  FROM PHIM
                                  JOIN THELOAI_FILM ON PHIM.MAPHIM = THELOAI_FILM.MAPHIM
                                  JOIN THELOAI ON THELOAI_FILM.MATHELOAI = THELOAI.MATHELOAI
                                  WHERE PHIM.MAPHIM = {$item['data']['MaPhim']}
                                  GROUP BY PHIM.MAPHIM";
                            $result = $conn->query($query);
                            echo htmlspecialchars($result->fetch_assoc()['TheLoai'] ?? 'Updating...');
                            ?>
                        </p>
                        <p><strong>Đạo diễn:</strong> <?= htmlspecialchars($item['data']['DaoDien'] ?? 'Updating...') ?></p>
                        <p><strong>Diễn viên:</strong> <?= htmlspecialchars($item['data']['DienVien'] ?? 'Updating...') ?></p>
                    </div>
                    <!-- Nút Xem Trailer -->
                    <?php if (!empty($item['data']['Trailer'])): ?>
                        <button class="btn-trailer" onclick="openTrailer('<?= htmlspecialchars($item['data']['Trailer']) ?>')">
                            <i class="fas fa-play-circle"></i> Xem Trailer
                        </button>
                    <?php else: ?>
                        <p class="text-muted">Trailer chưa có sẵn.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Nội dung phim -->
            <div class="movie-content">
                <h4>Nội Dung Phim</h4>
                <p><?= htmlspecialchars($item['data']['MoTa'] ?? 'Updating...') ?></p>
            </div>

<!-- Lịch chiếu -->
<div class="showtimes-section">
    <h5>Lịch Chiếu</h5>

    <!-- Container cho Chọn khu vực và Chọn rạp chiếu -->
    <div id="location-selection">
        <!-- Chọn khu vực -->
        <div class="mb-4" id="khuVucSelectContainer">
            <label for="khuVucSelect" class="form-label">Chọn khu vực:</label>
            <select id="khuVucSelect" class="form-select" onchange="loadRapChieu()">
                <option value="">-- Chọn khu vực --</option>
                <?php
                foreach ($khuVucList as $khuVuc) {
                    echo "<option value='{$khuVuc['MaKhuVuc']}'>{$khuVuc['TenKhuVuc']}</option>";
                }
                ?>
            </select>
        </div>

        <!-- Chọn rạp chiếu -->
        <div class="mb-4" id="rapChieuSelectContainer">
            <label for="rapChieuSelect" class="form-label">Chọn rạp chiếu:</label>
            <select id="rapChieuSelect" class="form-select" onchange="loadShowtimes()" disabled>
                <option value="">-- Chọn rạp chiếu --</option>
            </select>
        </div>
    </div>

    <!-- Container cho Lọc theo phòng chiếu và Hiển thị suất chiếu -->
    <div class="showtime" id="showtimeContainer">
        <p>Vui lòng chọn khu vực và rạp chiếu để xem lịch chiếu.</p>
    </div>
</div>
        </div>

        <!-- Sidebar: Phim đang chiếu -->
        <div class="col-lg-3 currently-showing">
            <h4>Phim Đang Chiếu</h4>
            <?php include("currently-showing.php"); ?>
        </div>
    </div>
</div>

<!-- Modal Trailer -->
<div id="trailerModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeTrailer()">×</span>
        <div id="youtubePlayerContainer"></div>
    </div>
</div>

<!-- JavaScript -->
<script>
// Truyền dữ liệu rạp chiếu từ PHP sang JavaScript
const rapChieuData = <?php echo json_encode($rapChieuData); ?>;
let selectedKhuVuc = "";
let selectedRapChieu = "";

// Biến toàn cục để lưu player
let youtubePlayer;

function openTrailer(videoUrl) {
    console.log("Opening trailer with URL:", videoUrl);
    const modal = document.getElementById("trailerModal");
    const playerContainer = document.getElementById("youtubePlayerContainer");

    if (!modal || !playerContainer) {
        console.error("Modal or player container not found!");
        return;
    }

    let videoId;
    if (videoUrl.includes("youtu.be")) {
        videoId = videoUrl.split('youtu.be/')[1];
    } else if (videoUrl.includes("youtube.com/watch?v=")) {
        videoId = videoUrl.split('v=')[1];
    } else if (videoUrl.includes("youtube.com/embed/")) {
        videoId = videoUrl.split('embed/')[1];
    }

    if (videoId) {
        const ampersandPosition = videoId.indexOf('&');
        if (ampersandPosition !== -1) {
            videoId = videoId.substring(0, ampersandPosition);
        }

        // Hiển thị modal trước khi tạo player
        modal.style.display = "block";
        
        // Tạo player YouTube
        if (youtubePlayer) {
            youtubePlayer.destroy();
        }
        
        youtubePlayer = new YT.Player('youtubePlayerContainer', {
            height: '100%',
            width: '100%',
            videoId: videoId,
            playerVars: {
                'autoplay': 0, // Không tự động phát
                'controls': 1,
                'playsinline': 1,
                'rel': 0,
                'showinfo': 0
            },
            events: {
                'onReady': function(event) {
                    // Bắt đầu phát video khi đã sẵn sàng
                    event.target.playVideo();
                    // Đảm bảo âm thanh được bật
                    event.target.unMute();
                    event.target.setVolume(100);
                }
            }
        });
        
        console.log("Player created with videoId:", videoId);
    } else {
        console.error("Invalid video URL:", videoUrl);
    }
}

function closeTrailer() {
    console.log("Closing trailer");
    const modal = document.getElementById("trailerModal");
    if (modal) {
        modal.style.display = "none";
        if (youtubePlayer) {
            youtubePlayer.stopVideo();
        }
    }
}

window.onclick = function(event) {
    const modal = document.getElementById("trailerModal");
    if (event.target == modal && modal) {
        modal.style.display = "none";
        if (youtubePlayer) {
            youtubePlayer.stopVideo();
        }
    }
}

function loadRapChieu() {
    selectedKhuVuc = document.getElementById('khuVucSelect').value;
    console.log('Selected Khu Vuc:', selectedKhuVuc);
    const rapChieuSelect = document.getElementById('rapChieuSelect');
    rapChieuSelect.innerHTML = '<option value="">-- Chọn rạp chiếu --</option>';
    rapChieuSelect.disabled = true;

    if (selectedKhuVuc && rapChieuData[selectedKhuVuc]) {
        const rapChieuList = rapChieuData[selectedKhuVuc];
        rapChieuList.forEach(rap => {
            const option = document.createElement('option');
            option.value = rap.MaRap;
            option.textContent = rap.TenRap;
            rapChieuSelect.appendChild(option);
        });
        rapChieuSelect.disabled = false;
        console.log('Dropdown enabled, options added:', rapChieuSelect.options.length);
    } else {
        console.error('No theaters available for this area');
    }
    loadShowtimes();
}

function loadShowtimes() {
    selectedRapChieu = document.getElementById('rapChieuSelect').value;
    const showtimeContainer = document.getElementById('showtimeContainer');
    showtimeContainer.innerHTML = '<p>Đang tải lịch chiếu...</p>';

    if (!selectedKhuVuc || !selectedRapChieu) {
        showtimeContainer.innerHTML = '<p>Vui lòng chọn khu vực và rạp chiếu để xem lịch chiếu.</p>';
        return;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const maPhim = urlParams.get('id');

    // Lấy ngày và giờ hiện tại từ trình duyệt
    const now = new Date();
    const currentDate = now.toISOString().split('T')[0]; // Ví dụ: "2025-03-25"
    const currentTime = now.toTimeString().split(' ')[0]; // Ví dụ: "14:00:00"

    console.log('Client date:', currentDate, 'Client time:', currentTime);
    console.log('Fetching showtimes with:', { maRap: selectedRapChieu, maPhim: maPhim });

    // Gửi currentDate và currentTime đến API
    fetch('../config/get_showtimes.php?maRap=' + selectedRapChieu + '&maPhim=' + maPhim + '¤tDate=' + currentDate + '¤tTime=' + currentTime, {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            console.log('Server log:', data.log);

            if (data.status === 200 && data.data && data.data.length > 0) {
                // Nhóm suất chiếu theo phim, ngày và phòng
                let groupedShowtimes = {};
                let rooms = new Set(); // Danh sách phòng chiếu duy nhất

                data.data.forEach(showtime => {
                    const filmName = showtime.TenPhim;
                    const date = showtime.NgayChieu;
                    const room = showtime.TenPhong;
                    const showtimeTime = showtime.GioChieuFormatted; // Ví dụ: "17:00"

                    // So sánh ngày và giờ
                    const showtimeDateTime = new Date(`${date}T${showtimeTime}:00`);
                    const currentDateTime = new Date(`${currentDate}T${currentTime}`);

                    // Chỉ giữ lại suất chiếu có thời gian sau thời gian hiện tại
                    if (showtimeDateTime > currentDateTime) {
                        if (!groupedShowtimes[filmName]) {
                            groupedShowtimes[filmName] = {};
                        }
                        if (!groupedShowtimes[filmName][date]) {
                            groupedShowtimes[filmName][date] = {};
                        }
                        if (!groupedShowtimes[filmName][date][room]) {
                            groupedShowtimes[filmName][date][room] = [];
                        }
                        groupedShowtimes[filmName][date][room].push(showtime);
                        rooms.add(room); // Thêm phòng vào danh sách
                    }
                });

                // Nếu không có suất chiếu nào hợp lệ
                if (Object.keys(groupedShowtimes).length === 0) {
                    showtimeContainer.innerHTML = '<p>Không có suất chiếu nào trong tương lai tại rạp này.</p>';
                    return;
                }

                // Tạo HTML cho thanh select lọc phòng
                let roomFilterHtml = `
                    <div class="mb-4" id="roomSelectContainer">
                        <label for="roomSelect" class="form-label">Lọc theo phòng chiếu:</label>
                        <select id="roomSelect" class="form-select" onchange="filterShowtimesByRoom()">
                            <option value="">-- Tất cả phòng --</option>
                `;
                rooms.forEach(room => {
                    roomFilterHtml += `<option value="${room}">${room}</option>`;
                });
                roomFilterHtml += `</select></div>`;

                // Lưu dữ liệu gốc để lọc sau này
                window.groupedShowtimes = groupedShowtimes;

                // Hiển thị suất chiếu ban đầu (tất cả phòng)
                let showtimesHtml = renderShowtimes(groupedShowtimes);
                showtimeContainer.innerHTML = roomFilterHtml + showtimesHtml;
            } else {
                console.log('No showtimes found:', data);
                showtimeContainer.innerHTML = '<p>Không có suất chiếu nào tại rạp này.</p>';
            }
        })
        .catch(error => {
            console.error('Error fetching showtimes:', error);
            showtimeContainer.innerHTML = '<p>Đã có lỗi xảy ra khi tải lịch chiếu: ' + error.message + '</p>';
        });
}
// Hàm render suất chiếu
// Hàm render suất chiếu
function renderShowtimes(groupedShowtimes) {
    let html = '';
    for (const filmName in groupedShowtimes) {
        html += `<div class="film-card">
                    <h6>${filmName}</h6>`;
        for (const date in groupedShowtimes[filmName]) {
            html += `<div class="date-group">`;
            for (const room in groupedShowtimes[filmName][date]) {
                html += `<div class="date-room-header">
                            <p class="date-label">${date}</p>
                            <h6 class="room-label">PHÒNG: ${room}</h6>
                        </div>
                        <div class="room-group">
                            <ul>`;
                groupedShowtimes[filmName][date][room].forEach(showtime => {
                    html += `<li class='time-link'>
                                <a href='list-chair.php?id=${showtime.MaSuatChieu}' class='btn'>${showtime.GioChieuFormatted}</a>
                            </li>`;
                });
                html += `</ul></div>`;
            }
            html += `</div>`;
        }
        html += `</div>`;
    }
    return html;
}

// Hàm lọc suất chiếu theo phòng
function filterShowtimesByRoom() {
    const selectedRoom = document.getElementById('roomSelect').value;
    const showtimeContainer = document.getElementById('showtimeContainer');
    const originalGroupedShowtimes = window.groupedShowtimes;

    // Lấy lại HTML của thanh select "Lọc theo phòng chiếu"
    const roomFilterHtml = document.getElementById('roomSelectContainer').outerHTML;

    if (!selectedRoom) {
        // Nếu chọn "Tất cả phòng", hiển thị toàn bộ suất chiếu
        let showtimesHtml = renderShowtimes(originalGroupedShowtimes);
        showtimeContainer.innerHTML = roomFilterHtml + showtimesHtml;
        return;
    }

    // Lọc suất chiếu theo phòng đã chọn
    let filteredShowtimes = {};
    for (const filmName in originalGroupedShowtimes) {
        for (const date in originalGroupedShowtimes[filmName]) {
            if (originalGroupedShowtimes[filmName][date][selectedRoom]) {
                if (!filteredShowtimes[filmName]) {
                    filteredShowtimes[filmName] = {};
                }
                if (!filteredShowtimes[filmName][date]) {
                    filteredShowtimes[filmName][date] = {};
                }
                filteredShowtimes[filmName][date][selectedRoom] = originalGroupedShowtimes[filmName][date][selectedRoom];
            }
        }
    }

    // Hiển thị suất chiếu đã lọc
    let showtimesHtml = renderShowtimes(filteredShowtimes);
    showtimeContainer.innerHTML = roomFilterHtml + showtimesHtml;
}
</script>
<script src="https://www.youtube.com/iframe_api"></script>
<?php include('../includes/footer.php'); ?>
<?php
} else {
    echo '<h5>' . htmlspecialchars($item['message']) . '</h5>';
}
?>