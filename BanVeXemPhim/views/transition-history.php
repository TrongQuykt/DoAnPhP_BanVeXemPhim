<?php
// Lấy NDId từ session
$NDId = isset($_SESSION['NDId']) ? $_SESSION['NDId'] : null;
if (!$NDId) {
    echo "Người dùng chưa đăng nhập.";
    exit();
}

// Đặt múi giờ cho PHP
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Phân trang
$limit = 10; // Số hóa đơn mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Lấy tổng số hóa đơn của người dùng (không lọc theo tháng/năm)
$totalQuery = "SELECT COUNT(*) as total 
               FROM HoaDon 
               WHERE MaND = ?";
$stmt = mysqli_prepare($conn, $totalQuery);
mysqli_stmt_bind_param($stmt, "s", $NDId);
mysqli_stmt_execute($stmt);
$totalResult = mysqli_stmt_get_result($stmt);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $limit);

// Lấy danh sách hóa đơn của người dùng (không lọc theo tháng/năm)
$query = "SELECT HD.MaHD, HD.NgayLapHD, HD.TongTien, SC.MaSuatChieu, P.TenPhim 
          FROM HoaDon HD 
          JOIN ChiTietHoaDon CTHD ON HD.MaHD = CTHD.MaHD 
          JOIN SuatChieu SC ON CTHD.MaSuatChieu = SC.MaSuatChieu 
          JOIN Phim P ON SC.MaPhim = P.MaPhim 
          WHERE HD.MaND = ? 
          GROUP BY HD.MaHD 
          ORDER BY HD.NgayLapHD DESC 
          LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "sii", $NDId, $limit, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Giao Dịch</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">

        <!-- Bảng lịch sử giao dịch -->
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>STT</th>
                    <th>Ngày mua</th>
                    <th>Tên phim</th>
                    <th>Tổng tiền</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $stt = $offset + 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $stt++ ?></td>
                            <td><?= date('Y-m-d H:i:s', strtotime($row['NgayLapHD'])) ?></td>
                            <td><?= htmlspecialchars($row['TenPhim']) ?></td>
                            <td><?= number_format($row['TongTien'], 0, ',', '.') ?> VNĐ</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info view-ticket-details" 
                                        data-mahd="<?= $row['MaHD'] ?>" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#ticketDetailsModal">
                                    Chi tiết
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Không có hóa đơn nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <!-- Nút Previous -->
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                            <span aria-hidden="true">«</span>
                        </a>
                    </li>
                    <!-- Các trang -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <!-- Nút Next -->
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                            <span aria-hidden="true">»</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <!-- Modal hiển thị chi tiết vé -->
    <div class="modal fade" id="ticketDetailsModal" tabindex="-1" aria-labelledby="ticketDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ticketDetailsModalLabel">Chi tiết vé</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="ticketDetailsContent">
                    <!-- Nội dung chi tiết vé sẽ được tải bằng AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS và Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Xử lý nút "Chi tiết"
        const viewButtons = document.querySelectorAll('.view-ticket-details');
        viewButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault(); // Ngăn hành vi mặc định của nút
                const maHD = this.getAttribute('data-mahd');
                fetchTicketDetails(maHD);
            });
        });

        function fetchTicketDetails(maHD) {
            fetch(`/BanVeXemPhim/views/get_ticket_details.php?maHD=${maHD}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('ticketDetailsContent').innerHTML = data;
                })
                .catch(error => {
                    console.error('Error fetching ticket details:', error);
                    document.getElementById('ticketDetailsContent').innerHTML = '<p>Đã có lỗi xảy ra. Vui lòng thử lại.</p>';
                });
        }
    });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('ticketDetailsModal');

    modal.addEventListener('hidden.bs.modal', function () {
        document.getElementById('ticketDetailsContent').innerHTML = '<p>Đang tải...</p>';
    });
});

        </script>
    <style>
        .page-item .page-link, .page-item {
            background-color: none;
            width: none;
            border-radius: none;
        }
    </style>
</body>
</html>