<?php
declare(strict_types=1);

// IT 2001 Web Programming – Lab 02 assignment, group B: Order receipt (solution)

require_once 'functions.php';
$menu = require 'data.php';

$order = [2 => 1, 4 => 4, 5 => 2];        // dish id => quantity
$zone  = $_GET['zone'] ?? 'suburb';

$subtotal = 0.0;

foreach ($order as $dishId => $qty) {
    foreach ($menu as $dish) {
        if ($dish['id'] === $dishId) {
            $lineTotal = $dish['price'] * $qty;
            $subtotal += $lineTotal;
            echo "$qty x " . htmlspecialchars($dish['name']) . ' = '
                . number_format($lineTotal, 2) . ' KM<br>';
            break;
        }
    }
}

echo 'Subtotal: ' . number_format($subtotal, 2) . ' KM<br>';

try {
    $fee = deliveryFee($subtotal, $zone);
    echo 'Delivery to ' . htmlspecialchars($zone) . ': '
        . number_format($fee, 2) . ' KM<br>';
    echo 'To pay: ' . number_format($subtotal + $fee, 2) . ' KM<br>';
} catch (InvalidArgumentException $e) {
    echo 'Order failed: ' . htmlspecialchars($e->getMessage()) . '<br>';
}
