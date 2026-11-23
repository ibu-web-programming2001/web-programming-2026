<?php
use PHPUnit\Framework\TestCase;

/**
 * Base class for API tests: real HTTP requests with cURL against a RUNNING server.
 *
 * 1) Start the API – choose ONE:
 *      a) PHP built-in server:  php -S localhost:8000 server.php   (in the week folder)
 *                               -> http://localhost:8000/backend
 *      b) XAMPP / WAMP:         start Apache + MySQL, project inside htdocs/ (XAMPP) or www/ (WAMP)
 *                               -> http://localhost/campus-events-app/week-09/backend
 *
 * 2) Tell the tests the URL: edit API_BASE_URL in backend/phpunit.xml
 *    (or set it only for one run:
 *       macOS/Linux:  API_BASE_URL=http://localhost/campus-events-app/week-09/backend vendor/bin/phpunit --testsuite api
 *       PowerShell:   $env:API_BASE_URL="http://localhost/campus-events-app/week-09/backend"; vendor/bin/phpunit --testsuite api
 *       cmd.exe:      set API_BASE_URL=http://localhost/campus-events-app/week-09/backend
 *                     vendor\bin\phpunit --testsuite api )
 *
 * 3) Run (in backend/):  composer test:api
 *
 * When the server is not running at that URL, the tests are SKIPPED (not failed).
 */
abstract class ApiTestCase extends TestCase
{
    private static $tokens = [];

    protected function base_url()
    {
        return rtrim(getenv('API_BASE_URL') ?: 'http://localhost:8000/backend', '/');
    }

    protected function setUp(): void
    {
        if ($this->request('GET', '/')['status'] === 0) {
            $this->markTestSkipped('API is not running at ' . $this->base_url());
        }
    }

    /** @return array ['status' => 200, 'body' => decoded JSON] */
    protected function request($method, $path, $body = null, $token = null)
    {
        $headers = ['Accept: application/json'];
        if ($token) {
            $headers[] = "Authorization: Bearer $token";
        }

        $curl = curl_init($this->base_url() . $path);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_TIMEOUT => 10
        ]);
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);   // 0 = no connection

        return ['status' => $status, 'body' => json_decode((string) $response, true)];
    }

    /** Log in once per test run and reuse the token. */
    protected function token($role)
    {
        $accounts = [
            'admin' => ['admin@campusevents.ba', 'Admin123!'],
            'student' => ['lana@campusevents.ba', 'Student123!']
        ];
        if (!isset(self::$tokens[$role])) {
            [$email, $password] = $accounts[$role];
            $response = $this->request('POST', '/auth/login', ['email' => $email, 'password' => $password]);
            $this->assertSame(200, $response['status'], "Login as $role failed – did you import database/campusevents.sql?");
            self::$tokens[$role] = $response['body']['token'];
        }
        return self::$tokens[$role];
    }
}
