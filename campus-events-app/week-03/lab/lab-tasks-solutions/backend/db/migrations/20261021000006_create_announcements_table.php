<?php
use Phinx\Migration\AbstractMigration;

/** Lab 03 – Task 6 (SOLUTION): the same table as database/announcements.sql, as a migration. */
final class CreateAnnouncementsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('announcements')
            ->addColumn('club_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('title', 'string', ['limit' => 150, 'null' => false])
            ->addColumn('body', 'text', ['null' => false])
            ->addColumn('is_pinned', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('club_id', 'clubs', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
