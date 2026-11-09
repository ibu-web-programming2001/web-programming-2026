<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ReviewDao.php';
require_once __DIR__ . '/../dao/EventDao.php';
require_once __DIR__ . '/../dao/RegistrationDao.php';

class ReviewService extends BaseService
{
    protected $entity_name = 'Review';
    private $event_dao;
    private $registration_dao;

    public function __construct($dao = null, $event_dao = null, $registration_dao = null)
    {
        parent::__construct($dao ?? new ReviewDao());
        $this->event_dao = $event_dao ?? new EventDao();
        $this->registration_dao = $registration_dao ?? new RegistrationDao();
    }

    public function get_by_event($event_id)
    {
        if (!$this->event_dao->get_by_id($event_id)) {
            throw new NotFoundException("Event not found.");
        }
        return $this->dao->get_by_event($event_id);
    }

    /*
     * TODO Task 5.2 – add($review)
     *   Check, in this order:
     *   1. Validator::required($review, ['user_id', 'event_id']);
     *   2. The event exists: if $this->event_dao->get_by_id($review['event_id']) finds nothing, throw:
     *        new ValidationException("The selected event does not exist.")
     *   3. One review per event per student: if $this->dao->get_by_user_and_event($review['user_id'], $review['event_id'])
     *      finds a review, throw:
     *        new ValidationException("You have already reviewed this event. Edit your existing review instead.")
     *   4. NEW rule – only students who were at the event can review it:
     *      if $this->registration_dao->has_attended($review['user_id'], $review['event_id']) is false, throw:
     *        new ValidationException("You can only review events you attended.")
     *   5. Save it: return parent::add($this->empty_to_null($review, ['comment']));
     *      (parent::add() calls validate() from Task 5.1.)
     */
    public function add($review)
    {
        Validator::required($review, ['user_id', 'event_id']);
        if (!$this->event_dao->get_by_id($review['event_id'])) {
            throw new ValidationException("The selected event does not exist.");
        }
        if ($this->dao->get_by_user_and_event($review['user_id'], $review['event_id'])) {
            throw new ValidationException("You have already reviewed this event. Edit your existing review instead.");
        }
        if (!$this->registration_dao->has_attended($review['user_id'], $review['event_id'])) {
            throw new ValidationException("You can only review events you attended.");
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

    /*
     * TODO Task 5.1 – validate($review, $id = null)
     *   1. rating is required:                Validator::required($review, ['rating']);
     *   2. rating is a whole number 1–5:      Validator::integer($review['rating'], 'rating', 1, 5);
     *   3. if a comment is given (!empty):
     *        it has at most 1000 characters:  Validator::length($review['comment'], 'comment', 0, 1000);
     *        it contains no HTML:             Validator::no_html($review['comment'], 'comment');   (Task 2.2)
     */
    protected function validate($review, $id = null)
    {
        Validator::required($review, ['rating']);
        Validator::integer($review['rating'], 'rating', 1, 5);
        if (!empty($review['comment'])) {
            Validator::length($review['comment'], 'comment', 0, 1000);
            Validator::no_html($review['comment'], 'comment');
        }
    }
}
