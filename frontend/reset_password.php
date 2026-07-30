<?php
require_once '../backend/config.php';

use App\Controllers\PasswordResetController;

$resetController = new PasswordResetController();
$showForm = true;
$actionResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actionResult = $resetController->resetPassword();
    if ($actionResult && $actionResult['status'] === 'success') {
        $showForm = false;
    }
}

$user = $resetController->showResetForm();
if (!$user) {
    $showForm = false;
    if (!$actionResult) {
        $actionResult = ['status' => 'error', 'message' => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.'];
    }
}

require_once 'header.php';
?>

<div class="auth-page-wrapper">
    <div class="auth-container active" id="container">
        <div class="form-container sign-in">
            <form action="reset_password.php" method="POST">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                <h1>Đặt lại mật khẩu</h1>

                <?php if ($actionResult): ?>
                    <?php $alertClass = $actionResult['status'] === 'success' ? 'auth-alert-success' : 'auth-alert-error'; ?>
                    <div class="auth-alert <?= $alertClass ?>">
                        <?= htmlspecialchars($actionResult['message']) ?>
                    </div>
                <?php endif; ?>

                <?php if ($showForm): ?>
                    <input type="password" name="new_password" placeholder="Mật khẩu mới" required />
                    <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu mới" required />

                    <button type="submit" class="auth-action">Đổi mật khẩu</button>
                <?php else: ?>
                    <div class="auth-action" style="text-align:center">
                        <a href="login.php">Quay lại đăng nhập</a>
                    </div>
                <?php endif; ?>

                <p class="auth-mobile-switch">
                    <a href="login.php">Quay lại đăng nhập</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>


