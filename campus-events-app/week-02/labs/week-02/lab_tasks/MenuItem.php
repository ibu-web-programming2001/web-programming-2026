<?php
// MenuItem.php
declare(strict_types=1);

class MenuItem
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly float $price,
        public readonly bool $vegetarian = false,
    ) {}

    // builds a MenuItem from one row of data.php
    public static function fromArray(array $row): self
    {
        return new self(
            $row['id'],
            $row['name'],
            (float) $row['price'],
            $row['vegetarian'] ?? false,
        );
    }
}
