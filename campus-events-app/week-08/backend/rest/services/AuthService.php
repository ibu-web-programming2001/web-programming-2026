<?php
require_once __DIR__ . '/UserService.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../../utils/Roles.php';
require_once __DIR__ . '/../../utils/Totp.php';
require_once __DIR__ . '/../../config/config.php';

use Firebase\JWT\JWT;

class AuthService
{
    private $user_service;
    private $user_dao;

    public function __construct($user_service = null, $user_dao = null)
    {
        $this->user_service = $user_service ?? new UserService();
        $this->user_dao = $user_dao ?? new UserDao();
    }

    /** Create a student account and log the user in immediately. */
    public function register($data)
    {
        Validator::required($data, ['first_name', 'last_name', 'email', 'password']);
        if (isset($data['password_confirm']) && $data['password_confirm'] !== $data['password']) {
            throw new ValidationException('Passwords do not match.');
        }

        // Rule: everybody who registers is a STUDENT – never trust a "role" sent by the client
        $data['role'] = Roles::STUDENT;
        $user = $this->user_service->add($data);     // validates (also the optional faculty) + hashes the password

        Logger::info("REGISTER user={$user['id']} email={$user['email']}");
        return ['user' => $user, 'token' => $this->create_token($user)];
    }

    /**
     * Step 1: email + password.
     * Step 2 (only when 2FA is enabled): the 6-digit code from the authenticator app, sent as "code".
     */
    public function login($data)
    {
        Validator::required($data, ['email', 'password']);
        $user = $this->user_dao->get_by_email(strtolower(trim($data['email'])));

        // Same message for "unknown email" and "wrong password" – do not tell attackers which emails exist
        if (!$user || !password_verify($data['password'], $user['password'])) {
            Logger::error("LOGIN FAILED email={$data['email']}");
            throw new UnauthorizedException('Invalid email or password.');
        }

        if (!empty($user['two_factor_enabled'])) {
            if (empty($data['code'])) {
                // Password was correct – ask the client for the second factor (no token yet!)
                return ['two_factor_required' => true];
            }
            if (!Totp::verify($user['two_factor_secret'], $data['code'])) {
                Logger::error("LOGIN FAILED (2FA code) user={$user['id']}");
                throw new UnauthorizedException('Invalid two-factor code.');
            }
        }

        $user = $this->public_user($user);
        Logger::info("LOGIN user={$user['id']} role={$user['role']}");
        return ['user' => $user, 'token' => $this->create_token($user)];
    }

    /** The logged-in user's own account. */
    public function get_profile($user_id)
    {
        return $this->user_service->get_by_id($user_id);
    }

    /** Users may change their name and password – NOT their email or role. */
    public function update_profile($user_id, $data)
    {
        $current = $this->user_service->get_by_id($user_id);
        if (!empty($data['password']) && ($data['password_confirm'] ?? null) !== $data['password']) {
            throw new ValidationException('Passwords do not match.');
        }
        $changes = array_merge($current, [
            'first_name' => $data['first_name'] ?? $current['first_name'],
            'last_name' => $data['last_name'] ?? $current['last_name'],
            'password' => $data['password'] ?? null
        ]);
        return $this->user_service->update($changes, $user_id);
    }

    /* ---------- Two-factor authentication (TOTP) ---------- */

    /** Create a new secret (not active yet) and return it once, so the user can scan it. */
    public function setup_two_factor($user_id)
    {
        $user = $this->user_service->get_by_id($user_id);
        $secret = Totp::generate_secret();
        $this->user_dao->update(['two_factor_secret' => $secret, 'two_factor_enabled' => 0], $user_id);

        return [
            'secret' => $secret,
            'otpauth_uri' => Totp::provisioning_uri($secret, $user['email'])
        ];
    }

    /** Activate 2FA only after the user proves the app works (one valid code). */
    public function enable_two_factor($user_id, $code)
    {
        $user = $this->user_dao->get_with_secrets($user_id);
        if (empty($user['two_factor_secret'])) {
            throw new ValidationException('Start the two-factor setup first.');
        }
        if (!Totp::verify($user['two_factor_secret'], $code)) {
            throw new ValidationException('The code is not valid. Check the time on your phone and try again.');
        }
        $this->user_dao->update(['two_factor_enabled' => 1], $user_id);
        Logger::info("2FA ENABLED user=$user_id");
        return ['two_factor_enabled' => true];
    }

    public function disable_two_factor($user_id, $code)
    {
        $user = $this->user_dao->get_with_secrets($user_id);
        if (empty($user['two_factor_enabled']) || !Totp::verify($user['two_factor_secret'], $code)) {
            throw new ValidationException('The code is not valid.');
        }
        $this->user_dao->update(['two_factor_enabled' => 0, 'two_factor_secret' => null], $user_id);
        Logger::info("2FA DISABLED user=$user_id");
        return ['two_factor_enabled' => false];
    }

    /* ---------- Single Sign-On with Google ---------- */

    /**
     * The frontend signs in with Google and receives an ID TOKEN (a JWT signed by Google).
     * We ask Google to verify it, check it was issued for OUR app, then log the user in with OUR token.
     */
    public function login_with_google($id_token)
    {
        if (Config::GOOGLE_CLIENT_ID() === '') {
            throw new ExternalServiceException('Google sign-in is not configured on this server.');
        }
        if (empty($id_token)) {
            throw new ValidationException("Field 'id_token' is required.");
        }

        $curl = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($id_token));
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_CONNECTTIMEOUT => 3]);
        $body = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($body === false) {
            Logger::error('SSO Google not reachable: ' . curl_error($curl));
            throw new ExternalServiceException('Google sign-in is not available at the moment.');
        }
        $claims = json_decode($body, true);
        if ($status !== 200 || !is_array($claims)) {
            Logger::error("SSO Google rejected the token (HTTP $status)");
            throw new UnauthorizedException('Invalid Google token.');
        }
        // The token must be meant for OUR application and the email must be verified by Google
        if (($claims['aud'] ?? '') !== Config::GOOGLE_CLIENT_ID() || ($claims['email_verified'] ?? '') !== 'true') {
            Logger::error('SSO Google token for another app or unverified email');
            throw new UnauthorizedException('Invalid Google token.');
        }

        $user = $this->user_dao->get_by_email(strtolower($claims['email']));
        if (!$user) {
            // First Google login -> create a student account (random password, they always use Google)
            $this->user_service->add([
                'first_name' => $claims['given_name'] ?? 'Google',
                'last_name' => $claims['family_name'] ?? 'User',
                'email' => strtolower($claims['email']),
                'password' => bin2hex(random_bytes(16)) . 'A1',
                'role' => Roles::STUDENT
            ]);
            $user = $this->user_dao->get_by_email(strtolower($claims['email']));
            Logger::info("REGISTER (Google SSO) user={$user['id']}");
        }

        $user = $this->public_user($user);
        Logger::info("LOGIN (Google SSO) user={$user['id']} role={$user['role']}");
        return ['user' => $user, 'token' => $this->create_token($user)];
    }

    /**
     * A JWT has 3 parts: header.payload.signature
     * The payload is only Base64 encoded (anybody can READ it) – never put secrets in it.
     * The signature (made with JWT_SECRET) proves that WE created it and nobody changed it.
     */
    private function create_token($user)
    {
        $payload = [
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role' => $user['role']
            ],
            'iat' => time(),                                   // issued at
            'exp' => time() + (int) Config::JWT_EXPIRATION()   // expires – without it a token is valid forever
        ];
        return JWT::encode($payload, Config::JWT_SECRET(), 'HS256');
    }

    /** Never send the password hash or the 2FA secret to the client. */
    private function public_user($user)
    {
        unset($user['password'], $user['two_factor_secret']);
        return $user;
    }
}
