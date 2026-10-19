<?php
// order_demo.php
declare(strict_types=1);
require_once 'Order.php';
$menu = require 'data.php';

$margherita = MenuItem::fromArray(findDish($menu, 1));
$burek      = MenuItem::fromArray(findDish($menu, 3));

$order = new Order('suburb');
$order->addItem($margherita, 2);
$order->addItem($burek, 3);
$order->addItem($margherita, 1);   // same dish again -> the line becomes Margherita x 3

// qty 0 is not allowed: the exception is caught and the order stays unchanged
$error = null;
try {
    $order->addItem($burek, 0);
} catch (InvalidArgumentException $e) {
    $error = $e->getMessage();
}

// one JSON document, so the response stays valid JSON
header('Content-Type: application/json');
echo json_encode(
    ['order' => $order->toArray(), 'qty_0_error' => $error],
    JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
);
