# Resources

## User

```php
$user = $client->users()->getMe();
```

## Services

```php
$services = $client->services()->getServices();
```

## Locations

```php
$countries = $client->locations()->getCountries();
$counties = $client->locations()->getCounties('RO');
$localities = $client->locations()->searchLocalities('RO', 'Bucharest');
$streets = $client->locations()->searchStreets(123, 'Main');
$postalCodes = $client->locations()->searchStreetPostalCodes(123, 'Main Street');
$streetsByPostalCode = $client->locations()->searchStreetsByPostalCode('RO', '010101');
```

## Orders

```php
$order = $client->orders()->getOrder(12345);
$client->orders()->deleteOrder(12345);
$waybillStream = $client->orders()->downloadWaybill(12345);
$statuses = $client->orders()->getStatusesForManyOrders([12345, 67890]);
```

## Order To Send

```php
$orderToSend = $client->ordersToSend()->getOrderToSend(555);
```

## Add Parcel (v2 only)

These methods intentionally use v2 endpoints only. All add-parcel operations accept **tightly-typed Request DTOs** and return **strongly-typed Response DTOs** for compile-time safety.

### Reload Form (Preview Pricing & Services)

```php
use Daika7ana\Ecolet\DTOs\AddParcelRequest;
use Daika7ana\Ecolet\DTOs\RecipientAddress;
use Daika7ana\Ecolet\DTOs\ParcelDetails;
use Daika7ana\Ecolet\DTOs\ParcelDimensions;
use Daika7ana\Ecolet\DTOs\AdditionalServices;
use Daika7ana\Ecolet\DTOs\CourierInfo;
use Daika7ana\Ecolet\DTOs\CourierPickup;

$request = new AddParcelRequest(
    sender: new RecipientAddress(...),
    receiver: new RecipientAddress(...),
    parcel: new ParcelDetails(...),
    courier: new CourierInfo(
        pickup: new CourierPickup(type: 'courier'),
    ),
);

$result = $client->addParcel()->reloadForm($request);

// Check response type
if ($result->isFormResponse()) {
    // Access pricing safely with typed DTOs
    $pricing = $result->formResponse->pricing;
    $pricesGross = $pricing->pricesGross;      // array<string, float>
    $statuses = $pricing->statuses;            // array<string, bool>
    $pickupDates = $pricing->pickupDates;      // array<string, array>

    // Check for validation errors
    if ($result->formResponse->hasErrors()) {
        $errorMessages = $result->formResponse->getErrorMessages();
        // array<string, array<string>>
    }
}
```

### Send Order (Create Shipment)

```php
$result = $client->addParcel()->sendOrder($request);

if ($result->isOrderResponse()) {
    $orderId = $result->orderToSendId;  // int
}
```

### Save Order to Send (Draft Shipment)

```php
$result = $client->addParcel()->saveOrderToSend($request);

if ($result->isOrderResponse()) {
    $orderId = $result->orderToSendId;  // int
}
```

### Request DTO Structure

All add-parcel operations use `AddParcelRequest` containing:

- `sender`: `RecipientAddress` — Sender details
- `receiver`: `RecipientAddress` — Receiver details
- `parcel`: `ParcelDetails` — Parcel type, weight, dimensions, declared value
- `parcels`: `array<ParcelDetails>` (optional) — Multiple parcels for multipack shipments
- `additionalServices`: `AdditionalServices` (optional) — COD, open package, etc.
- `courier`: `CourierInfo` — Pickup method and service selection
- `shipmentDetails`: `ShipmentDetails` (optional) — UIT code, forklift details
- `couponInfo`: `CouponInfo` (optional) — Promotion code

### Response DTO Methods

All add-parcel responses return `AddParcelResult` with:

- `isFormResponse(): bool` — Detects reload-form response (contains pricing)
- `isOrderResponse(): bool` — Detects send/save response (contains order ID)
- `formResponse?: AddParcelFormResponse` — Pricing, services, validation errors
  - `pricing: ServicePricingInfo` — Net/gross prices, fees, service statuses, pickup dates
  - `hasErrors(): bool` — Whether form has validation errors
  - `getErrorMessages(): array<string, array<string>>` — Field-grouped error messages
- `orderToSendId?: int` — Order ID when send/save succeeds

## Map Points

```php
$points = $client->mapPoints()->getMapPoints('RO', []);
```
