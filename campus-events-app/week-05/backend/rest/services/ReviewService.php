<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ReviewDao.php';
require_once __DIR__ . '/../dao/EventDao.php';

class ReviewService extends BaseService
{
    protected $entity_name = 'Review';
    private $event_dao;

    public function __construct($dao = null, $event_dao = null)
    {
        parent::__construct($dao ?? new ReviewDao());
        $this->event_dao = $event_dao ?? new EventDao();
    }

    public function get_by_event($event_id)
    {
        if (!$this->event_dao->get_by_id($event_id)) {
            throw new NotFoundException("Event not found.");
        }
        return $this->dao->get_by_event($event_id);
    }

    public function add($review)
    {
        Validator::required($review, ['user_id', 'event_id']);
        if (!$this->event_dao->get_by_id($review['event_id'])) {
            throw new ValidationException("The selected event does not exist.");
        }
        // Rule: one review per event per student
        if ($this->dao->get_by_user_and_event($review['user_id'], $review['event_id'])) {
            throw new ValidationException("You have already reviewed this event. Edit your existing review instead.");
        }
        return parent::add($this->empty_to_null($review, ['comment']));
    }

    /** Only rating and comment can be changed – not the event or the author of the review. */
    public function update($review, $id)
    {
        $review = $this->empty_to_null($review, ['comment']);
        $changes = ['rating' => $review['rating'] ?? null, 'comment' => $review['comment'] ?? null];
        return parent::update($changes, $id);
    }

    protected function validate($review, $id = null)
    {
        Validator::required($review, ['rating']);
        Validator::integer($review['rating'], 'rating', 1, 5);
        if (!empty($review['comment'])) {
            Validator::length($review['comment'], 'comment', 0, 1000);
        }
    }
}
