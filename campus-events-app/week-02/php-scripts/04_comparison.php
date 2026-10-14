<?php
/*
 * Slide 13 – Predict the output: == compares values, === compares values AND types
 * Run:  php 04_comparison.php
 */
declare(strict_types=1);
header("Content-Type: text/plain; charset=utf-8");

$cases = [
    '0 == "a"'      => 0 == "a",      // false – PHP 8: number vs text compares as text
    '"1" == "01"'   => "1" == "01",   // true  – both look numeric: compared as numbers
    '"10" == "1e1"' => "10" == "1e1", // true  – 1e1 is 10 in scientific notation
    'null == false' => null == false, // true  – both are "empty"
    '"1" === 1'     => "1" === 1,     // false – string vs int: different types
    '[] == false'   => [] == false,   // true  – an empty array is falsy
    '"5" === 5'     => "5" === 5,     // false – quiz question 1
    '"5" == 5'      => "5" == 5,      // true
];

printf("%-16s %s\n", "Expression", "Result");
printf("%-16s %s\n", "----------", "------");
foreach ($cases as $expression => $result) {
    printf("%-16s %s\n", $expression, var_export($result, true));
}

// The rule: use === and !== by default.
echo "\n--- role check ---\n";
$user = ["name" => "Ana", "role" => "admin"];
if ($user["role"] === "admin") {
    echo "{$user['name']}: only admins get here\n";
}

// Classic bug with loose comparison (the reason for the rule)
$input = "0";                        // what a form or query string sends: always a string
var_dump($input == false);           // true  – "0" is falsy, so == treats it as false
var_dump($input === false);          // false – different types
