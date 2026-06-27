# Generators

## Service Skeleton Generator

`php artisan carve:generate-service billing --with-openapi --with-client --with-tests`

Generates a Laravel service skeleton into `carve-output/services/billing/` with controllers, models, routes, config, migrations, tests, Dockerfile, and OpenAPI spec.

## OpenAPI Generator

`php artisan carve:generate-openapi billing`

Generates an OpenAPI 3.1 YAML file for a boundary's routes, using FormRequest validation rules for request schemas and API Resources for response schemas.

## Client SDK Generator

`php artisan carve:generate-client billing`

Generates a PHP client class for the monolith to call the new service, with typed methods, retries, timeouts, and request ID propagation.

## Feature Flag Config Generator

Generates `config/carve_services.php` with per-service enabled/shadow/rollout settings.

## Contract Test Generator

Generates PHPUnit test stubs that compare monolith vs service response shapes.

## Manifest

Every generation run produces a `manifest.json` listing all created files, warnings, and next steps.
