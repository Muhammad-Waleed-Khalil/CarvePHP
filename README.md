# CarvePHP

**Find service boundaries in Laravel monoliths using static analysis + runtime tracing.**

[![Tests](https://github.com/Muhammad-Waleed-Khalil/CarvePHP/actions/workflows/tests.yml/badge.svg)](https://github.com/Muhammad-Waleed-Khalil/CarvePHP/actions/workflows/tests.yml)

CarvePHP is a Laravel-first toolkit that analyzes your existing monolith and produces actionable migration reports. It combines static code analysis with runtime tracing to identify service boundaries with evidence.

> **Status:** v0.1.2-alpha — Preview release. Ready for experimentation and feedback. Not yet production-ready for automatic extraction.
>
> **Latest alpha:** v0.1.2-alpha

---

## Installation

```bash
composer require "carvephp/carve:>=0.1.0-alpha <0.2.0" --dev
```

After installing, publish the config and run the environment check:

```bash
php artisan carve:install
php artisan carve:doctor
```

---

## Quickstart

```bash
# Static scan your codebase
php artisan carve:scan --pretty

# Build dependency graph (combines static + runtime traces)
php artisan carve:analyze

# Detect candidate service boundaries
php artisan carve:boundaries --report=carve-boundaries.md

# Generate full migration report
php artisan carve:report --output=carve-report.md
```

For better results, enable runtime tracing (optional):

```bash
php artisan carve:trace-install
# Follow the instructions, then:
echo "CARVE_TRACE_ENABLED=true" >> .env
php artisan migrate
```

**Sample report snippet:**

```markdown
## Candidate 1: Billing
| Metric       | Value |
|--------------|-------|
| Confidence   | 85.3% |
| Cohesion     | 92.0% |
| Coupling     | 14.7% |
| Risk         | 22.1% |

**Tables:** `invoices`, `payments`, `customers`
**Controllers:** `InvoiceController`, `PaymentController`
**Why suggested:** invoices and payments co-occurred 4× in runtime traces and are both touched by billing routes.
```

---

## What It Does Today

| Feature | Status |
|---------|--------|
| Static scan (routes, controllers, models, migrations, raw SQL) | ✅ |
| Runtime tracing middleware (HTTP + queue + query events) | ✅ |
| Dependency graph builder (static + trace merge) | ✅ |
| Table affinity clustering for boundary detection | ✅ |
| Comprehensive Markdown migration report | ✅ |
| Environment doctor command | ✅ |
| JSONL + database trace stores | ✅ |

## What It Does Not Do (Yet)

| Feature | Status |
|---------|--------|
| Automatic service generation | ❌ (planned for v0.2–v0.3) |
| One-command microservice extraction | ❌ (you stay in control) |
| Database splitting automation | ❌ (manual after report) |
| Production-ready extraction workflow | ❌ (v1.0 target) |
| Shadow / diff mode for extraction validation | 🔄 (experimental stub) |

---

## Requirements

- PHP 8.2+
- Laravel 11, 12, or 13
- Composer 2.x

## Commands

| Command                  | Description                                      |
| ------------------------ | ------------------------------------------------ |
| `carve:install`          | Publish config and migrations                    |
| `carve:doctor`           | Check environment readiness                      |
| `carve:scan`             | Run static analysis on the monolith              |
| `carve:trace-install`    | Guide runtime tracing setup                      |
| `carve:analyze`          | Build dependency graph from static scan + traces |
| `carve:boundaries`       | Detect candidate service boundaries              |
| `carve:report`           | Generate comprehensive migration report          |
| `carve:generate-service` | Generate service stub (experimental)             |
| `carve:generate-openapi` | Generate OpenAPI stub (experimental)             |
| `carve:generate-client`  | Generate client SDK stub (experimental)          |
| `carve:shadow`           | Shadow traffic/diff support (experimental)       |
| `carve:diff`             | Compare saved shadow responses (experimental)    |

---

## Architecture

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  Static Scanner  │     │  Runtime Tracer  │     │  Command CLI    │
│  (routes, models,│     │  (middleware,    │     │  (doctor, scan, │
│   migrations,    │────▶│   listeners,     │────▶│   analyze,      │
│   db_usages)     │     │   stores)        │     │   boundaries,   │
└─────────────────┘     └─────────────────┘     │   report)       │
        │                       │                └────────┬────────┘
        ▼                       ▼                         │
┌─────────────────────────────────────────┐                │
│          Graph Builder                   │◀──────────────┘
│  (WeightedGraph + Nodes + Edges)        │
└────────────────┬────────────────────────┘
                 ▼
┌─────────────────────────────────────────┐
│        Boundary Detection                │
│  (TableAffinityClusterer + Scoring)     │
└────────────────┬────────────────────────┘
                 ▼
┌─────────────────────────────────────────┐
│           Report Writer                  │
│  (Markdown + JSON)                      │
└─────────────────────────────────────────┘
```

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## Security

See [SECURITY.md](SECURITY.md).

## License

MIT — see [LICENSE](LICENSE) for details.
