<?php
declare(strict_types=1);

// TODO 1: one variable of each type
$string = 'Burek';
$int    = 3;
$float  = 4.50;
$bool   = true;
$array  = ['pizza', 'pita', 'grill'];
$null   = null;

// TODO 2: var_dump() each of them, each on its own line
foreach ([$string, $int, $float, $bool, $array, $null] as $value) {
    var_dump($value);
    echo '<br>';
}

// TODO 3: predictions
var_dump("1" == "01");     // my prediction: true  – both are numeric strings, compared as numbers (1 == 1)
echo '<br>';
var_dump("10" == "1e1");   // my prediction: true  – "1e1" is the number 10 in scientific notation
echo '<br>';
var_dump(100 == "1e2");    // my prediction: true  – "1e2" is converted to 100
echo '<br>';
var_dump(0 == "a");        // my prediction: false – PHP 8: "a" is not numeric, so 0 is compared as the string "0"
echo '<br>';
var_dump(null == false);   // my prediction: true  – null is converted to bool false
echo '<br>';
var_dump("abc" == 0);      // my prediction: false – same rule as 0 == "a" (it was true in PHP 7)
echo '<br>';
var_dump("1" === 1);       // my prediction: false – different types (string and int)
echo '<br>';

// TODO 4
function half(int $n): float
{
    return $n / 2;
}

var_dump(half(8));         // float(4)
echo '<br>';

// With declare(strict_types=1) the call half("8") throws:
//   TypeError: half(): Argument #1 ($n) must be of type int, string given
// Without the declare line PHP silently converts "8" to 8 and returns float(4).
try {
    var_dump(half("8"));
} catch (TypeError $e) {
    echo 'TypeError: ' . htmlspecialchars($e->getMessage());
}
echo '<br>';

// === is safer because it compares the type and the value without any conversion,
// so surprising results like "10" == "1e1" or null == false cannot happen.
