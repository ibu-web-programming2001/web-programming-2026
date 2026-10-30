<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/exceptions.php';
require_once __DIR__ . '/../../utils/Validator.php';

/**
 * Talks to the Open-Meteo API (third-party, no API key needed):
 *  - geocoding API: city name -> latitude/longitude
 *  - forecast API:  latitude/longitude -> daily forecast (max. 16 days ahead)
 *
 * Rules for every external call:
 *  - always set a timeout (the API can be slow or down)
 *  - check the HTTP status and the JSON we get back
 *  - on failure throw ExternalServiceException with a FRIENDLY message
 *    -> the rest of the application keeps working
 *  - return only the fields our frontend needs (not the raw response)
 */
class OpenMeteoService
{
    const UNAVAILABLE = 'The weather service is not available at the moment. Please try again later.';
    const MAX_FORECAST_DAYS = 16;

    /** WMO weather codes -> text + Bootstrap Icon (https://open-meteo.com/en/docs, "WMO Weather interpretation codes"). */
    private const WEATHER_CODES = [
        0 => ['Clear sky', 'sun'],
        1 => ['Mainly clear', 'cloud-sun'], 2 => ['Partly cloudy', 'cloud-sun'], 3 => ['Overcast', 'clouds'],
        45 => ['Fog', 'cloud-fog'], 48 => ['Fog', 'cloud-fog'],
        51 => ['Light drizzle', 'cloud-drizzle'], 53 => ['Drizzle', 'cloud-drizzle'], 55 => ['Heavy drizzle', 'cloud-drizzle'],
        56 => ['Freezing drizzle', 'cloud-drizzle'], 57 => ['Freezing drizzle', 'cloud-drizzle'],
        61 => ['Light rain', 'cloud-rain'], 63 => ['Rain', 'cloud-rain'], 65 => ['Heavy rain', 'cloud-rain-heavy'],
        66 => ['Freezing rain', 'cloud-sleet'], 67 => ['Freezing rain', 'cloud-sleet'],
        71 => ['Light snow', 'cloud-snow'], 73 => ['Snow', 'cloud-snow'], 75 => ['Heavy snow', 'cloud-snow'], 77 => ['Snow grains', 'cloud-snow'],
        80 => ['Rain showers', 'cloud-rain'], 81 => ['Rain showers', 'cloud-rain'], 82 => ['Violent rain showers', 'cloud-rain-heavy'],
        85 => ['Snow showers', 'cloud-snow'], 86 => ['Snow showers', 'cloud-snow'],
        95 => ['Thunderstorm', 'cloud-lightning-rain'], 96 => ['Thunderstorm with hail', 'cloud-lightning-rain'], 99 => ['Thunderstorm with hail', 'cloud-lightning-rain']
    ];

    /** Daily forecast for a city. */
    public function forecast($city, $days = 7)
    {
        Validator::integer($days, 'days', 1, self::MAX_FORECAST_DAYS);
        $place = $this->geocode($city);

        $url = Config::OPEN_METEO_FORECAST_URL() . '/v1/forecast?' . http_build_query([
            'latitude' => $place['latitude'],
            'longitude' => $place['longitude'],
            'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max',
            'timezone' => 'auto',
            'forecast_days' => (int) $days
        ]);
        $data = $this->request($url);
        if (!isset($data['daily']['time']) || !is_array($data['daily']['time'])) {
            error_log("Open-Meteo forecast without daily data ($url)");
            throw new ExternalServiceException(self::UNAVAILABLE);
        }

        // Open-Meteo returns parallel arrays (time[], temperature_2m_max[]...) – we turn them into one object per day
        $daily = $data['daily'];
        $result = [];
        foreach ($daily['time'] as $i => $date) {
            $code = $daily['weather_code'][$i] ?? null;
            [$description, $icon] = self::WEATHER_CODES[$code] ?? ['Unknown', 'question-circle'];
            $result[] = [
                'date' => $date,
                'weather_code' => $code,
                'description' => $description,
                'icon' => $icon,
                'temp_max' => isset($daily['temperature_2m_max'][$i]) ? round($daily['temperature_2m_max'][$i]) : null,
                'temp_min' => isset($daily['temperature_2m_min'][$i]) ? round($daily['temperature_2m_min'][$i]) : null,
                'precipitation_probability' => $daily['precipitation_probability_max'][$i] ?? null
            ];
        }

        return [
            'city' => $place['name'],
            'country' => $place['country'],
            'latitude' => $place['latitude'],
            'longitude' => $place['longitude'],
            'days' => $result
        ];
    }

    /** Forecast for ONE day ($date = YYYY-MM-DD), or null when that day has no forecast (past, or too far ahead). */
    public function forecast_for_day($city, $date)
    {
        $days_ahead = (int) floor((strtotime($date) - strtotime(date('Y-m-d'))) / 86400);
        if ($days_ahead < 0 || $days_ahead >= self::MAX_FORECAST_DAYS) {
            return null;
        }
        $forecast = $this->forecast($city, $days_ahead + 1);
        foreach ($forecast['days'] as $day) {
            if ($day['date'] === $date) {
                return $day;
            }
        }
        return null;
    }

    /** City name -> the first matching place (name, country, latitude, longitude). */
    public function geocode($city)
    {
        $city = trim((string) $city);
        if (mb_strlen($city) < 2) {
            throw new ValidationException('Please enter at least 2 characters of the city name.');
        }

        $url = Config::OPEN_METEO_GEOCODING_URL() . '/v1/search?' . http_build_query([
            'name' => $city,
            'count' => 1,
            'language' => 'en',
            'format' => 'json'
        ]);
        $data = $this->request($url);

        $place = $data['results'][0] ?? null;
        if (!$place) {
            throw new NotFoundException("No city called '$city' was found.");
        }
        return [
            'name' => $place['name'],
            'country' => $place['country'] ?? null,
            'latitude' => $place['latitude'],
            'longitude' => $place['longitude']
        ];
    }

    /** One GET request with cURL – all error handling in one place. */
    private function request($url)
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => (int) Config::OPEN_METEO_TIMEOUT(),
            CURLOPT_HTTPHEADER => ['Accept: application/json']
        ]);

        $body = curl_exec($curl);
        $error = curl_error($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($body === false) {                       // timeout, DNS problem, no internet...
            error_log("Open-Meteo request failed: $error ($url)");
            throw new ExternalServiceException(self::UNAVAILABLE);
        }
        if ($status !== 200) {
            error_log("Open-Meteo returned HTTP $status ($url)");
            throw new ExternalServiceException(self::UNAVAILABLE);
        }
        $data = json_decode($body, true);
        if (!is_array($data)) {
            error_log("Open-Meteo returned invalid JSON ($url)");
            throw new ExternalServiceException(self::UNAVAILABLE);
        }
        return $data;
    }
}
