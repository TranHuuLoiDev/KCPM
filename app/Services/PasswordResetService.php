<?php
namespace App\Services;

use App\Models\UserModel;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PasswordResetService {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function requestReset($email) {
        $email = trim($email);

        if ($email === '') {
            return ['status' => 'error', 'message' => 'Vui lòng nhập email!'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'message' => 'Email không hợp lệ!'];
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            return ['status' => 'error', 'message' => 'Không tìm thấy tài khoản với email này!'];
        }

        $resetCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->userModel->setResetToken($user['id'], $resetCode);

        $message = "Xin chào {$user['first_name']},\n\n" .
            "Bạn hoặc ai đó đã yêu cầu đặt lại mật khẩu cho tài khoản này.\n" .
            "Mã đặt lại mật khẩu của bạn là:\n\n" .
            "$resetCode\n\n" .
            "Nếu bạn không yêu cầu, hãy bỏ qua email này.\n" .
            "Cảm ơn!";

        $sendResult = $this->sendEmail($email, 'Mã đặt lại mật khẩu', $message);
        if (!$sendResult['success']) {
            return ['status' => 'error', 'message' => $sendResult['message'] ?: 'Không thể gửi email. Vui lòng thử lại sau.'];
        }

        return ['status' => 'success', 'message' => 'Mã đặt lại mật khẩu đã được gửi tới email của bạn. Vui lòng kiểm tra email.'];
    }

    public function resetPassword($email, $code, $newPassword, $confirmPassword) {
        $email = trim($email);
        $code = trim($code);

        if ($email === '' || $code === '') {
            return ['status' => 'error', 'message' => 'Vui lòng nhập email và mã xác thực!'];
        }

        if ($newPassword === '' || $confirmPassword === '') {
            return ['status' => 'error', 'message' => 'Vui lòng nhập mật khẩu mới và xác nhận!'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['status' => 'error', 'message' => 'Mật khẩu xác nhận không khớp!'];
        }

        if (strlen($newPassword) < 6) {
            return ['status' => 'error', 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự!'];
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            return ['status' => 'error', 'message' => 'Không tìm thấy tài khoản với email này!'];
        }

        if ($user['remember_token'] !== $code) {
            return ['status' => 'error', 'message' => 'Mã xác thực không đúng!'];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        if (!$this->userModel->updatePassword($user['id'], $hashedPassword)) {
            return ['status' => 'error', 'message' => 'Lỗi khi cập nhật mật khẩu: ' . $this->userModel->getError()];
        }

        $this->userModel->clearResetToken($user['id']);

        return ['status' => 'success', 'message' => 'Đổi mật khẩu thành công! Bây giờ bạn có thể đăng nhập với mật khẩu mới.'];
    }

    private function sendEmail($to, $subject, $message) {
        if (MAIL_USERNAME === 'your_mailtrap_username' || MAIL_PASSWORD === 'your_mailtrap_password') {
            return ['success' => false, 'message' => 'Chưa cấu hình Mailtrap SMTP trong config.php hoặc biến môi trường.'];
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = MAIL_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = MAIL_USERNAME;
            $mail->Password = MAIL_PASSWORD;
            $mail->SMTPSecure = MAIL_ENCRYPTION;
            $mail->Port = MAIL_PORT;

            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addAddress($to);
            $mail->addReplyTo(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);

            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = strip_tags($message);
            $mail->isHTML(false);
            $mail->CharSet = 'UTF-8';
            $mail->SMTPDebug = 0;

            $sent = $mail->send();
            return ['success' => $sent, 'message' => $sent ? '' : ''];
        } catch (Exception $e) {
            error_log('Mail send error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi gửi email: ' . $e->getMessage()];
        }
    }
}
