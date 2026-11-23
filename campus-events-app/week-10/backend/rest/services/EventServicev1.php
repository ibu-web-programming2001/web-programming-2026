<?php
require_once __DIR__ . '/../dao/EventDao.php';

/**
 * VERSION 1 – a service that only passes calls through to the DAO.
 *
 * Problem: NO business rules. Whatever the client sends is saved:
 * an empty title, 500 free seats in a room for 30, an event in the past, a non-existing club...
 */
class EventServicev1
{
    private $dao;

    public function __construct()
    {
        $this->dao = new EventDao();
    }

    public function get_all()
    {
        return $this->dao->get_all();
    }

    public function get_by_id($id)
    {
        return $this->dao->get_by_id($id);
    }

    public function add($event)
    {
        return $this->dao->add($event);
    }

    public function update($event, $id)
    {
        return $this->dao->update($event, $id);
    }

    public function delete($id)
    {
        return $this->dao->delete($id);
    }
}
