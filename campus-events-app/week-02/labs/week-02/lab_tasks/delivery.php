<?php
// delivery.php
declare(strict_types=1);
require_once 'functions.php';

$tests = [
    [20, 'center'], [49.99, 'suburb'], [50, 'suburb'],
    [80, 'outside'], [30, 'outside'], [30, 'mars'],
];

foreach ($tests as $t) {
    try {
        $fee = deliveryFee($t[0], $t[1]);
        echo "$t[0] KM to $t[1]: " . number_format($fee, 2) . ' KM<br>';
    } catch (InvalidArgumentException $e) {
        echo "$t[0] KM to $t[1]: " . $e->getMessage() . '<br>';
    }
}
