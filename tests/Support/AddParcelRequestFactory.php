<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Support;

use Daika7ana\Ecolet\DTOs\AddParcel\AdditionalServices;
use Daika7ana\Ecolet\DTOs\AddParcel\AddParcelRequest;
use Daika7ana\Ecolet\DTOs\AddParcel\CourierInfo;
use Daika7ana\Ecolet\DTOs\AddParcel\CourierPickup;
use Daika7ana\Ecolet\DTOs\AddParcel\ParcelDetails;
use Daika7ana\Ecolet\DTOs\AddParcel\RecipientAddress;
use Daika7ana\Ecolet\Enums\CourierPickupType;
use Daika7ana\Ecolet\Enums\ParcelType;

final class AddParcelRequestFactory
{
    public static function make(
        ?RecipientAddress $sender = null,
        ?RecipientAddress $receiver = null,
        ?ParcelDetails $parcel = null,
        ?AdditionalServices $additionalServices = null,
        ?CourierInfo $courier = null,
        ?array $parcels = null,
    ): AddParcelRequest {
        $mainParcel = $parcel ?? self::parcel();
        $parcelItems = $parcels ?? [$mainParcel];

        return new AddParcelRequest(
            sender: $sender ?? self::sender(),
            receiver: $receiver ?? self::receiver(),
            parcel: $mainParcel,
            additionalServices: $additionalServices ?? new AdditionalServices(),
            courier: $courier ?? self::selfPickup(),
            parcels: $parcelItems,
        );
    }

    public static function sender(): RecipientAddress
    {
        return new RecipientAddress(
            name: 'Test Company',
            country: 'ro',
            locality: 'Bucuresti',
            localityId: 323,
            postalCode: '011318',
            streetName: 'Test Street',
            streetNumber: '123',
            contactPerson: 'John Doe',
            email: 'john@example.com',
            phone: '0123456789',
        );
    }

    public static function receiver(): RecipientAddress
    {
        return new RecipientAddress(
            name: 'Test Recipient',
            country: 'ro',
            locality: 'Bucuresti',
            localityId: 323,
            postalCode: '011318',
            streetName: 'Recipient Street',
            streetNumber: '456',
            contactPerson: 'Jane Doe',
            email: 'jane@example.com',
            phone: '0987654321',
        );
    }

    public static function parcel(int $weight = 1000, ParcelType $type = ParcelType::Package): ParcelDetails
    {
        return new ParcelDetails(
            type: $type,
            weight: $weight,
        );
    }

    /**
     * @return array<int, ParcelDetails>
     */
    public static function parcels(int ...$weights): array
    {
        $normalizedWeights = $weights === [] ? [1000] : $weights;

        return array_map(
            static fn(int $weight): ParcelDetails => self::parcel($weight),
            $normalizedWeights,
        );
    }

    public static function selfPickup(): CourierInfo
    {
        return new CourierInfo(
            pickup: new CourierPickup(type: CourierPickupType::Self),
        );
    }

    public static function courierPickup(
        string $day = 'Friday',
        string $date = '2026-04-10',
        string $time = '10:00',
        ?string $service = null,
    ): CourierInfo {
        return new CourierInfo(
            pickup: new CourierPickup(
                type: CourierPickupType::Courier,
                day: $day,
                date: $date,
                time: $time,
            ),
            service: $service,
        );
    }
}
