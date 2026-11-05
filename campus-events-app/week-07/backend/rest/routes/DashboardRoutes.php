<?php
/**
 * VERSION 3 (FINAL) – PRESENTATION LAYER – dashboard statistics
 */

Flight::route('GET /dashboard', function () {
    Flight::json(Flight::dashboard_service()->get_stats());
});
