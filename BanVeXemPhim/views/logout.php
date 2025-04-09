<?php
require_once '../config/function.php';
session_start();
$_SESSION['NDloggedIn'] = false;

redirect('views/login.php', 'success', 'Đăng xuất thành công');
