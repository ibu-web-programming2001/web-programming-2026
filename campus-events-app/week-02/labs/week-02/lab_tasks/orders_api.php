<?php
declare(strict_types=1);
require_once 'functions.php';
$menu = require 'data.php';
$file = __DIR__ . '/orders.json';

function loadOrders(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }
    return json_decode(file_get_contents($file), true) ?? [];
}

function saveOrders(string $file, array $orders): void
{
    $json = json_encode($orders, JSON_PRETTY_PRINT);
    file_put_contents($file, $json, LOCK_EX);
}

function respond(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$orders = loadOrders($file);
$id     = (int) ($_GET['id'] ?? 0);   // 0 = no id was sent

if ($method === 'GET') {
    // GET orders_api.php -> all orders
    if ($id === 0) {
        respond($orders);
    }

    // GET orders_api.php?id=3 -> one order
    foreach ($orders as $order) {
        if ($order['id'] === $id) {
            respond($order);
        }
    }
    respond(['error' => 'Order not found'], 404);

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) {
        respond(['error' => 'The body must be valid JSON'], 400);
    }

    $dishId = $data['dish_id'] ?? null;
    $qty    = $data['qty'] ?? null;
    $zone   = $data['zone'] ?? null;

    if (!is_int($dishId) || findDish($menu, $dishId) === null) {
        respond(['error' => 'dish_id must be the id of a dish from the menu'], 400);
    }
    if (!is_int($qty) || $qty < 1 || $qty > 20) {
        respond(['error' => 'qty must be a whole number from 1 to 20'], 400);
    }
    if (!in_array($zone, ['center', 'suburb', 'outside'], true)) {
        respond(['error' => 'zone must be center, suburb or outside'], 400);
    }

    // new id = highest existing id + 1
    $maxId = 0;
    foreach ($orders as $order) {
        if ($order['id'] > $maxId) {
            $maxId = $order['id'];
        }
    }

    $dish     = findDish($menu, $dishId);
    $subtotal = $dish['price'] * $qty;

    $newOrder = [
        'id'      => $maxId + 1,
        'dish_id' => $dishId,
        'qty'     => $qty,
        'zone'    => $zone,
        'total'   => $subtotal + deliveryFee($subtotal, $zone),
    ];

    $orders[] = $newOrder;
    saveOrders($file, $orders);
    respond($newOrder, 201);

} elseif ($method === 'DELETE') {
    foreach ($orders as $i => $order) {
        if ($order['id'] === $id) {
            unset($orders[$i]);
            // array_values: the indexes start from 0 again, so the file stays a JSON list
            saveOrders($file, array_values($orders));
            respond(['message' => 'Order deleted']);
        }
    }
    respond(['error' => 'Order not found'], 404);

} else {
    respond(['error' => 'Method not allowed'], 405);
}
