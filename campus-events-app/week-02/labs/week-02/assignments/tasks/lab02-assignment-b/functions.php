<?php
// Do not change this file.
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

    if ($total >= 50) {
        return $zone === 'outside' ? $fee / 2 : 0.0;
    }

    return $fee;
}
