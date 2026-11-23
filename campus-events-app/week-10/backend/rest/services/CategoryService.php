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

    /** Rule: a category that still contains events cannot be deleted. */
    public function delete($id)
    {
        $this->get_by_id($id);
        $events = $this->event_dao->count_by_category($id);
        if ($events > 0) {
            throw new ValidationException("This category still contains $events event(s). Move or delete them first.");
        }
        $this->dao->delete($id);
    }

    protected function validate($category, $id = null)
    {
        Validator::required($category, ['name']);
        Validator::length($category['name'], 'name', 2, 60);
        if (!empty($category['description'])) {
            Validator::length($category['description'], 'description', 0, 255);
        }

        // Rule: category names are unique
        $existing = $this->dao->get_by_name(trim($category['name']));
        if ($existing && $existing['id'] != $id) {
            throw new ValidationException("A category named '{$category['name']}' already exists.");
        }
    }
}
