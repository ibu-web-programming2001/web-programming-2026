<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../utils/Totp.php';

class TotpTest extends TestCase
{
    /** Official test vector from RFC 6238 (secret "12345678901234567890", time 59 s). */
    public function test_matches_the_rfc_test_vector()
    {
        $this->assertSame('287082', Totp::code('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', 59));
    }

    public function test_accepts_the_previous_and_next_time_step()
    {
        $secret = Totp::generate_secret();
        $now = 1_800_000_000;
        $this->assertTrue(Totp::verify($secret, Totp::code($secret, $now - 30), $now));
        $this->assertTrue(Totp::verify($secret, Totp::code($secret, $now + 30), $now));
        $this->assertFalse(Totp::verify($secret, Totp::code($secret, $now - 120), $now));
    }

    public function test_secret_is_base32()
    {
        $this->assertMatchesRegularExpression('/^[A-Z2-7]{32}$/', Totp::generate_secret());
    }
}
