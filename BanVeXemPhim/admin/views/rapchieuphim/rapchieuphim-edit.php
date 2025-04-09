<?php
require_once '../../../config/function.php';
include('../../includes/header.php');
// Kiểm tra quyền truy cập
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    redirect('../sign-in.php', 'error', 'Vui lòng đăng nhập để quản lý rạp chiếu phim.');
}

// Lấy thông tin rạp chiếu phim để chỉnh sửa
$maRap = check_valid_ID('id');
$query = "SELECT * FROM rapchieuphim WHERE MaRap = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $maRap);
$stmt->execute();
$result = $stmt->get_result();
$rap = $result->fetch_assoc();

if (!$rap) {
    redirect('../../rapchieuphim.php', 'error', 'Rạp chiếu phim không tồn tại.');
}

// Lấy danh sách khu vực để hiển thị trong dropdown
$query = "SELECT * FROM khuvuc WHERE TrangThai = 1";
$khuVucResult = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa rạp chiếu phim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <h2>Chỉnh sửa rạp chiếu phim</h2>
        <form action="../../controllers/rapchieuphim-controller.php" method="post">
            <input type="hidden" name="id" value="<?php echo $rap['MaRap']; ?>">
            <div class="form-group">
                <label for="tenrap">Tên rạp (*)</label>
                <input type="text" name="tenrap" class="form-control" value="<?php echo $rap['TenRap']; ?>">
                <?php if (isset($_SESSION['messages']['tenrap'])) { ?>
                    <span class="text-danger"><?php echo $_SESSION['messages']['tenrap']; ?></span>
                <?php } ?>
            </div>

            <div class="form-group">
                <label for="diachi">Địa chỉ (*)</label>
                <input type="text" name="diachi" class="form-control" value="<?php echo $rap['DiaChi']; ?>">
                <?php if (isset($_SESSION['messages']['diachi'])) { ?>
                    <span class="text-danger"><?php echo $_SESSION['messages']['diachi']; ?></span>
                <?php } ?>
            </div>

            <div class="form-group">
                <label for="makhuvuc">Khu vực (*)</label>
                <select name="makhuvuc" class="form-control">
                    <option value="0">Chọn khu vực</option>
                    <?php while ($khuVuc = mysqli_fetch_assoc($khuVucResult)) { ?>
                        <option value="<?php echo $khuVuc['MaKhuVuc']; ?>" <?php echo $rap['MaKhuVuc'] == $khuVuc['MaKhuVuc'] ? 'selected' : ''; ?>>
                            <?php echo $khuVuc['TenKhuVuc']; ?>
                        </option>
                    <?php } ?>
                </select>
                <?php if (isset($_SESSION['messages']['makhuvuc'])) { ?>
                    <span class="text-danger"><?php echo $_SESSION['messages']['makhuvuc']; ?></span>
                <?php } ?>
            </div>

            <div class="form-group">
                <label for="status">Trạng thái</label>
                <select name="status" class="form-control">
                    <option value="1" <?php echo $rap['TrangThai'] == 1 ? 'selected' : ''; ?>>Online</option>
                    <option value="0" <?php echo $rap['TrangThai'] == 0 ? 'selected' : ''; ?>>Offline</option>
                </select>
            </div>

            <button type="submit" name="updateRapChieuPhim" class="btn btn-primary">Cập nhật</button>
            <a href="../../rapchieuphim.php" class="btn btn-secondary">Hủy</a>
        </form>
    </div>
    <?php unset($_SESSION['messages']); unset($_SESSION['form_data']); ?>
</body>
</html>
<?php include('../../includes/footer.php'); ?>