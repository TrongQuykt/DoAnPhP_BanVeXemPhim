<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once("function.php");

// Lấy ngày và giờ hiện tại từ query string (từ client)
$currentDate = isset($_GET['currentDate']) ? $_GET['currentDate'] : date('Y-m-d');
$currentTime = isset($_GET['currentTime']) ? $_GET['currentTime'] : date('H:i:s');

$maRap = isset($_GET['maRap']) ? intval($_GET['maRap']) : 0;
$maPhim = isset($_GET['maPhim']) ? intval($_GET['maPhim']) : 0;

if ($maRap <= 0 || $maPhim <= 0) {
    echo json_encode(['status' => 400, 'message' => 'Tham số không hợp lệ']);
    exit;
}

// Lấy suất chiếu của rạp và phim cụ thể, từ ngày hiện tại trở đi, bao gồm thông tin phòng
$query = "SELECT sc.*, p.TenPhim, ph.TenPhong 
          FROM suatchieu sc 
          JOIN phim p ON sc.MaPhim = p.MaPhim 
          JOIN phong ph ON sc.MaPhong = ph.MaPhong 
          WHERE sc.MaRap = ? AND sc.MaPhim = ? AND sc.TrangThai = 1 
          AND p.TrangThai = 1 
          AND (
              (DATE(sc.GioChieu) = ? AND TIME(sc.GioChieu) > ?) 
              OR DATE(sc.GioChieu) > ?
          ) 
          ORDER BY sc.GioChieu";
$stmt = $conn->prepare($query);
$stmt->bind_param("iisss", $maRap, $maPhim, $currentDate, $currentTime, $currentDate);
$stmt->execute();
$result = $stmt->get_result();

$showtimes = [];
while ($row = $result->fetch_assoc()) {
    $row['NgayChieu'] = date('Y-m-d', strtotime($row['GioChieu']));
    $row['GioChieuFormatted'] = date('H:i', strtotime($row['GioChieu']));
    $showtimes[] = $row;
}

// Tạo thông tin log
$log = [
    'current_system_date' => $currentDate,
    'current_system_time' => $currentTime,
    'maRap' => $maRap,
    'maPhim' => $maPhim,
    'showtimes' => $showtimes
];

// Trả về phản hồi JSON bao gồm cả log
echo json_encode([
    'status' => 200,
    'data' => $showtimes,
    'log' => $log
]);

$stmt->close();
?>