<?php
use Phinx\Seed\AbstractSeed;

/**
 * Sample data – the same rows as database/campusevents.sql.
 *   vendor/bin/phinx seed:run -s CampusEventsSeeder
 *
 * Test accounts: admin@campusevents.ba (Admin123!), ema/tarik/lana@campusevents.ba (Student123!)
 */
class CampusEventsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $admin_hash = '$2y$10$0Hge22rw38Y05WNSVUtghO.6HmmiOeHLTQFdBesqg33d5z3feOvBG';     // Admin123!
        $student_hash = '$2y$10$TxLK/Oju/TDbgoGjSYisyeJl1vBJlpwilOPqh5Sm.XMuec4FZp6ni';   // Student123!

        $this->table('users')->insert([
            ['id' => 1, 'first_name' => 'Lejla', 'last_name' => 'Hadžić', 'email' => 'admin@campusevents.ba', 'password' => $admin_hash, 'role' => 'admin', 'faculty' => null],
            ['id' => 2, 'first_name' => 'Ema', 'last_name' => 'Kovač', 'email' => 'ema@campusevents.ba', 'password' => $student_hash, 'role' => 'student', 'faculty' => 'FENS'],
            ['id' => 3, 'first_name' => 'Tarik', 'last_name' => 'Begić', 'email' => 'tarik@campusevents.ba', 'password' => $student_hash, 'role' => 'student', 'faculty' => 'FBA'],
            ['id' => 4, 'first_name' => 'Lana', 'last_name' => 'Mehić', 'email' => 'lana@campusevents.ba', 'password' => $student_hash, 'role' => 'student', 'faculty' => 'FASS'],
        ])->saveData();

        $clubs = [
            [1, 'IBU Music Club', 'FASS', 2015, 'Bands, open mics and concerts on campus.'],
            [2, 'Google Developer Student Club', 'FENS', 2019, 'Workshops, hackathons and tech talks for future developers.'],
            [3, 'IBU Sports Association', 'All faculties', 2012, 'Tournaments, hikes and recreation for every student.'],
            [4, 'Debate & Culture Club', 'FASS', 2016, 'Debates, film nights, trips and culture.'],
            [5, 'Entrepreneurship Club', 'FBA', 2018, 'Pitch nights, career fairs and networking.'],
            [6, 'Robotics Club', 'FENS', 2020, 'Build robots, compete and learn electronics.'],
        ];
        $this->table('clubs')->insert(array_map(
            fn($c) => ['id' => $c[0], 'name' => $c[1], 'faculty' => $c[2], 'founded_year' => $c[3], 'description' => $c[4]],
            $clubs
        ))->saveData();

        $this->table('categories')->insert([
            ['id' => 1, 'name' => 'Music', 'description' => 'Concerts, open mics and jam sessions'],
            ['id' => 2, 'name' => 'Tech', 'description' => 'Hackathons, workshops and tech talks'],
            ['id' => 3, 'name' => 'Sports', 'description' => 'Tournaments, hikes and fitness'],
            ['id' => 4, 'name' => 'Culture', 'description' => 'Film, theatre, debates and trips'],
            ['id' => 5, 'name' => 'Career', 'description' => 'Career fairs, pitches and networking'],
        ])->saveData();

        // Dates relative to today, so "upcoming", "starting soon" and "past" always look realistic
        $at = fn($days, $time) => date('Y-m-d', strtotime("$days days")) . " $time:00";
        // [id, title, club, category, location, city, day offset, time, capacity, available seats]
        $events = [
            [1, 'Welcome Concert', 1, 1, 'IBU Amphitheatre', 'Sarajevo', 3, '19:00', 200, 198],
            [2, 'Hack the Campus – 24h Hackathon', 2, 2, 'FENS Lab 2', 'Sarajevo', 10, '09:00', 60, 59],
            [3, 'Futsal Tournament', 3, 3, 'IBU Sports Hall', 'Sarajevo', 6, '17:00', 40, 39],
            [4, 'Git & GitHub Workshop', 2, 2, 'Room B-103', 'Sarajevo', 2, '16:00', 30, 29],
            [5, 'Movie Night: Classic Cinema', 4, 4, 'Main Hall', 'Sarajevo', 8, '20:00', 120, 120],
            [6, 'Startup Pitch Night', 5, 5, 'Business Lab', 'Sarajevo', 14, '18:00', 80, 80],
            [7, 'Robot Build Day', 6, 2, 'Robotics Lab', 'Sarajevo', 5, '10:00', 2, 0],
            [8, 'Hiking Trip to Trebević', 3, 3, 'Trebević', 'Sarajevo', 12, '08:00', 25, 25],
            [9, 'Open Mic Night', 1, 1, 'Campus Café', 'Sarajevo', -7, '20:00', 50, 48],
            [10, 'Career Fair 2026', 5, 5, 'IBU Campus', 'Sarajevo', -14, '10:00', 300, 299],
            [11, 'Mostar Culture Trip', 4, 4, 'Old Bridge', 'Mostar', 20, '08:00', 45, 45],
            [12, 'Chess Blitz Night', 4, 4, 'Library Hall', 'Sarajevo', 1, '18:00', 16, 16],
        ];
        $this->table('events')->insert(array_map(fn($e) => [
            'id' => $e[0], 'title' => $e[1], 'club_id' => $e[2], 'category_id' => $e[3], 'location' => $e[4],
            'city' => $e[5], 'starts_at' => $at($e[6], $e[7]), 'capacity' => $e[8], 'available_seats' => $e[9]
        ], $events))->saveData();

        $ago = fn($days) => date('Y-m-d H:i:s', strtotime("-$days days"));
        // [user, event, registered days ago, cancelled days ago|null, status]
        $registrations = [
            [2, 1, 5, null, 'registered'], [2, 2, 3, null, 'registered'], [2, 4, 1, null, 'registered'],
            [3, 1, 4, null, 'registered'], [3, 7, 2, null, 'registered'],
            [4, 7, 2, null, 'registered'], [4, 3, 6, null, 'registered'],
            [2, 9, 12, null, 'attended'], [3, 10, 20, null, 'attended'], [4, 9, 12, null, 'attended'],
            [4, 5, 8, 6, 'cancelled'],
        ];
        $this->table('registrations')->insert(array_map(fn($r) => [
            'user_id' => $r[0], 'event_id' => $r[1], 'registered_at' => $ago($r[2]),
            'cancelled_at' => $r[3] === null ? null : $ago($r[3]), 'status' => $r[4]
        ], $registrations))->saveData();

        $this->table('reviews')->insert([
            ['user_id' => 2, 'event_id' => 9, 'rating' => 5, 'comment' => 'Amazing atmosphere – I will definitely perform next time!'],
            ['user_id' => 4, 'event_id' => 9, 'rating' => 4, 'comment' => 'Great performers, a bit crowded.'],
            ['user_id' => 3, 'event_id' => 10, 'rating' => 5, 'comment' => 'Got my first internship interview here.'],
        ])->saveData();
    }
}
