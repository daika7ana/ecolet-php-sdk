<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Tests\Unit\DTOs\AddParcel;

use Daika7ana\Ecolet\DTOs\AddParcel\AdditionalServices;
use Daika7ana\Ecolet\DTOs\AddParcel\AddParcelRequest;
use Daika7ana\Ecolet\DTOs\AddParcel\CouponInfo;
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

    public function testFactoriesCastScalarFieldsToConstructorTypes(): void
    {
        $address = RecipientAddress::fromArray([
            'name' => 123,
            'country' => 'ro',
            'locality' => 'Constanta',
            'locality_id' => '3150',
            'postal_code' => 900003,
            'street_name' => 'Main',
            'street_number' => 10,
            'contact_person' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => 712345678,
        ]);
        $coupon = CouponInfo::fromArray([
            'code' => 99,
        ]);

        $this->assertSame('123', $address->name);
        $this->assertSame('900003', $address->postalCode);
        $this->assertSame('10', $address->streetNumber);
        $this->assertSame('712345678', $address->phone);
        $this->assertSame(3150, $address->localityId);
        $this->assertSame('99', $coupon->code);
    }

    public function testFactoriesIgnoreMalformedNestedPayloads(): void
    {
        $request = AddParcelRequest::fromArray([
            'sender' => 'invalid',
            'receiver' => 123,
            'parcel' => 'invalid',
            'parcels' => ['invalid', ['type' => ParcelType::Package->value, 'amount' => '2']],
            'additional_services' => 'invalid',
            'courier' => 'invalid',
            'shipment_details' => 'invalid',
            'coupon' => 'invalid',
        ]);
        $services = AdditionalServices::fromArray([
            'cod' => true,
            'rod' => 'invalid',
            'sms_notify' => 1,
        ]);

        $this->assertSame('', $request->sender->streetNumber);
        $this->assertSame('', $request->receiver->name);
        $this->assertSame(ParcelType::Package, $request->parcel->type);
        $this->assertCount(1, $request->parcels);
        $this->assertSame(2, $request->parcels[0]->amount);
        $this->assertSame('courier', $request->courier->pickup->type->value);
        $this->assertNull($request->shipmentDetails);
        $this->assertNull($request->coupon);

        $this->assertFalse($services->cod);
        $this->assertFalse($services->rod);
        $this->assertFalse($services->smsNotify);
    }
}
