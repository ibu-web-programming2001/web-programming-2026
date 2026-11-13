<?php
/*
 * Configuration TEMPLATE.
 *
 * 1. Copy this file to config.php (same folder).
 * 2. Put your own values into config.php.
 *
 * config.php is listed in .gitignore and must NEVER be committed,
 * because it contains secrets (database password, JWT secret, API keys).
 * This example file IS committed so everybody knows which settings exist.
 */
class Config
{
    /* ---------- Database ---------- */
    public static function DB_HOST()     { return self::get_env('DB_HOST', '127.0.0.1'); }
    public static function DB_PORT()     { return self::get_env('DB_PORT', 3306); }
    public static function DB_NAME()     { return self::get_env('DB_NAME', 'campusevents'); }
    public static function DB_USER()     { return self::get_env('DB_USER', 'root'); }
    public static function DB_PASSWORD() { return self::get_env('DB_PASSWORD', ''); }

    /* ---------- External API: Open-Meteo (https://open-meteo.com/en/docs – free, no API key) ---------- */
    public static function OPEN_METEO_GEOCODING_URL() { return self::get_env('OPEN_METEO_GEOCODING_URL', 'https://geocoding-api.open-meteo.com'); }
    public static function OPEN_METEO_FORECAST_URL()  { return self::get_env('OPEN_METEO_FORECAST_URL', 'https://api.open-meteo.com'); }
    /** Seconds to wait before we give up – the app must keep working when the API is slow or down. */
    public static function OPEN_METEO_TIMEOUT()       { return self::get_env('OPEN_METEO_TIMEOUT', 5); }

    /* ---------- Authentication (JWT) ---------- */
    /** Long random string (at least 32 characters). Anybody who knows it can create valid tokens! */
    public static function JWT_SECRET()     { return self::get_env('JWT_SECRET', 'change-me-to-a-long-random-string-of-at-least-32-characters'); }
    /** How long a token is valid, in seconds (default: 24 hours). */
    public static function JWT_EXPIRATION() { return self::get_env('JWT_EXPIRATION', 60 * 60 * 24); }

    /* ---------- Single Sign-On (optional) ---------- */
    /** OAuth client id from Google Cloud Console → APIs & Services → Credentials. Empty = Google login disabled. */
    public static function GOOGLE_CLIENT_ID() { return self::get_env('GOOGLE_CLIENT_ID', ''); }

    /**
     * Environment variables (set on the server) win over the default values above.
     */
    public static function get_env($name, $default)
    {
        $value = getenv($name);
        return ($value !== false && trim($value) !== '') ? $value : $default;
    }
}
