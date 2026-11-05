<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/EventRoutes.php.
 * swagger-php reads the #[OA\...] attributes; the methods are empty – they only carry the documentation.
 * Keep it in sync with the routes: one method per endpoint.
 */
class EventPaths
{
    #[OA\Get(
        path: '/events',
        tags: ['events'],
        summary: 'List all events (with club, category and rating)',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of events',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Event'))
            )
        ]
    )]
    public function getEvents() {}

    #[OA\Get(
        path: '/events/{id}',
        tags: ['events'],
        summary: 'Get one event',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                example: 1,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The event',
                content: new OA\JsonContent(ref: '#/components/schemas/Event')
            ),
            new OA\Response(
                response: 404,
                description: 'Event not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function getEventsById() {}

    #[OA\Get(
        path: '/events/{id}/reviews',
        tags: ['events', 'reviews'],
        summary: 'All reviews of one event',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                example: 9,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reviews',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Review'))
            ),
            new OA\Response(
                response: 404,
                description: 'Event not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function getEventsByIdReviews() {}

    #[OA\Get(
        path: '/events/{id}/weather',
        tags: ['events', 'weather'],
        summary: 'Weather forecast for the day of the event',
        description: 'Uses Open-Meteo for the city of the event. Forecasts exist only up to 16 days ahead – otherwise available=false.',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                example: 1,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Weather on the event day',
                content: new OA\JsonContent(ref: '#/components/schemas/EventWeather')
            ),
            new OA\Response(
                response: 404,
                description: 'Event not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 503,
                description: 'Open-Meteo not available',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function getEventsByIdWeather() {}

    #[OA\Post(
        path: '/events',
        tags: ['events'],
        summary: 'Create an event',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/EventInput')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created event',
                content: new OA\JsonContent(ref: '#/components/schemas/Event')
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid data',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function postEvents() {}

    #[OA\Put(
        path: '/events/{id}',
        tags: ['events'],
        summary: 'Update an event',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                example: 5,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/EventInput')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated event',
                content: new OA\JsonContent(ref: '#/components/schemas/Event')
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid data',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Event not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function putEventsById() {}

    #[OA\Delete(
        path: '/events/{id}',
        tags: ['events'],
        summary: 'Delete an event',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                example: 5,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Deleted',
                content: new OA\JsonContent(ref: '#/components/schemas/Message')
            ),
            new OA\Response(
                response: 400,
                description: 'Students are registered for this event',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Event not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function deleteEventsById() {}
}
