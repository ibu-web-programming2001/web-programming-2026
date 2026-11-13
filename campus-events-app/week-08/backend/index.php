<?php
/*
 * Entry point of the REST API.
 *
 *   request -> [middleware] -> routes -> services -> dao -> MySQL
 */
require __DIR__ . '/vendor/autoload.php';

if (!file_exists(__DIR__ . '/config/config.php')) {
    http_response_code(500);
    exit('Missing backend/config/config.php – copy config.example.php to config.php and set your values.');
}

require_once __DIR__ . '/rest/services/CategoryService.php';
require_once __DIR__ . '/rest/services/ClubService.php';
require_once __DIR__ . '/rest/services/EventService.php';
require_once __DIR__ . '/rest/services/RegistrationService.php';
require_once __DIR__ . '/rest/services/ReviewService.php';
require_once __DIR__ . '/rest/services/UserService.php';
require_once __DIR__ . '/rest/services/DashboardService.php';
require_once __DIR__ . '/rest/services/OpenMeteoService.php';
require_once __DIR__ . '/rest/services/AuthService.php';
require_once __DIR__ . '/rest/services/EventServicev2.php';          // week 05 demo routes
require_once __DIR__ . '/rest/services/OpenMeteoServicev1.php';   // week 06 demo route

require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/middleware/RequestLogMiddleware.php';
require_once __DIR__ . '/middleware/ErrorMiddleware.php';
require_once __DIR__ . '/middleware/RoleMiddleware.php';
require_once __DIR__ . '/utils/Roles.php';

// Services: Flight::event_service()->...
Flight::register('category_service', 'CategoryService');
Flight::register('club_service', 'ClubService');
Flight::register('event_service', 'EventService');
Flight::register('registration_service', 'RegistrationService');
Flight::register('review_service', 'ReviewService');
Flight::register('user_service', 'UserService');
Flight::register('dashboard_service', 'DashboardService');
Flight::register('open_meteo_service', 'OpenMeteoService');
Flight::register('auth_service', 'AuthService');
Flight::register('event_service_v2', 'EventServicev2');
Flight::register('open_meteo_service_v1', 'OpenMeteoServicev1');

/* ---------- Global middleware ---------- */

// 1. Request logging: every request -> logs/info.log
$request_logger = new RequestLogMiddleware();
Flight::before('start', function () use ($request_logger) {
    $request_logger->before();
});

// 2. Error handling: every unhandled exception -> JSON response (+ logs/error.log)
$error_handler = new ErrorMiddleware();
Flight::map('error', [$error_handler, 'handle']);
Flight::map('notFound', [$error_handler, 'not_found']);

/* ---------- Public routes (no token needed) ---------- */

Flight::route('GET /', function () {
    Flight::json(['app' => 'CampusEvents API', 'message' => 'The backend is running.', 'docs' => 'public/v1/docs/']);
});
require_once __DIR__ . '/rest/routes/AuthRoutes.php';

// Authentication VERSION 1 – the token is checked by hand INSIDE the route (see the file)
require_once __DIR__ . '/rest/routes/AuthDemoRoutesv1.php';

/* ---------- Protected routes – Authentication VERSION 2 (FINAL) ----------
 * AuthMiddleware runs before EVERY route in this group: written once, impossible to forget.
 * Admin-only routes additionally have  ->addMiddleware(new RoleMiddleware([Roles::ADMIN]))  (Authorization VERSION 2)
 */
Flight::group('', function () {
    // Authorization VERSION 1 – role checked by hand inside the route
    require_once __DIR__ . '/rest/routes/RoleDemoRoutesv1.php';

    // week 05 demo versions – they change events, so from now on only admins may use them
    Flight::group('', function () {
        require_once __DIR__ . '/rest/routes/EventRoutesv1.php';
        Flight::group('/v2', function () {
            require __DIR__ . '/rest/routes/EventRoutesv2.php';
        });
    }, [new RoleMiddleware([Roles::ADMIN])]);

    require_once __DIR__ . '/rest/routes/CategoryRoutes.php';
    require_once __DIR__ . '/rest/routes/ClubRoutes.php';
    require_once __DIR__ . '/rest/routes/EventRoutes.php';
    require_once __DIR__ . '/rest/routes/RegistrationRoutes.php';
    require_once __DIR__ . '/rest/routes/ReviewRoutes.php';
    require_once __DIR__ . '/rest/routes/UserRoutes.php';
    require_once __DIR__ . '/rest/routes/DashboardRoutes.php';
    require_once __DIR__ . '/rest/routes/WeatherRoutes.php';
}, [new AuthMiddleware()]);

Flight::start();
