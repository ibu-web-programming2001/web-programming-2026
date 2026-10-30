<?php
use Phinx\Migration\AbstractMigration;

/**
 * A migration describes ONE change of the database schema, in PHP.
 * change() is reversible: Phinx knows how to undo create()/addColumn() on rollback.
 */
final class CreateUsersTable extends AbstractMigration
{
    public function change(): void
    {
        // Phinx adds the "id" primary key (auto increment) automatically
        $this->table('users')
            ->addColumn('first_name', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('last_name', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('email', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('role', 'enum', ['values' => ['admin', 'student'], 'default' => 'student', 'null' => false])
            ->addColumn('faculty', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['email'], ['unique' => true])
            ->create();
    }
}
