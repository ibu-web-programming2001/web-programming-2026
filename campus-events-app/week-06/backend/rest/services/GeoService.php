<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/exceptions.php';
require_once __DIR__ . '/../../utils/HttpClient.php';
require_once __DIR__ . '/CountryService.php';

/**
 * IP address -> location (city, country, coordinates, timezone) with ipwho.is (free, no API key).
 * Then the country code is used with CountryService to add the country's languages, currency...
 * -> a service may use another service (like EventService uses OpenMeteoService).
 */
class GeoService
{
    const UNAVAILABLE = 'The location service is not available at the moment. Please try again later.';

    private $country_service;

    public function __construct($country_service = null)
    {
        $this->country_service = $country_service ?? new CountryService();
    }

    /**
     * $ip        – IP from ?ip=... (optional)
     * $client_ip – IP of the visitor who sent the request (used when ?ip is empty)
     */
    public function lookup($ip = '', $client_ip = '')
    {
        $ip = trim((string) $ip);
        if ($ip !== '') {
            $this->validate_public_ip($ip);
        } elseif ($this->is_public_ip($client_ip)) {
            $ip = $client_ip;
        }
        // $ip still empty (e.g. you are on localhost = 127.0.0.1): ipwho.is then locates OUR server's public IP

        $data = HttpClient::get_json(
            rtrim(Config::IP_GEO_URL(), '/') . '/' . rawurlencode($ip),
            Config::EXTERNAL_API_TIMEOUT(),
            self::UNAVAILABLE
        );
        // ipwho.is answers HTTP 200 even on errors -> the real result is in "success"
        if (empty($data['success'])) {
            error_log('ipwho.is error: ' . ($data['message'] ?? 'unknown') . " (ip: $ip)");
            throw new ExternalServiceException(self::UNAVAILABLE);
        }

        return [
            'ip' => $data['ip'],
            'type' => $data['type'] ?? null,
            'city' => $data['city'] ?? null,
            'region' => $data['region'] ?? null,
            'country' => $data['country'] ?? null,
            'country_code' => $data['country_code'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'timezone' => $data['timezone']['id'] ?? null,
            'isp' => $data['connection']['isp'] ?? null,
            'country_info' => $this->country_info($data['country_code'] ?? null)
        ];
    }

    /** Languages, currency... of the country. Optional extra – if it fails, the location is still returned. */
    private function country_info($code)
    {
        if (!$code) {
            return null;
        }
        try {
            return $this->country_service->get_by_code($code);
        } catch (HttpException $e) {
            return null;
        }
    }

    private function validate_public_ip($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new ValidationException("'$ip' is not a valid IP address.");
        }
        if (!$this->is_public_ip($ip)) {
            throw new ValidationException("'$ip' is a private or local IP address – it has no location.");
        }
    }

    /** false for 127.0.0.1, 192.168.x.x, 10.x.x.x ... (they exist only inside a local network). */
    private function is_public_ip($ip)
    {
        return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
