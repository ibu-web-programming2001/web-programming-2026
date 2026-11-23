<?php
use PHPUnit\Framework\TestCase;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../../rest/services/AuthService.php';

class AuthServiceTest extends TestCase
{
    private $user_dao;
    private $user_service;
    private $service;

    protected function setUp(): void
    {
        $this->user_dao = $this->createMock(UserDao::class);
        $this->user_service = $this->createMock(UserService::class);
        $this->service = new AuthService($this->user_service, $this->user_dao);
    }

    public function test_login_with_the_correct_password_returns_a_valid_token()
    {
        $this->user_dao->method('get_by_email')->willReturn([
            'id' => 2, 'email' => 'ema@campusevents.ba', 'first_name' => 'Ema', 'last_name' => 'Kovač',
            'role' => 'student', 'password' => password_hash('Student123!', PASSWORD_BCRYPT)
        ]);

        $result = $this->service->login(['email' => 'ema@campusevents.ba', 'password' => 'Student123!']);

        $this->assertArrayNotHasKey('password', $result['user']);   // the hash never leaves the service
        $payload = JWT::decode($result['token'], new Key(Config::JWT_SECRET(), 'HS256'));
        $this->assertSame(2, $payload->user->id);
        $this->assertSame('student', $payload->user->role);
        $this->assertGreaterThan(time(), $payload->exp);
    }

    public function test_login_with_a_wrong_password_gives_401()
    {
        $this->user_dao->method('get_by_email')->willReturn([
            'id' => 2, 'role' => 'student', 'password' => password_hash('Student123!', PASSWORD_BCRYPT)
        ]);

        $this->expectException(UnauthorizedException::class);
        $this->service->login(['email' => 'ema@campusevents.ba', 'password' => 'wrong']);
    }

    public function test_login_with_an_unknown_email_gives_the_same_401()
    {
        $this->user_dao->method('get_by_email')->willReturn(null);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Invalid email or password.');
        $this->service->login(['email' => 'nobody@campusevents.ba', 'password' => 'whatever1']);
    }

    public function test_login_with_2fa_asks_for_the_code_first()
    {
        $secret = Totp::generate_secret();
        $this->user_dao->method('get_by_email')->willReturn([
            'id' => 2, 'email' => 'ema@campusevents.ba', 'first_name' => 'Ema', 'last_name' => 'K', 'role' => 'student',
            'password' => password_hash('Student123!', PASSWORD_BCRYPT),
            'two_factor_enabled' => 1, 'two_factor_secret' => $secret
        ]);

        // password only -> no token yet
        $step1 = $this->service->login(['email' => 'ema@campusevents.ba', 'password' => 'Student123!']);
        $this->assertSame(['two_factor_required' => true], $step1);

        // password + correct code -> token, and the secret is never returned
        $step2 = $this->service->login(['email' => 'ema@campusevents.ba', 'password' => 'Student123!', 'code' => Totp::code($secret)]);
        $this->assertArrayHasKey('token', $step2);
        $this->assertArrayNotHasKey('two_factor_secret', $step2['user']);
    }

    public function test_login_with_a_wrong_2fa_code_gives_401()
    {
        $this->user_dao->method('get_by_email')->willReturn([
            'id' => 2, 'role' => 'student', 'password' => password_hash('Student123!', PASSWORD_BCRYPT),
            'two_factor_enabled' => 1, 'two_factor_secret' => Totp::generate_secret()
        ]);

        $this->expectException(UnauthorizedException::class);
        $this->service->login(['email' => 'ema@campusevents.ba', 'password' => 'Student123!', 'code' => '000000']);
    }

    public function test_register_always_creates_a_student()
    {
        $this->user_service->expects($this->once())
            ->method('add')
            ->with($this->callback(fn($user) => $user['role'] === Roles::STUDENT))
            ->willReturn(['id' => 9, 'email' => 'x@campusevents.ba', 'first_name' => 'X', 'last_name' => 'Y', 'role' => 'student']);

        $this->service->register([
            'first_name' => 'X', 'last_name' => 'Y', 'email' => 'x@campusevents.ba',
            'password' => 'Secret123', 'role' => 'admin'      // attacker tries to become admin
        ]);
    }

    public function test_register_checks_password_confirmation()
    {
        $this->expectException(ValidationException::class);
        $this->service->register([
            'first_name' => 'X', 'last_name' => 'Y', 'email' => 'x@campusevents.ba',
            'password' => 'Secret123', 'password_confirm' => 'Secret124'
        ]);
    }
}
