<?php

namespace Tests\Services;

use App\Models\UserModel;
use App\Services\AuthService;
use PHPUnit\Framework\TestCase;

class AuthServicesTest extends TestCase
{
    private AuthService $service;
    private UserModel $userModel;

    private string $testEmail;
    private string $testPhone;
    private string $testPassword = 'Test@123456';

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AuthService();
        $this->userModel = new UserModel();

        $this->testEmail = 'auth_test_' . uniqid() . '@example.com';
        $this->testPhone = '09' . str_pad((string) random_int(10000000, 99999999), 8, '0');

        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $user = $this->userModel->findByEmail($this->testEmail);

        if ($user) {
            $this->userModel->delete((int) $user['id']);
        }

        $_SESSION = [];

        parent::tearDown();
    }

    private function validRegisterData(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Auth',
            'last_name' => 'Test',
            'email' => $this->testEmail,
            'phone' => $this->testPhone,
            'birth_date' => '2000-01-01',
            'password' => $this->testPassword,
            'confirm_password' => $this->testPassword,
        ], $overrides);
    }

    private function createTestUser(): int
    {
        $result = $this->service->register($this->validRegisterData());

        $this->assertSame('success', $result['status']);

        $user = $this->userModel->findByEmail($this->testEmail);

        $this->assertNotNull($user);

        return (int) $user['id'];
    }

    // ---- Login ----

    public function testLoginSucceedsWithValidCredentials(): void
    {
        $this->createTestUser();

        $result = $this->service->login(
            $this->testEmail,
            $this->testPassword
        );

        $this->assertSame('success', $result['status']);
        $this->assertSame('Đăng nhập thành công!', $result['message']);
        $this->assertSame('user', $result['role']);

        $this->assertArrayHasKey('user', $_SESSION);
        $this->assertSame($this->testEmail, $_SESSION['user']['email']);
    }

    public function testLoginFailsWhenEmailIsEmpty(): void
    {
        $result = $this->service->login('', $this->testPassword);

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Vui lòng nhập email và mật khẩu!',
            $result['message']
        );
    }

    public function testLoginFailsWhenPasswordIsEmpty(): void
    {
        $result = $this->service->login($this->testEmail, '');

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Vui lòng nhập email và mật khẩu!',
            $result['message']
        );
    }

    public function testLoginFailsWithInvalidEmail(): void
    {
        $result = $this->service->login(
            'nonexistent_' . uniqid() . '@example.com',
            $this->testPassword
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Email hoặc mật khẩu không đúng!',
            $result['message']
        );
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $this->createTestUser();

        $result = $this->service->login(
            $this->testEmail,
            'WrongPassword123'
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Email hoặc mật khẩu không đúng!',
            $result['message']
        );
    }

    // ---- Register ----

    public function testRegisterFailsWhenRequiredDataMissing(): void
    {
        $result = $this->service->register(
            $this->validRegisterData([
                'first_name' => '',
            ])
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Vui lòng nhập đầy đủ thông tin bắt buộc!',
            $result['message']
        );
    }

    public function testRegisterFailsWithInvalidEmail(): void
    {
        $result = $this->service->register(
            $this->validRegisterData([
                'email' => 'invalid-email',
            ])
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Email không hợp lệ!',
            $result['message']
        );
    }

    public function testRegisterFailsWhenPasswordsDoNotMatch(): void
    {
        $result = $this->service->register(
            $this->validRegisterData([
                'confirm_password' => 'DifferentPassword123',
            ])
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Mật khẩu xác nhận không khớp!',
            $result['message']
        );
    }

    public function testRegisterFailsWhenPasswordTooShort(): void
    {
        $result = $this->service->register(
            $this->validRegisterData([
                'password' => '12345',
                'confirm_password' => '12345',
            ])
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Mật khẩu phải có ít nhất 6 ký tự!',
            $result['message']
        );
    }

    public function testRegisterFailsWhenEmailAlreadyExists(): void
    {
        $this->createTestUser();

        $result = $this->service->register(
            $this->validRegisterData([
                'phone' => '0988888888',
            ])
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Email này đã được sử dụng!',
            $result['message']
        );
    }

    public function testRegisterFailsWhenPhoneAlreadyExists(): void
    {
        $this->createTestUser();

        $duplicateEmail = 'auth_duplicate_' . uniqid() . '@example.com';

        $result = $this->service->register(
            $this->validRegisterData([
                'email' => $duplicateEmail,
            ])
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Số điện thoại này đã được sử dụng!',
            $result['message']
        );

        $duplicateUser = $this->userModel->findByEmail($duplicateEmail);

        if ($duplicateUser) {
            $this->userModel->delete((int) $duplicateUser['id']);
        }
    }

    public function testRegisterSucceedsWithValidData(): void
    {
        $result = $this->service->register(
            $this->validRegisterData()
        );

        $this->assertSame('success', $result['status']);
        $this->assertSame(
            'Đăng ký tài khoản thành công!',
            $result['message']
        );

        $user = $this->userModel->findByEmail($this->testEmail);

        $this->assertNotNull($user);
        $this->assertSame($this->testEmail, $user['email']);
        $this->assertSame($this->testPhone, $user['phone']);
        $this->assertNotSame(
            $this->testPassword,
            $user['password']
        );

        $this->assertTrue(
            password_verify($this->testPassword, $user['password'])
        );
    }

    // ---- Logout ----

    public function testLogoutSucceeds(): void
    {
        $_SESSION['user'] = [
            'id' => 1,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '0900000000',
            'role' => 'user',
        ];

        $result = $this->service->logout();

        $this->assertSame('success', $result['status']);
        $this->assertSame(
            'Đăng xuất thành công!',
            $result['message']
        );
        $this->assertArrayNotHasKey('user', $_SESSION);
    }
}