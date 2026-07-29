<?php
require_once '../config.php';
require_once '../app/init.php';
require_once 'admin_header.php';
require_once 'admin_sidebar.php';

use App\Controllers\DashboardController;

$controller = new DashboardController();
$data = $controller->index();
?>

<div class="container-fluid">
    <div class="admin-page-header d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="mb-0 text-white fw-bold">Dashboard tổng quan</h1>
            <p class="mb-0 mt-2 text-muted">Theo dõi nhanh số lượng phim, người dùng, lượt đặt vé và doanh thu đã thanh toán.</p>
        </div>
    </div>

    <!-- Thống kê -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card d-flex align-items-center h-100 p-4" style="background: linear-gradient(135deg, #F44336, #D32F2F);">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25" style="width: 60px; height: 60px;">
                    <i class="bi bi-film fs-2 text-white"></i>
                </div>
                <div class="ms-4 text-white">
                    <p class="mb-1 text-white text-opacity-75 fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">Tổng phim</p>
                    <h3 class="mb-0 fw-bold"><?= number_format($data['total_movies']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card d-flex align-items-center h-100 p-4" style="background: linear-gradient(135deg, #4CAF50, #388E3C);">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25" style="width: 60px; height: 60px;">
                    <i class="bi bi-people fs-2 text-white"></i>
                </div>
                <div class="ms-4 text-white">
                    <p class="mb-1 text-white text-opacity-75 fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">Người dùng</p>
                    <h3 class="mb-0 fw-bold"><?= number_format($data['total_users']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card d-flex align-items-center h-100 p-4" style="background: linear-gradient(135deg, #2196F3, #1976D2);">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25" style="width: 60px; height: 60px;">
                    <i class="bi bi-ticket-perforated fs-2 text-white"></i>
                </div>
                <div class="ms-4 text-white">
                    <p class="mb-1 text-white text-opacity-75 fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">Vé đã bán</p>
                    <h3 class="mb-0 fw-bold"><?= number_format($data['total_bookings']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card d-flex align-items-center h-100 p-4" style="background: linear-gradient(135deg, #FF9800, #F57C00);">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25" style="width: 60px; height: 60px;">
                    <i class="bi bi-cash-coin fs-2 text-white"></i>
                </div>
                <div class="ms-4 text-white">
                    <p class="mb-1 text-white text-opacity-75 fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">Doanh thu</p>
                    <h3 class="mb-0 fw-bold"><?= number_format($data['total_revenue'] / 1000000, 1) ?>M</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Đặt vé hôm nay -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="admin-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-white"><i class="bi bi-calendar-day me-2"></i>Đặt vé hôm nay (<?= $data['today_bookings_count'] ?> vé)</h5>
                    <a href="manage_booking.php" class="btn btn-sm btn-admin-secondary">Quản lý vé</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Mã vé</th>
                                <th>Khách hàng</th>
                                <th>Phim</th>
                                <th>Phòng</th>
                                <th>Giờ chiếu</th>
                                <th>Ghế</th>
                                <th>Giá</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['today_bookings_list'])): ?>
                                <?php foreach ($data['today_bookings_list'] as $row): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['id']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                        <td><?= htmlspecialchars($row['movie_name'] ?? '---') ?></td>
                                        <td><?= htmlspecialchars($row['room_name'] ?? '---') ?></td>
                                        <td><?= !empty($row['start_time']) ? date('H:i', strtotime($row['start_time'])) : '---' ?></td>
                                        <td><?= htmlspecialchars($row['seats'] ?? '---') ?></td>
                                        <td><?= number_format($row['total_price'], 0, ',', '.') ?>đ</td>
                                        <td>
                                            <?php if ($row['status'] == 'paid'): ?>
                                                <span class="badge bg-success">Đã thanh toán</span>
                                            <?php elseif ($row['status'] == 'pending'): ?>
                                                <span class="badge bg-warning text-dark">Chờ thanh toán</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Đã hủy</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-ticket-perforated me-2"></i>Chưa có đặt vé nào hôm nay
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Phim phổ biến & Khách hàng thân thiết -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="admin-card h-100">
                <h5 class="mb-3 text-white"><i class="bi bi-fire me-2 text-danger"></i>Phim phổ biến nhất</h5>
                <div class="table-responsive">
                    <table class="table table-hover admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Phim</th>
                                <th>Số vé</th>
                                <th>Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['popular_movies'])): ?>
                                <?php foreach ($data['popular_movies'] as $row): ?>
                                    <tr>
                                        <td class="fw-bold text-white"><?= htmlspecialchars($row['movie_name']) ?></td>
                                        <td><?= $row['ticket_count'] ?></td>
                                        <td class="text-success fw-bold"><?= number_format($row['revenue'], 0, ',', '.') ?>đ</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">Chưa có dữ liệu</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="admin-card h-100">
                <h5 class="mb-3 text-white"><i class="bi bi-award me-2 text-warning"></i>Khách hàng thân thiết</h5>
                <div class="table-responsive">
                    <table class="table table-hover admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Khách hàng</th>
                                <th>Số vé</th>
                                <th>Chi tiêu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['loyal_customers'])): ?>
                                <?php foreach ($data['loyal_customers'] as $row): ?>
                                    <tr>
                                        <td class="fw-bold text-white"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                        <td><?= $row['ticket_count'] ?></td>
                                        <td class="text-warning fw-bold"><?= number_format($row['total_spent'], 0, ',', '.') ?>đ</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">Chưa có dữ liệu</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>
