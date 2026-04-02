# Ecolet PHP Client v1 Checklist

## Package Foundation

- [x] Align package metadata and dependencies in `composer.json`
- [x] Add PSR-7, PSR-17, and PSR-18 interoperability dependencies
- [x] Keep Guzzle as the default transport
- [x] Keep Symfony HttpFoundation as an additive bridge/helper, not the primary public contract

## Library Structure

- [x] Create library structure under `src/`
- [x] Add main `Client` entry point
- [x] Add `Config` namespace
- [x] Add `Auth` namespace
- [x] Add `Http` namespace
- [x] Add `Resources` namespace
- [x] Add `DTOs` namespace
- [x] Add `Exceptions` namespace
- [x] Add `Support` namespace

## HTTP Layer

- [x] Define internal request executor abstraction
- [x] Accept PSR-7 requests and return PSR-7 responses
- [x] Support injecting any PSR-18-compatible client
- [x] Add default Guzzle-backed implementation
- [x] Add request factories for JSON requests
- [x] Add request factories for `application/x-www-form-urlencoded` requests
- [x] Add optional Symfony HttpFoundation bridge/helper utilities

## Authentication

- [x] Implement password-grant authentication against `POST /api/v1/oauth/token`
- [x] Implement refresh-token support
- [x] Add immutable token value object
- [x] Add token store interface
- [x] Add in-memory token store implementation
- [x] Attach `Bearer` token to authenticated requests
- [x] Support optional `Accept-Language` header

## Error and Response Handling

- [x] Add authentication exception type
- [x] Add validation / unprocessable entity (422) exception type
- [x] Add unexpected status exception type
- [x] Add transport-level exception type
- [x] Centralise JSON decoding
- [x] Centralise response-to-DTO mapping
- [x] Add binary / stream handling for waybill downloads

## Resources

- [x] Implement **User** resource (`GET /me`)
- [x] Implement **Services** resource (`GET /services`)
- [x] Implement **Locations** resource
  - [x] `GET /locations/countries`
  - [x] `GET /locations/{countryCode}/counties`
  - [x] `GET /locations/{countryCode}/localities/{searchQuery}`
  - [x] `GET /locations/{localityId}/streets/{searchQuery}`
  - [x] `GET /locations/{localityId}/search-street-postal-codes/{streetName}`
  - [x] `GET /locations/{countryCode}/search-streets-by-postal-code/{postalCode}`
- [x] Implement **Order** resource
  - [x] `GET /order/{id}`
  - [x] `DELETE /order/{id}`
  - [x] `GET /order/{id}/download-waybill`
  - [x] `POST /order/get-statuses-for-many-orders`
- [x] Implement **Order to Send** resource (`GET /order-to-send/{id}`)
- [x] Implement **Add Parcel** resource (v2-only, v1 counterparts deliberately excluded)
  - [x] `POST /api/v2/add-parcel/reload-form`
  - [x] `POST /api/v2/add-parcel/send-order`
  - [x] `POST /api/v2/add-parcel/save-order-to-send`
- [x] Implement **Map Points** resource (`POST /map-points/{countryCode}`)

## API Versioning

- [x] Keep auth and all general resources on `/api/v1` unless docs state otherwise
- [x] Add Parcel operations (`reload-form`, `send-order`, `save-order-to-send`) use `/api/v2` exclusively — no v1 implementation

## DTOs and Request Models

- [x] Add typed immutable DTOs for stable response schemas (`User`, `Service`, `Country`, `County`, `Locality`, `Street`, `Address`, `OrderStatus`, `Ordered`, `OrderBody`, `OrderToSend`, `OrderWithStatuses`)
- [x] Add list / collection result objects where appropriate
- [x] Add request DTOs for Add Parcel operations
- [x] Support v2 multipack payload shape (multiple `parcels[]` entries)
- [x] Decide and implement waybill return type (PSR-7 stream or value object with content-type)
- [x] Prefer typed, stable return types over raw arrays throughout

## Testing

- [x] Add `phpunit.xml.dist` configuration
- [x] Auth — password grant success
- [x] Auth — password grant failure
- [x] Auth — token refresh success
- [x] Auth — token refresh failure
- [x] Token injection into authenticated requests
- [x] Transport error mapping
- [x] User resource
- [x] Services resource
- [x] Locations resource
- [x] Orders resource
- [x] Add Parcel resource — v2 payload shape, single parcel
- [x] Add Parcel resource — v2 payload shape, multipack
- [x] Add Parcel resource — response handling consistent across operations
- [x] Map Points resource
- [x] Custom PSR-18 client injection (contract test)

## Documentation

- [x] Add `README.md`
- [x] Document installation
- [x] Document authentication flow (password grant + refresh)
- [x] Document default Guzzle usage
- [x] Document custom PSR-18 client injection
- [x] Document token storage behaviour and how to implement a custom store
- [x] Document supported v1 resources
- [x] Document v1 exclusions (auth-code flow, etc.)
- [x] Document v1/v2 endpoint split for Add Parcel

## Verification

- [x] Composer dependency resolution succeeds
- [x] Autoload generation succeeds
- [x] Token requests use `Content-Type: application/x-www-form-urlencoded`
- [x] Authenticated requests send `Accept: application/json`
- [x] Authenticated requests send `Authorization: Bearer {token}`
- [x] Add Parcel operations target `/api/v2`
- [x] Other selected resources target `/api/v1`
- [x] Symfony HttpFoundation support remains additive (not replacing PSR contract)
- [x] Waybill download returns a safe binary / stream abstraction (not forced JSON parse)
