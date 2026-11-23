<?php
require_once __DIR__ . '/../dao/EventDao.php';
require_once __DIR__ . '/../dao/ClubDao.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/RegistrationDao.php';
require_once __DIR__ . '/../dao/ReviewDao.php';
require_once __DIR__ . '/../../utils/Roles.php';
require_once __DIR__ . '/RegistrationService.php';

/**
 * Numbers for the dashboard. It does not own a table – it combines several DAOs.
 */
class DashboardService
{
    private $event_dao;
    private $club_dao;
    private $user_dao;
    private $registration_dao;
    private $review_dao;

    public function __construct($event_dao = null, $club_dao = null, $user_dao = null, $registration_dao = null, $review_dao = null)
    {
        $this->review_dao = $review_dao ?? new ReviewDao();
        $this->event_dao = $event_dao ?? new EventDao();
        $this->club_dao = $club_dao ?? new ClubDao();
        $this->user_dao = $user_dao ?? new UserDao();
        $this->registration_dao = $registration_dao ?? new RegistrationDao();
    }

    /** Personalised dashboard: campus statistics for admins, "my events" for students. */
    public function get_for_user($user)
    {
        return $user['role'] === Roles::ADMIN ? $this->get_stats() : $this->get_student_stats($user['id']);
    }

    public function get_student_stats($user_id)
    {
        $my_registrations = $this->registration_dao->get_by_user($user_id);
        $upcoming = array_filter($my_registrations, fn($r) => (int) $r['is_upcoming'] === 1);
        $attended = array_filter($my_registrations, fn($r) => $r['status'] === 'attended');

        return [
            'role' => Roles::STUDENT,
            'upcoming_events' => $this->event_dao->count_upcoming(),
            'upcoming_registrations' => count($upcoming),
            'attended' => count($attended),
            'total_registrations' => count($my_registrations),
            'reviews' => $this->review_dao->count_by_user($user_id),
            'max_upcoming_registrations' => RegistrationService::MAX_UPCOMING_REGISTRATIONS,
            'starting_soon' => $this->registration_dao->get_starting_soon(7, $user_id)
        ];
    }

    public function get_stats()
    {
        return [
            'role' => Roles::ADMIN,
            'events' => $this->event_dao->count_all(),
            'upcoming_events' => $this->event_dao->count_upcoming(),
            'clubs' => $this->club_dao->count_all(),
            'students' => $this->user_dao->count_by_role(Roles::STUDENT),
            'upcoming_registrations' => $this->registration_dao->count_upcoming(),
            'attended' => $this->registration_dao->count_attended(),
            'registrations_per_category' => $this->registration_dao->count_per_category(),
            'starting_soon' => $this->event_dao->get_starting_soon(7)
        ];
    }
}
