<?php
require_once '../../../config/function.php';
include('../../includes/header.php');
// Kiểm tra quyền truy cập
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    redirect('../sign-in.php', 'error', 'Vui lòng đăng nhập để quản lý khu vực.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm khu vực</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <h2>Thêm khu vực</h2>
        <form action="../../controllers/khuvuc-controller.php" method="post">
            <div class="form-group">
                <label for="tenkhuvuc">Tên khu vực (*)</label>
                <input type="text" name="tenkhuvuc" class="form-control" value="<?php echo isset($_SESSION['form_data']['tenkhuvuc']) ? $_SESSION['form_data']['tenkhuvuc'] : ''; ?>">
                <?php if (isset($_SESSION['messages']['tenkhuvuc'])) { ?>
                    <span class="text-danger"><?php echo $_SESSION['messages']['tenkhuvuc']; ?></span>
                <?php } ?>
            </div>

            <div class="form-group">
                <label for="mota">Mô tả</label>
                <textarea name="mota" class="form-control"><?php echo isset($_SESSION['form_data']['mota']) ? $_SESSION['form_data']['mota'] : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="status">Trạng thái</label>
                <select name="status" class="form-control">
                    <option value="1">Online</option>
                    <option value="0">Offline</option>
                </select>
            </div>

            <button type="submit" name="saveKhuVuc" class="btn btn-primary">Thêm</button>
            <a href="../../khuvuc.php" class="btn btn-secondary">Hủy</a>
        </form>
    </div>
    <?php unset($_SESSION['messages']); unset($_SESSION['form_data']); ?>
</body>
</html>
<?php include('../../includes/footer.php'); ?>