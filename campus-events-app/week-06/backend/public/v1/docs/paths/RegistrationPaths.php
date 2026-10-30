<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/RegistrationRoutes.php.
 * swagger-php reads the #[OA\...] attributes; the methods are empty – they only carry the documentation.
 * Keep it in sync with the routes: one method per endpoint.
 */
class RegistrationPaths
{
    #[OA\Get(
        path: '/registrations',
        tags: ['registrations'],
        summary: 'List registrations',
        parameters: [
            new OA\Parameter(
                name: 'user_id',
                in: 'query',
                required: false,
                description: 'Only registrations of this student',
                example: 2,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of registrations',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Registration')
                )
            )
        ]
    )]
    public function getRegistrations() {}

    #[OA\Get(
        path: '/registrations/{id}',
        tags: ['registrations'],
        summary: 'Get one registration',
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
                description: 'The registration',
                content: new OA\JsonContent(ref: '#/components/schemas/Registration')
            ),
            new OA\Response(
                response: 404,
                description: 'Registration not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function getRegistrationsById() {}

    #[OA\Post(
        path: '/registrations',
        tags: ['registrations'],
        summary: 'Register for an event',
        description: 'Rules: the event has not started, a seat is free, max. 3 upcoming registrations per student, no duplicate registration.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RegistrationInput')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created registration',
                content: new OA\JsonContent(ref: '#/components/schemas/Registration')
            ),
            new OA\Response(
                response: 400,
                description: 'A business rule was broken',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function postRegistrations() {}

    #[OA\Put(
        path: '/registrations/{id}',
        tags: ['registrations'],
        summary: 'Change the status of a registration',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                example: 4,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        enum: ['cancelled', 'attended'],
                        example: 'attended'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated registration',
                content: new OA\JsonContent(ref: '#/components/schemas/Registration')
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid status change',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Registration not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function putRegistrationsById() {}

    #[OA\Put(
        path: '/registrations/{id}/cancel',
        tags: ['registrations'],
        summary: 'Cancel a registration (the seat becomes free)',
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
                description: 'Cancelled registration',
                content: new OA\JsonContent(ref: '#/components/schemas/Registration')
            ),
            new OA\Response(
                response: 400,
                description: 'Not active or the event already started',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Registration not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function putRegistrationsByIdCancel() {}

    #[OA\Put(
        path: '/registrations/{id}/attend',
        tags: ['registrations'],
        summary: 'Check a student in (status attended)',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                example: 4,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated registration',
                content: new OA\JsonContent(ref: '#/components/schemas/Registration')
            ),
            new OA\Response(
                response: 400,
                description: 'Registration is not active',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Registration not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function putRegistrationsByIdAttend() {}

    #[OA\Delete(
        path: '/registrations/{id}',
        tags: ['registrations'],
        summary: 'Delete a registration',
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
                description: 'Deleted',
                content: new OA\JsonContent(ref: '#/components/schemas/Message')
            ),
            new OA\Response(
                response: 404,
                description: 'Registration not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function deleteRegistrationsById() {}
}
