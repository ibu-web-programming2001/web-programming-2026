<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ClubDao.php';
require_once __DIR__ . '/../dao/EventDao.php';

class ClubService extends BaseService
{
    protected $entity_name = 'Club';
    private $event_dao;

    public function __construct($dao = null, $event_dao = null)
    {
        parent::__construct($dao ?? new ClubDao());
        $this->event_dao = $event_dao ?? new EventDao();
    }

    public function add($club)
    {
        return parent::add($this->empty_to_null($club, ['faculty', 'founded_year', 'description']));
    }

    public function update($club, $id)
    {
        return parent::update($this->empty_to_null($club, ['faculty', 'founded_year', 'description']), $id);
    }

    /** Rule: a club with events cannot be deleted. */
    public function delete($id)
    {
        $this->get_by_id($id);
        $events = $this->event_dao->count_by_club($id);
        if ($events > 0) {
            throw new ValidationException("This club still organises $events event(s). Delete them first.");
        }
        $this->dao->delete($id);
    }

    protected function validate($club, $id = null)
    {
        Validator::required($club, ['name']);
        Validator::length($club['name'], 'name', 2, 100);
        if (!empty($club['faculty'])) {
            Validator::length($club['faculty'], 'faculty', 2, 40);
        }
        if (isset($club['founded_year'])) {
            Validator::integer($club['founded_year'], 'founded_year', 1990, (int) date('Y'));
        }

        // Rule: club names are unique
        $existing = $this->dao->get_by_name(trim($club['name']));
        if ($existing && $existing['id'] != $id) {
            throw new ValidationException("A club named '{$club['name']}' already exists.");
        }
    }
}
