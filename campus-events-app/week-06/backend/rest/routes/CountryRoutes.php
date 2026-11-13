<?php
/**
 * PRESENTATION LAYER – external data: countries/languages (GitHub JSON file) and IP geolocation (ipwho.is)
 * Routes only read the request and call a service – no rules, no cURL here.
 */

// /countries?search=bos&language=Bosnian  (both optional)
Flight::route('GET /countries', function () {
    $search = Flight::request()->query['search'] ?? '';
    $language = Flight::request()->query['language'] ?? '';
    Flight::json(Flight::country_service()->search($search, $language));
});

// Defined BEFORE /countries/@code – Flight uses the first route that matches
Flight::route('GET /countries/languages', function () {
    Flight::json(Flight::country_service()->languages());
});

Flight::route('GET /countries/@code:[A-Za-z]{2,3}', function ($code) {
    Flight::json(Flight::country_service()->get_by_code($code));
});

// /geo/ip?ip=8.8.8.8  – without ?ip: the location of the visitor (or of the server when you run on localhost)
Flight::route('GET /geo/ip', function () {
    $ip = Flight::request()->query['ip'] ?? '';
    Flight::json(Flight::geo_service()->lookup($ip, Flight::request()->ip));
});
