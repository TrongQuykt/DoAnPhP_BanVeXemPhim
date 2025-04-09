<?php
session_start();
require_once '../config/function.php';
require_once '../config/sendmail.php';


// xử lý categories
$messages = [];
if (isset($_POST['signup'])) {
    $tennd = validate($_POST['tennd']);
    $password = validate($_POST['password']);
    $tendn = validate($_POST['tendn']);
    $email = validate($_POST['email']); // Thêm trường Gmail
    $re_password = validate($_POST['re_password']);
    // $captchaResponse = $_POST['g-recaptcha-response'];
    $secretKey = "6LddNHoqAAAAAOyi3IX4uU4dxgNnB29kbHUgjQcK";
    // $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$captchaResponse}");
    $responseKeys = json_decode($response, true);

    // if (intval($responseKeys["success"]) !== 1) {
    //     $messages['captcha'] = "Vui lòng xác nhận rằng bạn không phải là robot.";
    // }
    $role = 0;
    $status = 1;

    // Kiểm tra Họ và tên
    if (empty($tennd)) {
        $messages['tennd'] = "Tên người dùng không được để trống.";
    } else if (!preg_match('/^[\p{L}\s.,]+$/u', $tennd)) {
        $messages['tennd'] = "Tên người dùng không được dùng kí tự đặc biệt và số";
    }

    // Kiểm tra Tên đăng nhập
    if (empty($tendn)) {
        $messages['tendn'] = "Tên đăng nhập không được để trống.";
    } else if (!preg_match('/^[a-zA-Z0-9]+$/', $tendn)) {
        $messages['tendn'] = "Tên đăng nhập chỉ chấp nhận chữ cái và số.";
    }
    if (isExistValue('TaiKhoan', 'TenDangNhap', $tendn)) {
        $messages['tendn'] = "Tên đăng nhập đã tồn tại";
    }

    // Kiểm tra Gmail (truy vấn vào bảng NguoiDung)
    if (empty($email)) {
        $messages['email'] = "Gmail không được để trống.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $messages['email'] = "Gmail không hợp lệ.";
    } else if (isExistValue('NguoiDung', 'Email', $email)) { // Sửa đổi: kiểm tra trong bảng NguoiDung
        $messages['email'] = "Gmail đã tồn tại.";
    }

    // Kiểm tra Mật khẩu
    if (empty($password)) {
        $messages['password'] = "Mật khẩu không được để trống.";
    } else if (!preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{6,}$/', $password)) {
        $messages['password'] = "Mật khẩu phải có ít nhất 6 kí tự, bao gồm một chữ in hoa, một số và một ký tự đặc biệt.";
    }
    if (empty($re_password)) {
        $messages['re_password'] = "Xác nhận mật khẩu không được để trống.";
    }

    $passwordDetails = validateAndHashPassword($password, $re_password);

    if ($passwordDetails['status'] == false) {
        $messages['password'] = $passwordDetails['message'];
    }
    $hashedPassword = $passwordDetails['hashed'];

    // Nếu không có lỗi, tiến hành thêm dữ liệu vào cơ sở dữ liệu
    if (empty($messages)) {
        // Thêm vào bảng TaiKhoan (không có cột Email)
        $query = "INSERT INTO TaiKhoan (TenDangNhap, MatKhau, TenND, Quyen)
                  VALUES ('$tendn', '$hashedPassword', '$tennd', '$role')";

        if (mysqli_query($conn, $query)) {
            $maND = mysqli_insert_id($conn);
            // Thêm vào bảng NguoiDung (bao gồm cột Email)
            $insert_query = "INSERT INTO NguoiDung (MaND, TenND, Email, NguoiTao, NgayTao, NguoiCapNhat, NgayCapNhat, TrangThai)
                            VALUES ('$maND', '$tennd', '$email', '0', CURRENT_TIMESTAMP, '0', CURRENT_TIMESTAMP, '1')";
            mysqli_query($conn, $insert_query);
            redirect('views/login.php', 'success', 'Tạo tài khoản thành công');
        } else {
            redirect('views/register.php', 'error', 'Tạo tài khoản thất bại');
        }
    } else {
        $_SESSION['form_data'] = $_POST;
        redirect('views/register.php', 'messages', $messages);
    }
}
if (isset($_POST['login'])) {
    $tendn = validate($_POST['tendn']);
    $password = validate($_POST['password']);
    $messages = [];

    if (empty($tendn)) {
        $messages['tendn'] = 'Tên đăng nhập không được bỏ trống';
    }
    if (empty($password)) {
        $messages['password'] = 'Mật khẩu không được bỏ trống';
    }

    if (empty($messages)) {
        $user = getByID('TaiKhoan', 'TenDangNhap', $tendn);
        if ($user['status'] == 200 && $user['data']['Quyen'] == 0) {
            if (password_verify($password, $user['data']['MatKhau'])) {
                $_SESSION['NDloggedIn'] = true;
                $_SESSION['NDId'] = $user['data']['MaND'];
                $_SESSION['lastActivity'] = time();
                $_SESSION['role'] = 'user';
                // Kiểm tra nếu checkbox 'rememberMe' được chọn
                if (isset($_POST['remember_me']) && $_POST['remember_me'] == '1') {
                    $_SESSION['rememberMe'] = true; // Lưu vào session

                    // Lưu cookie cho 'username' trong 30 ngày
                    setcookie('username', $username, time() + (30 * 24 * 60 * 60), "/"); // 30 ngày
                } else {
                    $_SESSION['rememberMe'] = false;

                    // Xóa cookie nếu 'rememberMe' không được chọn
                    if (isset($_COOKIE['username'])) {
                        setcookie('username', '', time() - 3600, "/");
                    }
                }

                redirect('index.php', 'success', 'Đăng nhập thành công');
            } else {
                $messages['password'] = 'Sai mật khẩu';
                $_SESSION['form_data'] = $_POST;
                redirect('views/login.php', 'messages', $messages);
            }
        } else {
            redirect('views/login.php', 'error', 'Đăng nhập thất bại');
        }
    } else {
        // Lưu thông tin lỗi và dữ liệu form vào session nếu có lỗi
        $_SESSION['form_data'] = $_POST;
        redirect('views/login.php', 'messages', $messages);
    }
}
//====== user-edit =======//
if (isset($_POST['updateInf'])) {
    $messages = [];
    $id = validate($_POST['mand']);
    $name = validate($_POST['tennd']);
    $ngay_sinh = validate($_POST['ngay_sinh']) ?? null;
    $gioi_tinh = validate($_POST['gioi_tinh']) ?? null;
    $sdt = validate($_POST['sdt']) ?? null;
    $email = validate($_POST['email']) ?? null;

    // Lấy email gốc từ database để so sánh
    $query_original = "SELECT Email FROM nguoidung WHERE MaND = '$id'";
    $result = mysqli_query($conn, $query_original);
    $original_email = mysqli_fetch_assoc($result)['Email'];

    // Kiểm tra tên người dùng
    if (empty($name)) {
        $messages['tennd'] = "Họ và tên không được để trống.";
    } elseif (!preg_match('/^[a-zA-ZÀ-ỹ\s]+$/', $name)) {
        $messages['tennd'] = "Họ và tên không được chứa ký tự đặc biệt hoặc số.";
    }

    // Kiểm tra email
    if (empty($email)) {
        $messages['email'] = "Email không được để trống.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $messages['email'] = "Email không đúng định dạng.";
    } elseif ($email !== $original_email && isExistValue('nguoidung', 'Email', $email, 'MaND', $id)) {
        $messages['email'] = "Email đã tồn tại.";
    }

    // Kiểm tra số điện thoại
    if (!empty($sdt) && !preg_match('/^0[0-9]{9}$/', $sdt)) {
        $messages['sdt'] = "Số điện thoại phải bắt đầu bằng 0 và có đúng 10 chữ số.";
    }

    if (empty($messages)) {
        $query = "UPDATE nguoidung SET
                TenND = '$name',
                NgaySinh = '$ngay_sinh',
                GioiTinh = '$gioi_tinh',
                SDT = '$sdt',
                Email = '$email',
                NguoiCapNhat = '$id',
                NgayCapNhat = CURRENT_TIMESTAMP
                WHERE MaND = '$id'";

        if (mysqli_query($conn, $query)) {
            redirect('views/profile-user.php', 'success', 'Cập nhật tài khoản thành công');
        } else {
            redirect('views/profile-user.php', 'error', 'Cập nhật tài khoản thất bại');
        }
    } else {
        redirect('views/profile-user.php', 'messages', $messages);
        $_SESSION['form_data'] = $_POST;
    }
}

if (isset($_POST['change-password-form'])) {
    $messages = [];

    // Kiểm tra và lấy MaND
    $id = isset($_POST['mand']) ? validate($_POST['mand']) : null;
    if (empty($id)) {
        $messages['mand'] = 'Không tìm thấy mã người dùng';
        echo json_encode([
            'status' => 'error',
            'messages' => $messages
        ]);
        exit();
    }

    // Lấy thông tin người dùng
    $user = getByID('taikhoan', 'MaND', $id);
    if (!$user || !isset($user['data'])) {
        $messages['mand'] = 'Người dùng không tồn tại';
        echo json_encode([
            'status' => 'error',
            'messages' => $messages
        ]);
        exit();
    }

    // Lấy dữ liệu từ form
    $pwd = validate($_POST['old-password']);
    $newPassword = validate($_POST['new-password']);
    $rePassword = validate($_POST['new-repassword']);

    // Kiểm tra mật khẩu hiện tại
    if (empty($pwd)) {
        $messages['old-password'] = 'Không được để trống';
    } elseif (!password_verify($pwd, $user['data']['MatKhau'])) {
        $messages['old-password'] = 'Sai mật khẩu';
    }

    // Kiểm tra mật khẩu mới
    if (empty($newPassword)) {
        $messages['new-password'] = 'Không được để trống';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{6,}$/', $newPassword)) {
        $messages['new-password'] = "Mật khẩu phải có ít nhất 6 kí tự, bao gồm một chữ in hoa, một số và một ký tự đặc biệt.";
    }

    // Kiểm tra xác nhận mật khẩu
    if (empty($rePassword)) {
        $messages['new-repassword'] = 'Không được để trống';
    }

    // Kiểm tra và mã hóa mật khẩu mới
    $passwordDetails = validateAndHashPassword($newPassword, $rePassword);
    if ($passwordDetails['status'] == false) {
        $messages['password'] = $passwordDetails['message'];
    }
    $hashedPassword = $passwordDetails['hashed'];

    // Nếu không có lỗi, cập nhật mật khẩu
    if (empty($messages)) {
        $query = "UPDATE taikhoan SET
                    MatKhau = '$hashedPassword'
                WHERE MaND = '$id'";

        if (mysqli_query($conn, $query)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Cập nhật mật khẩu thành công'
            ]);
            redirect('views/profile-user.php', 'success', 'Cập nhật mật khẩu thành công');
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Cập nhật mật khẩu thất bại'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'messages' => $messages
        ]);
    }
    exit();
}

//====== user-edit =======//
if (isset($_POST['updateAvt'])) {
    $messages = [];
    $id = validate($_POST['mand']);
    $user = getByID('NguoiDung', 'MaND', $id);
    $currentAvatar = $user['data']['Anh'];
    $unique = uniqid('user_', false);

    if (isset($_FILES['avatar'])) {
        if (!empty($currentAvatar)) {
            $avatarPath = $_SERVER['DOCUMENT_ROOT'] . "/BanVeXemPhim/uploads/avatars/" . $currentAvatar;
            if (file_exists($avatarPath)) {
                $deleteResult = deleteImage($avatarPath);
                if (!$deleteResult['success']) {
                    $messages[] = $deleteResult['message'];
                }
            }
        }
        $avatarResult = uploadImage($_FILES['avatar'], $_SERVER['DOCUMENT_ROOT'] . "/BanVeXemPhim/uploads/avatars/", $unique);
        if ($avatarResult['success']) {
            $avatar = $avatarResult['filename'];
        } else {
            $messages[] = $avatarResult['message'];
        }
    }
    if (empty($messages)) {

        $query = "UPDATE NguoiDung SET
                Anh = '$avatar',
                NguoiCapNhat = '$id',
                NgayCapNhat = CURRENT_TIMESTAMP
                WHERE MaND = '$id'";

        if (mysqli_query($conn, $query)) {
            redirect('views/profile-user.php', 'success', 'Cập nhật avatar thành công');
        } else {
            redirect('views/profile-user.php', 'error', 'Cập nhật avatar thất bại');
        }
    } else {
        redirect('views/profile-user.php', 'messages', $messages);
        $_SESSION['form_data'] = $_POST;
    }
}

if (isset($_POST['forget-password'])) {
    // Đặt header để đảm bảo trả về JSON
    header('Content-Type: application/json; charset=UTF-8');

    $messages = [];
    $recipientEmail = validate($_POST['email-fpwd']);
    $username = validate($_POST['username-fpwd']);
    $subject = 'Yêu Cầu Lấy Lại Mật Khẩu - CGV';

    // Kiểm tra tên đăng nhập
    if (empty($username)) {
        $messages["username-fpwd"] = "Tên đăng nhập không được để trống";
    } else {
        $user = getByID('taikhoan', 'TenDangNhap', $username);
        if (!$user || !isset($user['data']['TenDangNhap'])) {
            $messages["username-fpwd"] = "Tên đăng nhập không tồn tại";
            // Trả về ngay nếu tên đăng nhập không tồn tại
            echo json_encode([
                'status' => 'error',
                'messages' => $messages
            ]);
            exit();
        }
    }

    // Chỉ kiểm tra email nếu tên đăng nhập tồn tại
    if (empty($recipientEmail)) {
        $messages["email-fpwd"] = "Email không được để trống";
    } else if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        $messages["email-fpwd"] = "Email không đúng định dạng";
    } else {
        $userByEmail = getByID('NguoiDung', 'Email', $recipientEmail);
        if (!$userByEmail || !isset($userByEmail['data']['Email'])) {
            $messages["email-fpwd"] = "Email không tồn tại trong hệ thống";
        } else {
            if ($user['data']['MaND'] != $userByEmail['data']['MaND']) {
                $messages["email-fpwd"] = "Email không khớp với tên đăng nhập này";
            }
        }
    }

    // Nếu không có lỗi, tiến hành cập nhật mật khẩu và gửi email
    if (empty($messages)) {
        $password = generateRandomPassword(6);
        $newPassword = password_hash($password, PASSWORD_DEFAULT);

        $query = "UPDATE taikhoan SET
                    MatKhau = '$newPassword'
                  WHERE TenDangNhap = '$username'";

        if (mysqli_query($conn, $query)) {
            $body = '
            <h2>Mật khẩu mới là: ' . $password . '</h2></br>
            <h4>Vui lòng lấy mật khẩu được cấp này, vào trong hồ sơ người dùng để tự đổi mật khẩu mới theo ý muốn!</h4>';

            if (sendEmail($recipientEmail, $subject, $body)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Đã gửi mật khẩu mới qua Gmail thành công.'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Thay đổi mật khẩu thành công nhưng gửi email thất bại.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Thay đổi mật khẩu thất bại.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'messages' => $messages
        ]);
    }
    exit();
}

$conn->close();