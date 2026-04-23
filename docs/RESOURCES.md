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
$isValidPostalCode = $streetsByPostalCode->isValid;
$matchingStreets = $streetsByPostalCode->streets;
```

`searchStreetPostalCodes()` returns a `Collection<StreetPostalCode>` with `code`, `number`, and `block` fields.
`searchStreetsByPostalCode()` returns a `StreetsByPostalCodeResult` with `isValid` and `streets`.

## Orders

```php
$order = $client->orders()->getOrder(12345);
$trackingNumber = $order->number;
$senderEmail = $order->sender?->email;
$orderStatuses = $order->statuses;

$waybill = $client->orders()->downloadWaybill(12345);
$filename = $waybill->getFilename();
$headers = $waybill->getDownloadHeaders();
$pdf = $waybill->getContents();

$statuses = $client->orders()->getStatusesForManyOrders(['80438360579', '80000001']);
$deleteResult = $client->orders()->deleteOrder(12345);
$deleteMessages = $deleteResult->messages;
```

`downloadWaybill()` returns `Daika7ana\Ecolet\DTOs\Orders\WaybillDocument`, not a raw stream. Use its helper methods when converting it into a framework response.
`getStatusesForManyOrders()` returns a `Collection<OrderWithStatuses>` keyed by request order.

## Order To Send

```php
$orderToSend = $client->ordersToSend()->getOrderToSend(555);

$status = $orderToSend->status;
$createdOrderId = $orderToSend->orderId;
$lastError = $orderToSend->error;
```

## Add Parcel (v2 only)

These methods intentionally use v2 endpoints only. All add-parcel operations accept **tightly-typed Request DTOs** and return **strongly-typed Response DTOs** for compile-time safety.

### Reload Form (Preview Pricing & Services)

```php
use Daika7ana\Ecolet\DTOs\AddParcel\AddParcelRequest;
use Daika7ana\Ecolet\DTOs\AddParcel\CourierInfo;
use Daika7ana\Ecolet\DTOs\AddParcel\CourierPickup;
use Daika7ana\Ecolet\DTOs\AddParcel\ParcelDetails;
use Daika7ana\Ecolet\DTOs\AddParcel\RecipientAddress;
use Daika7ana\Ecolet\Enums\CourierPickupType;

$request = new AddParcelRequest(
    sender: new RecipientAddress(...),
    receiver: new RecipientAddress(...),
    parcel: new ParcelDetails(...),
    courier: new CourierInfo(
        pickup: new CourierPickup(type: CourierPickupType::Courier),
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
        // string[]
    }
}
```

When preparing the final send-order request, set `CourierInfo::$service` from the selected service slug and pass any selected pickup `day`, `date`, and `time` on `CourierPickup`.

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
    - `hasErrors(): bool` — Whether the response contains any real validation messages
    - `getErrorMessages(): string[]` — Flattened validation messages
- `orderToSendId?: int` — Order ID when send/save succeeds

### Typical Add Parcel Workflow

1. Call `reloadForm()` to get availability, pricing, and pickup slots.
2. Pick a service from `pricing->statuses` / `pricing->additionalServices`.
3. Submit `sendOrder()` or `saveOrderToSend()` with the chosen `service` and pickup schedule.
4. Poll `ordersToSend()->getOrderToSend()` until `orderId` is available.
5. Fetch the created order with `orders()->getOrder()`.
6. Download the waybill with `orders()->downloadWaybill()`.

## Map Points

```php
$points = $client->mapPoints()->getMapPoints('RO', []);

$pickupPoints = $points->mapPoints;
$boundingBox = $points->boundingBox;
$firstPoint = $points->mapPoints->first();
$pointNames = $points->mapPoints->pluck('name')->all();
```

`mapPoints` is returned as `Collection<MapPoint>`, so you can use collection helpers like `first()`, `filter()`, `pluck()`, `keys()`, and `values()` directly on the response.
