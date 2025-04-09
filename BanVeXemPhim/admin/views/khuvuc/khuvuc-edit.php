<?php
require_once '../../../config/function.php';
include('../../includes/header.php');
// Kiểm tra quyền truy cập
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    redirect('../sign-in.php', 'error', 'Vui lòng đăng nhập để quản lý khu vực.');
}

// Lấy thông tin khu vực để chỉnh sửa
$maKhuVuc = check_valid_ID('id');
$query = "SELECT * FROM khuvuc WHERE MaKhuVuc = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $maKhuVuc);
$stmt->execute();
$result = $stmt->get_result();
$khuVuc = $result->fetch_assoc();

if (!$khuVuc) {
    redirect('../../khuvuc.php', 'error', 'Khu vực không tồn tại.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa khu vực</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <h2>Chỉnh sửa khu vực</h2>
        <form action="../../controllers/khuvuc-controller.php" method="post">
            <input type="hidden" name="id" value="<?php echo $khuVuc['MaKhuVuc']; ?>">
            <div class="form-group">
                <label for="tenkhuvuc">Tên khu vực (*)</label>
                <input type="text" name="tenkhuvuc" class="form-control" value="<?php echo $khuVuc['TenKhuVuc']; ?>">
                <?php if (isset($_SESSION['messages']['tenkhuvuc'])) { ?>
                    <span class="text-danger"><?php echo $_SESSION['messages']['tenkhuvuc']; ?></span>
                <?php } ?>
            </div>

            <div class="form-group">
                <label for="mota">Mô tả</label>
                <textarea name="mota" class="form-control"><?php echo $khuVuc['MoTa']; ?></textarea>
            </div>

            <div class="form-group">
                <label for="status">Trạng thái</label>
                <select name="status" class="form-control">
                    <option value="1" <?php echo $khuVuc['TrangThai'] == 1 ? 'selected' : ''; ?>>Online</option>
                    <option value="0" <?php echo $khuVuc['TrangThai'] == 0 ? 'selected' : ''; ?>>Offline</option>
                </select>
            </div>

            <button type="submit" name="updateKhuVuc" class="btn btn-primary">Cập nhật</button>
            <a href="../../khuvuc.php" class="btn btn-secondary">Hủy</a>
        </form>
    </div>
    <?php unset($_SESSION['messages']); unset($_SESSION['form_data']); ?>
</body>
</html>
<?php include('../../includes/footer.php'); ?>