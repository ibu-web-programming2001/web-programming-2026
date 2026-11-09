<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/AnnouncementDao.php';
require_once __DIR__ . '/../dao/ClubDao.php';

class AnnouncementService extends BaseService
{
    protected $entity_name = 'Announcement';
    private $club_dao;

    public function __construct($dao = null, $club_dao = null)
    {
        parent::__construct($dao ?? new AnnouncementDao());
        $this->club_dao = $club_dao ?? new ClubDao();
    }

    /** All announcements of one club (AnnouncementDao::get_by_club() from Lab 3). */
    public function get_by_club($club_id)
    {
        if (!$this->club_dao->get_by_id($club_id)) {
            throw new NotFoundException("Club not found.");
        }
        return $this->dao->get_by_club($club_id);
    }

    /** The newest announcements for the dashboard – at most 20 at once. */
    public function get_latest($limit = 5)
    {
        Validator::integer($limit, 'limit', 1, 20);
        return $this->dao->get_latest($limit);
    }

    public function add($announcement)
    {
        return parent::add($this->normalize($announcement));
    }

    public function update($announcement, $id)
    {
        return parent::update($this->normalize($announcement), $id);
    }

    /*
     * TODO Task 8 – validate($announcement, $id = null)
     *   BaseService calls validate() in add() and update(). Check, in this order:
     *   1. Validator::required($announcement, ['club_id', 'title', 'body']);
     *   2. the club exists: if $this->club_dao->get_by_id($announcement['club_id']) finds nothing, throw:
     *        new ValidationException("The selected club does not exist.")
     *      Keep the club in a variable ($club) – step 5 needs its name.
     *   3. the title has 3–150 characters: Validator::length(..., 'title', 3, 150);
     *      the body has 10–2000 characters: Validator::length(..., 'body', 10, 2000);
     *   4. neither the title nor the body contains HTML: Validator::no_html(..., 'title'); Validator::no_html(..., 'body');  (Task 2.2)
     *   5. NEW rule – a club can have only ONE pinned announcement. Only when $announcement['is_pinned'] is 1:
     *        go through $this->dao->get_by_club($announcement['club_id']) and if one of them is pinned
     *        AND its id != $id, throw:
     *        new ValidationException("'{$club['name']}' already has a pinned announcement. Unpin it first.")
     *      (The id check lets the pinned announcement itself be edited.)
     */
    protected function validate($announcement, $id = null)
    {
        Validator::required($announcement, ['club_id', 'title', 'body']);
        $club = $this->club_dao->get_by_id($announcement['club_id']);
        if (!$club) {
            throw new ValidationException("The selected club does not exist.");
        }
        Validator::length($announcement['title'], 'title', 3, 150);
        Validator::length($announcement['body'], 'body', 10, 2000);
        Validator::no_html($announcement['title'], 'title');
        Validator::no_html($announcement['body'], 'body');

        // Rule: one pinned announcement per club
        if ($announcement['is_pinned'] == 1) {
            foreach ($this->dao->get_by_club($announcement['club_id']) as $other) {
                if ($other['is_pinned'] && $other['id'] != $id) {
                    throw new ValidationException("'{$club['name']}' already has a pinned announcement. Unpin it first.");
                }
            }
        }
    }

    /** is_pinned comes from a checkbox: anything "truthy" becomes 1, everything else 0. */
    private function normalize($announcement)
    {
        $announcement['is_pinned'] = !empty($announcement['is_pinned']) ? 1 : 0;
        return $announcement;
    }
}
