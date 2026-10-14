<?php
/* The Event class from slide 23 (same code as in 12_oop.php) – included by 15_errors.php. */
declare(strict_types=1);

class Event
{
    public function __construct(
        public readonly int $id,
        public string $title,
        private int $available_seats,
    ) {}

    public function has_seats(): bool
    {
        return $this->available_seats > 0;
    }

    public function take_seat(): void
    {
        if (!$this->has_seats()) {
            throw new RuntimeException("Sold out");
        }
        $this->available_seats--;
    }
}
