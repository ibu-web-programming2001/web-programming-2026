<?php
/*
 * Slide 12 – Variables and types: $ prefix, declare(strict_types=1), var_dump()
 * Run:  php 03_types.php
 */
declare(strict_types=1);
header("Content-Type: text/plain; charset=utf-8");

function seats_left(int $capacity, int $taken): int
{
    return $capacity - $taken;
}

echo seats_left(120, 115), "\n";        // 5

// Same call with a string: with strict_types this is a TypeError
try {
    echo seats_left("120", 115), "\n";  // TypeError
} catch (TypeError $e) {
    echo "TypeError: ", $e->getMessage(), "\n";
}

// var_dump shows the type AND the value – use it when something looks wrong
echo "\n--- var_dump ---\n";
$capacity = 120;
$price    = 9.99;
$title    = "Hackathon";
$is_free  = false;
$venue    = null;
$tags     = ["tech", "student"];

var_dump($capacity);   // int(120)
var_dump($price);      // float(9.99)
var_dump($title);      // string(9) "Hackathon"
var_dump($is_free);    // bool(false)
var_dump($venue);      // NULL
var_dump($tags);       // array(2) { ... }
