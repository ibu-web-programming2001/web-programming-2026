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

    /* ---------- External data: countries + languages (static JSON file on GitHub – free, no API key) ---------- */
    public static function COUNTRIES_DATA_URL()   { return self::get_env('COUNTRIES_DATA_URL', 'https://raw.githubusercontent.com/mledoze/countries/master/countries.json'); }
    /** The file is ~1.4 MB and rarely changes, so we download it at most once per X hours and keep a local copy. */
    public static function COUNTRIES_CACHE_HOURS() { return self::get_env('COUNTRIES_CACHE_HOURS', 24); }

    /* ---------- External API: IP geolocation (https://ipwho.is – free, no API key) ---------- */
    public static function IP_GEO_URL()            { return self::get_env('IP_GEO_URL', 'https://ipwho.is'); }
    public static function EXTERNAL_API_TIMEOUT()  { return self::get_env('EXTERNAL_API_TIMEOUT', 10); }

    /**
     * Environment variables (set on the server) win over the default values above.
     */
    public static function get_env($name, $default)
    {
        $value = getenv($name);
        return ($value !== false && trim($value) !== '') ? $value : $default;
    }
}
