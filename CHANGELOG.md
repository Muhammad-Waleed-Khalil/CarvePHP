# Changelog

## Unreleased / v0.1.0-alpha

### Added
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
- **80+ PHPUnit tests** covering all core functionality.
- **PHPStan level 6** clean with zero errors.
- **Laravel Pint** coding standards enforced.
- **GitHub Actions CI** — runs validate, pint, phpstan, phpunit.

### Changed
- Graph builder and boundary detection stubs filled with full implementations.
- Fixture scan data expanded to represent a realistic demo monolith (Billing + Support domains).

### Fixed
- `started_at` column in carve_traces migration made nullable.
- TraceRecorder now passes requestId, startedAt, endedAt to TraceRecord.
- Store failures no longer break HTTP requests.
- Service provider correctly binds all runtime dependencies.

### Removed
- None.

### Security
- Runtime tracing does not capture request bodies or SQL bindings by default.
- Sensitive value masking configurable via `config/carve.php`.
