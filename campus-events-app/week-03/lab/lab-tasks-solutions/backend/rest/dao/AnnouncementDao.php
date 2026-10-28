<?php
require_once __DIR__ . '/BaseDao.php';

/** Lab 03 – Task 6 (SOLUTION): DAO for the new announcements table. */
class AnnouncementDao extends BaseDao
{
    /** Announcement columns + the name of the club that posted it. */
    private const SELECT = "
        SELECT a.*, cl.name AS club_name
        FROM announcements a
        JOIN clubs cl ON cl.id = a.club_id";

    public function __construct()
    {
        parent::__construct('announcements', ['club_id', 'title', 'body', 'is_pinned']);
    }

    /** Pinned announcements first, then the newest. */
    public function get_all()
    {
        return $this->query(self::SELECT . " ORDER BY a.is_pinned DESC, a.created_at DESC, a.id DESC");
    }

    public function get_by_id($id)
    {
        return $this->query_unique(self::SELECT . " WHERE a.id = :id", ['id' => $id]);
    }

    public function get_by_club($club_id)
    {
        return $this->query(
            self::SELECT . " WHERE a.club_id = :club_id ORDER BY a.is_pinned DESC, a.created_at DESC, a.id DESC",
            ['club_id' => $club_id]
        );
    }

    /** The $limit newest announcements (for the dashboard). */
    public function get_latest($limit = 5)
    {
        return $this->query(self::SELECT . " ORDER BY a.created_at DESC, a.id DESC LIMIT " . (int) $limit);
    }
}
