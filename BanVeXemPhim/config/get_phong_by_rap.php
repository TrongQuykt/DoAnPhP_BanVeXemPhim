<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
require_once("function.php");

$maRap = isset($_GET['maRap']) ? intval($_GET['maRap']) : 0;

if ($maRap <= 0) {
    echo json_encode(['status' => 400, 'message' => 'Rạp không hợp lệ']);
    exit;
}

$query = "SELECT * FROM phong WHERE MaRap = ? AND TrangThai = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $maRap);
$stmt->execute();
$result = $stmt->get_result();

$phongList = [];
while ($row = $result->fetch_assoc()) {
    $phongList[] = $row;
}

echo json_encode(['status' => 200, 'data' => $phongList]);
$stmt->close();
?>