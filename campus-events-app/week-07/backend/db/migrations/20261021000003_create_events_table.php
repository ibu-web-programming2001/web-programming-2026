<?php
use Phinx\Migration\AbstractMigration;

final class CreateEventsTable extends AbstractMigration
{
    public function change(): void
    {
        // Foreign key columns must have the same type as the "id" they point to (unsigned int)
        $this->table('events')
            ->addColumn('title', 'string', ['limit' => 150, 'null' => false])
            ->addColumn('club_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('category_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('location', 'string', ['limit' => 120, 'null' => false])
            ->addColumn('city', 'string', ['limit' => 60, 'default' => 'Sarajevo', 'null' => false])
            ->addColumn('starts_at', 'datetime', ['null' => false])
            ->addColumn('capacity', 'integer', ['null' => false])
            ->addColumn('available_seats', 'integer', ['null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['starts_at'])
            ->addForeignKey('club_id', 'clubs', 'id', ['delete' => 'RESTRICT'])
            ->addForeignKey('category_id', 'categories', 'id', ['delete' => 'RESTRICT'])
            ->create();
    }
}
