<?php
require_once __DIR__ . '/../dao/EventDao.php';
require_once __DIR__ . '/../dao/ClubDao.php';

/**
 * VERSION 2 – rules written directly in the service, errors returned as arrays.
 *
 * Better: the rules exist now.
 * Still a problem:
 *  - every caller must check ['success'] – it is easy to forget
 *  - the result does not say WHAT kind of error it is (invalid data? not found?) -> which HTTP status?
 *  - the same checks ("is it empty?", "is it a number?") will be copied into every service
 */
class EventServicev2
{
    private $dao;
    private $club_dao;

    public function __construct()
    {
        $this->dao = new EventDao();
        $this->club_dao = new ClubDao();
    }

    public function get_all()
    {
        return ['success' => true, 'data' => $this->dao->get_all()];
    }

    public function get_by_id($id)
    {
        $event = $this->dao->get_by_id($id);
        if (!$event) {
            return ['success' => false, 'error' => 'Event not found.'];
        }
        return ['success' => true, 'data' => $event];
    }

    public function add($event)
    {
        if (empty($event['title'])) {
            return ['success' => false, 'error' => 'Title is required.'];
        }
        if (empty($event['capacity']) || $event['capacity'] < 1) {
            return ['success' => false, 'error' => 'There must be at least one seat.'];
        }
        if (empty($event['club_id']) || !$this->club_dao->get_by_id($event['club_id'])) {
            return ['success' => false, 'error' => 'The selected club does not exist.'];
        }
        if (empty($event['starts_at']) || $event['starts_at'] <= date('Y-m-d H:i:s')) {
            return ['success' => false, 'error' => 'A new event must start in the future.'];
        }

        // Rule: a new event has all seats free – the client cannot choose this value
        $event['available_seats'] = $event['capacity'];

        return ['success' => true, 'data' => $this->dao->add($event)];
    }

    public function delete($id)
    {
        if (!$this->dao->get_by_id($id)) {
            return ['success' => false, 'error' => 'Event not found.'];
        }
        $this->dao->delete($id);
        return ['success' => true];
    }
}
