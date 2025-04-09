<?php
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    redirect('sign-in.php', 'error', 'Vui lòng đăng nhập');
}
$current_year = date('Y');
$last_year = $current_year - 1;

$current_year_revenue = get_yearly_revenue($current_year);
$last_year_revenue = get_yearly_revenue($last_year);

$current_year_revenue_json = json_encode($current_year_revenue);
$last_year_revenue_json = json_encode($last_year_revenue);

//Doanh thu ngày
$today = date('Y-m-d');
$today_revenue = time_revenue2($today, $today);
// Lấy 7 ngày gần nhất từ hôm nay
$days = [];
$revenues = [];
for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime("-$i days")); // Ngày lùi lại từng ngày
    $day_month = date('d-m', strtotime($date)); // Lấy ngày và tháng (d-m)
    $revenue = time_revenue2($date, $date); // Giả sử đây là hàm tính doanh thu theo ngày
    $days[] = $day_month;
    $revenues[] = $revenue;
}
$monthly_revenue = [];

// Lặp từ tháng 1 đến tháng 12
for ($month = 1; $month <= 12; $month++) {
    // Tính năm và tháng tương ứng
    $year = date('Y');
    $month_str = str_pad($month, 2, '0', STR_PAD_LEFT);
    $month_date = "$year-$month_str";

    // Tính ngày đầu và ngày cuối của tháng
    $month_start = date('Y-m-01', strtotime($month_date));
    $month_end = date('Y-m-t', strtotime($month_date));

    // Gọi hàm time_revenue để tính doanh thu cho tháng này
    $revenue = time_revenue2($month_start, $month_end);

    // Nếu không có doanh thu, gán giá trị bằng 0
    if ($revenue === null) {
        $revenue = 0;
    }

    // Thêm doanh thu vào mảng
    $monthly_revenue[] = $revenue;
}

// Chuyển mảng doanh thu này sang định dạng JSON để JavaScript có thể sử dụng
$monthly_revenue_json = json_encode($monthly_revenue);


// Đảo ngược mảng days và revenues để hiển thị từ ngày cũ đến ngày mới
$days = array_reverse($days);
$revenues = array_reverse($revenues);

//Doanh thu cả rạp
$revenue = ticket_revenue2();
//Tổng số khách hàng
$totalCustomers = count_user();
$total_bill = count_record('hoadon');

$users = getAll('nguoidung');

// Mảng để lưu trữ mã người dùng và doanh thu
$user_revenues = [];
// Lặp qua từng người dùng để lấy mã và doanh thu
foreach ($users as $user) {
    if ($user['TenND'] != 'Admin') {
        $revenue_user = client_revenue2($user['MaND']);
        $user_revenues[] = [
            'username' => $user['TenND'],
            'revenue' => $revenue_user
        ];
    }
}

// Sắp xếp mảng theo doanh thu giảm dần
usort($user_revenues, function ($a, $b) {
    return $b['revenue'] <=> $a['revenue'];
});

// Lấy 5 người có doanh thu cao nhất
$top_users = array_slice($user_revenues, 0, 5);


// Tạo mảng cho biểu đồ
$labels = [];
$data = [];

foreach ($top_users as $user_revenue) {
    $labels[] = $user_revenue['username']; // Hoặc tên người dùng nếu có
    $data[] = $user_revenue['revenue'];
}

// Chuyển đổi mảng thành JSON để sử dụng trong JavaScript
$labels_json = json_encode($labels);
$data_json = json_encode($data);

//film
$films = getAll('phim');
$film_revenues = [];
foreach ($films as $film) {
    $revenue_film = film_revenue2($film['MaPhim']);
    $film_revenues[] = [
        'filmname' => $film['TenPhim'],
        'revenue' => $revenue_film
    ];
}

// Sắp xếp mảng theo doanh thu giảm dần
usort($film_revenues, function ($a, $b) {
    return $b['revenue'] <=> $a['revenue'];
});
// Lấy 5 người có doanh thu cao nhất
$top_films = array_slice($film_revenues, 0, 5);

$name_film = [];
$data_film = [];
foreach ($top_films as $film_revenue) {
    $name_film[] = $film_revenue['filmname'];
    $data_film[] = $film_revenue['revenue'];
}

// Chuyển đổi mảng thành JSON để sử dụng trong JavaScript
$labels_film_json = json_encode($name_film);
$data_film_json = json_encode($data_film);

?>
<div class="dashboard-container">
    <!-- Tổng quan -->
    <div class="row overview-section">
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card overview-card shadow-lg">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-title">Tổng doanh thu</p>
                            <h3 class="card-value"><?= number_format($revenue, 0, ',', '.') ?> VNĐ</h3>
                        </div>
                        <div class="card-icon bg-gradient-primary">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card overview-card shadow-lg">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-title">Doanh thu hôm nay</p>
                            <h3 class="card-value"><?= number_format($today_revenue ?? 0, 0, ',', '.') ?> VNĐ</h3>
                        </div>
                        <div class="card-icon bg-gradient-success">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card overview-card shadow-lg">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-title">Tổng khách hàng</p>
                            <h3 class="card-value"><?= number_format($totalCustomers, 0, ',', '.') ?></h3>
                        </div>
                        <div class="card-icon bg-gradient-danger">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card overview-card shadow-lg">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-title">Tổng hóa đơn</p>
                            <h3 class="card-value"><?= number_format($total_bill, 0, ',', '.') ?></h3>
                        </div>
                        <div class="card-icon bg-gradient-info">
                            <i class="bi bi-receipt-cutoff"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ -->
    <div class="row chart-section mt-5">
        <div class="col-lg-5 mb-4">
            <div class="card chart-card shadow-lg">
                <div class="card-body">
                    <h4 class="chart-title">Doanh thu theo ngày</h4>
                    <canvas id="chart-bars" class="chart-canvas"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-7 mb-4">
            <div class="card chart-card shadow-lg">
                <div class="card-body">
                    <h4 class="chart-title">Xu hướng doanh thu</h4>
                    <p class="chart-subtitle">Năm <span class="highlight"><?= $current_year ?></span> (<?= number_format($current_year_revenue) ?> VNĐ) <i id="arrow-icon" class="fa"></i> <span id="revenue-change"></span> so với năm <span class="highlight"><?= $last_year ?></span> (<?= number_format($last_year_revenue) ?> VNĐ)</p>
                    <canvas id="chart-line" class="chart-canvas"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row chart-section mt-5">
        <div class="col-lg-6 mb-4">
            <div class="card chart-card shadow-lg bg-dark">
                <div class="card-body">
                    <h4 class="chart-title">Top 5 khách hàng chi tiêu cao</h4>
                    <canvas id="chart-bars-user" class="chart-canvas"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card chart-card shadow-lg bg-gradient-info">
                <div class="card-body">
                    <h4 class="chart-title">Top 5 phim doanh thu cao</h4>
                    <canvas id="chart-bars-film" class="chart-canvas"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
body {
    background: linear-gradient(135deg, #1e3a8a, #6b21a8);
    font-family: 'Poppins', sans-serif;
    color: #fff;
}

.dashboard-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.overview-card {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 15px;
    backdrop-filter: blur(10px);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.overview-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
}

.card-body {
    padding: 20px;
}

.card-title {
    font-size: 0.9rem;
    text-transform: uppercase;
    color: #e5e7eb;
    margin-bottom: 5px;
}

.card-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #fff;
}

.card-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #fff;
}

.bg-gradient-primary { background: linear-gradient(45deg, #3b82f6, #1e3a8a); }
.bg-gradient-success { background: linear-gradient(45deg, #22c55e, #15803d); }
.bg-gradient-danger { background: linear-gradient(45deg, #ef4444, #991b1b); }
.bg-gradient-info { background: linear-gradient(45deg, #06b6d4, #0e7490); }

.chart-section .card {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    backdrop-filter: blur(10px);
}

.chart-title {
    font-size: 1.5rem;
    font-weight: 600;
    text-align: center;
    margin-bottom: 15px;
    color: #fff;
}

.chart-subtitle {
    font-size: 0.9rem;
    text-align: center;
    color: #e5e7eb;
    margin-bottom: 20px;
}

.chart-subtitle .highlight {
    color: #facc15;
    font-weight: 700;
}

.chart-canvas {
    height: 300px !important;
}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Mảng ngày và doanh thu từ PHP
    var days = <?php echo json_encode($days); ?>;
    var revenues = <?php echo json_encode($revenues); ?>;

    // Khởi tạo biểu đồ
    var ctx = document.getElementById('chart-bars').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar', // Chọn loại biểu đồ cột
        data: {
            labels: days, // Gán ngày cho các nhãn trục X
            datasets: [{
                label: 'Doanh thu theo ngày (VNĐ)', // Tiêu đề cho dữ liệu
                data: revenues, // Dữ liệu doanh thu
                tension: 0.4,
                borderWidth: 0,
                borderRadius: 3,
                borderSkipped: false,
                backgroundColor: "#fff",
                maxBarThickness: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                }
            },
            interaction: {
                intersect: false,
                mode: 'index',
            },
            scales: {
                y: {
                    grid: {
                        drawBorder: false,
                        display: false,
                        drawOnChartArea: false,
                        drawTicks: false,
                    },
                    ticks: {
                        suggestedMin: 0,
                        suggestedMax: 500,
                        beginAtZero: true,
                        padding: 15,
                        font: {
                            size: 14,
                            family: "Arial",
                            style: 'normal',
                            lineHeight: 2
                        },
                        color: "#fff"
                    },
                },
                x: {
                    grid: {
                        drawBorder: false,
                        display: false,
                        drawOnChartArea: false,
                        drawTicks: false
                    },
                    ticks: {
                        display: true,
                        color: "#fff",
                        font: {
                            size: 14,
                            family: "Arial",
                            style: 'normal',
                            lineHeight: 2
                        },
                    },
                },
            },
        },
    });

    //Top 5 nguoi co chi tieu cao nhat
    var users = <?php echo $labels_json; ?>;
    var revenues = <?php echo $data_json; ?>;

    var ctx1 = document.getElementById('chart-bars-user').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: users,
            datasets: [{
                data: revenues,
                tension: 0.4,
                borderWidth: 0,
                borderRadius: 3,
                borderSkipped: false,
                backgroundColor: "#fff",
                maxBarThickness: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false,
                }
            },

            interaction: {
                intersect: false,
                mode: 'nearest',
                axis: 'y'
            },
            scales: {
                x: {
                    grid: {
                        drawBorder: true,
                        display: true,
                        drawOnChartArea: true,
                        drawTicks: true,
                        color: "#fff"
                    },
                    ticks: {
                        display: true,
                        color: "#fff",
                        font: {
                            size: 13,
                            family: "Arial",
                            style: 'normal',
                            lineHeight: 3
                        },
                    },
                },
                y: {
                    grid: {
                        drawBorder: false,
                        display: false,
                        drawOnChartArea: false,
                        drawTicks: false,
                    },
                    ticks: {
                        suggestedMin: 0,
                        suggestedMax: 500,
                        beginAtZero: true,
                        padding: 10,
                        font: {
                            size: 13,
                            family: "Arial",
                            style: 'normal',
                            lineHeight: 2
                        },
                        color: "#fff",
                    },
                },
            },
        },
    });


    var films = <?php echo $labels_film_json; ?>;
    var revenues_film = <?php echo $data_film_json; ?>;

    var ctx3 = document.getElementById('chart-bars-film').getContext('2d');
    new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: films,
            datasets: [{
                data: revenues_film,
                tension: 0.4,
                borderWidth: 0,
                borderRadius: 3,
                borderSkipped: false,
                backgroundColor: "#fff",
                maxBarThickness: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false,
                }
            },
            interaction: {
                intersect: false,
                mode: 'nearest',
                axis: 'y'
            },
            scales: {
                x: {
                    grid: {
                        drawBorder: true,
                        display: true,
                        drawOnChartArea: true,
                        drawTicks: true,
                        color: "#fff",
                        borderDash: [5, 5]
                    },
                    ticks: {
                        display: true,
                        color: "#fff",
                        font: {
                            size: 13,
                            family: "Arial",
                            style: 'normal',
                            lineHeight: 3
                        },
                    },
                },
                y: {
                    grid: {
                        drawBorder: false,
                        display: false,
                        drawOnChartArea: false,
                        drawTicks: false,
                        borderDash: [5, 5]
                    },
                    ticks: {
                        suggestedMin: 0,
                        suggestedMax: 500,
                        beginAtZero: true,
                        padding: 15,
                        font: {
                            size: 12,
                            family: "Arial",
                            style: 'normal',
                            lineHeight: 2
                        },
                        color: "#fff",

                    },
                },
            },
        },
    });

    var ctx2 = document.getElementById("chart-line").getContext("2d");

    var gradientStroke1 = ctx2.createLinearGradient(0, 230, 0, 50);
    gradientStroke1.addColorStop(1, 'rgba(203,12,159,0.2)');
    gradientStroke1.addColorStop(0.2, 'rgba(72,72,176,0.0)');
    gradientStroke1.addColorStop(0, 'rgba(203,12,159,0)'); //purple colors

    // Mảng doanh thu theo tháng từ PHP
    var monthlyRevenue = <?php echo $monthly_revenue_json; ?>;

    // Khởi tạo biểu đồ
    new Chart(ctx2, {
        type: "line",
        data: {
            labels: ["Th1", "Th2", "Th3", "Th4", "Th5", "Th6", "Th7", "Th8", "Th9", "Th10", "Th11",
                "Th12"
            ], // Tháng bằng tiếng Việt
            datasets: [{
                label: "Doanh thu hàng tháng (VNĐ)",
                tension: 0.4,
                borderWidth: 0,
                pointRadius: 0,
                borderColor: "#cb0c9f", // Màu đường viền
                borderWidth: 3,
                backgroundColor: gradientStroke1,
                fill: true,
                data: monthlyRevenue, // Dữ liệu doanh thu từ PHP
                maxBarThickness: 6
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                }
            },
            interaction: {
                intersect: false,
                mode: 'index',
            },
            scales: {
                y: {
                    grid: {
                        drawBorder: false,
                        display: true,
                        drawOnChartArea: true,
                        drawTicks: false,
                        borderDash: [5, 5]
                    },
                    ticks: {
                        display: true,
                        padding: 10,
                        color: '#000',
                        font: {
                            size: 13,
                            family: "Arial",
                            style: 'normal',
                            lineHeight: 2
                        },
                    }
                },
                x: {
                    grid: {
                        drawBorder: false,
                        display: false,
                        drawOnChartArea: false,
                        drawTicks: false,
                        borderDash: [5, 5]
                    },
                    ticks: {
                        display: true,
                        color: '#000',
                        padding: 20,
                        font: {
                            size: 13,
                            family: "Arial",
                            style: 'normal',
                            lineHeight: 3
                        },
                    }
                },
            },
        },
    });
    let revenueLastYear = <?php echo $last_year_revenue_json; ?>;
    let revenueThisYear = <?php echo $current_year_revenue_json; ?>;

    let revenueChange = 0;

    if (revenueLastYear === 0 && revenueThisYear !== 0) {
        revenueChange = 100;
    } else if (revenueLastYear !== 0) {
        revenueChange = ((revenueThisYear - revenueLastYear) / revenueLastYear) * 100;
    }

    revenueChange = Math.max(-100, Math.min(100, revenueChange)); // Cho phép -100 đến 100%

    let revenueChangeElement = document.getElementById("revenue-change");
    let arrowIcon = document.getElementById("arrow-icon");

    if (revenueChange > 0) {
        revenueChangeElement.textContent = `${revenueChange.toFixed(1)}% nhiều hơn`;
        arrowIcon.classList.remove("text-danger");
        arrowIcon.classList.add("text-success");
        arrowIcon.classList.remove("fa-arrow-down");
        arrowIcon.classList.add("fa-arrow-up");
    } else if (revenueChange < 0) {
        revenueChangeElement.textContent = `${Math.abs(revenueChange).toFixed(1)}% ít hơn`;
        arrowIcon.classList.remove("text-success");
        arrowIcon.classList.add("text-danger");
        arrowIcon.classList.remove("fa-arrow-up");
        arrowIcon.classList.add("fa-arrow-down");
    } else {
        revenueChangeElement.textContent = "Không thay đổi";
        arrowIcon.classList.remove("text-success", "text-danger");
        arrowIcon.classList.add("fa-close");
    }
</script>