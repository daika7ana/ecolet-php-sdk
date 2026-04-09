<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Smoke\Support;

use Daika7ana\Ecolet\DTOs\AddParcel\AdditionalServices;
use Daika7ana\Ecolet\DTOs\AddParcel\AddParcelRequest;
use Daika7ana\Ecolet\DTOs\AddParcel\CourierInfo;
use Daika7ana\Ecolet\DTOs\AddParcel\CourierPickup;
use Daika7ana\Ecolet\DTOs\AddParcel\ParcelDetails;
use Daika7ana\Ecolet\DTOs\AddParcel\ParcelDimensions;
use Daika7ana\Ecolet\DTOs\AddParcel\RecipientAddress;
use Daika7ana\Ecolet\Enums\CourierPickupType;
use Daika7ana\Ecolet\Enums\ParcelShape;
use Daika7ana\Ecolet\Enums\ParcelType;

final class AddParcelSmokePayloadFactory
{
    public static function workflowPayload(
        ?string $service = null,
        ?string $pickupDay = null,
        ?string $pickupDate = null,
        ?string $pickupTime = null,
    ): AddParcelRequest {
        return new AddParcelRequest(
            sender: new RecipientAddress(
                name: 'Test Company',
                country: 'ro',
                county: 'Constanta',
                locality: 'Constanta',
                localityId: 3150,
                postalCode: '900003',
                streetName: 'Str. Interioara',
                streetNumber: '103',
                block: '1',
                entrance: 'A2',
                floor: '1',
                flat: 'A3a',
                contactPerson: 'Test Test',
                email: 'user@example.com',
                phone: '0214824089',
            ),
            receiver: new RecipientAddress(
                name: 'Test Company',
                country: 'ro',
                county: 'Constanta',
                locality: 'Constanta',
                localityId: 3150,
                postalCode: '900003',
                streetName: 'Str. Dezrobirii',
                streetNumber: '296',
                block: '1',
                entrance: 'A2',
                floor: '1',
                flat: 'A3a',
                contactPerson: 'Test Test',
                email: 'user@example.com',
                phone: '0214824089',
            ),
            parcel: new ParcelDetails(
                type: ParcelType::Package,
                weight: 1,
                shape: ParcelShape::Standard,
                observations: 'FRAGILE',
                amount: 1,
            ),
            additionalServices: new AdditionalServices(
                cod: true,
                codAmount: 500,
            ),
            courier: new CourierInfo(
                pickup: new CourierPickup(
                    type: CourierPickupType::Courier,
                    day: $pickupDay,
                    date: $pickupDate,
                    time: $pickupTime,
                ),
                service: $service,
            ),
            parcels: [
                new ParcelDetails(
                    type: ParcelType::Package,
                    weight: 1,
                    dimensions: new ParcelDimensions(length: 10, width: 10, height: 10),
                    content: 'Biscuits 400gr',
                    declaredValue: 50,
                    amount: 1,
                ),
            ],
        );
    }

    public static function malformedPayload(): AddParcelRequest
    {
        return new AddParcelRequest(
            sender: new RecipientAddress(
                name: 'x',
                country: 'ro',
                county: 'Constanta',
                locality: 'Constanta',
                localityId: 3150,
                postalCode: '1',
                streetName: 'x',
                streetNumber: '1',
                contactPerson: 'x',
                email: 'bad-email',
                phone: '1',
            ),
            receiver: new RecipientAddress(
                name: 'x',
                country: 'ro',
                county: 'Constanta',
                locality: 'Constanta',
                localityId: 3150,
                postalCode: '1',
                streetName: 'x',
                streetNumber: '1',
                contactPerson: 'x',
                email: 'bad-email',
                phone: '1',
            ),
            parcel: new ParcelDetails(
                type: ParcelType::Package,
                weight: 1,
            ),
            additionalServices: new AdditionalServices(),
            courier: new CourierInfo(
                pickup: new CourierPickup(type: CourierPickupType::Self),
            ),
            parcels: [
                new ParcelDetails(
                    type: ParcelType::Package,
                    weight: 1,
                ),
            ],
        );
    }
}
