<?php
/**
 * TOTP – Time-based One-Time Passwords (RFC 6238), the 6-digit codes of
 * Google Authenticator / Microsoft Authenticator / Authy.
 *
 * 1. The server creates a random SECRET and shows it to the user once (QR code).
 * 2. The authenticator app and the server both calculate:
 *        code = HMAC-SHA1(secret, current 30-second time step)  -> 6 digits
 * 3. At login the user types the code; the server calculates it too and compares.
 * Nothing is sent over the network – both sides only share the secret and the clock.
 */
class Totp
{
    const PERIOD = 30;     // a new code every 30 seconds
    const DIGITS = 6;
    const BASE32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /** Random secret in Base32 (the format authenticator apps expect). */
    public static function generate_secret($length = 32)
    {
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32[random_int(0, 31)];
        }
        return $secret;
    }

    /** The code for a given moment (default: now). */
    public static function code($secret, $time = null)
    {
        $step = intdiv($time ?? time(), self::PERIOD);
        $hash = hash_hmac('sha1', pack('J', $step), self::base32_decode($secret), true);

        // "Dynamic truncation" from the RFC: take 4 bytes at an offset given by the last nibble
        $offset = ord($hash[19]) & 0x0F;
        $number = ((ord($hash[$offset]) & 0x7F) << 24)
                | (ord($hash[$offset + 1]) << 16)
                | (ord($hash[$offset + 2]) << 8)
                | ord($hash[$offset + 3]);

        return str_pad((string) ($number % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /** Accept the current code and the one before/after (clocks are never perfectly in sync). */
    public static function verify($secret, $code, $time = null)
    {
        $time = $time ?? time();
        $code = preg_replace('/\s+/', '', (string) $code);
        foreach ([-1, 0, 1] as $window) {
            if (hash_equals(self::code($secret, $time + $window * self::PERIOD), $code)) {
                return true;
            }
        }
        return false;
    }

    /** Link that authenticator apps understand (shown as a QR code in the frontend). */
    public static function provisioning_uri($secret, $email, $issuer = 'CampusEvents')
    {
        return 'otpauth://totp/' . rawurlencode("$issuer:$email")
            . '?secret=' . $secret . '&issuer=' . rawurlencode($issuer)
            . '&digits=' . self::DIGITS . '&period=' . self::PERIOD;
    }

    private static function base32_decode($secret)
    {
        $bits = '';
        foreach (str_split(strtoupper($secret)) as $char) {
            $bits .= str_pad(decbin(strpos(self::BASE32, $char)), 5, '0', STR_PAD_LEFT);
        }
        $bytes = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $bytes .= chr(bindec($byte));
            }
        }
        return $bytes;
    }
}
