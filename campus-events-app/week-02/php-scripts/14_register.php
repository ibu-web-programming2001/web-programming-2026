<?php
/*
 * Slide 25 – Working with forms: PHP reads the fields from $_POST
 * Submitted by 14_register_form.html, or from Postman:
 *   POST http://localhost:8000/14_register.php   body (x-www-form-urlencoded): email=ana@campusevents.ba, event_id=2
 *   GET  http://localhost:8000/14_register.php   -> 405 (GET reads, POST changes)
 */
declare(strict_types=1);
header("Content-Type: text/plain; charset=utf-8");

if (PHP_SAPI === "cli") {
    exit("Open this file through a web server (see the comment at the top).\n");
}

header("Content-Type: text/plain; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Use POST – never change data with a GET request\n");
}

$email    = trim($_POST["email"] ?? "");
$event_id = (int) ($_POST["event_id"] ?? 0);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit("Invalid email\n");
}
if ($event_id < 1) {
    http_response_code(400);
    exit("Invalid event_id\n");
}

echo "Registered ", htmlspecialchars($email), " for event $event_id\n";
echo "\n\$_POST = ";
print_r($_POST);   // what the browser actually sent – all values are strings
