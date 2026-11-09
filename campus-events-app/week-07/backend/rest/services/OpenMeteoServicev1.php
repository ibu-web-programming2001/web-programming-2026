<?php
/**
 * VERSION 1 – the naive external API call (DO NOT DO THIS IN YOUR PROJECT)
 *
 * Problems:
 *  - no timeout: if Open-Meteo is slow, OUR request hangs for a long time
 *  - no error handling: no internet / HTTP 500 / unknown city / invalid JSON -> PHP warnings -> our API answers 500
 *  - the raw responses are sent to the frontend (two APIs, parallel arrays, codes instead of words)
 *  - nothing is logged
 *
 * Compare with OpenMeteoService.php (final version).
 */
class OpenMeteoServicev1
{
    public function forecast($city)
    {
        // 1) city name -> coordinates
        $geo = json_decode(file_get_contents('https://geocoding-api.open-meteo.com/v1/search?name=' . urlencode($city)), true);
        $place = $geo['results'][0];

        // 2) coordinates -> forecast
        $response = file_get_contents('https://api.open-meteo.com/v1/forecast?latitude=' . $place['latitude']
            . '&longitude=' . $place['longitude']
            . '&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max&timezone=auto');
        return json_decode($response, true);
    }
}
