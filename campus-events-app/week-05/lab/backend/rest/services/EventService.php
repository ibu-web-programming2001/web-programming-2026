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

    /*
     * TODO Task 4.1 – add($event)
     *   1. $event = $this->normalize($event);       – cleans the input (written for you, see the bottom of the class)
     *   2. Rule: a new event has all seats free – the client must not choose this value:
     *        $event['available_seats'] = $event['capacity'] ?? null;
     *   3. $this->validate($event);
     *   4. Rule: a new event must start in the future: Validator::in_future($event['starts_at'], 'starts_at');  (Task 2.1)
     *   5. $created = $this->dao->add($event);  and return $this->dao->get_by_id($created['id']);
     */
    public function add($event)
    {
        $event = $this->normalize($event);
        $event['available_seats'] = $event['capacity'] ?? null;   // all seats are free – ignore what the client sent
        $this->validate($event);
        Validator::in_future($event['starts_at'], 'starts_at');

        $created = $this->dao->add($event);
        return $this->dao->get_by_id($created['id']);
    }

    /*
     * TODO Task 4.2 – update($event, $id)
     *   1. $event = $this->normalize($event);
     *   2. $this->get_by_id($id);              – 404 if the event does not exist
     *   3. $this->validate($event, $id);
     *   4. Rule: the capacity cannot be lower than the seats that are already taken:
     *        $taken = $this->registration_dao->count_taken_by_event($id);
     *        if the capacity is smaller than $taken, throw:
     *        new ValidationException("$taken students are already registered – the capacity cannot be lower than that.")
     *   5. Rule: free seats = capacity - taken:   $event['available_seats'] = $event['capacity'] - $taken;
     *   6. $this->dao->update($event, $id);  and return $this->dao->get_by_id($id);
     *   (An old event may keep its date when it is edited, so there is NO in_future() check here.)
     */
    public function update($event, $id)
    {
        $event = $this->normalize($event);
        $this->get_by_id($id);
        $this->validate($event, $id);

        $taken = $this->registration_dao->count_taken_by_event($id);
        if ($event['capacity'] < $taken) {
            throw new ValidationException("$taken students are already registered – the capacity cannot be lower than that.");
        }
        $event['available_seats'] = $event['capacity'] - $taken;

        $this->dao->update($event, $id);
        return $this->dao->get_by_id($id);
    }

    /* ---------- finders – they use the EventDao methods you wrote in Lab 3 ---------- */

    /** All events of one club. A club that does not exist is a 404, not an empty list. */
    public function get_by_club($club_id)
    {
        if (!$this->club_dao->get_by_id($club_id)) {
            throw new NotFoundException("Club not found.");
        }
        return $this->dao->get_by_club($club_id);
    }

    public function get_by_category($category_id)
    {
        if (!$this->category_dao->get_by_id($category_id)) {
            throw new NotFoundException("Category not found.");
        }
        return $this->dao->get_by_category($category_id);
    }

    /*
     * TODO Task 4.3 – search($text)
     *   EventDao::search() (Lab 3) finds events by title or description. The service checks the text first:
     *   1. $text = trim((string) $text);
     *   2. the text has 2–50 characters:    Validator::length($text, 'text', 2, 50);
     *      (a search for "a" would return almost every event)
     *   3. return $this->dao->search($text);
     */
    public function search($text)
    {
        $text = trim((string) $text);
        Validator::length($text, 'text', 2, 50);
        return $this->dao->search($text);
    }

    /*
     * TODO Task 4.4 – filter($filters)
     *   EventDao::filter() (Lab 3) builds the WHERE clause from the filters that are present.
     *   The values come from the URL (?club_id=2&min_free_seats=10), so the service checks them first:
     *   1. if club_id is given (!empty):        Validator::integer($filters['club_id'], 'club_id', 1);
     *      and the club exists – if $this->club_dao->get_by_id(...) finds nothing, throw:
     *        new NotFoundException("Club not found.")
     *   2. the same for category_id:           Validator::integer(..., 'category_id', 1);   "Category not found."
     *   3. if min_free_seats is given (isset):  Validator::integer($filters['min_free_seats'], 'min_free_seats', 0);
     *   4. return $this->dao->filter($filters);
     */
    public function filter($filters = [])
    {
        if (!empty($filters['club_id'])) {
            Validator::integer($filters['club_id'], 'club_id', 1);
            if (!$this->club_dao->get_by_id($filters['club_id'])) {
                throw new NotFoundException("Club not found.");
            }
        }
        if (!empty($filters['category_id'])) {
            Validator::integer($filters['category_id'], 'category_id', 1);
            if (!$this->category_dao->get_by_id($filters['category_id'])) {
                throw new NotFoundException("Category not found.");
            }
        }
        if (isset($filters['min_free_seats'])) {
            Validator::integer($filters['min_free_seats'], 'min_free_seats', 0);
        }
        return $this->dao->filter($filters);
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
