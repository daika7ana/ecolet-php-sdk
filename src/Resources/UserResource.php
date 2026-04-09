<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\DTOs\Users\User;
use Daika7ana\Ecolet\Exceptions\UnexpectedStatusException;
use Daika7ana\Ecolet\Exceptions\ValidationException;
use Daika7ana\Ecolet\Support\ApiResponseMapper;

class UserResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Get the authenticated user's information.
     *
        * @throws UnexpectedStatusException
        * @throws ValidationException
     */
    public function getMe(): User
    {
        $request = $this->client->createRequest('GET', '/v1/me');
        $response = $this->client->send($request);

        $data = ApiResponseMapper::decodeJson($response);

        return User::fromArray($data['user']);
    }
}
