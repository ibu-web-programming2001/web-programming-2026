<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/CategoryDao.php';
require_once __DIR__ . '/../dao/EventDao.php';

class CategoryService extends BaseService
{
    protected $entity_name = 'Category';
    private $event_dao;

    /** DAOs can be passed in (used by unit tests); by default real DAOs are created. */
    public function __construct($dao = null, $event_dao = null)
    {
        parent::__construct($dao ?? new CategoryDao());
        $this->event_dao = $event_dao ?? new EventDao();
    }

    public function add($category)
    {
        return parent::add($this->empty_to_null($category, ['description']));
    }

    public function update($category, $id)
    {
        return parent::update($this->empty_to_null($category, ['description']), $id);
    }

    /*
     * TODO Task 3.2 – delete($id)
     *   Rule: a category that still contains events cannot be deleted.
     *   1. $this->get_by_id($id);   – throws NotFoundException ("Category not found.") if it does not exist
     *   2. $events = $this->event_dao->count_by_category($id);
     *   3. If $events > 0, throw:
     *        new ValidationException("This category still contains $events event(s). Move or delete them first.")
     *   4. Otherwise delete it: $this->dao->delete($id);
     *   ClubService::delete() is a finished example of the same rule.
     */
    public function delete($id)
    {
        $this->get_by_id($id);
        $events = $this->event_dao->count_by_category($id);
        if ($events > 0) {
            throw new ValidationException("This category still contains $events event(s). Move or delete them first.");
        }
        $this->dao->delete($id);
    }

    /*
     * TODO Task 3.1 – validate($category, $id = null)
     *   BaseService calls validate() in add() and update(). $id is null when a category is added,
     *   and the id of the category when it is updated. Check, in this order:
     *   1. name is required:                   Validator::required($category, ['name']);
     *   2. name has 2–60 characters:           Validator::length($category['name'], 'name', 2, 60);
     *   3. if a description is given (!empty), it has at most 255 characters: Validator::length(..., 'description', 0, 255);
     *   4. the name is unique: $existing = $this->dao->get_by_name(trim($category['name']));
     *      If $existing is found AND $existing['id'] != $id, throw:
     *        new ValidationException("A category named '{$category['name']}' already exists.")
     *      (The id check lets a category keep its own name when it is updated.)
     *   ClubService::validate() is a finished example.
     */
    protected function validate($category, $id = null)
    {
        Validator::required($category, ['name']);
        Validator::length($category['name'], 'name', 2, 60);
        if (!empty($category['description'])) {
            Validator::length($category['description'], 'description', 0, 255);
        }

        $existing = $this->dao->get_by_name(trim($category['name']));
        if ($existing && $existing['id'] != $id) {
            throw new ValidationException("A category named '{$category['name']}' already exists.");
        }
    }
}
