<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/DashboardRoutes.php.
 * swagger-php reads the #[OA\...] attributes; the methods are empty – they only carry the documentation.
 * Keep it in sync with the routes: one method per endpoint.
 */
class DashboardPaths
{
    #[OA\Get(
        path: '/dashboard',
        tags: ['dashboard'],
        summary: 'Statistics for the dashboard',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Counters, registrations per category and events starting soon',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'events', type: 'integer', example: 12),
                        new OA\Property(property: 'upcoming_events', type: 'integer', example: 10),
                        new OA\Property(property: 'clubs', type: 'integer', example: 6),
                        new OA\Property(property: 'students', type: 'integer', example: 3),
                        new OA\Property(property: 'upcoming_registrations', type: 'integer', example: 7),
                        new OA\Property(property: 'attended', type: 'integer', example: 3),
                        new OA\Property(
                            property: 'registrations_per_category',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'category', type: 'string', example: 'Music'),
                                    new OA\Property(property: 'total', type: 'integer', example: 4)
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'starting_soon',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Event')
                        )
                    ]
                )
            )
        ]
    )]
    public function getDashboard() {}
}
