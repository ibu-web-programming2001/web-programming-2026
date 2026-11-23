<?php

class RegistrationsApiTest extends ApiTestCase
{
    public function test_student_sees_only_own_registrations()
    {
        $response = $this->request('GET', '/registrations', null, $this->token('student'));

        $this->assertSame(200, $response['status']);
        foreach ($response['body'] as $registration) {
            $this->assertSame(4, $registration['user_id']);        // Lana has id 4
        }
    }

    public function test_student_cannot_open_someone_elses_registration()
    {
        // Registration 1 belongs to Ema (sample data)
        $response = $this->request('GET', '/registrations/1', null, $this->token('student'));
        $this->assertSame(403, $response['status']);
    }

    public function test_student_registers_and_cancels()
    {
        $token = $this->token('student');
        $before = $this->request('GET', '/events/12', null, $token)['body']['available_seats'];

        // user_id 2 is ignored – the registration belongs to the logged-in student
        $registration = $this->request('POST', '/registrations', ['user_id' => 2, 'event_id' => 12], $token);
        $this->assertSame(201, $registration['status']);
        $this->assertSame(4, $registration['body']['user_id']);
        $this->assertSame($before - 1, $this->request('GET', '/events/12', null, $token)['body']['available_seats']);

        $cancelled = $this->request('PUT', "/registrations/{$registration['body']['id']}/cancel", null, $token);
        $this->assertSame(200, $cancelled['status']);
        $this->assertSame('cancelled', $cancelled['body']['status']);
        $this->assertSame($before, $this->request('GET', '/events/12', null, $token)['body']['available_seats']);

        // clean up (admin only)
        $this->request('DELETE', "/registrations/{$registration['body']['id']}", null, $this->token('admin'));
    }

    public function test_student_cannot_delete_registrations()
    {
        $response = $this->request('DELETE', '/registrations/1', null, $this->token('student'));
        $this->assertSame(403, $response['status']);
    }
}
