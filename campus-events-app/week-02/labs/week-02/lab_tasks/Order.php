<?php
// Order.php
declare(strict_types=1);
require_once 'functions.php';
require_once 'MenuItem.php';

class Order
{
    private array $lines = [];   // each line: ['item' => MenuItem, 'qty' => int]

    public function __construct(private string $zone) {}

    public function addItem(MenuItem $item, int $qty): void
    {
        if ($qty < 1) {
            throw new InvalidArgumentException('Quantity must be at least 1');
        }

        // the same item again: increase its qty instead of adding a new line
        foreach ($this->lines as $i => $line) {
            if ($line['item']->id === $item->id) {
                $this->lines[$i]['qty'] += $qty;
                return;
            }
        }

        $this->lines[] = ['item' => $item, 'qty' => $qty];
    }

    public function subtotal(): float
    {
        $subtotal = 0.0;
        foreach ($this->lines as $line) {
            $subtotal += $line['item']->price * $line['qty'];
        }
        return $subtotal;
    }

    public function total(): float
    {
        return $this->subtotal() + deliveryFee($this->subtotal(), $this->zone);
    }

    public function toArray(): array
    {
        $lines = [];
        foreach ($this->lines as $line) {
            $lines[] = [
                'name'       => $line['item']->name,
                'qty'        => $line['qty'],
                'line_total' => $line['item']->price * $line['qty'],
            ];
        }

        return [
            'zone'     => $this->zone,
            'lines'    => $lines,
            'subtotal' => $this->subtotal(),
            'delivery' => deliveryFee($this->subtotal(), $this->zone),
            'total'    => $this->total(),
        ];
    }
}
