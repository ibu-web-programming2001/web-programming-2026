<?php
/**
 * PRESENTATION LAYER – dashboard statistics
 */

Flight::route('GET /dashboard', function () {
    Flight::json(Flight::dashboard_service()->get_for_user(Flight::get('user')));
});
