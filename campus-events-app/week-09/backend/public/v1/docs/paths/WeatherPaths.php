<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/WeatherRoutes.php.
 * swagger-php reads the #[OA\...] attributes; the methods are empty – they only carry the documentation.
 * Keep it in sync with the routes: one method per endpoint.
 */
class WeatherPaths
{
    #[OA\Get(
        path: '/weather/forecast',
        tags: ['weather'],
        summary: 'Daily weather forecast for a city (Open-Meteo)',
        parameters: [
            new OA\Parameter(
                name: 'city',
                in: 'query',
                required: true,
                description: 'City name',
                example: 'Mostar',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'days',
                in: 'query',
                required: false,
                description: 'Number of days (1–16, default 7)',
                example: 7,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Forecast',
                content: new OA\JsonContent(ref: '#/components/schemas/Forecast')
            ),
            new OA\Response(
                response: 400,
                description: 'City name too short or invalid days',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Unknown city',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 503,
                description: 'Open-Meteo not available',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function getWeatherForecast() {}
}
