<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/CountryRoutes.php.
 * Schemas are defined here (next to the paths) to keep this example in one file.
 */
#[OA\Schema(
    schema: 'CountryShort',
    properties: [
        new OA\Property(property: 'code', type: 'string', example: 'BA'),
        new OA\Property(property: 'name', type: 'string', example: 'Bosnia and Herzegovina'),
        new OA\Property(property: 'capital', type: 'string', example: 'Sarajevo'),
        new OA\Property(property: 'region', type: 'string', example: 'Europe'),
        new OA\Property(property: 'flag', type: 'string', example: '🇧🇦'),
        new OA\Property(property: 'languages', type: 'array', items: new OA\Items(type: 'string'), example: ['Bosnian', 'Croatian', 'Serbian'])
    ]
)]
#[OA\Schema(
    schema: 'Country',
    properties: [
        new OA\Property(property: 'code', type: 'string', example: 'BA'),
        new OA\Property(property: 'code3', type: 'string', example: 'BIH'),
        new OA\Property(property: 'name', type: 'string', example: 'Bosnia and Herzegovina'),
        new OA\Property(property: 'official_name', type: 'string', example: 'Bosnia and Herzegovina'),
        new OA\Property(property: 'capital', type: 'string', example: 'Sarajevo'),
        new OA\Property(property: 'region', type: 'string', example: 'Europe'),
        new OA\Property(property: 'subregion', type: 'string', example: 'Southeast Europe'),
        new OA\Property(property: 'flag', type: 'string', example: '🇧🇦'),
        new OA\Property(property: 'flag_url', type: 'string', example: 'https://flagcdn.com/ba.svg'),
        new OA\Property(property: 'languages', type: 'array', items: new OA\Items(ref: '#/components/schemas/Language')),
        new OA\Property(
            property: 'currencies',
            type: 'array',
            items: new OA\Items(properties: [
                new OA\Property(property: 'code', type: 'string', example: 'BAM'),
                new OA\Property(property: 'name', type: 'string', example: 'Bosnia and Herzegovina convertible mark'),
                new OA\Property(property: 'symbol', type: 'string', example: 'KM')
            ])
        ),
        new OA\Property(property: 'calling_code', type: 'string', example: '+387'),
        new OA\Property(property: 'area_km2', type: 'number', example: 51209),
        new OA\Property(property: 'landlocked', type: 'boolean', example: false),
        new OA\Property(property: 'borders', type: 'array', items: new OA\Items(type: 'string'), example: ['HRV', 'MNE', 'SRB']),
        new OA\Property(property: 'latitude', type: 'number', example: 44),
        new OA\Property(property: 'longitude', type: 'number', example: 18)
    ]
)]
#[OA\Schema(
    schema: 'Language',
    properties: [
        new OA\Property(property: 'code', type: 'string', example: 'bos'),
        new OA\Property(property: 'name', type: 'string', example: 'Bosnian'),
        new OA\Property(property: 'country_count', type: 'integer', example: 1, description: 'Only in GET /countries/languages')
    ]
)]
#[OA\Schema(
    schema: 'GeoLocation',
    properties: [
        new OA\Property(property: 'ip', type: 'string', example: '8.8.8.8'),
        new OA\Property(property: 'type', type: 'string', example: 'IPv4'),
        new OA\Property(property: 'city', type: 'string', example: 'Mountain View'),
        new OA\Property(property: 'region', type: 'string', example: 'California'),
        new OA\Property(property: 'country', type: 'string', example: 'United States'),
        new OA\Property(property: 'country_code', type: 'string', example: 'US'),
        new OA\Property(property: 'latitude', type: 'number', example: 37.39),
        new OA\Property(property: 'longitude', type: 'number', example: -122.08),
        new OA\Property(property: 'timezone', type: 'string', example: 'America/Los_Angeles'),
        new OA\Property(property: 'isp', type: 'string', example: 'Google LLC'),
        new OA\Property(property: 'country_info', ref: '#/components/schemas/Country', nullable: true)
    ]
)]
class CountryPaths
{
    #[OA\Get(
        path: '/countries',
        tags: ['countries'],
        summary: 'List of countries, optional search by name and filter by language',
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Part of the name or ISO code', example: 'bos', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'language', in: 'query', required: false, description: 'Language name or code (e.g. Bosnian, spa)', schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Countries (empty list when nothing matches)', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/CountryShort'))),
            new OA\Response(response: 503, description: 'Country data not available', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getCountries() {}

    #[OA\Get(
        path: '/countries/languages',
        tags: ['countries'],
        summary: 'All official languages with the number of countries that speak them',
        responses: [
            new OA\Response(response: 200, description: 'Languages, most widespread first', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Language'))),
            new OA\Response(response: 503, description: 'Country data not available', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getLanguages() {}

    #[OA\Get(
        path: '/countries/{code}',
        tags: ['countries'],
        summary: 'One country by ISO code (2 or 3 letters)',
        parameters: [
            new OA\Parameter(name: 'code', in: 'path', required: true, example: 'BA', schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Country', content: new OA\JsonContent(ref: '#/components/schemas/Country')),
            new OA\Response(response: 404, description: 'Unknown country code', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 503, description: 'Country data not available', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getCountry() {}

    #[OA\Get(
        path: '/geo/ip',
        tags: ['countries'],
        summary: 'Location of an IP address (ipwho.is) + its country info',
        description: 'Without ?ip the location of the visitor is returned (on localhost: the public IP of the server).',
        parameters: [
            new OA\Parameter(name: 'ip', in: 'query', required: false, description: 'Public IPv4 or IPv6 address', example: '8.8.8.8', schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Location', content: new OA\JsonContent(ref: '#/components/schemas/GeoLocation')),
            new OA\Response(response: 400, description: 'Invalid or private IP address', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 503, description: 'Location service not available', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getGeoIp() {}
}
