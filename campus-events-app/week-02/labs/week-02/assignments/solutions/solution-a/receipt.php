<?php
declare(strict_types=1);

// IT 2001 Web Programming – Lab 02 assignment: Order receipt (solution)

require_once 'functions.php';
$menu = require 'data.php';

$order = [1 => 2, 3 => 3, 6 => 1];        // dish id => quantity
$zone  = $_GET['zone'] ?? 'center';

$subtotal = 0.0;

foreach ($order as $dishId => $qty) {
    foreach ($menu as $dish) {
        if ($dish['id'] === $dishId) {
            $lineTotal = $dish['price'] * $qty;
            $subtotal += $lineTotal;
            echo htmlspecialchars($dish['name']) . " x $qty = "
                . number_format($lineTotal, 2) . ' KM<br>';
            break;
        }
    }
}

echo 'Subtotal: ' . number_format($subtotal, 2) . ' KM<br>';

try {
    $fee = deliveryFee($subtotal, $zone);
    echo 'Delivery (' . htmlspecialchars($zone) . '): '
        . number_format($fee, 2) . ' KM<br>';
    echo 'Total: ' . number_format($subtotal + $fee, 2) . ' KM<br>';
} catch (InvalidArgumentException $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage()) . '<br>';
}
