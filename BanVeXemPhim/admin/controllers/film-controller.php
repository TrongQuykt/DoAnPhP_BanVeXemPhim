<?php
require '../../config/function.php';
getAdmin();
$messages = [];

//====== film-add =======//
if (isset($_POST['saveFilm'])) {
    $messages = [];
    $name = validate($_POST['ten_phim']);
    $phanloai = validate($_POST['phan_loai']);
    $dao_dien = validate($_POST['dao_dien']);
    $dien_vien = validate($_POST['dien_vien']);
    $quoc_gia = $_POST['quoc_gia'] ?? [];
    if (!empty($_POST['other_nation'])) {
        $quoc_gia[] = validate($_POST['other_nation']);
    }
    $quoc_gia = implode(', ', $quoc_gia);
    $mota = validate($_POST['mo_ta']);
    $theloai = $_POST['the_loai'] ?? [];
    $namphathanh = validate($_POST['nam_phat_hanh']);
    $thoiluong = validate($_POST['thoi_luong']);
    $status = validate($_POST['status']);
    $trailer = validate($_POST['trailer']); // Thêm trường trailer
    $id = uniqid('film_', false);
    $anh_phim = '';
    $banner = '';
    $slug = str_slug($name);

    // Kiểm tra tên phim
    if (empty($name)) {
        $messages['ten_phim'] = 'Tên phim không được để trống';
    } else {
        // Kiểm tra tên phim có bị trùng không
        if (isExistValue('PHIM', 'TenPhim', $name)) {
            $messages['ten_phim'] = 'Tên phim đã tồn tại. Vui lòng chọn tên khác.';
        }
    }

    // Kiểm tra tên đạo diễn
    if (empty($dao_dien)) {
        $messages['dao_dien'] = 'Tên đạo diễn không được để trống';
    }

    // Kiểm tra tên diễn viên
    if (empty($dien_vien)) {
        $messages['dien_vien'] = 'Tên diễn viên không được để trống';
    }

    if (isset($_FILES['anh_phim'])) {
        $imgResult = uploadImage($_FILES['anh_phim'], "../../uploads/film-imgs/", $id);
        if ($imgResult['success']) {
            $anh_phim = $imgResult['filename'];
        } else {
            $messages[] = $imgResult['message'];
        }
    }

    if (isset($_FILES['banner'])) {
        $imgResult = uploadImage($_FILES['banner'], "../../uploads/film-imgs/", "$id-$namphathanh");
        if ($imgResult['success']) {
            $banner = $imgResult['filename'];
        } else {
            $messages[] = $imgResult['message'];
        }
    }

    if (empty($messages)) {
        $query = "INSERT INTO PHIM (TenPhim, TenRutGon, ThoiLuong, Anh, Banner, Trailer, DaoDien, DienVien, QuocGia, NamPhatHanh, PhanLoai, MoTa, NguoiTao, NgayTao, NguoiCapNhat, NgayCapNhat, TrangThai)
                  VALUES ('$name', '$slug', '$thoiluong', '$anh_phim', '$banner', '$trailer', '$dao_dien', '$dien_vien', '$quoc_gia', '$namphathanh', '$phanloai', '$mota', '$created', CURRENT_TIMESTAMP, '$created', CURRENT_TIMESTAMP, '$status')";
        if (mysqli_query($conn, $query)) {
            $maPhim = mysqli_insert_id($conn);
            foreach ($theloai as $maTheLoai) {
                $insertQuery = "INSERT INTO TheLoai_Film (MaTheLoai, MaPhim) VALUES ('$maTheLoai', '$maPhim')";
                mysqli_query($conn, $insertQuery);
            }
            redirect('film.php', 'success', 'Thêm phim thành công', 'admin');
        } else {
            redirect('views/film/film-add.php', 'error', 'Thêm phim thất bại', 'admin');
        }
    } else {
        $_SESSION['form_data'] = $_POST;
        redirect('views/film/film-add.php', 'messages', $messages, 'admin');
    }
}

//====== film-edit =======//
if (isset($_POST['editFilm'])) {
    $messages = [];
    $name = validate($_POST['ten_phim']);
    $id = validate($_POST['ma_phim']);
    $phanloai = validate($_POST['phan_loai']);
    $dao_dien = validate($_POST['dao_dien']);
    $dien_vien = validate($_POST['dien_vien']);
    $quoc_gia = $_POST['quoc_gia'] ?? [];
    if (!empty($_POST['other_nation'])) {
        $quoc_gia[] = validate($_POST['other_nation']);
    }
    $quoc_gia = implode(', ', $quoc_gia);
    $mota = validate($_POST['mo_ta']);
    $theloai = $_POST['the_loai'] ?? [];
    $namphathanh = validate($_POST['nam_phat_hanh']);
    $thoiluong = validate($_POST['thoi_luong']);
    $status = validate($_POST['status']);
    $trailer = validate($_POST['trailer']); // Thêm trường trailer

    $film = getByID('Phim', 'MaPhim', $id);
    $anh_phim = $film['data']['Anh'];
    $banner = $film['data']['Banner'];
    $unique = uniqid('film_', false);

    // Kiểm tra tên phim
    if (empty($name)) {
        $messages['ten_phim'] = 'Tên phim không được để trống';
    } else {
        // Kiểm tra tên phim có bị trùng không (bỏ qua phim hiện tại)
        if (isExistValue('PHIM', 'TenPhim', $name, 'MaPhim', $id)) {
            $messages['ten_phim'] = 'Tên phim đã tồn tại. Vui lòng chọn tên khác.';
        }
    }

    // Kiểm tra tên đạo diễn
    if (empty($dao_dien)) {
        $messages['dao_dien'] = 'Tên đạo diễn không được để trống';
    }

    // Kiểm tra tên diễn viên
    if (empty($dien_vien)) {
        $messages['dien_vien'] = 'Tên diễn viên không được để trống';
    }

    if (isset($_FILES['anh_phim']) && $_FILES['anh_phim']['error'] == 0) {
        $filmPath = "../../uploads/film-imgs/" . $anh_phim;
        if (!empty($anh_phim) && file_exists($filmPath)) {
            $deleteResult = deleteImage($filmPath);
            if (!$deleteResult['success']) {
                $messages[] = $deleteResult['message'];
            }
        }
        $filmResult = uploadImage($_FILES['anh_phim'], "../../uploads/film-imgs/", $unique);
        if ($filmResult['success']) {
            $anh_phim = $filmResult['filename'];
        } else {
            $messages[] = $filmResult['message'];
        }
    }

    if (isset($_FILES['banner']) && $_FILES['banner']['error'] == 0) {
        $bannerPath = "../../uploads/film-imgs/" . $banner;
        $slug = str_slug($name);
        if (!empty($banner) && file_exists($bannerPath)) {
            $deleteResult = deleteImage($bannerPath);
            if (!$deleteResult['success']) {
                $messages[] = $deleteResult['message'];
            }
        }
        $filmResult = uploadImage($_FILES['banner'], "../../uploads/film-imgs/", "$unique-$namphathanh");
        if ($filmResult['success']) {
            $banner = $filmResult['filename'];
        } else {
            $messages[] = $filmResult['message'];
        }
    }

    if (empty($messages)) {
        $deleteQuery = "DELETE FROM THELOAI_FILM WHERE MAPHIM = '$id'";
        mysqli_query($conn, $deleteQuery);

        $slug = str_slug($name);

        $query = "UPDATE PHIM SET
                TenPhim = '$name',
                TenRutGon = '$slug',
                ThoiLuong = '$thoiluong',
                Anh = '$anh_phim',
                Banner = '$banner',
                Trailer = '$trailer',
                DaoDien = '$dao_dien',
                DienVien = '$dien_vien',
                QuocGia = '$quoc_gia',
                NamPhatHanh = '$namphathanh',
                PhanLoai = '$phanloai',
                MoTa = '$mota',
                NguoiCapNhat = '$created',
                NgayCapNhat = CURRENT_TIMESTAMP,
                TrangThai = '$status'
                WHERE MaPhim = '$id'";

        if (mysqli_query($conn, $query)) {
            foreach ($theloai as $maTheLoai) {
                $insertQuery = "INSERT INTO TheLoai_Film (MaTheLoai, MaPhim) VALUES ('$maTheLoai', '$id')";
                mysqli_query($conn, $insertQuery);
            }
            redirect('film.php', 'success', 'Cập nhật phim thành công', 'admin');
        } else {
            redirect('views/film/film-edit.php?id=' . $id, 'error', 'Cập nhật phim thất bại', 'admin');
        }
    } else {
        $_SESSION['form_data'] = $_POST;
        redirect('views/film/film-edit.php?id=' . $id, 'messages', $messages, 'admin');
    }
}

//====== changeStatus ======//
if (isset($_POST['changeStatus'])) {
    $id = validate($_POST['ma_phim']);
    $status = validate($_POST['status']);

    if (in_array($status, [0, 1, 2])) {
        $edit_query = "UPDATE Phim SET TrangThai = '$status' WHERE MaPhim = '$id'";
        if (mysqli_query($conn, $edit_query)) {
            redirect('film.php', 'success', 'Cập nhật trạng thái thành công', 'admin');
        } else {
            redirect('film.php', 'error', 'Cập nhật trạng thái thất bại', 'admin');
        }
    } else {
        redirect('film.php', 'error', 'Trạng thái không hợp lệ', 'admin');
    }
}
?>