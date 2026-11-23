<?php
use Phinx\Migration\AbstractMigration;

/**
 * Week 07 – the schema EVOLVES: two-factor authentication needs two new columns.
 * Everybody who pulls this commit runs "vendor/bin/phinx migrate" and gets them.
 */
final class AddTwoFactorToUsers extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users')
            ->addColumn('two_factor_secret', 'string', ['limit' => 64, 'null' => true, 'after' => 'faculty'])
            ->addColumn('two_factor_enabled', 'boolean', ['default' => false, 'null' => false, 'after' => 'two_factor_secret'])
            ->update();
    }
}
