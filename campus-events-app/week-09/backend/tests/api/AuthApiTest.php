<?php

class AuthApiTest extends ApiTestCase
{
    public function test_protected_endpoint_without_token_gives_401()
    {
        $response = $this->request('GET', '/events');

        $this->assertSame(401, $response['status']);
        $this->assertArrayHasKey('error', $response['body']);
    }

    public function test_invalid_token_gives_401()
    {
        $response = $this->request('GET', '/events', null, 'not.a.token');
        $this->assertSame(401, $response['status']);
    }

    public function test_login_with_wrong_password_gives_401()
    {
        $response = $this->request('POST', '/auth/login', ['email' => 'admin@campusevents.ba', 'password' => 'wrong']);
        $this->assertSame(401, $response['status']);
    }

    public function test_login_returns_user_without_password_and_token()
    {
        $response = $this->request('POST', '/auth/login', ['email' => 'admin@campusevents.ba', 'password' => 'Admin123!']);

        $this->assertSame(200, $response['status']);
        $this->assertSame('admin', $response['body']['user']['role']);
        $this->assertArrayNotHasKey('password', $response['body']['user']);
        $this->assertCount(3, explode('.', $response['body']['token']));   // header.payload.signature
    }

    public function test_me_returns_the_logged_in_user()
    {
        $response = $this->request('GET', '/auth/me', null, $this->token('student'));

        $this->assertSame(200, $response['status']);
        $this->assertSame('lana@campusevents.ba', $response['body']['email']);
    }

    public function test_unknown_endpoint_gives_json_404()
    {
        $response = $this->request('GET', '/does-not-exist', null, $this->token('admin'));

        $this->assertSame(404, $response['status']);
        $this->assertArrayHasKey('error', $response['body']);
    }
}
