<?php
require '../config/function.php';
include('includes/header.php');
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    redirect('sign-in.php', 'error', 'Vui lòng đăng nhập');
}
if (isset($_SESSION['EmployedIn']) && $_SESSION['EmployedIn'] === true) {
    redirect('index.php', 'error', 'Bạn không phải admin!', 'admin');
}
// Lấy chuỗi tìm kiếm từ GET request và loại bỏ khoảng trắng thừa
$searchString = isset($_GET['searchString']) ? trim($_GET['searchString']) : '';
$records_per_page = isset($_POST['records_per_page']) ? (int)$_POST['records_per_page'] : 5;
$pagination = setupPagination($conn, 'ghe', $records_per_page, $searchString);
$data = $pagination['data'];
$records_per_page = $pagination['records_per_page'];
?>
    <style>
        body{
            background: linear-gradient(135deg, #1e3a8a, #6b21a8);
        }
        </style>
<div id="toast"></div>

<?php alertMessage() ?>

<!-- Hiển thị nội dung danh sách ghế -->
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center pb-0">
                <h5><?php echo $title ?></h5>
                <form method="POST" class="d-inline">
                    <label for="records_per_page" class="me-2 fs-6">Chọn hiển thị số bản ghi:</label>
                    <select name="records_per_page" id="records_per_page" class="form-select"
                        onchange="this.form.submit()">
                        <option value="2" <?= $records_per_page == 2 ? 'selected' : '' ?>>2</option>
                        <option value="5" <?= $records_per_page == 5 ? 'selected' : '' ?>>5</option>
                        <option value="10" <?= $records_per_page == 10 ? 'selected' : '' ?>>10</option>
                        <option value="20" <?= $records_per_page == 20 ? 'selected' : '' ?>>20</option>
                    </select>
                </form>
                <div class="col-3">
                    <form class="mb-3 mb-lg-0 me-3 input-group w-100 flex-nowrap" role="search" method="GET" action="#">
                        <button type="submit" class="bg-transparent p-0 border-0">
                            <span class="input-group-text bg-dark text-white border" style="cursor: pointer;">
                                <i class="bi bi-search"></i> <!-- Biểu tượng tìm kiếm -->
                            </span>
                        </button>
                        <input type="search" name="searchString" class="form-control ps-2" placeholder="Search..."
                            aria-label="Search" value="<?= htmlspecialchars($searchString) ?>">
                        <!-- Ô nhập từ khóa tìm kiếm -->
                        <input type="hidden" name="page" value="<?= $current_page ?>"> <!-- Ẩn trang hiện tại -->
                    </form>
                </div>
                <a href="views/chair/chair-add.php" class="btn btn-lg me-5 btn-add"
                    style="--bs-btn-padding-y: .5rem; --bs-btn-padding-x: 20px; --bs-btn-font-size: 1.25rem;">
                    <i class="bi bi-plus me-1 fs-3" style="margin-bottom: 5px;"></i>Thêm</a>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table table-striped table-borderless align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-center text-uppercase text-xs font-weight-bolder ">STT</th>
                                <th class="text-center text-uppercase text-xs font-weight-bolder ">Tên ghế</th>
                                <th class="text-center text-uppercase text-xs font-weight-bolder ">Tên phòng</th>
                                <th class="text-center text-uppercase text-xs font-weight-bolder ">Loại ghế</th>
                                <th class="text-center text-uppercase text-xs font-weight-bolder ">Giá ghế</th>
                                <th class="text-center text-uppercase text-xs font-weight-bolder ">Trạng thái</th>
                                <th class="text-center text-uppercase text-xs font-weight-bolder ">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stt = 0;
                            if (!empty($data)) {
                                foreach ($data as $item) {
                                    $stt++;
                            ?>
                                    <tr>
                                        <th class="text-center text-xs font-weight-bolder"><?= $stt ?></th>
                                        <th class="text-center text-xs font-weight-bolder"><?= $item['TenGhe']; ?></th>
                                        <th class="text-center text-xs font-weight-bolder">
                                            <?php
                                            $phong = getByID('Phong', 'MaPhong', $item['MaPhong']);
                                            $tenphong = $phong['data']['TenPhong'];
                                            echo $tenphong;
                                            ?>
                                        </th>
                                        <th class="text-center text-xs font-weight-bolder"><?= $item['LoaiGhe']; ?></th>
                                        <th class="text-center text-xs font-weight-bolder">
                                            <?= number_format($item['GiaGhe']); ?> VNĐ</th>
                                        <th class="text-center text-s font-weight-bolder">
                                            <form action="controllers/chair-controller.php" method="POST"
                                                style="display:inline;">
                                                <input type="hidden" name="maghe" value="<?= $item['MaGhe'] ?>">
                                                <input type="hidden" name="status"
                                                    value="<?= $item['TrangThai'] == 1 ? 0 : 1 ?>">
                                                <button type="submit" name="changeStatus"
                                                    class="badge badge-sm <?= $item['TrangThai'] == 1 ? 'bg-gradient-success' : 'bg-gradient-secondary' ?> text-uppercase"
                                                    style="border: none; cursor: pointer;">
                                                    <?= $item['TrangThai'] == 1 ? 'ON' : 'OFF' ?>
                                                </button>
                                            </form>
                                        </th>
                                        <td class="align-middle text-center text-sm">
                                            <a class="btn btn-info m-0"
                                                style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                                href="views/chair/chair-edit.php?id=<?= $item['MaGhe'] ?>">
                                                <i class="bi bi-pencil"></i> Sửa
                                            </a>
                                            <a class="btn btn-danger m-0 delete-btn" data-id="<?= $item['MaGhe'] ?>"
                                                data-url="views/chair/chair-delete.php"
                                                style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                                data-bs-toggle="modal" data-bs-target="#confirmModal">
                                                <i class="bi bi-trash"></i> Xoá
                                            </a>
                                            <div class="modal fade" id="confirmModal" tabindex="-1"
                                                aria-labelledby="confirmModalLabel" aria-hidden="true">
                                                <div class="modal-dialog mt-10">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="confirmModalLabel">Xác Nhận Xóa</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="p-2 fs-5">Bạn có muốn xóa không?</p>
                                                        </div>
                                                        <div class="modal-footer d-flex justify-content-center">
                                                            <button type="button" class="btn btn-sm btn-success"
                                                                id="confirmYes">Có</button>
                                                            <button type="button" class="btn btn-sm btn-danger me-2"
                                                                data-bs-dismiss="modal">Không</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="10" class="text-center">Không có bản ghi nào</td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Phân trang -->
            <div class="card-footer">
                <?php echo paginate_html($pagination['total_pages'], $pagination['current_page']); ?>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
