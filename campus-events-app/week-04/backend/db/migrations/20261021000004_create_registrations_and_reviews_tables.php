<?php
use Phinx\Migration\AbstractMigration;

final class CreateRegistrationsAndReviewsTables extends AbstractMigration
{
    public function change(): void
    {
        $this->table('registrations')
            ->addColumn('user_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('event_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('registered_at', 'datetime', ['null' => false])
            ->addColumn('cancelled_at', 'datetime', ['null' => true])
            ->addColumn('status', 'enum', ['values' => ['registered', 'cancelled', 'attended'], 'default' => 'registered', 'null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE'])
            ->create();

        $this->table('reviews')
            ->addColumn('user_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('event_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('rating', 'integer', ['limit' => 255, 'null' => false])   // TINYINT
            ->addColumn('comment', 'text', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['user_id', 'event_id'], ['unique' => true])   // one review per student and event
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('event_id', 'events', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
