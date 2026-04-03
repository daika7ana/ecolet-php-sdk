<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Resources;

use Daika7ana\Ecolet\Client;
use Daika7ana\Ecolet\DTOs\Common\Collection;
use Daika7ana\Ecolet\DTOs\Services\Service;
use Daika7ana\Ecolet\Support\ApiResponseMapper;

class ServicesResource
{
    public function __construct(
        private Client $client,
    ) {}

    /**
     * Get all available services.
     *
     * @return Collection<Service>
     *
     * @throws \Daika7ana\Ecolet\Exceptions\UnexpectedStatusException
     * @throws \Daika7ana\Ecolet\Exceptions\ValidationException
     */
    public function getServices(): Collection
    {
        $request = $this->client->createRequest('GET', '/v1/services');
        $response = $this->client->send($request);

        $data = ApiResponseMapper::decodeJson($response);

        $services = array_map(
            static fn(array $item) => Service::fromArray($item),
            $data['services'],
        );

        return new Collection($services);
    }
}
