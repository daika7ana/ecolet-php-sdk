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

These methods intentionally use v2 endpoints only.

```php
$reloadResult = $client->addParcel()->reloadForm($payload);
$sendResult = $client->addParcel()->sendOrder($payload);
$saveResult = $client->addParcel()->saveOrderToSend($payload);
```

## Map Points

```php
$points = $client->mapPoints()->getMapPoints('RO', []);
```
