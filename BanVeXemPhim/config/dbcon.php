<?php
define('DB_SERVER', "localhost");
define('DB_USERNAME', "root");
define('DB_PASSWORD', "");
define('DB_DATABASE', "project_film");
define('DB_PORT', "3306"); // Cổng MySQL, mặc định là 3306

$conn = @mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);

mysqli_set_charset($conn, 'utf8');
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

