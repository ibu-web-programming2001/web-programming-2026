<?php
// functions.php – you will reuse this file in later tasks
declare(strict_types=1);

function deliveryFee(float $total, string $zone): float
{
    if ($total < 0) {
        throw new InvalidArgumentException('Total cannot be negative');
    }

    $fee = match ($zone) {
        'center'  => 2.0,
        'suburb'  => 4.0,
        'outside' => 7.0,
        default   => throw new InvalidArgumentException("Unknown zone: $zone"),
    };

    // 50 KM or more: free, or half price for outside
    if ($total >= 50) {
        return $zone === 'outside' ? $fee / 2 : 0.0;
    }

    return $fee;
}

// Task 5: returns the dish with this id, or null if it is not in the menu
function findDish(array $menu, int $id): ?array
{
    foreach ($menu as $dish) {
        if ($dish['id'] === $id) {
            return $dish;
        }
    }
    return null;
}
