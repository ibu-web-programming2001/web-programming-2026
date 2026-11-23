<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../rest/services/EventService.php';

/**
 * Unit tests for EventService.
 * The DAOs are replaced by MOCKS: fake objects whose answers we decide in the test.
 * -> no database needed, tests run in milliseconds, and we test ONLY the business rules.
 */
class EventServiceTest extends TestCase
{
    private $event_dao;
    private $club_dao;
    private $category_dao;
    private $registration_dao;
    private $service;

    protected function setUp(): void
    {
        $this->event_dao = $this->createMock(EventDao::class);
        $this->club_dao = $this->createMock(ClubDao::class);
        $this->category_dao = $this->createMock(CategoryDao::class);
        $this->registration_dao = $this->createMock(RegistrationDao::class);

        // By default the club and category exist
        $this->club_dao->method('get_by_id')->willReturn(['id' => 2, 'name' => 'Google Developer Student Club']);
        $this->category_dao->method('get_by_id')->willReturn(['id' => 2, 'name' => 'Tech']);

        $this->service = new EventService($this->event_dao, $this->club_dao, $this->category_dao, $this->registration_dao,
                                          $this->createMock(OpenMeteoService::class));
    }

    private function valid_event($changes = [])
    {
        return array_merge([
            'title' => 'Intro to Docker Workshop',
            'club_id' => 2,
            'category_id' => 2,
            'location' => 'FENS Lab 1',
            'starts_at' => date('Y-m-d', strtotime('+30 days')) . 'T17:00',   // HTML datetime-local format
            'capacity' => 25
        ], $changes);
    }

    public function test_new_event_has_all_seats_free_and_a_clean_date()
    {
        $this->event_dao->expects($this->once())
            ->method('add')
            ->with($this->callback(function ($event) {
                return $event['available_seats'] === 25 && preg_match('/^\d{4}-\d{2}-\d{2} 17:00:00$/', $event['starts_at']) === 1;
            }))
            ->willReturn(['id' => 99]);
        $this->event_dao->method('get_by_id')->willReturn(['id' => 99, 'title' => 'Intro to Docker Workshop']);

        $event = $this->service->add($this->valid_event(['available_seats' => 1000]));

        $this->assertSame(99, $event['id']);
    }

    public function test_event_of_an_unknown_club_is_rejected()
    {
        $club_dao = $this->createMock(ClubDao::class);
        $club_dao->method('get_by_id')->willReturn(null);
        $service = new EventService($this->event_dao, $club_dao, $this->category_dao, $this->registration_dao,
                                    $this->createMock(OpenMeteoService::class));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The selected club does not exist.');

        $service->add($this->valid_event());
    }

    public function test_new_event_in_the_past_is_rejected()
    {
        $this->event_dao->expects($this->never())->method('add');   // nothing may be saved

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('must start in the future');
        $this->service->add($this->valid_event(['starts_at' => '2020-01-01 10:00']));
    }

    public function test_capacity_cannot_drop_below_taken_seats()
    {
        $this->event_dao->method('get_by_id')->willReturn(['id' => 1]);
        $this->registration_dao->method('count_taken_by_event')->willReturn(2);

        $this->expectException(ValidationException::class);
        $this->service->update($this->valid_event(['capacity' => 1]), 1);
    }

    public function test_update_recalculates_available_seats()
    {
        $this->event_dao->method('get_by_id')->willReturn(['id' => 1]);
        $this->registration_dao->method('count_taken_by_event')->willReturn(2);
        $this->event_dao->expects($this->once())
            ->method('update')
            ->with($this->callback(fn($event) => $event['available_seats'] === 3), 1);

        $this->service->update($this->valid_event(['capacity' => 5]), 1);
    }

    public function test_event_with_registrations_cannot_be_deleted()
    {
        $this->event_dao->method('get_by_id')->willReturn(['id' => 1]);
        $this->registration_dao->method('count_taken_by_event')->willReturn(1);
        $this->event_dao->expects($this->never())->method('delete');

        $this->expectException(ValidationException::class);
        $this->service->delete(1);
    }

    public function test_missing_event_gives_404()
    {
        $this->event_dao->method('get_by_id')->willReturn(null);

        try {
            $this->service->get_by_id(999);
            $this->fail('Exception expected');
        } catch (NotFoundException $e) {
            $this->assertSame(404, $e->get_status());
        }
    }
}
