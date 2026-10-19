<?php
declare(strict_types=1);
$menu = require 'data.php';

function respond(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// TODO 1: only GET is allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(['error' => 'Method not allowed'], 405);
}

// TODO 2: ?id=3 -> one dish
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        respond(['error' => 'id must be a positive whole number'], 400);
    }

    foreach ($menu as $dish) {
        if ($dish['id'] === $id) {
            respond($dish);
        }
    }
    respond(['error' => 'Dish not found'], 404);
}

// TODO 3: the list, filtered by the optional parameters
$category   = $_GET['category'] ?? null;
$vegetarian = ($_GET['vegetarian'] ?? '') === '1';
$maxPrice   = null;

if (isset($_GET['max_price'])) {
    $maxPrice = filter_var($_GET['max_price'], FILTER_VALIDATE_FLOAT);
    if ($maxPrice === false) {
        respond(['error' => 'max_price must be a number'], 400);
    }
}

$result = [];
foreach ($menu as $dish) {
    if ($category !== null && $dish['category'] !== $category) {
        continue;
    }
    if ($vegetarian && !$dish['vegetarian']) {
        continue;
    }
    if ($maxPrice !== null && $dish['price'] > $maxPrice) {
        continue;
    }
    $result[] = $dish;
}

respond($result);
