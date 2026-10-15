<?php
require_once __DIR__ . '/../dao/EventDao.php';
require_once __DIR__ . '/../dao/ClubDao.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/RegistrationDao.php';
require_once __DIR__ . '/../../utils/Roles.php';

/**
 * Numbers for the dashboard. It does not own a table – it combines several DAOs.
 */
class DashboardService
{
    private $event_dao;
    private $club_dao;
    private $user_dao;
    private $registration_dao;

    public function __construct($event_dao = null, $club_dao = null, $user_dao = null, $registration_dao = null)
    {
        $this->event_dao = $event_dao ?? new EventDao();
        $this->club_dao = $club_dao ?? new ClubDao();
        $this->user_dao = $user_dao ?? new UserDao();
        $this->registration_dao = $registration_dao ?? new RegistrationDao();
    }

    public function get_stats()
    {
        return [
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
