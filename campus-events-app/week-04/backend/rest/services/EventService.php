<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/EventDao.php';
require_once __DIR__ . '/../dao/ClubDao.php';
require_once __DIR__ . '/../dao/CategoryDao.php';
require_once __DIR__ . '/../dao/RegistrationDao.php';

class EventService extends BaseService
{
    protected $entity_name = 'Event';
    private $club_dao;
    private $category_dao;
    private $registration_dao;

    public function __construct($dao = null, $club_dao = null, $category_dao = null, $registration_dao = null)
    {
        parent::__construct($dao ?? new EventDao());
        $this->club_dao = $club_dao ?? new ClubDao();
        $this->category_dao = $category_dao ?? new CategoryDao();
        $this->registration_dao = $registration_dao ?? new RegistrationDao();
    }

    public function add($event)
    {
        $event = $this->normalize($event);
        // Rule: a new event has all seats free – the client cannot choose this value
        $event['available_seats'] = $event['capacity'] ?? null;
        $this->validate($event);

        // Rule: a new event must be in the future (an old event may keep its date when it is edited)
        if ($event['starts_at'] <= date('Y-m-d H:i:s')) {
            throw new ValidationException("A new event must start in the future.");
        }

        $created = $this->dao->add($event);
        return $this->dao->get_by_id($created['id']);
    }

    public function update($event, $id)
    {
        $event = $this->normalize($event);
        $this->get_by_id($id);
        $this->validate($event, $id);

        // Rule: free seats = capacity - seats that are already taken
        $taken = $this->registration_dao->count_taken_by_event($id);
        if ($event['capacity'] < $taken) {
            throw new ValidationException("$taken students are already registered – the capacity cannot be lower than that.");
        }
        $event['available_seats'] = $event['capacity'] - $taken;

        $this->dao->update($event, $id);
        return $this->dao->get_by_id($id);
    }

    /** Rule: an event with registrations cannot be deleted (cancel the registrations first). */
    public function delete($id)
    {
        $this->get_by_id($id);
        $taken = $this->registration_dao->count_taken_by_event($id);
        if ($taken > 0) {
            throw new ValidationException("$taken student(s) are registered for this event – it cannot be deleted.");
        }
        $this->dao->delete($id);
    }

    protected function validate($event, $id = null)
    {
        Validator::required($event, ['title', 'club_id', 'category_id', 'location', 'starts_at', 'capacity']);
        Validator::length($event['title'], 'title', 3, 150);
        Validator::length($event['location'], 'location', 2, 120);
        Validator::integer($event['capacity'], 'capacity', 1, 5000);
        Validator::datetime($event['starts_at'], 'starts_at');

        if (!$this->club_dao->get_by_id($event['club_id'])) {
            throw new ValidationException("The selected club does not exist.");
        }
        if (!$this->category_dao->get_by_id($event['category_id'])) {
            throw new ValidationException("The selected category does not exist.");
        }
        if (isset($event['city'])) {
            Validator::length($event['city'], 'city', 2, 60);
        }

        // Rule: the same event cannot be entered twice (same title at the same time)
        $existing = $this->dao->get_by_title_and_start(trim($event['title']), $event['starts_at']);
        if ($existing && $existing['id'] != $id) {
            throw new ValidationException("'{$event['title']}' already exists at this date and time.");
        }
    }

    /** Clean up input: optional fields "" -> NULL, "2026-11-20T18:00" (HTML datetime-local) -> "2026-11-20 18:00:00". */
    private function normalize($event)
    {
        $event = $this->empty_to_null($event, ['city', 'description']);
        if (array_key_exists('city', $event) && $event['city'] === null) {
            unset($event['city']);                       // the database default (Sarajevo) is used
        }
        if (!empty($event['starts_at'])) {
            $value = str_replace('T', ' ', trim($event['starts_at']));
            $event['starts_at'] = strlen($value) === 16 ? $value . ':00' : $value;
        }
        return $event;
    }
}
