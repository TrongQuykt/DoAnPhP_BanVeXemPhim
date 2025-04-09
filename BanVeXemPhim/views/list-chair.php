<?php
require_once("../config/function.php");
$name = getByID('SuatChieu', 'MaSuatChieu', check_valid_ID('id'));
$nametitle = getByID('Phim', 'MaPhim', $name['data']['MaPhim']);
$title = 'Chọn ghế - ' . $nametitle['data']['TenPhim'] . '';
include('../includes/header.php');

ob_start();
$isLoggedIn = isset($_SESSION['NDloggedIn']) && $_SESSION['NDloggedIn'] == TRUE;

?>

<?php
$id_result = check_valid_ID('id');
if (!is_numeric($id_result)) {
    echo '<h5>' . htmlspecialchars($id_result) . '</h5>';
    return false;
}

$item = getByID('SuatChieu', 'MaSuatChieu', $id_result);
if ($item['status'] == 200) {
    $maPhong = $item['data']['MaPhong'];
    $maRap = $item['data']['MaRap'];

    // Lấy thông tin phòng
    $phong = getByID('Phong', 'MaPhong', $maPhong);
    $phongName = ($phong['status'] == 200) ? htmlspecialchars($phong['data']['TenPhong']) : "Không xác định";

    // Lấy thông tin rạp
    $rap = getByID('RapChieuPhim', 'MaRap', $maRap);
    $rapName = ($rap['status'] == 200) ? htmlspecialchars($rap['data']['TenRap']) : "Không xác định";

    // Lấy thông tin khu vực từ MaKhuVuc trong bảng Rap
    $khuVuc = ($rap['status'] == 200) ? getByID('KhuVuc', 'MaKhuVuc', $rap['data']['MaKhuVuc']) : ['status' => 404];
    $khuVucName = ($khuVuc['status'] == 200) ? htmlspecialchars($khuVuc['data']['TenKhuVuc']) : "Không xác định";

    global $conn;
    // Sắp xếp ghế theo hàng và số ghế
    $query = "SELECT * FROM GHE WHERE MaPhong = '$maPhong' ORDER BY SUBSTRING(TenGhe, 1, 1), CAST(SUBSTRING(TenGhe, 2) AS UNSIGNED)";
    $seats = mysqli_query($conn, $query);

    $booked = [];
    $booked_query = "SELECT g.MaGhe 
                     FROM ChiTietHoaDon ctd 
                     JOIN GHE g ON ctd.MaGhe = g.MaGhe 
                     WHERE ctd.MaSuatChieu = '$id_result' AND ctd.TrangThai = 1";
    $booked_result = mysqli_query($conn, $booked_query);
    while ($bookedSeat = mysqli_fetch_assoc($booked_result)) {
        $booked[] = $bookedSeat['MaGhe'];
    }

    // Chuyển kết quả thành mảng để dễ xử lý
    $seatsArray = [];
    while ($seat = mysqli_fetch_assoc($seats)) {
        $seatsArray[] = $seat;
        error_log("Ghế {$seat['TenGhe']}: LoaiGhe = {$seat['LoaiGhe']}, GiaGhe = " . (isset($seat['GiaGhe']) ? $seat['GiaGhe'] : 'Không có'));
    }
}
?>
<?php
    $film = getByID('Phim', 'MaPhim', $item['data']['MaPhim']);
    $maPhim = $film['data']['MaPhim'];
    $showtime = new DateTime($item['data']['GioChieu']);
    
    // Lấy phân loại độ tuổi từ bảng phim
    $phanLoai = isset($film['data']['PhanLoai']) ? htmlspecialchars($film['data']['PhanLoai']) : '';
?>
<div id="toast"></div>

<?php alertMessage() ?>

<div class="chair my-5">
    <div class="container movie-content w-100 shadow">
        <?php
            $film = getByID('Phim', 'MaPhim', $item['data']['MaPhim']);
            $maPhim = $film['data']['MaPhim'];
            $showtime = new DateTime($item['data']['GioChieu']);
        ?>
        <h4 class="text-center mb-4 text-uppercase fw-bold">Chọn ghế cho phim:
            <?= htmlspecialchars($film['data']['TenPhim']) ?></h4>
        <div class="type-chair">
            <ul class="d-flex flex-row justify-content-center">
                <li class="seat vip">Ghế VIP</li>
                <li class="seat single">Ghế đơn</li>
                <li class="seat couple">Ghế đôi</li>
                <li class="seat silver">Ghế Silver</li>
                <li class="seat gold">Ghế Gold</li>
                <li class="seat platinum">Ghế Platinum</li>
                <li class="seat choosed">Ghế đã chọn</li>
                <li class="seat selected">Ghế đã đặt</li>
            </ul>
        </div>
        <div class="room px-5">
            <div class="d-flex justify-content-between align-items-center">
                <div class="exit exit-left">
                    <span>EXIT</span>
                    <div class="arrow arrow-left"></div>
                </div>
                <div class="d-flex justify-content-center flex-column">
                    <div class="tv mx-5"></div>
                    <span class="text-center text-secondary">Màn hình</span>
                </div>
                <div class="exit exit-right">
                    <span>EXIT</span>
                    <div class="arrow arrow-right"></div>
                </div>
            </div>

            <div class="list-chair mt-5">
                <ul class="container-fluid d-flex flex-column">
                    <?php
                        $currentRow = '';
                        $processedSeats = [];

                        for ($i = 0; $i < count($seatsArray); $i++) {
                            if (in_array($i, $processedSeats)) {
                                continue;
                            }

                            $seat = $seatsArray[$i];
                            $rowLetter = substr($seat['TenGhe'], 0, 1);
                            $seatNumber = (int)substr($seat['TenGhe'], 1);
                            $seatId = htmlspecialchars($seat['MaGhe']);
                            $seatPrice = isset($seat['GiaGhe']) ? (int)$seat['GiaGhe'] : 0;

                            $isBooked = in_array($seatId, $booked);

                            if ($rowLetter != $currentRow) {
                                if ($currentRow != '') {
                                    echo '</div><div class="col-1 fw-bold text-secondary">' . $currentRow . '</div></li>';
                                }
                                $currentRow = $rowLetter;
                                echo '<li class="d-flex mb-2 text-center"><div class="col-1 fw-bold text-secondary">' . $currentRow . '</div><div class="list col-10 text-center justify-content-center m-auto">';
                            }

                            $seatClass = match (mb_strtolower($seat['LoaiGhe'], 'UTF-8')) {
                                'đơn' => 'single',
                                'vip' => 'vip',
                                'đôi' => 'couple',
                                'silver' => 'silver',
                                'gold' => 'gold',
                                'platinum' => 'platinum',
                                default => 'single'
                            };

                            if ($seatClass == 'couple') {
                                $nextSeatIndex = $i + 1;
                                if ($nextSeatIndex < count($seatsArray)) {
                                    $nextSeat = $seatsArray[$nextSeatIndex];
                                    $nextRowLetter = substr($nextSeat['TenGhe'], 0, 1);
                                    $nextSeatNumber = (int)substr($nextSeat['TenGhe'], 1);
                                    $nextSeatId = htmlspecialchars($nextSeat['MaGhe']);
                                    $nextSeatPrice = isset($nextSeat['GiaGhe']) ? (int)$nextSeat['GiaGhe'] : 0;

                                    if ($nextRowLetter == $rowLetter && mb_strtolower($nextSeat['LoaiGhe'], 'UTF-8') == 'đôi' && $nextSeatNumber == $seatNumber + 1) {
                                        $isNextBooked = in_array($nextSeatId, $booked);
                                        $seatNumberPair = htmlspecialchars($seatNumber) . '-' . htmlspecialchars($nextSeatNumber);
                                        $disabledClass = ($isBooked || $isNextBooked) ? 'disabled' : '';
                                        $totalPrice = $seatPrice + $nextSeatPrice;
                                        echo '<button class="mx-1 ' . $seatClass . ' rounded seat-button ' . $disabledClass . '" data-row="' . $rowLetter . '" onclick="toggleSeatSelection(this)" data-seat-ids="' . $seatId . ',' . $nextSeatId . '" data-price="' . $totalPrice . '"><span>' . $seatNumberPair . '</span></button>';
                                        $processedSeats[] = $nextSeatIndex;
                                    } else {
                                        $disabledClass = $isBooked ? 'disabled' : '';
                                        echo '<button class="mx-1 ' . $seatClass . ' rounded seat-button ' . $disabledClass . '" data-row="' . $rowLetter . '" onclick="toggleSeatSelection(this)" data-seat-ids="' . $seatId . '" data-price="' . $seatPrice . '"><span>' . htmlspecialchars($seatNumber) . '</span></button>';
                                    }
                                } else {
                                    $disabledClass = $isBooked ? 'disabled' : '';
                                    echo '<button class="mx-1 ' . $seatClass . ' rounded seat-button ' . $disabledClass . '" data-row="' . $rowLetter . '" onclick="toggleSeatSelection(this)" data-seat-ids="' . $seatId . '" data-price="' . $seatPrice . '"><span>' . htmlspecialchars($seatNumber) . '</span></button>';
                                }
                            } else {
                                $disabledClass = $isBooked ? 'disabled' : '';
                                echo '<button class="mx-1 ' . $seatClass . ' rounded seat-button ' . $disabledClass . '" data-row="' . $rowLetter . '" onclick="toggleSeatSelection(this)" data-seat-ids="' . $seatId . '" data-price="' . $seatPrice . '"><span>' . htmlspecialchars($seatNumber) . '</span></button>';
                            }
                        }
                        if ($currentRow != '') {
                            echo '</div><div class="col-1 fw-bold text-secondary">' . $currentRow . '</div></li>';
                        }
                    ?>
                </ul>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <span id="totalSeatPrice">Tổng tiền ghế: 0 VND</span>
        </div>

        <!-- Thêm phần hiển thị thông tin chi tiết với ảnh phim -->
        <div class="container mt-4">
    <div class="ticket-wrapper">
        <div class="ticket-main">
            <div class="ticket-header">
                <h5 class="mb-0">CINEMA TICKET</h5>
            </div>
            <div class="ticket-body">
                <div class="ticket-poster">
                    <img src="../uploads/film-imgs/<?= htmlspecialchars($film['data']['Anh']) ?>" alt="<?= htmlspecialchars($film['data']['TenPhim']) ?>" class="img-fluid">
                </div>
                <div class="ticket-info">
                    <p><strong>Phim:</strong> <?= htmlspecialchars($film['data']['TenPhim']) ?></p>
                    <p><strong>Khu vực:</strong> <?= $khuVucName ?></p>
                    <p><strong>Rạp:</strong> <?= $rapName ?></p>
                    <p><strong>Phòng:</strong> <?= $phongName ?></p>
                    <p><strong>Giờ chiếu:</strong> <?= $showtime->format('d/m/Y H:i') ?></p>
                    <p><strong>Ghế:</strong> <span id="selectedSeats">Chưa chọn ghế</span></p>
                </div>
            </div>
        </div>
        <div class="ticket-side">
            <div class="ticket-side-content">
                <p class="total-price"><strong>Tổng tiền:</strong> <span id="totalPriceDisplay">0 VND</span></p>
            </div>
        </div>
    </div>
</div>
        <div>
            <form id="paymentForm" class="text-center" action="select-combo.php" method="POST">
                <input type="hidden" name="seatsInput" id="seatsInput">
                <button type="button" id="paymentButton" onclick="handlePayment()">Tiếp tục</button>
            </form>
        </div>
    </div>
</div>

<!-- MODAL NO SEAT SELECTED -->
<div class="modal fade" id="noSeatSelectedModal" tabindex="-1" aria-labelledby="noSeatSelectedModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="noSeatSelectedModalLabel">Thông báo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-danger fw-bold">
                Bạn chưa chọn ghế >.< </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ĐĂNG NHẬP -->
<div class="modal fade" id="modalLogged" tabindex="-1" aria-labelledby="modalLoggedLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLoggedLabel">Thông báo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-danger fw-bold">
                Bạn chưa đăng nhập trước khi thanh toán
            </div>
            <div class="modal-footer">
                <a href="/BanVeXemPhim/views/login.php" class="btn btn-primary">Đăng nhập</a>
            </div>
        </div>
    </div>
</div>

<!-- MODAL VƯỢT QUÁ GIỚI HẠN -->
<div class="modal fade" id="maxTicketsModal" tabindex="-1" aria-labelledby="maxTicketsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="maxTicketsModalLabel">Thông báo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-danger fw-bold">
                Bạn chỉ được đặt tối đa 8 vé trong một lần. Vui lòng chọn lại!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
<!-- MODAL GHẾ TRỐNG LẺ LOI -->
<div class="modal fade" id="emptySeatModal" tabindex="-1" aria-labelledby="emptySeatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emptySeatModalLabel">Thông báo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-danger fw-bold">
                Bạn không được để trống 1 ghế ở bên trái, giữa hoặc bên phải trong cùng hàng ghế mà bạn vừa chọn.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
<!-- MODAL XÁC NHẬN ĐỘ TUỔI -->
<div class="modal fade" id="ageConfirmationModal" tabindex="-1" aria-labelledby="ageConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ageConfirmationModalLabel">
                    Xác nhận mua vé cho người có độ tuổi phù hợp <span id="ageRatingBadge" class="badge bg-warning text-dark"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="ageConfirmationMessage"></p>
                <p>Văn Hóa, Thể Thao Và Du Lịch. CGV sẽ không hoàn tiền nếu người xem không đáp ứng được điều kiện. Vui lòng tham khảo <a href="#" class="regulation-link text-danger fw-bold" data-bs-toggle="modal" data-bs-target="#regulationModal">Quy định</a> của Bộ Văn Hóa, Thể Thao và Du Lịch.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Từ chối</button>
                <button type="button" class="btn btn-primary" onclick="confirmAge()">Xác nhận</button>
            </div>
        </div>
    </div>
</div>
<!-- MODAL QUY ĐỊNH -->
<div class="modal fade" id="regulationModal" tabindex="-1" aria-labelledby="regulationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="regulationModalLabel">Quy định của Bộ Văn Hóa, Thể Thao và Du Lịch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="fw-bold">QUY ĐỊNH VỀ PHÂN LOẠI PHIM THEO ĐỘ TUỔI</h6>
                <p>Theo quy định của Bộ Văn Hóa, Thể Thao và Du Lịch, các bộ phim được chiếu tại rạp phải được phân loại theo độ tuổi để đảm bảo phù hợp với khán giả. Cụ thể:</p>
                <ul>
                    <li><strong>P:</strong> Phim được phép phổ biến đến mọi đối tượng, không giới hạn độ tuổi.</li>
                    <li><strong>K:</strong> Phim được phép phổ biến đến mọi đối tượng, nhưng khuyến nghị có phụ huynh đi kèm đối với trẻ em dưới 13 tuổi.</li>
                    <li><strong>T13:</strong> Phim được phép phổ biến đến khán giả từ 13 tuổi trở lên. Trẻ em dưới 13 tuổi cần có phụ huynh hoặc người giám hộ đi kèm.</li>
                    <li><strong>T16:</strong> Phim được phép phổ biến đến khán giả từ 16 tuổi trở lên. Người xem dưới 16 tuổi không được phép xem, kể cả khi có phụ huynh đi kèm.</li>
                    <li><strong>T18:</strong> Phim được phép phổ biến đến khán giả từ 18 tuổi trở lên. Người xem dưới 18 tuổi không được phép xem, kể cả khi có phụ huynh đi kèm.</li>
                </ul>
                <h6 class="fw-bold mt-3">QUY ĐỊNH VỀ KIỂM TRA ĐỘ TUỔI</h6>
                <p>Khán giả khi tham gia xem phim tại rạp cần mang theo giấy tờ tùy thân (CMND/CCCD, hộ chiếu, giấy khai sinh, thẻ học sinh/sinh viên, v.v.) để chứng minh độ tuổi. Rạp chiếu phim có quyền yêu cầu xuất trình giấy tờ tùy thân để xác minh độ tuổi trước khi cho phép vào phòng chiếu.</p>
                <h6 class="fw-bold mt-3">QUY ĐỊNH VỀ HOÀN TIỀN</h6>
                <p>Trong trường hợp khán giả không đáp ứng được điều kiện độ tuổi theo phân loại của phim, rạp chiếu phim sẽ không hoàn tiền vé đã mua. Khán giả cần kiểm tra kỹ phân loại độ tuổi của phim trước khi đặt vé.</p>
                <h6 class="fw-bold mt-3">TRÁCH NHIỆM CỦA KHÁN GIẢ</h6>
                <p>Khán giả có trách nhiệm tuân thủ các quy định về độ tuổi khi xem phim tại rạp. Việc cố tình vi phạm (như sử dụng giấy tờ giả mạo, đưa trẻ em dưới độ tuổi quy định vào xem phim, v.v.) có thể dẫn đến việc bị từ chối phục vụ hoặc xử lý theo quy định của pháp luật.</p>
                <p>Quy định này được ban hành nhằm bảo vệ quyền lợi của khán giả và đảm bảo môi trường xem phim lành mạnh, phù hợp với từng độ tuổi.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
<script>
// Biến toàn cục để lưu trữ dữ liệu form trước khi submit
let formDataToSubmit = null;

// Hàm chọn/bỏ chọn ghế
function toggleSeatSelection(button) {
    if (button.classList.contains('disabled')) {
        return; // Không làm gì nếu ghế đã bị đặt
    }

    // Đếm số vé hiện tại trước khi chọn ghế mới
    const selectedSeats = document.querySelectorAll('.choosed');
    let totalTickets = 0;

    selectedSeats.forEach(seat => {
        const seatClass = seat.classList;
        if (seatClass.contains('couple')) {
            totalTickets += 2; // Ghế đôi tính 2 vé
        } else {
            totalTickets += 1; // Ghế đơn tính 1 vé
        }
    });

    // Nếu ghế đang được chọn (chưa có class 'choosed'), kiểm tra giới hạn
    if (!button.classList.contains('choosed')) {
        const seatClass = button.classList;
        const additionalTickets = seatClass.contains('couple') ? 2 : 1;

        // Kiểm tra nếu tổng số vé vượt quá 8
        if (totalTickets + additionalTickets > 8) {
            var maxTicketsModal = new bootstrap.Modal(document.getElementById('maxTicketsModal'), {});
            maxTicketsModal.show();
            return; // Ngăn không cho chọn thêm ghế
        }
    }

    // Cho phép chọn/bỏ chọn ghế
    button.classList.toggle('choosed');
    updateTotalSeatPrice();
    updateSelectedSeats();
}

// Cập nhật tổng tiền ghế
function updateTotalSeatPrice() {
    const selectedSeats = document.querySelectorAll('.choosed');
    let totalSeatPrice = 0;
    selectedSeats.forEach(seat => {
        const seatPrice = parseInt(seat.getAttribute('data-price')) || 0;
        totalSeatPrice += seatPrice;
    });
    document.getElementById('totalSeatPrice').textContent = `Tổng tiền ghế: ${totalSeatPrice.toLocaleString('vi-VN')} VND`;
    document.getElementById('totalPriceDisplay').textContent = `${totalSeatPrice.toLocaleString('vi-VN')} VND`;
}

// Cập nhật danh sách ghế đã chọn
function updateSelectedSeats() {
    const selectedSeats = document.querySelectorAll('.choosed');
    const seatNames = Array.from(selectedSeats).map(seat => {
        const rowLetter = seat.getAttribute('data-row') || '';
        const seatNumber = seat.textContent.trim() || '';
        if (rowLetter && seatNumber) {
            return rowLetter + seatNumber;
        }
        return null; // Trả về null nếu không hợp lệ
    }).filter(name => name !== null); // Loại bỏ các giá trị null
    const selectedSeatsText = seatNames.length > 0 ? seatNames.join(', ') : 'Chưa chọn ghế';
    document.getElementById('selectedSeats').textContent = selectedSeatsText;
}

// Hàm xử lý khi bấm "Tiếp tục"
function handlePayment() {
    const selectedSeats = document.querySelectorAll('.choosed');
    
    let totalTickets = 0;
    let totalSeatPrice = 0;
    selectedSeats.forEach(seat => {
        const seatClass = seat.classList;
        const seatPrice = parseInt(seat.getAttribute('data-price')) || 0;
        totalSeatPrice += seatPrice;
        if (seatClass.contains('couple')) {
            totalTickets += 2;
        } else {
            totalTickets += 1;
        }
    });

    // Kiểm tra nếu tổng số vé vượt quá 8
    if (totalTickets > 8) {
        var maxTicketsModal = new bootstrap.Modal(document.getElementById('maxTicketsModal'), {});
        maxTicketsModal.show();
        return;
    }

    // Kiểm tra điều kiện ghế trống lẻ loi
    const rows = [...new Set(Array.from(selectedSeats).map(seat => seat.getAttribute('data-row')))]; // Lấy danh sách các hàng có ghế được chọn

    for (const rowLetter of rows) {
        // Lấy tất cả ghế trong hàng
        const allSeatsInRow = document.querySelectorAll(`.seat-button[data-row="${rowLetter}"]`);
        let selectedSeatNumbers = [];

        // Thu thập số ghế đã chọn trong hàng
        allSeatsInRow.forEach(seat => {
            if (seat.classList.contains('choosed')) {
                const seatNumStr = seat.textContent.trim();
                if (seat.classList.contains('couple')) {
                    const [start, end] = seatNumStr.split('-').map(num => parseInt(num));
                    if (!isNaN(start) && !isNaN(end)) {
                        selectedSeatNumbers.push(start, end);
                    }
                } else {
                    const seatNum = parseInt(seatNumStr);
                    if (!isNaN(seatNum)) {
                        selectedSeatNumbers.push(seatNum);
                    }
                }
            }
        });

        // Sắp xếp số ghế đã chọn và loại bỏ trùng lặp
        selectedSeatNumbers = [...new Set(selectedSeatNumbers)].sort((a, b) => a - b);

        // Nếu không có ghế nào được chọn trong hàng, bỏ qua
        if (selectedSeatNumbers.length === 0) {
            continue;
        }

        // Kiểm tra ghế trống lẻ loi ở giữa
        for (let i = 0; i < selectedSeatNumbers.length - 1; i++) {
            const currentSeat = selectedSeatNumbers[i];
            const nextSeat = selectedSeatNumbers[i + 1];
            const gap = nextSeat - currentSeat;

            if (gap === 2) { // Có 1 ghế trống ở giữa
                const middleSeatNumber = currentSeat + 1;
                const middleSeat = Array.from(allSeatsInRow).find(seat => {
                    const seatNumStr = seat.textContent.trim();
                    if (seat.classList.contains('couple')) {
                        const [start, end] = seatNumStr.split('-').map(num => parseInt(num));
                        return !isNaN(start) && !isNaN(end) && start <= middleSeatNumber && end >= middleSeatNumber;
                    } else {
                        const seatNum = parseInt(seatNumStr);
                        return !isNaN(seatNum) && seatNum === middleSeatNumber;
                    }
                });

                if (middleSeat && !middleSeat.classList.contains('choosed') && !middleSeat.classList.contains('disabled')) {
                    var emptySeatModal = new bootstrap.Modal(document.getElementById('emptySeatModal'), {});
                    emptySeatModal.show();
                    return; // Ngăn không cho tiếp tục
                }
            }
        }

        // Kiểm tra ghế trống lẻ loi ở bên trái và bên phải
        const allSeatNumbers = Array.from(allSeatsInRow).map(seat => {
            const seatNumStr = seat.textContent.trim();
            if (seat.classList.contains('couple')) {
                const [start, end] = seatNumStr.split('-').map(num => parseInt(num));
                if (!isNaN(start) && !isNaN(end)) {
                    return { start, end, isDisabled: seat.classList.contains('disabled') };
                }
            } else {
                const seatNum = parseInt(seatNumStr);
                if (!isNaN(seatNum)) {
                    return { start: seatNum, end: seatNum, isDisabled: seat.classList.contains('disabled') };
                }
            }
            return null; // Trả về null nếu dữ liệu không hợp lệ
        }).filter(seat => seat !== null).sort((a, b) => a.start - b.start);

        // Nếu không có ghế hợp lệ trong hàng, bỏ qua
        if (allSeatNumbers.length === 0) {
            continue;
        }

        const minSeat = allSeatNumbers[0].start; // Ghế nhỏ nhất trong hàng
        const maxSeat = allSeatNumbers[allSeatNumbers.length - 1].end; // Ghế lớn nhất trong hàng

        const firstSelectedSeat = selectedSeatNumbers[0]; // Ghế đã chọn nhỏ nhất
        const lastSelectedSeat = selectedSeatNumbers[selectedSeatNumbers.length - 1]; // Ghế đã chọn lớn nhất

        // Kiểm tra bên trái
        if (firstSelectedSeat > minSeat) {
            let leftGap = 0;
            for (let i = minSeat; i < firstSelectedSeat; i++) {
                const seat = allSeatNumbers.find(s => s.start <= i && s.end >= i);
                if (seat && !seat.isDisabled) {
                    leftGap++;
                }
            }
            if (leftGap === 1) {
                var emptySeatModal = new bootstrap.Modal(document.getElementById('emptySeatModal'), {});
                emptySeatModal.show();
                return; // Ngăn không cho tiếp tục
            }
        }

        // Kiểm tra bên phải
        if (lastSelectedSeat < maxSeat) {
            let rightGap = 0;
            for (let i = lastSelectedSeat + 1; i <= maxSeat; i++) {
                const seat = allSeatNumbers.find(s => s.start <= i && s.end >= i);
                if (seat && !seat.isDisabled) {
                    rightGap++;
                }
            }
            if (rightGap === 1) {
                var emptySeatModal = new bootstrap.Modal(document.getElementById('emptySeatModal'), {});
                emptySeatModal.show();
                return; // Ngăn không cho tiếp tục
            }
        }
    }

    // Nếu không có ghế được chọn
    const selectedSeatNumbers = Array.from(selectedSeats).map(seat => {
        const rowLetter = seat.getAttribute('data-row');
        const seatNumber = seat.textContent.trim();
        if (rowLetter && seatNumber) {
            return rowLetter + seatNumber;
        }
        return null;
    }).filter(seat => seat);

    const selectedSeatIds = Array.from(selectedSeats).map(seat => {
        return seat.getAttribute('data-seat-ids');
    }).filter(id => id).join(',');

    if (selectedSeatNumbers.length === 0) {
        var noSeatSelectedModal = new bootstrap.Modal(document.getElementById('noSeatSelectedModal'), {});
        noSeatSelectedModal.show();
        return;
    }

    // Kiểm tra đăng nhập
    const isLoggedIn = <?= json_encode($isLoggedIn) ?>;
    if (!isLoggedIn) {
        var modalLogged = new bootstrap.Modal(document.getElementById('modalLogged'), {});
        modalLogged.show();
        return;
    }

    // Chuẩn bị dữ liệu để submit form
    const dataArray = {
        MaGhe: selectedSeatIds,
        MaPhim: <?= json_encode($maPhim) ?>,
        MaPhong: <?= json_encode($maPhong) ?>,
        MaSuatChieu: <?= json_encode($id_result) ?>,
        TotalSeatPrice: totalSeatPrice
    };
    formDataToSubmit = dataArray; // Lưu dữ liệu để submit sau khi xác nhận độ tuổi

    // Kiểm tra phân loại độ tuổi
    const phanLoai = <?= json_encode($phanLoai) ?>;
    if (phanLoai && phanLoai !== '') {
        // Hiển thị modal xác nhận độ tuổi
        const ageConfirmationModal = new bootstrap.Modal(document.getElementById('ageConfirmationModal'), {});
        
        // Cập nhật nội dung modal
        document.getElementById('ageRatingBadge').textContent = phanLoai;
        let ageMessage = '';
        switch (phanLoai.toUpperCase()) {
            case 'T16':
                ageMessage = 'Tối xác nhận mua vé phim này cho người có độ tuổi từ 16 tuổi trở lên và đồng ý cung cấp giấy tờ tùy thân để xác thực độ tuổi.';
                break;
            case 'T18':
                ageMessage = 'Tối xác nhận mua vé phim này cho người có độ tuổi từ 18 tuổi trở lên và đồng ý cung cấp giấy tờ tùy thân để xác thực độ tuổi.';
                break;
            case 'T13':
                ageMessage = 'Tối xác nhận mua vé phim này cho người có độ tuổi từ 13 tuổi trở lên và đồng ý cung cấp giấy tờ tùy thân để xác thực độ tuổi.';
                break;
            case 'P':
                ageMessage = 'Phim này phù hợp với mọi độ tuổi.';
                break;
            case 'K':
                ageMessage = 'Phim này phù hợp với mọi độ tuổi, nhưng khuyến nghị có phụ huynh đi kèm đối với trẻ em dưới 13 tuổi.';
                break;
            default:
                ageMessage = 'Tối xác nhận mua vé phim này và đồng ý cung cấp giấy tờ tùy thân để xác thực độ tuổi nếu cần.';
        }
        document.getElementById('ageConfirmationMessage').textContent = ageMessage;

        // Hiển thị modal
        ageConfirmationModal.show();
    } else {
        // Nếu không có phân loại độ tuổi, submit form ngay
        document.getElementById('seatsInput').value = JSON.stringify(dataArray);
        document.getElementById('paymentForm').submit();
    }
}

// Hàm xử lý khi người dùng bấm "Xác nhận" trong modal độ tuổi
function confirmAge() {
    if (formDataToSubmit) {
        document.getElementById('seatsInput').value = JSON.stringify(formDataToSubmit);
        document.getElementById('paymentForm').submit();
    }
}
</script>

<style>
/* Ticket Wrapper */
/* Ticket Wrapper */
.ticket-wrapper {
    display: flex;
    background: linear-gradient(135deg, #ffffff 0%, #f5f5f5 100%); /* Nền trắng nhẹ */
    position: relative;
    margin: 30px 0;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
    border: 3px solid #c0392b; /* Viền đỏ đậm */
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); /* Bóng đổ */
    /* Hiệu ứng lưỡi cưa ở hai đầu */
    clip-path: polygon(
        0% 0%, 2% 5%, 4% 0%, 6% 5%, 8% 0%, 10% 5%, 12% 0%, 14% 5%, 16% 0%, 18% 5%, 20% 0%, 22% 5%, 24% 0%, 26% 5%, 28% 0%, 30% 5%, 32% 0%, 34% 5%, 36% 0%, 38% 5%, 40% 0%, 42% 5%, 44% 0%, 46% 5%, 48% 0%, 50% 5%, 52% 0%, 54% 5%, 56% 0%, 58% 5%, 60% 0%, 62% 5%, 64% 0%, 66% 5%, 68% 0%, 70% 5%, 72% 0%, 74% 5%, 76% 0%, 78% 5%, 80% 0%, 82% 5%, 84% 0%, 86% 5%, 88% 0%, 90% 5%, 92% 0%, 94% 5%, 96% 0%, 98% 5%, 100% 0%,
        100% 100%, 98% 95%, 96% 100%, 94% 95%, 92% 100%, 90% 95%, 88% 100%, 86% 95%, 84% 100%, 82% 95%, 80% 100%, 78% 95%, 76% 100%, 74% 95%, 72% 100%, 70% 95%, 68% 100%, 66% 95%, 64% 100%, 62% 95%, 60% 100%, 58% 95%, 56% 100%, 54% 95%, 52% 100%, 50% 95%, 48% 100%, 46% 95%, 44% 100%, 42% 95%, 40% 100%, 38% 95%, 36% 100%, 34% 95%, 32% 100%, 30% 95%, 28% 100%, 26% 95%, 24% 100%, 22% 95%, 20% 100%, 18% 95%, 16% 100%, 14% 95%, 12% 100%, 10% 95%, 8% 100%, 6% 95%, 4% 100%, 2% 95%, 0% 100%
    );
}

/* Hiệu ứng rách giữa */
.ticket-wrapper .ticket-side::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 2px;
    height: 100%;
    background: repeating-linear-gradient(
        0deg,
        transparent,
        transparent 5px,
        #c0392b 5px,
        #c0392b 10px
    ); /* Hiệu ứng rách đỏ */
}

/* Phần chính của vé */
.ticket-main {
    flex: 7;
    padding: 25px;
    position: relative;
    color: #333;
}

/* Phần phụ của vé */
.ticket-side {
    flex: 3;
    padding: 25px;
    background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); /* Gradient đỏ */
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

/* Tiêu đề vé */
.ticket-header {
    text-align: center;
    margin-bottom: 20px;
    position: relative;
    border-bottom: 2px dashed #c0392b; /* Đường kẻ ngang đỏ */
    padding-bottom: 10px;
}

.ticket-header h5 {
    color: #c0392b; /* Màu đỏ đậm */
    font-family: 'Montserrat', sans-serif;
    font-weight: 700; /* Font đậm */
    font-size: 1.6rem;
    text-transform: uppercase;
    margin: 0;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Nội dung vé */
.ticket-body {
    display: flex;
    align-items: center;
    gap: 25px;
}

/* Ảnh phim */
.ticket-poster {
    flex: 3;
}

.ticket-poster img {
    border: 3px solid #c0392b; /* Viền đỏ */
    border-radius: 10px; /* Bo tròn nhẹ */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    max-height: 100%;
    width: 100%;
    object-fit: cover;
}

/* Thông tin vé */
.ticket-info {
    flex: 7;
}

.ticket-info p {
    margin: 6px 0;
    font-size: 1.1rem;
    line-height: 1.6;
    font-family: 'Roboto', sans-serif;
    font-weight: 500; /* Font đậm */
}

.ticket-info p strong {
    color: #c0392b; /* Màu đỏ đậm */
    font-weight: 700;
}

/* Phần phụ: Tổng tiền */
.ticket-side-content {
    text-align: center;
    color: #fff;
}

.total-price {
    font-size: 1.0rem;
    font-family: 'Roboto', sans-serif;
    font-weight: 700; /* Font đậm */
    margin-bottom: 15px;
}

.total-price strong {
    color: #fff;
    font-weight: 700;
}


/* Responsive */
@media (max-width: 768px) {
    .ticket-wrapper {
        flex-direction: column;
        max-width: 100%;
        /* Hiệu ứng lưỡi cưa trên và dưới */
        clip-path: polygon(
            0% 0%, 5% 2%, 0% 4%, 5% 6%, 0% 8%, 5% 10%, 0% 12%, 5% 14%, 0% 16%, 5% 18%, 0% 20%, 5% 22%, 0% 24%, 5% 26%, 0% 28%, 5% 30%, 0% 32%, 5% 34%, 0% 36%, 5% 38%, 0% 40%, 5% 42%, 0% 44%, 5% 46%, 0% 48%, 5% 50%, 0% 52%, 5% 54%, 0% 56%, 5% 58%, 0% 60%, 5% 62%, 0% 64%, 5% 66%, 0% 68%, 5% 70%, 0% 72%, 5% 74%, 0% 76%, 5% 78%, 0% 80%, 5% 82%, 0% 84%, 5% 86%, 0% 88%, 5% 90%, 0% 92%, 5% 94%, 0% 96%, 5% 98%, 0% 100%,
            100% 100%, 95% 98%, 100% 96%, 95% 94%, 100% 92%, 95% 90%, 100% 88%, 95% 86%, 100% 84%, 95% 82%, 100% 80%, 95% 78%, 100% 76%, 95% 74%, 100% 72%, 95% 70%, 100% 68%, 95% 66%, 100% 64%, 95% 62%, 100% 60%, 95% 58%, 100% 56%, 95% 54%, 100% 52%, 95% 50%, 100% 48%, 95% 46%, 100% 44%, 95% 42%, 100% 40%, 95% 38%, 100% 36%, 95% 34%, 100% 32%, 95% 30%, 100% 28%, 95% 26%, 100% 24%, 95% 22%, 100% 20%, 95% 18%, 100% 16%, 95% 14%, 100% 12%, 95% 10%, 100% 8%, 95% 6%, 100% 4%, 95% 2%, 100% 0%
        );
    }

    .ticket-wrapper .ticket-side::before {
        top: 0;
        left: 0;
        width: 100%;
        height: 2px;
    }

    .ticket-main,
    .ticket-side {
        flex: 1;
    }

    .ticket-body {
        flex-direction: column;
        text-align: center;
    }

    .ticket-poster {
        margin-bottom: 20px;
    }

    .ticket-poster img {
        max-height: 140px;
    }

    .ticket-info p {
        font-size: 1rem;
    }

    .total-price {
        font-size: 1.1rem;
    }
}

.seat-button.disabled {
    pointer-events: none;
    background-color: #d3d3d3;
    opacity: 0.5;
}

.seat.vip, .seat-button.vip {
    background-color: #f39c12;
    color: #000000;
}
.seat.single, .seat-button.single {
    background-color: #9b59b6;
    color: #ffffff;
}
.seat.couple, .seat-button.couple {
    background-color: #e91e63;
    color: #ffffff;
}
.seat.silver, .seat-button.silver {
    background-color: #95a5a6;
    color: #000000;
}
.seat.gold, .seat-button.gold {
    background-color: #f1c40f;
    color: #000000;
}
.seat.platinum, .seat-button.platinum {
    background-color: #7f8c8d;
    color: #ffffff;
}
.seat.choosed, .seat-button.choosed {
    background-color: #3498db;
    color: #ffffff;
}
.seat.selected, .seat-button.selected {
    background-color: #d3d3d3;
    color: #000000;
}

.seat-button {
    border: none;
    padding: 10px;
    margin: 2px;
    cursor: pointer;
    font-weight: bold;
}

.type-chair ul li {
    margin: 0 10px;
    padding: 5px 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.seat-button.couple {
    padding: 2px 20px;
}

.exit {
    background-color: #2ecc71;
    color: #ffffff;
    padding: 5px 10px;
    border-radius: 5px;
    font-weight: bold;
    font-size: 14px;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.exit-left {
    margin-left: 40px;
}

.exit-right {
    margin-right: 40px;
}

.arrow {
    width: 0;
    height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    position: absolute;
    bottom: -10px;
}

.arrow-left {
    border-top: 10px solid #2ecc71;
    animation: blink 1s infinite;
}

.arrow-right {
    border-top: 10px solid #2ecc71;
    animation: blink 1s infinite;
}

@keyframes blink {
    0% { opacity: 1; }
    50% { opacity: 0.3; }
    100% { opacity: 1; }
}

.tv {
    width: 600px;
    height: 10px;
    background-color: #000;
    border-radius: 5px;
}
#totalSeatPrice {
    display: none;
}
.modal-content {
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.modal-header {
    background-color: #f8d7da;
    color: #721c24;
    border-bottom: 1px solid #f5c6cb;
}

.modal-body.text-danger {
    font-size: 1.1rem;
    line-height: 1.5;
}

.modal-footer .btn-secondary {
    background-color: #dc3545;
    border-color: #dc3545;
    color: #fff;
}

.modal-footer .btn-secondary:hover {
    background-color: #c82333;
    border-color: #bd2130;
}
/* CSS cho modal xác nhận độ tuổi */
#ageConfirmationModal .modal-content {
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

#ageConfirmationModal .modal-header {
    background-color: #fff3cd;
    color: #856404;
    border-bottom: 1px solid #ffeeba;
}

#ageConfirmationModal .modal-body {
    font-size: 1.1rem;
    line-height: 1.5;
}

#ageConfirmationModal .modal-footer .btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

#ageConfirmationModal .modal-footer .btn-primary:hover {
    background-color: #0056b3;
    border-color: #004085;
}

#ageConfirmationModal .modal-footer .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

#ageConfirmationModal .modal-footer .btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}
/* CSS cho từ "Quy định" */
.regulation-link {
    color: #dc3545 !important; /* Màu đỏ */
    text-decoration: none;
    cursor: pointer;
}

.regulation-link:hover {
    text-decoration: underline;
}

/* CSS cho modal quy định */
#regulationModal .modal-content {
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

#regulationModal .modal-header {
    background-color: #f8f9fa;
    color: #343a40;
    border-bottom: 1px solid #dee2e6;
}

#regulationModal .modal-body {
    font-size: 1rem;
    line-height: 1.6;
    max-height: 60vh;
    overflow-y: auto; /* Cho phép cuộn nếu nội dung dài */
}

#regulationModal .modal-body h6 {
    color: #dc3545; /* Màu đỏ cho tiêu đề phụ */
}

#regulationModal .modal-body ul {
    padding-left: 20px;
}

#regulationModal .modal-body li {
    margin-bottom: 10px;
}

#regulationModal .modal-footer .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

#regulationModal .modal-footer .btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}
</style>

<?php include('../includes/footer.php'); ?>