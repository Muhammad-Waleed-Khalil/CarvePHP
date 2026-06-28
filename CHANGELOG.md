# Changelog

## v0.2.0-alpha — 2026-06-28

### Changed

- **All analyzers use content-based detection instead of path filters.** Source files are now identified by scanning code content (e.g., `Route::`, `class *Controller`, `Schema::create`) rather than directory paths. This makes CarvePHP work with any project structure — standard Laravel, Bagisto monolith, custom layouts — without configuration changes.
- `SourceFileFinder` falls back to scanning `base_path()` when `include_paths` is empty, plus `exclude_paths` filtering.
- `config/carve.php`: `static_analysis.include_paths` defaults to `[]` (scan entire project root).

### Fixed

- **Node ID duplication bug.** `ControllerAnalyzer` now stores the short class name in `name` (e.g., `PageController`) instead of the full FQCN, so `GraphBuilder`'s namespace concatenation produces correct node IDs.
- **Route-to-controller matching with short names.** `GraphBuilder` falls back to `nameToFqcn` lookup when a route's controller name is a short class name (string syntax).
- Test fixtures updated to reflect content-based detection behavior.

### Known Limitations (v0.2.0-alpha)

- Routes using `Route::controller()->group()` pattern lose controller context — 465/493 Bagisto routes have method names instead of FQCNs in the `controller` field.
- No repository→model resolution — controllers that inject repositories produce 0 `uses_model` edges in the graph.
- No boundary candidates on Bagisto due to the above gaps.
- Proxy models (`konekt/concord`) and translatable models (`Astrotomic\Translatable`) are detected but their dual-table nature is not explicitly modeled.

## v0.1.2-alpha — 2026-06-27

### Fixed

- Correct README status version to v0.1.2-alpha.
- Correct Laravel compatibility statement to Laravel 11/12/13.
- Fix malformed command table on Packagist.
- Fix documented generator command names (carve:generate:service → carve:generate-service, etc.).
- Clarify alpha Composer install command (require with `^0.1@alpha`).

### Changed

- Removed Laravel 10 compatibility note (no longer allowed in composer.json constraints).

No runtime behavior changes.

## v0.1.1-alpha — 2026-06-27

### Added

- **Laravel 13 support.** Package now supports Laravel 11, 12, and 13. CI matrix includes PHP 8.2/8.3/8.4 × Laravel 11/12/13 (PHP 8.2 + L13 excluded; L13 requires PHP ^8.3).

### Changed

- `illuminate/*` constraints raised to `^11.0|^12.0|^13.0`.
- `orchestra/testbench` constraints raised to `^9.0|^10.0|^11.0`.

### Fixed

- `self::SUCCESS` replaced with `0` across all 12 Artisan commands (Symfony 8.0 removed the constant).
- `CarveTraceMiddleware::handle()` return type changed from `Response` to `mixed` (Symfony 8.0 type resolution).

## v0.1.0-alpha — 2026-06-27

### Added
- **Package Foundation:** Composer package with Laravel service provider, config, and auto-discovery.
- **Static Scanner:** Analyzes routes, controllers, models, migrations, and raw SQL usage using `nikic/php-parser`.
- **Runtime Tracer:** Middleware-based HTTP tracing with query event capture. Supports database and JSONL stores. Disabled by default.
- **Graph Builder:** Combines static scan data and runtime traces into a weighted dependency graph.
  - Node types: route, controller, model, table, event, job
  - Edge types: route_handled_by, uses_model, model_owns_table, touches_table, co_occurs
  - Runtime evidence weighted higher than static analysis
- **Boundary Detection:** TableAffinityClusterer groups related database tables based on co-occurrence and shared dependencies. Produces explainable boundary suggestions with confidence, cohesion, coupling, and risk scores.
- **Migration Report:** Comprehensive Markdown report with executive summary, route-to-table map, candidate boundaries, shared table analysis, and recommended extraction order.
- **Commands:**
  - `carve:doctor` — Environment readiness check
  - `carve:scan` — Static analysis scan
  - `carve:analyze` — Build dependency graph
  - `carve:boundaries` — Detect candidate service boundaries
  - `carve:report` — Generate full migration report
  - `carve:trace-install` — Runtime tracing setup guide
  - `carve:install` — Publish configuration
- **Demo Fixtures:** Realistic billing + support domain scan data and trace logs for testing.
- **99+ PHPUnit tests** covering all core functionality.
- **PHPStan level 6** clean with zero errors.
- **Laravel Pint** coding standards enforced.
- **GitHub Actions CI** — validate, pint, phpstan, phpunit across PHP 8.2–8.4 with Laravel 11–12.
- **Community Files:** README, CHANGELOG, CONTRIBUTING, SECURITY, LICENSE.

### Changed
- Graph builder and boundary detection stubs filled with full implementations.
- Fixture scan data expanded to represent a realistic demo monolith (Billing + Support domains).

### Fixed
- `started_at` column in carve_traces migration made nullable.
- TraceRecorder now passes requestId, startedAt, endedAt to TraceRecord.
- Store failures no longer break HTTP requests.
- Service provider correctly binds all runtime dependencies.
- CouplingScorer and BoundaryNameGuesser edge-case matching.

### Removed
- None.

### Known Limitations (v0.1.0-alpha)
- No automatic service generation or extraction.
- Generators (`generate:service`, `shadow`, `diff`) are experimental stubs.
- Runtime tracing must be manually configured.
- Boundary suggestions require human review before any extraction work.

### Security
- Runtime tracing does not capture request bodies or SQL bindings by default.
- Sensitive value masking configurable via `config/carve.php`.
