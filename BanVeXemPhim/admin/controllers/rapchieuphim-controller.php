<?php
require '../../config/function.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    redirect('sign-in.php', 'error', 'Vui lòng đăng nhập để quản lý rạp chiếu phim.');
}

// Hàm kiểm tra trùng tên rạp chiếu phim
function checkDuplicateRapChieuPhim($conn, $tenRap, $maRap = null) {
    $query = "SELECT COUNT(*) as count FROM rapchieuphim WHERE TenRap = ? AND TrangThai != -1";
    if ($maRap !== null) {
        $query .= " AND MaRap != ?";
    }
    
    $stmt = $conn->prepare($query);
    if ($maRap !== null) {
        $stmt->bind_param("si", $tenRap, $maRap);
    } else {
        $stmt->bind_param("s", $tenRap);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'] > 0;
}

// Xử lý thêm rạp chiếu phim
if (isset($_POST['saveRapChieuPhim'])) {
    $tenRap = trim($_POST['tenrap']);
    $diaChi = trim($_POST['diachi']);
    $maKhuVuc = intval($_POST['makhuvuc']);
    $trangThai = isset($_POST['status']) ? intval($_POST['status']) : 0;

    // Kiểm tra userId trong session
    if (!isset($_SESSION['userId'])) {
        redirect('admin/views/rapchieuphim/rapchieuphim-add.php', 'error', 'Không tìm thấy thông tin người dùng. Vui lòng đăng nhập lại.');
    }
    $nguoiTao = $_SESSION['userId'];

    // Kiểm tra dữ liệu
    $errors = [];
    if (empty($tenRap)) {
        $errors['tenrap'] = 'Vui lòng nhập tên rạp.';
    } else {
        // Kiểm tra trùng tên rạp
        if (checkDuplicateRapChieuPhim($conn, $tenRap)) {
            $errors['tenrap'] = 'Tên rạp đã tồn tại. Vui lòng chọn tên khác.';
        }
    }
    if (empty($diaChi)) {
        $errors['diachi'] = 'Vui lòng nhập địa chỉ.';
    }
    if ($maKhuVuc <= 0) {
        $errors['makhuvuc'] = 'Vui lòng chọn khu vực.';
    }
    
    if (!empty($errors)) {
        $_SESSION['messages'] = $errors;
        $_SESSION['form_data'] = $_POST;
        redirect('admin/views/rapchieuphim/rapchieuphim-add.php', 'error', 'Vui lòng kiểm tra lại dữ liệu.');
    }

    // Thêm rạp chiếu phim vào cơ sở dữ liệu
    $query = "INSERT INTO rapchieuphim (TenRap, DiaChi, MaKhuVuc, NguoiTao, NgayTao, NguoiCapNhat, NgayCapNhat, TrangThai) 
              VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, ?, CURRENT_TIMESTAMP, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssiiii", $tenRap, $diaChi, $maKhuVuc, $nguoiTao, $nguoiTao, $trangThai);
    
    if ($stmt->execute()) {
        redirect('admin/rapchieuphim.php', 'success', 'Thêm rạp chiếu phim thành công.');
    } else {
        redirect('admin/views/rapchieuphim/rapchieuphim-add.php', 'error', 'Có lỗi xảy ra khi thêm rạp chiếu phim: ' . $conn->error);
    }
}

// Xử lý sửa rạp chiếu phim
if (isset($_POST['updateRapChieuPhim'])) {
    $maRap = check_valid_ID('id');
    $tenRap = trim($_POST['tenrap']);
    $diaChi = trim($_POST['diachi']);
    $maKhuVuc = intval($_POST['makhuvuc']);
    $trangThai = isset($_POST['status']) ? intval($_POST['status']) : 0;

    // Kiểm tra userId trong session
    if (!isset($_SESSION['userId'])) {
        redirect("views/rapchieuphim/rapchieuphim-edit.php?id=$maRap", 'error', 'Không tìm thấy thông tin người dùng. Vui lòng đăng nhập lại.');
    }
    $nguoiCapNhat = $_SESSION['userId'];

    // Kiểm tra dữ liệu
    $errors = [];
    if (empty($tenRap)) {
        $errors['tenrap'] = 'Vui lòng nhập tên rạp.';
    } else {
        // Kiểm tra trùng tên rạp (ngoại trừ rạp đang sửa)
        if (checkDuplicateRapChieuPhim($conn, $tenRap, $maRap)) {
            $errors['tenrap'] = 'Tên rạp đã tồn tại. Vui lòng chọn tên khác.';
        }
    }
    if (empty($diaChi)) {
        $errors['diachi'] = 'Vui lòng nhập địa chỉ.';
    }
    if ($maKhuVuc <= 0) {
        $errors['makhuvuc'] = 'Vui lòng chọn khu vực.';
    }
    
    if (!empty($errors)) {
        $_SESSION['messages'] = $errors;
        $_SESSION['form_data'] = $_POST;
        redirect("views/rapchieuphim/rapchieuphim-edit.php?id=$maRap", 'error', 'Vui lòng kiểm tra lại dữ liệu.');
    }

    // Cập nhật rạp chiếu phim
    $query = "UPDATE rapchieuphim SET TenRap = ?, DiaChi = ?, MaKhuVuc = ?, NguoiCapNhat = ?, NgayCapNhat = CURRENT_TIMESTAMP, TrangThai = ? WHERE MaRap = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssiiii", $tenRap, $diaChi, $maKhuVuc, $nguoiCapNhat, $trangThai, $maRap);
    
    if ($stmt->execute()) {
        redirect('admin/rapchieuphim.php', 'success', 'Cập nhật rạp chiếu phim thành công.');
    } else {
        redirect("admin/views/rapchieuphim/rapchieuphim-edit.php?id=$maRap", 'error', 'Có lỗi xảy ra khi cập nhật rạp chiếu phim: ' . $conn->error);
    }
}

// Xử lý xóa rạp chiếu phim
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $maRap = check_valid_ID('id');
    
    // Xóa rạp chiếu phim
    $query = "DELETE FROM rapchieuphim WHERE MaRap = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $maRap);
    
    if ($stmt->execute()) {
        redirect('admin/rapchieuphim.php', 'success', 'Xóa rạp chiếu phim thành công.');
    } else {
        redirect('admin/rapchieuphim.php', 'error', 'Có lỗi xảy ra khi xóa rạp chiếu phim: ' . $conn->error);
    }
}
?>