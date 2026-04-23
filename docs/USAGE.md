# Ecolet PHP SDK - Usage Guide

Use this page as the entry point for package documentation.

## Documentation Sections

- [Quickstart](QUICKSTART.md)
- [Installation](INSTALLATION.md)
- [Configuration](CONFIGURATION.md)
- [Authentication](AUTHENTICATION.md)
- [Resources](RESOURCES.md)
- [Data Transfer Objects (DTOs)](DTOS.md)
- [Testing](TESTING.md)
- [Error Handling](ERRORS.md)

## Notes

- The package is framework-agnostic and PSR-first by design.
- Laravel/Symfony support is optional and additive via bridge utilities.
- Add Parcel v1 endpoints are intentionally not implemented.
- Add Parcel operations are implemented on v2 endpoints.
- General resources and auth endpoints use v1 paths unless documented otherwise.
- Collection helpers (`count`, `first`, `last`, `get`, `keys`, `values`, `has`, `hasKey`, `isEmpty`, `isNotEmpty`, `filter`, `reject`, `only`, `except`, `map`, `mapWithKeys`, `pluck`) are documented in [Data Transfer Objects (DTOs)](DTOS.md#collection-dto-helper-methods).
- Token access and restore flows (`getToken()`, `setToken()`) are documented in [Authentication](AUTHENTICATION.md).
- Waybill download helpers are documented in [Resources](RESOURCES.md#orders) and [Data Transfer Objects (DTOs)](DTOS.md#waybilldocument).
- Live happy-path and failure smoke coverage is documented in [Testing](TESTING.md).
