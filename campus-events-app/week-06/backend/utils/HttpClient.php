<?php
require_once __DIR__ . '/exceptions.php';

/**
 * One GET request with cURL that returns decoded JSON – shared by the services that call external APIs.
 * Same rules as in OpenMeteoService::request():
 *  - always a timeout, check the HTTP status and the JSON
 *  - on failure: log the real reason, throw ExternalServiceException (503) with a FRIENDLY message
 */
class HttpClient
{
    public static function get_json($url, $timeout, $friendly_message)
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,              // return the body as a string, do not print it
            CURLOPT_FOLLOWLOCATION => true,              // follow 301/302 redirects
            CURLOPT_CONNECTTIMEOUT => 3,                 // max. seconds to connect
            CURLOPT_TIMEOUT => (int) $timeout,           // max. seconds for the whole request
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_USERAGENT => 'CampusEvents/1.0'      // some APIs reject requests without a User-Agent
        ]);

        $body = curl_exec($curl);
        $error = curl_error($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($body === false) {                           // timeout, DNS problem, no internet...
            error_log("External request failed: $error ($url)");
            throw new ExternalServiceException($friendly_message);
        }
        if ($status !== 200) {
            error_log("External API returned HTTP $status ($url)");
            throw new ExternalServiceException($friendly_message);
        }
        $data = json_decode($body, true);
        if (!is_array($data)) {
            error_log("External API returned invalid JSON ($url)");
            throw new ExternalServiceException($friendly_message);
        }
        return $data;
    }
}
