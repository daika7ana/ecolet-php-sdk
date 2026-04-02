# Data Transfer Objects (DTOs)

The Ecolet API wrapper uses strongly-typed DTOs for both requests and responses, providing compile-time safety and IDE autocomplete.

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

Describes a single parcel's properties.

```php
$parcel = new ParcelDetails(
    type: string,                      // 'package', 'envelope', 'pallet'
    weight: int,                       // in grams
    dimensions?: ParcelDimensions,     // length, width, height in cm
    shape?: string,                    // 'standard', etc.
    declaredValue?: int,               // in cents for currency
    content?: string,                  // description
    observations?: string,             // special handling notes
    amount: int,                       // quantity of this parcel type
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
$courier = new CourierInfo(
    pickup: CourierPickup,             // required
    serviceSlug?: string,              // e.g., 'dpd_standard'
    contractId?: string,
);
```

### CourierPickup

Pickup method configuration.

```php
$pickup = new CourierPickup(
    type: string,                      // 'courier' or 'self'
    date?: DateTime,                   // optional: specific pickup date
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
