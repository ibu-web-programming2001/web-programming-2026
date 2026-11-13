<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/AuthRoutes.php.
 * swagger-php reads the #[OA\...] attributes; the methods are empty – they only carry the documentation.
 * Keep it in sync with the routes: one method per endpoint.
 */
class AuthPaths
{
    #[OA\Post(
        path: '/auth/register',
        tags: ['auth'],
        summary: 'Create a student account (role is always student)',
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['first_name', 'last_name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'Amar'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Hodžić'),
                    new OA\Property(property: 'email', type: 'string', example: 'amar@campusevents.ba'),
                    new OA\Property(
                        property: 'faculty',
                        type: 'string',
                        enum: ['FENS', 'FASS', 'FBA', 'FEDU'],
                        example: 'FENS'
                    ),
                    new OA\Property(property: 'password', type: 'string', example: 'Secret123'),
                    new OA\Property(property: 'password_confirm', type: 'string', example: 'Secret123')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User and JWT',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthResponse')
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid data or email taken',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function postAuthRegister() {}

    #[OA\Post(
        path: '/auth/login',
        tags: ['auth'],
        summary: 'Log in with email and password (+ 2FA code when enabled)',
        security: [],
        description: 'If the user has two-factor authentication enabled and no code is sent, the answer is {two_factor_required: true} and NO token. Send the same request again with \'code\'.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'admin@campusevents.ba'),
                    new OA\Property(property: 'password', type: 'string', example: 'Admin123!'),
                    new OA\Property(
                        property: 'code',
                        type: 'string',
                        example: '123456',
                        description: 'Only when 2FA is enabled'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User and JWT – or {two_factor_required: true}',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid email or password',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function postAuthLogin() {}

    #[OA\Get(
        path: '/auth/me',
        tags: ['auth'],
        summary: 'The logged-in user',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Current user',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
            new OA\Response(
                response: 401,
                description: 'Missing or invalid token',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function getAuthMe() {}

    #[OA\Put(
        path: '/auth/me',
        tags: ['auth'],
        summary: 'Update my name or password',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'Lejla'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Hadžić'),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        example: 'NewSecret123',
                        description: 'Optional'
                    ),
                    new OA\Property(property: 'password_confirm', type: 'string', example: 'NewSecret123')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated user',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid data',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 401,
                description: 'Missing or invalid token',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function putAuthMe() {}

    #[OA\Post(
        path: '/auth/2fa/setup',
        tags: ['auth'],
        summary: 'Start 2FA setup: get a secret and otpauth:// link (show it as a QR code)',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Secret',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'secret', type: 'string', example: 'JBSWY3DPEHPK3PXP'),
                        new OA\Property(property: 'otpauth_uri', type: 'string')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Missing or invalid token',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function postAuth2FaSetup() {}

    #[OA\Post(
        path: '/auth/2fa/enable',
        tags: ['auth'],
        summary: 'Turn 2FA on with the first code from the authenticator app',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['code'],
                properties: [new OA\Property(property: 'code', type: 'string', example: '123456')]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Enabled'),
            new OA\Response(
                response: 400,
                description: 'Invalid code',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function postAuth2FaEnable() {}

    #[OA\Post(
        path: '/auth/2fa/disable',
        tags: ['auth'],
        summary: 'Turn 2FA off (needs a valid code)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['code'],
                properties: [new OA\Property(property: 'code', type: 'string', example: '123456')]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Disabled'),
            new OA\Response(
                response: 400,
                description: 'Invalid code',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function postAuth2FaDisable() {}

    #[OA\Post(
        path: '/auth/google',
        tags: ['auth'],
        summary: 'Log in with a Google ID token (SSO)',
        security: [],
        description: 'The frontend gets the ID token from Google Identity Services. Needs GOOGLE_CLIENT_ID in the configuration.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['id_token'],
                properties: [new OA\Property(property: 'id_token', type: 'string')]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User and JWT',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid Google token',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 503,
                description: 'SSO not configured or Google not reachable',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function postAuthGoogle() {}
}
