# Data Transfer Objects (DTOs)

The Ecolet API wrapper uses strongly-typed DTOs for both requests and responses, providing compile-time safety and IDE autocomplete.

## Enums

Common enum types for request validation:

### ParcelType (String BackedEnum)

Valid parcel package types:

```php
use Daika7ana\Ecolet\Enums\ParcelType;

ParcelType::Package  // 'package'
ParcelType::Envelope // 'envelope'
ParcelType::Pallet   // 'pallet'
```

### ParcelShape (String BackedEnum)

Parcel shape classifications:

```php
use Daika7ana\Ecolet\Enums\ParcelShape;

ParcelShape::Standard    // 'standard'
ParcelShape::Nonstandard // 'nonstandard'
```

### CourierPickupType (String BackedEnum)

Pickup method types:

```php
use Daika7ana\Ecolet\Enums\CourierPickupType;

CourierPickupType::Courier // 'courier' — pickup by courier
CourierPickupType::Self    // 'self' — self-pickup at location
```

## Request DTOs

All add-parcel operations use `AddParcelRequest` as the main wrapper. It composes several nested DTOs:

### AddParcelRequest

Main request wrapper for reloadForm, sendOrder, and saveOrderToSend endpoints.

```php
$request = new AddParcelRequest(
    sender: RecipientAddress,          // required
    receiver: RecipientAddress,        // required
    parcel: ParcelDetails,             // required
    parcels?: array<ParcelDetails>,    // optional: multiple parcels
    additionalServices?: AdditionalServices,
    courier: CourierInfo,              // required
    shipmentDetails?: ShipmentDetails,
    couponInfo?: CouponInfo,
);
```

### RecipientAddress

Represents sender or receiver information.

```php
$address = new RecipientAddress(
    name: string,                      // required
    country: string,                   // required: 'ro', 'hu', etc.
    county: string,                    // required
    locality: string,                  // required
    localityId: int,                   // required: obtained from locations API
    postalCode: string,                // required
    streetName: string,                // required
    streetNumber: string,              // required
    block?: string,
    entrance?: string,
    floor?: string,
    flat?: string,
    contactPerson: string,             // required
    email: string,                     // required
    phone: string,                     // required
);
```

### ParcelDetails

Describes a single parcel's properties. Uses `ParcelType` and optional `ParcelShape` enums.

```php
use Daika7ana\Ecolet\Enums\ParcelShape;
use Daika7ana\Ecolet\Enums\ParcelType;

$parcel = new ParcelDetails(
    type: ParcelType::Package,        // required: enum
    weight: int,                      // in grams
    dimensions?: ParcelDimensions,    // length, width, height in cm
    shape?: ParcelShape::Standard,    // optional: enum
    declaredValue?: int,              // in cents for currency
    content?: string,                 // description
    observations?: string,            // special handling notes
    amount: int,                      // quantity of this parcel type
);
```

### ParcelDimensions

Physical dimensions of a parcel.

```php
$dimensions = new ParcelDimensions(
    length: int,                       // in cm
    width: int,                        // in cm
    height: int,                       // in cm
);
```

### AdditionalServices

Optional services for the shipment.

```php
$services = new AdditionalServices(
    cod: bool,                         // Cash on delivery
    codAmount?: int,                   // in cents for currency
    openPackage?: bool,
    openPackageAmount?: int,
    rod?: bool,                        // Return on delivery
    rodAmount?: int,
    rop?: bool,                        // Receiver on delivery
    ropAmount?: int,
    saturdayDelivery?: bool,
    saturdayDeliveryAmount?: int,
    sms?: bool,
    smsAmount?: int,
    swap?: bool,
    swapAmount?: int,
    epod?: bool,                       // Electronic proof of delivery
    epodAmount?: int,
);
```

### CourierInfo

Service and pickup method selection.

```php
use Daika7ana\Ecolet\Enums\CourierPickupType;

$courier = new CourierInfo(
    pickup: new CourierPickup(
        type: CourierPickupType::Courier,  // required: enum
        day?: string,                      // optional: weekday token returned by reloadForm
        date?: string,                     // optional: 'YYYY-MM-DD' format
        time?: string,                     // optional: time window
    ),
    service?: string,                  // e.g., 'fan_courier_standard'
    contractId?: int,
);
```

### CourierPickup

Pickup method configuration. Uses `CourierPickupType` enum.

```php
use Daika7ana\Ecolet\Enums\CourierPickupType;

$pickup = new CourierPickup(
    type: CourierPickupType::Self,     // required: enum
    day?: string,                      // optional: weekday token returned by reloadForm
    date?: string,                     // optional: 'YYYY-MM-DD' format
    time?: string,                     // optional: time window
);
```

### ShipmentDetails

Additional shipment attributes.

```php
$shipment = new ShipmentDetails(
    uitCode?: string,                  // UIT code for customs
    useForklift?: bool,
    enableForklift?: bool,
);
```

### CouponInfo

Promotion/discount code.

```php
$coupon = new CouponInfo(
    code: string,                      // Promotion code
);
```

## Response DTOs

All add-parcel operations return `AddParcelResult` which auto-detects the response type:

### AddParcelResult

Union-type response with auto-detection methods.

```php
$result = $client->addParcel()->reloadForm($request);

// Check response type
if ($result->isFormResponse()) {
    $formResponse = $result->formResponse;  // AddParcelFormResponse
} elseif ($result->isOrderResponse()) {
    $orderId = $result->orderToSendId;      // int
}
```

### AddParcelFormResponse

Returned by reloadForm endpoint with pricing and validation data.

```php
$form = $result->formResponse;

// Pricing information
$pricing = $form->pricing;              // ServicePricingInfo

// Metadata
$billingWeight = $form->billingWeight;  // int
$vat = $form->vat;                      // int

// Info messages
$info = $form->info;                    // array<string>

// Validation errors (field => messages)
$errors = $form->errors;                // array<string, array<string>>

// Helper methods
if ($form->hasErrors()) {
    $errorMessages = $form->getErrorMessages();
}
```

`hasErrors()` checks the flattened validation messages, so empty courier-specific error buckets do not count as an error state.

### ServicePricingInfo

Pricing and availability data by service.

```php
$pricing = $form->pricing;

// Service availability
$statuses = $pricing->statuses;         // array<serviceSlug => bool>

// Additional services availability
$addlServices = $pricing->additionalServices;  // array<string => array>

// Pickup date availability
$pickupDates = $pricing->pickupDates;   // array<serviceSlug => array>

// Pricing by service (in cents)
$pricesNet = $pricing->pricesNet;       // array<serviceSlug => float>
$pricesGross = $pricing->pricesGross;   // array<serviceSlug => float>

// Extra fees (in cents)
$fees = $pricing->fees;                 // array<serviceSlug => float>

// Standard service indicators
$isStandard = $pricing->isStandard;     // array<serviceSlug => bool>
```

## Order DTOs

### Order

```php
$order = $client->orders()->getOrder(12345);

$id = $order->id;
$number = $order->number;   // populated from Ecolet's number or awb field
$status = $order->status;
```

### OrderToSend

```php
$orderToSend = $client->ordersToSend()->getOrderToSend(555);

$id = $orderToSend->id;
$status = $orderToSend->status;
$error = $orderToSend->error;
$orderId = $orderToSend->orderId;
$originalRequest = $orderToSend->order; // AddParcelRequest|null
```

### WaybillDocument

```php
$waybill = $client->orders()->downloadWaybill(12345);

$filename = $waybill->getFilename();
$pdfContents = $waybill->getContents();
$headers = $waybill->getDownloadHeaders();
```

`WaybillDocument` keeps the PSR stream plus normalized helpers for common framework integrations.

## Creating From Arrays (Backward Compatibility)

For gradual migration or simpler use cases, DTOs support array initialization:

```php
$request = AddParcelRequest::fromArray([
    'sender' => [...],
    'receiver' => [...],
    'parcel' => [...],
    'courier' => [...],
]);

// Or convert back to array
$array = $request->toArray();
```

## Type Safety Benefits

Using DTOs ensures:

- **Compile-time validation**: Type hints catch errors during development
- **IDE autocomplete**: Full IntelliSense for nested properties
- **Schema enforcement**: API requires proper field types and nesting
- **Backward compatibility**: Resources still accept arrays but encourage typed DTOs

## Collection DTO Helper Methods

List-style endpoints (services, locations, statuses, map points, etc.) return `Daika7ana\\Ecolet\\DTOs\\Common\\Collection`.

### Basic Accessors

```php
$services = $client->services()->getServices();

$count = $services->count();          // int
$first = $services->first();          // first item or null
$last = $services->last();            // last item or null
$all = $services->get();              // full keyed array of items
$one = $services->get(0);             // single item by key/index or null
$values = $services->values();        // reindexed list of values
```

### Iteration

`Collection` is iterable, so you can loop directly:

```php
foreach ($services as $service) {
    echo $service->name;
}
```

### Transformations

```php
$serviceNames = $services->map(static fn($service) => $service->name);

$servicesById = $services->mapWithKeys(
    static fn($service) => [$service->id => $service]
);
```

- `map(callable $callback)` keeps original keys.
- `mapWithKeys(callable $callback)` lets you define new keys.
- Both return a new `Collection` (immutable style).

### Pluck

Use `pluck` to extract values from arrays/objects in each item.

```php
$names = $services->pluck('name');
// Collection with original keys: [0 => 'Express', 1 => 'Standard']

$namesById = $services->pluck('name', 'id');
// Collection keyed by item id: [10 => 'Express', 20 => 'Standard']
```

- First parameter: value key to extract.
- Second parameter (optional): key key to use for the resulting collection keys.
- If the optional key cannot be used as an array key, it falls back to the original item key.
