<?php
require_once 'config.php';

use App\Controllers\ProfileController;

if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$profileController = new ProfileController();
$actionResult = $profileController->handleRequest();

$userId = (int)($_SESSION['user']['id'] ?? 0);
$overview = $profileController->getProfileOverview($userId);
$user = $overview['user'] ?? null;

if (!$user) {
    unset($_SESSION['user']);
    header('Location: login.php');
    exit;
}

$fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$memberSince = !empty($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : 'Chưa cập nhật';
$birthDate = $user['birth_date'] ?? '';
$totalTickets = (int)($overview['total_tickets'] ?? 0);
$totalSpent = (int)($overview['total_spent'] ?? 0);

require_once 'header.php';
?>

<link rel="stylesheet" href="css/profile.css">

<div class="profile-page">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3">
                <aside class="profile-card p-4 text-center">
                    <div class="profile-avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h4 class="mb-1"><?= htmlspecialchars($fullName ?: 'Người dùng') ?></h4>
                    <p class="profile-muted small mb-4"><?= htmlspecialchars($user['email']) ?></p>

                    <div class="border-top border-secondary pt-4 mt-4">
                        <p class="profile-muted small mb-2">Thành viên từ</p>
                        <p class="text-danger fw-bold mb-0"><?= htmlspecialchars($memberSince) ?></p>
                    </div>
                </aside>

                <nav class="profile-card profile-menu p-3 mt-4 d-grid gap-2">
                    <a href="profile.php" class="btn active">
                        <i class="bi bi-person-circle"></i>
                        <span>Thông tin cá nhân</span>
                    </a>
                    <a href="booking_history.php" class="btn">
                        <i class="bi bi-ticket-perforated-fill"></i>
                        <span>Lịch sử đặt vé</span>
                    </a>
                    <a href="logout.php" class="btn btn-profile-logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Đăng xuất</span>
                    </a>
                </nav>
            </div>

            <div class="col-lg-9">
                <?php if ($actionResult): ?>
                    <?php $alertClass = $actionResult['status'] === 'success' ? 'alert-success' : 'alert-danger'; ?>
                    <div class="alert <?= $alertClass ?> d-flex align-items-center gap-2" role="alert">
                        <i class="bi <?= $actionResult['status'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' ?>"></i>
                        <span><?= htmlspecialchars($actionResult['message']) ?></span>
                    </div>
                <?php endif; ?>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="profile-card profile-stat profile-stat-ticket p-4">
                            <i class="bi bi-ticket-perforated-fill"></i>
                            <h3><?= $totalTickets ?></h3>
                            <p class="mb-0">Vé đã đặt</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-card profile-stat profile-stat-spent p-4">
                            <i class="bi bi-cash-coin"></i>
                            <h3><?= number_format($totalSpent, 0, ',', '.') ?>đ</h3>
                            <p class="mb-0">Tổng chi tiêu</p>
                        </div>
                    </div>
                </div>

                <section class="profile-card p-4 p-md-5">
                    <h3 class="profile-section-title">
                        <i class="bi bi-person-lines-fill text-danger"></i>
                        <span>Thông Tin Cá Nhân</span>
                    </h3>

                    <form method="POST" action="profile.php" class="profile-form">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="first_name">Họ</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="last_name">Tên</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="phone">Số điện thoại</label>
                                <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="birth_date">Ngày sinh</label>
                                <input type="date" id="birth_date" name="birth_date" class="form-control" value="<?= htmlspecialchars($birthDate ?? '') ?>">
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-profile-primary">
                                <i class="bi bi-save-fill me-2"></i>
                                Lưu thông tin
                            </button>
                        </div>
                    </form>
                </section>

                <section class="profile-card p-4 p-md-5 mt-4">
                    <h3 class="profile-section-title">
                        <i class="bi bi-key-fill"></i>
                        <span>Đổi Mật Khẩu</span>
                    </h3>

                    <form method="POST" action="profile.php" class="profile-form">
                        <input type="hidden" name="action" value="update_password">

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label" for="current_password">Mật khẩu hiện tại</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="new_password">Mật khẩu mới</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="confirm_password">Xác nhận mật khẩu mới</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-profile-warning">
                                <i class="bi bi-lock-fill me-2"></i>
                                Đổi mật khẩu
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
