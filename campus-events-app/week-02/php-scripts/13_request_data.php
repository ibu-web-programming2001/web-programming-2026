<?php
/*
 * Slide 24 – Request data: superglobals $_GET, $_POST, $_SERVER, ...
 *
 * Run as a web page:   php -S localhost:8000   (from this folder)  or put the folder in XAMPP htdocs
 * Then open / send with Postman:
 *   http://localhost:8000/13_request_data.php                     -> 400: club_id missing
 *   http://localhost:8000/13_request_data.php?club_id=abc         -> 400: not a number
 *   http://localhost:8000/13_request_data.php?club_id=2&q=<b>hi   -> 200, q is escaped
 */
declare(strict_types=1);
header("Content-Type: text/plain; charset=utf-8");

if (PHP_SAPI === "cli") {
    exit("Open this file through a web server (see the comment at the top).\n");
}

header("Content-Type: text/plain; charset=utf-8");

// $_SERVER: method, URL, headers, IP
$method = $_SERVER["REQUEST_METHOD"];            // "GET"
print_r($_SERVER);
echo "method:     $method\n";
echo "uri:        {$_SERVER['REQUEST_URI']}\n";
echo "client ip:  {$_SERVER['REMOTE_ADDR']}\n";
echo "user agent: ", $_SERVER["HTTP_USER_AGENT"] ?? "(none)", "\n";

// $_GET: URL query ?club_id=2
$club_id = $_GET["club_id"] ?? null;             // ?club_id=2
echo "\nraw \$_GET['club_id']: ";
var_dump($club_id);                              // always a string (or null) – never an int!

// validate input
$id = filter_input(INPUT_GET, "club_id", FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit("club_id must be a positive number\n");
}
echo "validated club_id: ";
var_dump($id);                                   // int(2)

// escape before printing anything that came from the client
$q = $_GET["q"] ?? "";
echo "\nraw q:     $q\n";
echo "escaped q: ", htmlspecialchars($q), "\n";  // <b>hi -> &lt;b&gt;hi

// everything the client sent: every value can be forged
echo "\n\$_GET = ";
print_r($_GET);
echo "\$_POST = ";
print_r($_POST);
echo "\$_COOKIE = ";
print_r($_COOKIE);
