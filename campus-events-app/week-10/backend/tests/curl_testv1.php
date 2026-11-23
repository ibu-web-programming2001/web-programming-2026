<?php
/*
 * VERSION 1 – "testing" by printing responses (like curlTest.php last year)
 *
 *     php tests/curl_testv1.php            (the server must be running)
 *
 * Problems:
 *  - a HUMAN has to read every response and decide if it is correct
 *  - nothing fails automatically, so a broken endpoint is easy to miss
 *  - cannot run in CI (week 13)
 * VERSION 2 (final): PHPUnit with assertions – tests/unit and tests/api.
 */
$base = getenv('API_BASE_URL') ?: 'http://localhost:8000/backend';

function call($method, $url, $body = null, $token = null)
{
    $curl = curl_init($url);
    $headers = ['Content-Type: application/json'];
    if ($token) $headers[] = "Authorization: Bearer $token";
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $body ? json_encode($body) : null
    ]);
    $response = curl_exec($curl);
    echo "$method $url -> HTTP " . curl_getinfo($curl, CURLINFO_HTTP_CODE) . "\n" . substr((string) $response, 0, 200) . "\n\n";
    return json_decode((string) $response, true);
}

call('GET', "$base/events");
$login = call('POST', "$base/auth/login", ['email' => 'admin@campusevents.ba', 'password' => 'Admin123!']);
call('GET', "$base/events/1", null, $login['token'] ?? null);
call('GET', "$base/events/999", null, $login['token'] ?? null);

echo "Did everything work? Read the output above and decide yourself...\n";
