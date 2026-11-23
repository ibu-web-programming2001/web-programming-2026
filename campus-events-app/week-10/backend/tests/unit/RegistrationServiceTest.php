<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../rest/services/RegistrationService.php';

class RegistrationServiceTest extends TestCase
{
    private $registration_dao;
    private $event_dao;
    private $user_dao;
    private $service;

    private $student = ['id' => 3, 'role' => 'student'];
    private $admin = ['id' => 1, 'role' => 'admin'];

    protected function setUp(): void
    {
        $this->registration_dao = $this->createMock(RegistrationDao::class);
        $this->event_dao = $this->createMock(EventDao::class);
        $this->user_dao = $this->createMock(UserDao::class);

        $this->user_dao->method('get_by_id')->willReturn(['id' => 3]);
        $this->event_dao->method('get_by_id')->willReturn([
            'id' => 7, 'title' => 'Hackathon', 'available_seats' => 2, 'starts_at' => date('Y-m-d H:i:s', strtotime('+5 days'))
        ]);

        $this->service = new RegistrationService($this->registration_dao, $this->event_dao, $this->user_dao);
    }

    /** A registration for an event that starts in 5 days, owned by user 2. */
    private function registration_of_user_2()
    {
        return ['id' => 1, 'user_id' => 2, 'event_id' => 7, 'status' => 'registered',
                'event_starts_at' => date('Y-m-d H:i:s', strtotime('+5 days'))];
    }

    public function test_registering_creates_a_registration_and_takes_one_seat()
    {
        $this->registration_dao->method('count_upcoming_by_user')->willReturn(0);
        $this->registration_dao->method('add')->willReturn(['id' => 50]);
        $this->registration_dao->method('get_by_id')->willReturn(['id' => 50, 'status' => 'registered']);
        $this->event_dao->expects($this->once())->method('change_available_seats')->with(7, -1)->willReturn(1);
        $this->registration_dao->expects($this->once())->method('commit');

        $registration = $this->service->register(3, 7);

        $this->assertSame(50, $registration['id']);
    }

    public function test_a_student_cannot_have_more_than_three_upcoming_registrations()
    {
        $this->registration_dao->method('count_upcoming_by_user')->willReturn(3);
        $this->registration_dao->expects($this->never())->method('add');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('at most 3 upcoming registrations');

        $this->service->register(3, 7);
    }

    public function test_a_sold_out_event_cannot_be_booked()
    {
        $event_dao = $this->createMock(EventDao::class);
        $event_dao->method('get_by_id')->willReturn([
            'id' => 7, 'title' => 'Hackathon', 'available_seats' => 0, 'starts_at' => date('Y-m-d H:i:s', strtotime('+5 days'))
        ]);
        $service = new RegistrationService($this->registration_dao, $event_dao, $this->user_dao);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('sold out');
        $service->register(3, 7);
    }

    public function test_a_past_event_cannot_be_booked()
    {
        $event_dao = $this->createMock(EventDao::class);
        $event_dao->method('get_by_id')->willReturn([
            'id' => 7, 'title' => 'Hackathon', 'available_seats' => 10, 'starts_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ]);
        $service = new RegistrationService($this->registration_dao, $event_dao, $this->user_dao);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('registration is closed');
        $service->register(3, 7);
    }

    public function test_transaction_is_rolled_back_when_the_seat_is_gone()
    {
        // Another student took the last seat between our check and our UPDATE
        $this->registration_dao->method('count_upcoming_by_user')->willReturn(0);
        $this->registration_dao->method('add')->willReturn(['id' => 50]);
        $this->event_dao->method('change_available_seats')->willReturn(0);
        $this->registration_dao->expects($this->once())->method('rollback');
        $this->registration_dao->expects($this->never())->method('commit');

        $this->expectException(ValidationException::class);
        $this->service->register(3, 7);
    }

    public function test_students_always_register_themselves()
    {
        $this->registration_dao->method('count_upcoming_by_user')->willReturn(0);
        $this->registration_dao->expects($this->once())
            ->method('add')
            ->with($this->callback(fn($registration) => $registration['user_id'] === 3))
            ->willReturn(['id' => 50]);
        $this->event_dao->method('change_available_seats')->willReturn(1);

        // The student tries to register in the name of user 2
        $this->service->add(['user_id' => 2, 'event_id' => 7], $this->student);
    }

    public function test_a_student_cannot_cancel_someone_elses_registration()
    {
        $this->registration_dao->method('get_by_id')->willReturn($this->registration_of_user_2());

        $this->expectException(ForbiddenException::class);
        $this->service->cancel(1, $this->student);
    }

    public function test_an_admin_can_cancel_any_registration()
    {
        $this->registration_dao->method('get_by_id')->willReturn($this->registration_of_user_2());
        $this->event_dao->expects($this->once())->method('change_available_seats')->with(7, 1);

        $this->service->cancel(1, $this->admin);
    }
}
