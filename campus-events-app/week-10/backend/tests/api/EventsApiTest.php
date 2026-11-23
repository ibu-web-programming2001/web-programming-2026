<?php

class EventsApiTest extends ApiTestCase
{
    public function test_student_can_list_events()
    {
        $response = $this->request('GET', '/events', null, $this->token('student'));

        $this->assertSame(200, $response['status']);
        $this->assertNotEmpty($response['body']);
        $this->assertArrayHasKey('club_name', $response['body'][0]);
    }

    public function test_student_cannot_create_an_event()
    {
        $response = $this->request('POST', '/events', [
            'title' => 'X', 'club_id' => 1, 'category_id' => 1, 'location' => 'Hall', 'starts_at' => '2030-01-01 10:00', 'capacity' => 10
        ], $this->token('student'));
        $this->assertSame(403, $response['status']);
    }

    public function test_invalid_event_gives_400()
    {
        $response = $this->request('POST', '/events', [
            'title' => '', 'club_id' => 1, 'category_id' => 1, 'location' => 'Hall', 'starts_at' => '2030-01-01 10:00', 'capacity' => 10
        ], $this->token('admin'));

        $this->assertSame(400, $response['status']);
        $this->assertStringContainsString('title', $response['body']['error']);
    }

    public function test_missing_event_gives_404()
    {
        $response = $this->request('GET', '/events/999999', null, $this->token('student'));
        $this->assertSame(404, $response['status']);
    }

    /** A complete CRUD cycle – it cleans up after itself. */
    public function test_admin_can_create_update_and_delete_an_event()
    {
        $token = $this->token('admin');
        $event = ['title' => 'Test Driven Workshop', 'club_id' => 2, 'category_id' => 2, 'location' => 'FENS Lab 1',
                  'starts_at' => '2030-03-01 17:00', 'capacity' => 20];

        $created = $this->request('POST', '/events', $event, $token);
        $this->assertSame(201, $created['status']);
        $this->assertSame(20, $created['body']['available_seats']);
        $id = $created['body']['id'];

        $updated = $this->request('PUT', "/events/$id", array_merge($event, ['title' => 'Test Driven Workshop II', 'capacity' => 40]), $token);
        $this->assertSame(200, $updated['status']);
        $this->assertSame('Test Driven Workshop II', $updated['body']['title']);
        $this->assertSame(40, $updated['body']['available_seats']);

        $deleted = $this->request('DELETE', "/events/$id", null, $token);
        $this->assertSame(200, $deleted['status']);

        $this->assertSame(404, $this->request('GET', "/events/$id", null, $token)['status']);
    }
}
