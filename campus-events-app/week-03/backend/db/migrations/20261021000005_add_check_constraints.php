<?php
use Phinx\Migration\AbstractMigration;

/**
 * Not everything has a builder method – CHECK constraints are written in SQL.
 * Raw SQL cannot be reversed automatically, so we write up() AND down() ourselves.
 */
final class AddCheckConstraints extends AbstractMigration
{
    public function up(): void
    {
        $this->execute('ALTER TABLE events ADD CONSTRAINT chk_events_seats CHECK (available_seats BETWEEN 0 AND capacity)');
        $this->execute('ALTER TABLE events ADD CONSTRAINT chk_events_capacity CHECK (capacity > 0)');
        $this->execute('ALTER TABLE reviews ADD CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)');
    }

    public function down(): void
    {
        $this->execute('ALTER TABLE events DROP CHECK chk_events_seats');
        $this->execute('ALTER TABLE events DROP CHECK chk_events_capacity');
        $this->execute('ALTER TABLE reviews DROP CHECK chk_reviews_rating');
    }
}
