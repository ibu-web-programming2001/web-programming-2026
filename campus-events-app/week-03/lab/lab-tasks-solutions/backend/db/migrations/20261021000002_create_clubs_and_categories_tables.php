<?php
use Phinx\Migration\AbstractMigration;

final class CreateClubsAndCategoriesTables extends AbstractMigration
{
    public function change(): void
    {
        $this->table('clubs')
            ->addColumn('name', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('faculty', 'string', ['limit' => 40, 'null' => true])
            ->addColumn('founded_year', 'smallinteger', ['null' => true])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['name'], ['unique' => true])
            ->create();

        $this->table('categories')
            ->addColumn('name', 'string', ['limit' => 60, 'null' => false])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['name'], ['unique' => true])
            ->create();
    }
}
