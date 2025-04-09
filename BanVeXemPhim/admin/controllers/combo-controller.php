<?php
require '../../config/function.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    redirect('sign-in.php', 'error', 'Vui lòng đăng nhập để quản lý combo.');
}

// Xử lý thêm combo
if (isset($_POST['saveCombo'])) {
    $tenCombo = trim($_POST['tencombo']);
    $moTa = trim($_POST['mota']);
    $giaCombo = floatval($_POST['giacombo']);
    $trangThai = isset($_POST['status']) ? intval($_POST['status']) : 0;

    // Kiểm tra userId trong session
    if (!isset($_SESSION['userId'])) {
        redirect('views/combo/combo-add.php', 'error', 'Không tìm thấy thông tin người dùng. Vui lòng đăng nhập lại.');
    }
    $nguoiTao = $_SESSION['userId'];

    // Xử lý upload ảnh
    $anh = '';
    if (isset($_FILES['anh']) && $_FILES['anh']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/combo-imgs/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $anh = time() . '_' . basename($_FILES['anh']['name']);
        $uploadFile = $uploadDir . $anh;
        if (!move_uploaded_file($_FILES['anh']['tmp_name'], $uploadFile)) {
            redirect('admin/views/combo/combo-add.php', 'error', 'Không thể upload ảnh.');
        }
    }

    // Kiểm tra dữ liệu
    $errors = [];
    if (empty($tenCombo)) $errors['tencombo'] = 'Vui lòng nhập tên combo.';
    if ($giaCombo <= 0) $errors['giacombo'] = 'Giá phải lớn hơn 0.';
    
    if (!empty($errors)) {
        $_SESSION['messages'] = $errors;
        $_SESSION['form_data'] = $_POST;
        redirect('admin/views/combo/combo-add.php', 'error', 'Vui lòng kiểm tra lại dữ liệu.');
    }

    // Thêm combo vào cơ sở dữ liệu
    $query = "INSERT INTO combo (TenCombo, MoTa, GiaCombo, Anh, NguoiTao, NgayTao, NguoiCapNhat, NgayCapNhat, TrangThai) 
              VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?, CURRENT_TIMESTAMP, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdssii", $tenCombo, $moTa, $giaCombo, $anh, $nguoiTao, $nguoiTao, $trangThai);
    
    if ($stmt->execute()) {
        redirect('admin/combo.php', 'success', 'Thêm combo thành công.');
    } else {
        redirect('views/combo/combo-add.php', 'error', 'Có lỗi xảy ra khi thêm combo: ' . $conn->error);
    }
}

// Xử lý sửa combo
if (isset($_POST['updateCombo'])) {
    $maCombo = check_valid_ID('id');
    $tenCombo = trim($_POST['tencombo']);
    $moTa = trim($_POST['mota']);
    $giaCombo = floatval($_POST['giacombo']);
    $trangThai = isset($_POST['status']) ? intval($_POST['status']) : 0;

    // Kiểm tra userId trong session
    if (!isset($_SESSION['userId'])) {
        redirect("views/combo/combo-edit.php?id=$maCombo", 'error', 'Không tìm thấy thông tin người dùng. Vui lòng đăng nhập lại.');
    }
    $nguoiCapNhat = $_SESSION['userId'];

    // Xử lý upload ảnh mới (nếu có)
    $anh = $_POST['old_anh']; // Giữ ảnh cũ nếu không upload ảnh mới
    if (isset($_FILES['anh']) && $_FILES['anh']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/combo-imgs/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $anh = time() . '_' . basename($_FILES['anh']['name']);
        $uploadFile = $uploadDir . $anh;
        if (!move_uploaded_file($_FILES['anh']['tmp_name'], $uploadFile)) {
            redirect("admin/views/combo/combo-edit.php?id=$maCombo", 'error', 'Không thể upload ảnh.');
        }
        // Xóa ảnh cũ nếu có
        if (!empty($_POST['old_anh']) && file_exists($uploadDir . $_POST['old_anh'])) {
            unlink($uploadDir . $_POST['old_anh']);
        }
    }

    // Kiểm tra dữ liệu
    $errors = [];
    if (empty($tenCombo)) $errors['tencombo'] = 'Vui lòng nhập tên combo.';
    if ($giaCombo <= 0) $errors['giacombo'] = 'Giá phải lớn hơn 0.';
    
    if (!empty($errors)) {
        $_SESSION['messages'] = $errors;
        $_SESSION['form_data'] = $_POST;
        redirect("admin/views/combo/combo-edit.php?id=$maCombo", 'error', 'Vui lòng kiểm tra lại dữ liệu.');
    }

    // Cập nhật combo
    $query = "UPDATE combo SET TenCombo = ?, MoTa = ?, GiaCombo = ?, Anh = ?, NguoiCapNhat = ?, NgayCapNhat = CURRENT_TIMESTAMP, TrangThai = ? WHERE MaCombo = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdssii", $tenCombo, $moTa, $giaCombo, $anh, $nguoiCapNhat, $trangThai, $maCombo);
    
    if ($stmt->execute()) {
        redirect('admin/combo.php', 'success', 'Cập nhật combo thành công.');
    } else {
        redirect("views/combo/combo-edit.php?id=$maCombo", 'error', 'Có lỗi xảy ra khi cập nhật combo: ' . $conn->error);
    }
}

// Xử lý xóa combo
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $maCombo = check_valid_ID('id');
    
    // Lấy thông tin combo để xóa ảnh
    $query = "SELECT Anh FROM combo WHERE MaCombo = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $maCombo);
    $stmt->execute();
    $result = $stmt->get_result();
    $combo = $result->fetch_assoc();

    // Xóa combo
    $query = "DELETE FROM combo WHERE MaCombo = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $maCombo);
    
    if ($stmt->execute()) {
        // Xóa ảnh nếu có
        if (!empty($combo['Anh']) && file_exists("../../uploads/combo-imgs/" . $combo['Anh'])) {
            unlink("../../uploads/combo-imgs/" . $combo['Anh']);
        }
        redirect('admin/combo.php', 'success', 'Xóa combo thành công.');
    } else {
        redirect('admin/combo.php', 'error', 'Có lỗi xảy ra khi xóa combo: ' . $conn->error);
    }
}
?>