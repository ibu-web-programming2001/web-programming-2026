<?php
declare(strict_types=1);
$menu = require 'data.php';

$total = 0;
// BUG 1: <= count($menu) reads one index too far (Undefined array key 6); it must be <
for ($i = 0; $i < count($menu); $i++) {
    // BUG 2: $menu[i] – the $ was missing, so PHP looked for a constant named i
    // BUG 3: + is addition; strings are joined with . (TypeError: Unsupported operand types)
    echo $menu[$i]['name'] . ': ' . $menu[$i]['price'] . ' KM<br>';
    // BUG 4: = replaced the total with the last price; += adds to it
    $total += $menu[$i]['price'];
}
// BUG 5: variables are not replaced inside single quotes; double quotes are needed
echo "Total: $total KM<br>";

$status = 'cooking';
switch ($status) {
    case 'new':
        echo 'Received<br>';
        break;
    case 'cooking':
        echo 'In the kitchen<br>';
        // BUG 6: break was missing, so the code "fell through" and also printed Unknown
        break;
    default:
        echo 'Unknown<br>';
}

$discountCode = '0';   // the customer typed the code "0"
// BUG 7: "0" == false is true because "0" is a falsy string;
//        "no code entered" means an empty string, checked with ===
if ($discountCode === '') {
    echo 'No discount code';
} else {
    echo 'Discount code: ' . $discountCode;
}
