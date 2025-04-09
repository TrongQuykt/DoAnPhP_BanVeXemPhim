<?php
require '../../config/function.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    redirect('sign-in.php', 'error', 'Vui lòng đăng nhập để quản lý khu vực.');
}

// Hàm kiểm tra trùng tên khu vực
function checkDuplicateKhuVuc($conn, $tenKhuVuc, $maKhuVuc = null) {
    $query = "SELECT COUNT(*) as count FROM khuvuc WHERE TenKhuVuc = ? AND TrangThai != -1";
    if ($maKhuVuc !== null) {
        $query .= " AND MaKhuVuc != ?";
    }
    
    $stmt = $conn->prepare($query);
    if ($maKhuVuc !== null) {
        $stmt->bind_param("si", $tenKhuVuc, $maKhuVuc);
    } else {
        $stmt->bind_param("s", $tenKhuVuc);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'] > 0;
}

// Xử lý thêm khu vực
if (isset($_POST['saveKhuVuc'])) {
    $tenKhuVuc = trim($_POST['tenkhuvuc']);
    $moTa = trim($_POST['mota']);
    $trangThai = isset($_POST['status']) ? intval($_POST['status']) : 0;

    // Kiểm tra userId trong session
    if (!isset($_SESSION['userId'])) {
        redirect('views/khuvuc/khuvuc-add.php', 'error', 'Không tìm thấy thông tin người dùng. Vui lòng đăng nhập lại.');
    }
    $nguoiTao = $_SESSION['userId'];

    // Kiểm tra dữ liệu
    $errors = [];
    if (empty($tenKhuVuc)) {
        $errors['tenkhuvuc'] = 'Vui lòng nhập tên khu vực.';
    } else {
        // Kiểm tra trùng tên khu vực
        if (checkDuplicateKhuVuc($conn, $tenKhuVuc)) {
            $errors['tenkhuvuc'] = 'Tên khu vực đã tồn tại. Vui lòng chọn tên khác.';
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['messages'] = $errors;
        $_SESSION['form_data'] = $_POST;
        redirect('admin/views/khuvuc/khuvuc-add.php', 'error', 'Vui lòng kiểm tra lại dữ liệu.');
    }

    // Thêm khu vực vào cơ sở dữ liệu
    $query = "INSERT INTO khuvuc (TenKhuVuc, MoTa, NguoiTao, NgayTao, NguoiCapNhat, NgayCapNhat, TrangThai) 
              VALUES (?, ?, ?, CURRENT_TIMESTAMP, ?, CURRENT_TIMESTAMP, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssiii", $tenKhuVuc, $moTa, $nguoiTao, $nguoiTao, $trangThai);
    
    if ($stmt->execute()) {
        redirect('admin/khuvuc.php', 'success', 'Thêm khu vực thành công.');
    } else {
        redirect('admin/views/khuvuc/khuvuc-add.php', 'error', 'Có lỗi xảy ra khi thêm khu vực: ' . $conn->error);
    }
}

// Xử lý sửa khu vực
if (isset($_POST['updateKhuVuc'])) {
    $maKhuVuc = check_valid_ID('id');
    $tenKhuVuc = trim($_POST['tenkhuvuc']);
    $moTa = trim($_POST['mota']);
    $trangThai = isset($_POST['status']) ? intval($_POST['status']) : 0;

    // Kiểm tra userId trong session
    if (!isset($_SESSION['userId'])) {
        redirect("views/khuvuc/khuvuc-edit.php?id=$maKhuVuc", 'error', 'Không tìm thấy thông tin người dùng. Vui lòng đăng nhập lại.');
    }
    $nguoiCapNhat = $_SESSION['userId'];

    // Kiểm tra dữ liệu
    $errors = [];
    if (empty($tenKhuVuc)) {
        $errors['tenkhuvuc'] = 'Vui lòng nhập tên khu vực.';
    } else {
        // Kiểm tra trùng tên khu vực (ngoại trừ khu vực đang sửa)
        if (checkDuplicateKhuVuc($conn, $tenKhuVuc, $maKhuVuc)) {
            $errors['tenkhuvuc'] = 'Tên khu vực đã tồn tại. Vui lòng chọn tên khác.';
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['messages'] = $errors;
        $_SESSION['form_data'] = $_POST;
        redirect("admin/views/khuvuc/khuvuc-edit.php?id=$maKhuVuc", 'error', 'Vui lòng kiểm tra lại dữ liệu.');
    }

    // Cập nhật khu vực
    $query = "UPDATE khuvuc SET TenKhuVuc = ?, MoTa = ?, NguoiCapNhat = ?, NgayCapNhat = CURRENT_TIMESTAMP, TrangThai = ? WHERE MaKhuVuc = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssiii", $tenKhuVuc, $moTa, $nguoiCapNhat, $trangThai, $maKhuVuc);
    
    if ($stmt->execute()) {
        redirect('admin/khuvuc.php', 'success', 'Cập nhật khu vực thành công.');
    } else {
        redirect("admin/views/khuvuc/khuvuc-edit.php?id=$maKhuVuc", 'error', 'Có lỗi xảy ra khi cập nhật khu vực: ' . $conn->error);
    }
}

// Xử lý xóa khu vực
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $maKhuVuc = check_valid_ID('id');
    
    // Kiểm tra xem khu vực có rạp chiếu phim nào không
    $query = "SELECT COUNT(*) as count FROM rapchieuphim WHERE MaKhuVuc = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $maKhuVuc);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        redirect('admin/khuvuc.php', 'error', 'Không thể xóa khu vực này vì vẫn còn rạp chiếu phim thuộc khu vực.');
    }

    // Xóa khu vực
    $query = "DELETE FROM khuvuc WHERE MaKhuVuc = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $maKhuVuc);
    
    if ($stmt->execute()) {
        redirect('admin/khuvuc.php', 'success', 'Xóa khu vực thành công.');
    } else {
        redirect('admin/khuvuc.php', 'error', 'Có lỗi xảy ra khi xóa khu vực: ' . $conn->error);
    }
}
?>