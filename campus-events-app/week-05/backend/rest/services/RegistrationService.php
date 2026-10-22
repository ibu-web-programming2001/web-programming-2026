<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/RegistrationDao.php';
require_once __DIR__ . '/../dao/EventDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class RegistrationService extends BaseService
{
    const MAX_UPCOMING_REGISTRATIONS = 3;

    protected $entity_name = 'Registration';
    private $event_dao;
    private $user_dao;

    public function __construct($dao = null, $event_dao = null, $user_dao = null)
    {
        parent::__construct($dao ?? new RegistrationDao());
        $this->event_dao = $event_dao ?? new EventDao();
        $this->user_dao = $user_dao ?? new UserDao();
    }

    public function get_by_user($user_id)
    {
        return $this->dao->get_by_user($user_id);
    }

    /** Creating a registration = registering a student for an event. */
    public function add($registration)
    {
        Validator::required($registration, ['user_id', 'event_id']);
        return $this->register($registration['user_id'], $registration['event_id']);
    }

    /**
     * Register a student for an event. Rules:
     *  - the event must not have started yet
     *  - the event must have a free seat
     *  - a student can have at most MAX_UPCOMING_REGISTRATIONS upcoming registrations
     *  - a student cannot register for the same event twice
     */
    public function register($user_id, $event_id)
    {
        Validator::integer($user_id, 'user_id', 1);
        Validator::integer($event_id, 'event_id', 1);

        if (!$this->user_dao->get_by_id($user_id)) {
            throw new ValidationException("The selected student does not exist.");
        }
        $event = $this->event_dao->get_by_id($event_id);
        if (!$event) {
            throw new ValidationException("The selected event does not exist.");
        }
        if ($event['starts_at'] <= date('Y-m-d H:i:s')) {
            throw new ValidationException("'{$event['title']}' has already started – registration is closed.");
        }
        if ($event['available_seats'] < 1) {
            throw new ValidationException("'{$event['title']}' is sold out.");
        }
        if ($this->dao->get_active_by_user_and_event($user_id, $event_id)) {
            throw new ValidationException("This student is already registered for '{$event['title']}'.");
        }
        if ($this->dao->count_upcoming_by_user($user_id) >= self::MAX_UPCOMING_REGISTRATIONS) {
            throw new ValidationException("A student can have at most " . self::MAX_UPCOMING_REGISTRATIONS . " upcoming registrations. Cancel one first.");
        }

        // Two tables change together (registrations + events) -> transaction: all or nothing
        $this->dao->begin_transaction();
        try {
            $registration = $this->dao->add([
                'user_id' => $user_id,
                'event_id' => $event_id,
                'registered_at' => date('Y-m-d H:i:s'),
                'status' => 'registered'
            ]);
            if ($this->event_dao->change_available_seats($event_id, -1) === 0) {
                throw new ValidationException("'{$event['title']}' is sold out.");
            }
            $this->dao->commit();
        } catch (Throwable $e) {
            $this->dao->rollback();
            throw $e;
        }

        return $this->dao->get_by_id($registration['id']);
    }

    /** Cancel a registration before the event starts: the seat becomes free again. */
    public function cancel($id)
    {
        $registration = $this->get_by_id($id);
        if ($registration['status'] !== 'registered') {
            throw new ValidationException("Only active registrations can be cancelled (this one is '{$registration['status']}').");
        }
        if ($registration['event_starts_at'] <= date('Y-m-d H:i:s')) {
            throw new ValidationException("The event has already started – the registration cannot be cancelled.");
        }

        $this->dao->begin_transaction();
        try {
            $this->dao->update(['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s')], $id);
            $this->event_dao->change_available_seats($registration['event_id'], +1);
            $this->dao->commit();
        } catch (Throwable $e) {
            $this->dao->rollback();
            throw $e;
        }

        return $this->dao->get_by_id($id);
    }

    /** The student came to the event (checked in by an admin). The seat stays taken. */
    public function mark_attended($id)
    {
        $registration = $this->get_by_id($id);
        if ($registration['status'] !== 'registered') {
            throw new ValidationException("Only active registrations can be marked as attended (this one is '{$registration['status']}').");
        }
        $this->dao->update(['status' => 'attended'], $id);
        return $this->dao->get_by_id($id);
    }

    /** Only the status can be changed: 'cancelled' or 'attended'. */
    public function update($registration, $id)
    {
        Validator::required($registration, ['status']);
        Validator::one_of($registration['status'], 'status', ['cancelled', 'attended']);
        return $registration['status'] === 'cancelled' ? $this->cancel($id) : $this->mark_attended($id);
    }

    /** Deleting an active registration gives the seat back. */
    public function delete($id)
    {
        $registration = $this->get_by_id($id);

        $this->dao->begin_transaction();
        try {
            $this->dao->delete($id);
            if ($registration['status'] === 'registered') {
                $this->event_dao->change_available_seats($registration['event_id'], +1);
            }
            $this->dao->commit();
        } catch (Throwable $e) {
            $this->dao->rollback();
            throw $e;
        }
    }
}
