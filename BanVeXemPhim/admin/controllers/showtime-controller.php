<?php
require '../../config/function.php';
getAdmin();
$messages = [];

//====== suatchieu-add =======//
if (isset($_POST['savesc'])) {
    $messages = [];
    $ngayBatDau = validate($_POST['ngaybatdau']);
    $ngayKetThuc = validate($_POST['ngayketthuc']);
    $khungGioList = isset($_POST['khunggio']) ? $_POST['khunggio'] : [];
    $mafilm = validate($_POST['maphim']);
    $status = validate($_POST['status']) == 1 ? 1 : 0;
    $maphong = validate($_POST['maphong']);
    $marap = validate($_POST['marap']);

    // Kiểm tra dữ liệu đầu vào
    if (empty($ngayBatDau)) {
        $messages['ngaybatdau'] = "Ngày bắt đầu không được để trống.";
    }
    if (empty($ngayKetThuc)) {
        $messages['ngayketthuc'] = "Ngày kết thúc không được để trống.";
    }
    if (empty($khungGioList)) {
        $messages['khunggio'] = "Phải chọn ít nhất một khung giờ chiếu.";
    }
    if (empty($mafilm)) {
        $messages['maphim'] = 'Tên phim không được để trống';
    }
    if (empty($maphong)) {
        $messages['maphong'] = 'Tên phòng không được để trống';
    }
    if (empty($marap)) {
        $messages['marap'] = 'Rạp chiếu phim không được để trống';
    }

    // Kiểm tra ngày hợp lệ
    $startDate = new DateTime($ngayBatDau);
    $endDate = new DateTime($ngayKetThuc);
    if ($startDate > $endDate) {
        $messages['ngayketthuc'] = "Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.";
    }

    // Nếu không có lỗi, tiến hành tạo các suất chiếu
    if (empty($messages)) {
        $success = true;
        $interval = new DateInterval('P1D'); // Tăng 1 ngày
        $period = new DatePeriod($startDate, $interval, $endDate->modify('+1 day')); // Lặp qua từng ngày

        foreach ($period as $date) {
            $ngayChieu = $date->format('Y-m-d');
            foreach ($khungGioList as $khungGio) {
                $gioChieu = "$ngayChieu $khungGio:00"; // Kết hợp ngày và giờ

                // Kiểm tra trùng lặp
                $checkQuery = "SELECT COUNT(*) FROM SuatChieu WHERE GioChieu = '$gioChieu' AND MaPhong = '$maphong' AND MaRap = '$marap'";
                $result = mysqli_query($conn, $checkQuery);
                $row = mysqli_fetch_array($result);

                if ($row[0] > 0) {
                    $messages['khunggio'] = "Giờ chiếu $gioChieu tại phòng và rạp đã tồn tại.";
                    $success = false;
                    break 2; // Thoát khỏi cả hai vòng lặp nếu có trùng lặp
                }

                // Thêm suất chiếu
                $query = "INSERT INTO SuatChieu (MaPhim, MaPhong, MaRap, GioChieu, NguoiTao, NgayTao, NguoiCapNhat, NgayCapNhat, TrangThai)
                          VALUES ('$mafilm', '$maphong', '$marap', '$gioChieu', '$created', CURRENT_TIMESTAMP, '$created', CURRENT_TIMESTAMP, '$status')";

                if (!mysqli_query($conn, $query)) {
                    $success = false;
                    break;
                }
            }
        }

        if ($success) {
            redirect('showtime.php', 'success', 'Thêm các suất chiếu thành công', 'admin');
        } else {
            redirect('views/showtime/showtime-add.php', 'error', 'Thêm suất chiếu thất bại', 'admin');
        }
    } else {
        $_SESSION['form_data'] = $_POST;
        redirect('views/showtime/showtime-add.php', 'messages', $messages, 'admin');
    }
}

//====== suatchieu-edit =======//
if (isset($_POST['editsc'])) {
    $messages = [];
    $id = validate($_POST['masc']);
    $giochieu = validate($_POST['giochieu']);
    $mafilm = validate($_POST['maphim']);
    $status = validate($_POST['status']) == 1 ? 1 : 0;
    $maphong = validate($_POST['maphong']);
    $marap = validate($_POST['marap']);

    if (empty($giochieu)) {
        $messages['giochieu'] = "Giờ chiếu không được để trống.";
    }
    if (empty($mafilm)) {
        $messages['maphim'] = 'Tên phim không được để trống';
    }
    if (empty($maphong)) {
        $messages['maphong'] = 'Tên phòng không được để trống';
    }
    if (empty($marap)) {
        $messages['marap'] = 'Rạp chiếu phim không được để trống';
    }

    $checkQuery = "SELECT COUNT(*) FROM SuatChieu WHERE GioChieu = '$giochieu' AND MaPhong = '$maphong' AND MaRap = '$marap' AND MaSuatChieu != '$id'";
    $result = mysqli_query($conn, $checkQuery);
    $row = mysqli_fetch_array($result);

    if ($row[0] > 0) {
        $messages['giochieu'] = "Giờ chiếu, phòng và rạp đã tồn tại.";
    }

    if (empty($messages)) {
        $query = "UPDATE SuatChieu SET
                MaPhim = '$mafilm',
                MaPhong = '$maphong',
                MaRap = '$marap',
                GioChieu = '$giochieu',
                NguoiCapNhat = '$created',
                NgayCapNhat = CURRENT_TIMESTAMP,
                TrangThai = '$status'
                WHERE MaSuatChieu = '$id'";

        if (mysqli_query($conn, $query)) {
            redirect('showtime.php', 'success', 'Cập nhật suất chiếu thành công', 'admin');
        } else {
            redirect('views/showtime/showtime-edit.php?id=' . $id, 'error', 'Cập nhật suất chiếu thất bại', 'admin');
        }
    } else {
        $_SESSION['form_data'] = $_POST;
        redirect('views/showtime/showtime-edit.php?id=' . $id, 'messages', $messages, 'admin');
    }
}

//====== changeStatus ======//
if (isset($_POST['changeStatus'])) {
    $id = validate($_POST['masc']);
    $status = validate($_POST['status']) == 1 ? 1 : 0;

    $edit_query = "UPDATE SUATCHIEU SET
                TrangThai = '$status',
                NguoiCapNhat = '$created',
                NgayCapNhat = CURRENT_TIMESTAMP
                WHERE MaSuatChieu = '$id'";

    if (mysqli_query($conn, $edit_query)) {
        redirect('showtime.php', 'success', 'Cập nhật trạng thái thành công', 'admin');
    } else {
        redirect('showtime.php', 'error', 'Cập nhật trạng thái thất bại', 'admin');
    }
}
$conn->close();
?>