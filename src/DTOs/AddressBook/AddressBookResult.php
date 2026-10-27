<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\DTOs\AddressBook;

use Daika7ana\Ecolet\DTOs\Common\Address;
use Daika7ana\Ecolet\DTOs\Common\Collection;

final readonly class AddressBookResult
{
    /**
     * @param Collection<int, Address> $addresses
     * @param array<string, mixed> $links Pagination links returned by the API
     * @param array<string, mixed> $meta Pagination metadata returned by the API
     */
    public function __construct(
        public Collection $addresses,
        public array $links = [],
        public array $meta = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $addresses = [];

        foreach (is_array($data['data'] ?? null) ? $data['data'] : [] as $item) {
            if (!is_array($item)) {
                continue;
            }

            $addresses[] = Address::fromArray($item);
        }

        return new self(
            addresses: new Collection($addresses),
            links: is_array($data['links'] ?? null) ? $data['links'] : [],
            meta: is_array($data['meta'] ?? null) ? $data['meta'] : [],
        );
    }
}
