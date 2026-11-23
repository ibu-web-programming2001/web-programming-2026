<?php
/**
 * PRESENTATION LAYER – authentication
 * register and login are PUBLIC; /auth/me needs a valid token (route-level middleware).
 */

Flight::route('POST /auth/register', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::auth_service()->register($data), 201);
});

Flight::route('POST /auth/login', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::auth_service()->login($data));
});

Flight::route('GET /auth/me', function () {
    Flight::json(Flight::auth_service()->get_profile(Flight::get('user')['id']));
})->addMiddleware(new AuthMiddleware());

Flight::route('PUT /auth/me', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::auth_service()->update_profile(Flight::get('user')['id'], $data));
})->addMiddleware(new AuthMiddleware());

/* ---------- Two-factor authentication (TOTP) – optional ---------- */

Flight::route('POST /auth/2fa/setup', function () {
    Flight::json(Flight::auth_service()->setup_two_factor(Flight::get('user')['id']));
})->addMiddleware(new AuthMiddleware());

Flight::route('POST /auth/2fa/enable', function () {
    $code = Flight::request()->data['code'] ?? '';
    Flight::json(Flight::auth_service()->enable_two_factor(Flight::get('user')['id'], $code));
})->addMiddleware(new AuthMiddleware());

Flight::route('POST /auth/2fa/disable', function () {
    $code = Flight::request()->data['code'] ?? '';
    Flight::json(Flight::auth_service()->disable_two_factor(Flight::get('user')['id'], $code));
})->addMiddleware(new AuthMiddleware());

/* ---------- Single Sign-On with Google – optional ---------- */

Flight::route('POST /auth/google', function () {
    $id_token = Flight::request()->data['id_token'] ?? '';
    Flight::json(Flight::auth_service()->login_with_google($id_token));
});
