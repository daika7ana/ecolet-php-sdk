<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\DTOs\AddParcel;

use Daika7ana\Ecolet\DTOs\AddParcel\AdditionalServices;
use Daika7ana\Ecolet\DTOs\AddParcel\CourierInfo;
use Daika7ana\Ecolet\DTOs\AddParcel\ParcelDetails;
use Daika7ana\Ecolet\DTOs\AddParcel\RecipientAddress;
use Daika7ana\Ecolet\DTOs\AddParcel\ShipmentDetails;
use Daika7ana\Ecolet\Enums\ParcelType;
use PHPUnit\Framework\TestCase;

class AddParcelDtoNullabilityTest extends TestCase
{
    public function testRecipientAddressPreservesMissingOptionalFieldsAsNull(): void
    {
        $address = RecipientAddress::fromArray([
            'name' => 'Sender',
            'country' => 'ro',
            'locality' => 'Constanta',
            'locality_id' => 3150,
            'postal_code' => '900003',
            'street_name' => 'Main',
            'street_number' => '10',
            'contact_person' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '0712345678',
        ]);

        $this->assertNull($address->county);
        $this->assertNull($address->block);
        $this->assertNull($address->entrance);
        $this->assertNull($address->floor);
        $this->assertNull($address->flat);

        $serialized = $address->toArray();
        $this->assertArrayNotHasKey('county', $serialized);
        $this->assertArrayNotHasKey('block', $serialized);
        $this->assertArrayNotHasKey('entrance', $serialized);
        $this->assertArrayNotHasKey('floor', $serialized);
        $this->assertArrayNotHasKey('flat', $serialized);
    }

    public function testOptionalParcelCourierAndShipmentFieldsRemainNullWhenMissing(): void
    {
        $parcel = ParcelDetails::fromArray([
            'type' => ParcelType::Package->value,
            'amount' => 1,
        ]);
        $courier = CourierInfo::fromArray([
            'pickup' => ['type' => 'courier'],
        ]);
        $shipment = ShipmentDetails::fromArray([]);

        $this->assertNull($parcel->content);
        $this->assertNull($parcel->observations);
        $this->assertArrayNotHasKey('content', $parcel->toArray());
        $this->assertArrayNotHasKey('observations', $parcel->toArray());

        $this->assertNull($courier->service);
        $this->assertArrayNotHasKey('service', $courier->toArray());

        $this->assertNull($shipment->uitCode);
        $this->assertSame([], $shipment->toArray());
    }

    public function testAdditionalServicesPreservesMissingRodCodeAsNull(): void
    {
        $services = AdditionalServices::fromArray([
            'rod' => ['status' => true],
        ]);

        $this->assertTrue($services->rod);
        $this->assertNull($services->rodCode);
        $this->assertArrayNotHasKey('rod_code', $services->toArray()['rod']);
    }
}
