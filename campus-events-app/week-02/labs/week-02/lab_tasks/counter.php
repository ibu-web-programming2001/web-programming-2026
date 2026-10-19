<?php
declare(strict_types=1);

// Version 1 (always prints 1):
//   $count = 0; $count++; echo $count;
// Every request runs the script again from the top and all variables are gone
// when it ends (shared-nothing), so $count always starts from 0.

// Version 2: the session keeps the value on the server between requests.
// The browser only stores the session id in a cookie (PHPSESSID).
// A private window has no such cookie, so it gets a new, empty session -> starts from 1.
session_start();

$_SESSION['count'] ??= 0;
$_SESSION['count']++;

echo $_SESSION['count'];
