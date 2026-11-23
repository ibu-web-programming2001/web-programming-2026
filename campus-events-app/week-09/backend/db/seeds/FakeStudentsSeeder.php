<?php
use Phinx\Seed\AbstractSeed;

/**
 * Generates realistic FAKE students with Faker – useful for testing paging, search and charts.
 *   vendor/bin/phinx seed:run -s FakeStudentsSeeder
 * All fake students have the password "Student123!".
 */
class FakeStudentsSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['CampusEventsSeeder'];
    }

    public function run(): void
    {
        $faker = Faker\Factory::create();
        $password = password_hash('Student123!', PASSWORD_BCRYPT);

        $students = [];
        for ($i = 0; $i < 20; $i++) {
            $students[] = [
                'first_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'email' => $faker->unique()->safeEmail(),
                'password' => $password,
                'role' => 'student',
                'faculty' => $faker->randomElement(['FENS', 'FASS', 'FBA', 'FEDU'])
            ];
        }
        $this->table('users')->insert($students)->saveData();
    }
}
