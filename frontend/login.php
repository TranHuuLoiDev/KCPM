<?php
require_once '../backend/config.php';

use App\Controllers\AuthController;

$authController = new AuthController();
$authController->handleRegister();
$authController->handleLogin();

if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    if (($_SESSION['user']['role'] ?? '') === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$showRegister = ($_GET['mode'] ?? '') === 'register';

require_once 'header.php';
?>

<div class="auth-page-wrapper">
    <div class="auth-container <?= $showRegister ? 'active' : '' ?>" id="container">
        <div class="form-container sign-up">
            <form action="login.php?mode=register" method="POST">
                <input type="hidden" name="auth_action" value="register">
                <h1>Tạo tài khoản</h1>

                <?php if ($showRegister && isset($_SESSION['error_msg'])): ?>
                    <div class="auth-alert auth-alert-error">
                        <?= htmlspecialchars($_SESSION['error_msg']) ?>
                        <?php unset($_SESSION['error_msg']); ?>
                    </div>
                <?php endif; ?>

                <div class="input-row">
                    <input type="text" name="first_name" placeholder="Họ" required />
                    <input type="text" name="last_name" placeholder="Tên" required />
                </div>

                <input type="email" name="email" placeholder="Email" required />
                <input type="text" name="phone" placeholder="Số điện thoại" required />

                <div class="auth-date-field">
                    <label for="birth_date">Ngày sinh</label>
                    <input type="date" id="birth_date" name="birth_date" />
                </div>

                <div class="input-row">
                    <input type="password" name="password" placeholder="Mật khẩu" required />
                    <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required />
                </div>

                <button type="submit" class="auth-action">Đăng ký</button>

                <p class="auth-mobile-switch">
                    Đã có tài khoản?
                    <button type="button" class="auth-inline-switch" data-auth-switch="login">Đăng nhập</button>
                </p>
            </form>
        </div>

        <div class="form-container sign-in">
            <form action="login.php" method="POST">
                <input type="hidden" name="auth_action" value="login">
                <h1>Đăng nhập</h1>

                <?php if (!$showRegister && isset($_SESSION['error_msg'])): ?>
                    <div class="auth-alert auth-alert-error">
                        <?= htmlspecialchars($_SESSION['error_msg']) ?>
                        <?php unset($_SESSION['error_msg']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success_msg'])): ?>
                    <div class="auth-alert auth-alert-success">
                        <?= htmlspecialchars($_SESSION['success_msg']) ?>
                        <?php unset($_SESSION['success_msg']); ?>
                    </div>
                <?php endif; ?>

                <input type="email" name="email" required placeholder="Email" />
                <input type="password" name="password" required placeholder="Mật khẩu" />

                <div class="auth-forgot-link">
                    <button type="button" class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                        Quên mật khẩu?
                    </button>
                </div>

                <button type="submit" class="auth-action">Đăng nhập</button>

                <p class="auth-mobile-switch">
                    Chưa có tài khoản?
                    <button type="button" class="auth-inline-switch" data-auth-switch="register">Đăng ký ngay</button>
                </p>
            </form>
        </div>

        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Xin chào!</h1>
                    <p>Đăng ký với thông tin cá nhân của bạn để sử dụng tất cả tính năng của trang web</p>
                    <button type="button" class="hidden" id="register" data-auth-switch="register">Đăng ký</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Chào mừng trở lại!</h1>
                    <p>Nhập thông tin cá nhân của bạn để sử dụng tất cả tính năng của trang web</p>
                    <button type="button" class="hidden" id="login" data-auth-switch="login">Đăng nhập</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const container = document.getElementById('container');
    const switchButtons = document.querySelectorAll('[data-auth-switch]');

    switchButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const mode = button.dataset.authSwitch;
            const nextUrl = mode === 'register' ? 'login.php?mode=register' : 'login.php';

            container.classList.toggle('active', mode === 'register');
            window.history.replaceState(null, '', nextUrl);
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const forgotModalEle = document.getElementById('forgotPasswordModal');
        const forgotModal = forgotModalEle ? new bootstrap.Modal(forgotModalEle) : null;
        const forgotForm = document.getElementById('forgotPasswordForm');
        const forgotStep = document.getElementById('forgotStep');
        const forgotMessage = document.getElementById('forgotMessage');
        const forgotEmail = document.getElementById('forgotEmail');
        const forgotCode = document.getElementById('forgotCode');
        const forgotNewPassword = document.getElementById('forgotNewPassword');
        const forgotConfirmPassword = document.getElementById('forgotConfirmPassword');
        const forgotSubmit = document.getElementById('forgotSubmit');

        if (forgotForm) {
            forgotForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                const formData = new FormData(forgotForm);
                formData.append('ajax', '1');

                const response = await fetch('forgot_password.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (error) {
                    console.error('Invalid JSON response:', text);
                    forgotMessage.textContent = 'Lỗi máy chủ: phản hồi không hợp lệ.';
                    forgotMessage.className = 'auth-alert auth-alert-error';
                    forgotMessage.style.display = 'block';
                    return;
                }

                forgotMessage.textContent = result.message || 'Có lỗi xảy ra';
                forgotMessage.className = result.status === 'success' ? 'auth-alert auth-alert-success' : 'auth-alert auth-alert-error';
                forgotMessage.style.display = 'block';

                if (formData.get('action') === 'request_reset' && result.status === 'success') {
                    forgotStep.value = 'reset_password';
                    forgotCode.closest('.form-control-group').style.display = 'block';
                    forgotNewPassword.closest('.form-control-group').style.display = 'block';
                    forgotConfirmPassword.closest('.form-control-group').style.display = 'block';
                    forgotEmail.readOnly = true;
                    forgotSubmit.textContent = 'Xác nhận mã và đổi mật khẩu';
                }

                if (formData.get('action') === 'reset_password' && result.status === 'success') {
                    forgotForm.querySelectorAll('input').forEach((input) => input.disabled = true);
                    forgotSubmit.disabled = true;
                    forgotSubmit.textContent = 'Hoàn tất';
                }
            });
        }
    });
</script>

<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="forgotPasswordModalLabel">Quên mật khẩu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div id="forgotMessage" class="auth-alert" style="display:none;"></div>
                <form id="forgotPasswordForm" method="POST">
                    <input type="hidden" name="action" value="request_reset" id="forgotStep">
                    <div class="form-control-group mb-3">
                        <label for="forgotEmail" class="form-label">Email</label>
                        <input id="forgotEmail" type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="form-control-group mb-3" style="display:none;">
                        <label for="forgotCode" class="form-label">Mã xác thực</label>
                        <input id="forgotCode" type="text" name="code" class="form-control" placeholder="Mã xác thực">
                    </div>
                    <div class="form-control-group mb-3" style="display:none;">
                        <label for="forgotNewPassword" class="form-label">Mật khẩu mới</label>
                        <input id="forgotNewPassword" type="password" name="new_password" class="form-control" placeholder="Mật khẩu mới">
                    </div>
                    <div class="form-control-group mb-3" style="display:none;">
                        <label for="forgotConfirmPassword" class="form-label">Xác nhận mật khẩu mới</label>
                        <input id="forgotConfirmPassword" type="password" name="confirm_password" class="form-control" placeholder="Xác nhận mật khẩu mới">
                    </div>
                    <button id="forgotSubmit" type="submit" class="auth-action">Gửi mã xác thực</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>


