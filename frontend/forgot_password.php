<?php
require_once '../backend/config.php';

use App\Controllers\PasswordResetController;

ob_start();
$resetController = new PasswordResetController();
$actionResult = null;
$step = 'email';
$emailValue = '';

$isAjax = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAjax = !empty($_POST['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    if ($isAjax) {
        @ini_set('display_errors', '0');
        @error_reporting(0);
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'request_reset') {
        $emailValue = trim($_POST['email'] ?? '');
        $actionResult = $resetController->requestReset();
        if ($actionResult && $actionResult['status'] === 'success') {
            $step = 'verify';
        }
    } elseif ($action === 'reset_password') {
        $emailValue = trim($_POST['email'] ?? '');
        $actionResult = $resetController->resetPassword();
        if ($actionResult && $actionResult['status'] === 'success') {
            $step = 'done';
        } else {
            $step = 'verify';
        }
    }

    if ($isAjax) {
        if (ob_get_length() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $actionResult['status'] ?? 'error',
            'message' => $actionResult['message'] ?? 'Có lỗi xảy ra',
        ]);
        exit;
    }
}
if (ob_get_length() > 0) {
    ob_end_clean();
}

require_once 'header.php';
?>

<div class="auth-page-wrapper">
    <div class="auth-container active" id="container">
        <div class="form-container sign-in">
            <?php if ($step === 'email'): ?>
                <form action="forgot_password.php" method="POST">
                    <input type="hidden" name="action" value="request_reset">
                    <h1>Quên mật khẩu</h1>

                    <?php if ($actionResult): ?>
                        <?php $alertClass = $actionResult['status'] === 'success' ? 'auth-alert-success' : 'auth-alert-error'; ?>
                        <div class="auth-alert <?= $alertClass ?>">
                            <?= htmlspecialchars($actionResult['message']) ?>
                        </div>
                    <?php endif; ?>

                    <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($emailValue) ?>" />

                    <button type="submit" class="auth-action">Gửi mã xác thực</button>

                    <p class="auth-mobile-switch">
                        <a href="login.php">Quay lại đăng nhập</a>
                    </p>
                </form>
            <?php elseif ($step === 'verify'): ?>
                <form action="forgot_password.php" method="POST">
                    <input type="hidden" name="action" value="reset_password">
                    <h1>Xác thực mã đặt lại</h1>

                    <?php if ($actionResult): ?>
                        <?php $alertClass = $actionResult['status'] === 'success' ? 'auth-alert-success' : 'auth-alert-error'; ?>
                        <div class="auth-alert <?= $alertClass ?>">
                            <?= htmlspecialchars($actionResult['message']) ?>
                        </div>
                    <?php endif; ?>

                    <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($emailValue) ?>" />
                    <input type="text" name="code" placeholder="Mã xác thực" required />
                    <input type="password" name="new_password" placeholder="Mật khẩu mới" required />
                    <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu mới" required />

                    <button type="submit" class="auth-action">Đổi mật khẩu</button>

                    <p class="auth-mobile-switch">
                        <a href="login.php">Quay lại đăng nhập</a>
                    </p>
                </form>
            <?php else: ?>
                <div class="text-center">
                    <?php $alertClass = $actionResult['status'] === 'success' ? 'auth-alert-success' : 'auth-alert-error'; ?>
                    <div class="auth-alert <?= $alertClass ?>">
                        <?= htmlspecialchars($actionResult['message']) ?>
                    </div>
                    <a class="btn auth-action" href="login.php">Đăng nhập</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>


