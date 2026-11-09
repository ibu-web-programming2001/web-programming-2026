<?php
/**
 * PRESENTATION LAYER – external API (Open-Meteo)
 * The frontend never calls Open-Meteo directly: our backend does it, so we control
 * timeouts, error handling, logging and the shape of the data.
 */

/**
 * VERSION 1 – naive call, raw response (see OpenMeteoServicev1). Not documented in Swagger on purpose.
 */
Flight::route('GET /weather/v1/forecast', function () {
    $city = Flight::request()->query['city'] ?? '';
    Flight::json(Flight::open_meteo_service_v1()->forecast($city));
});

/**
 * VERSION 2 (FINAL) – timeout, error handling, only the fields we need.
 */
Flight::route('GET /weather/forecast', function () {
    $city = Flight::request()->query['city'] ?? '';
    $days = Flight::request()->query['days'] ?? 7;
    Flight::json(Flight::open_meteo_service()->forecast($city, $days));
});
