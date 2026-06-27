# CarvePHP

**Find service boundaries in Laravel monoliths using static analysis + runtime tracing.**

CarvePHP is a Laravel-first toolkit that analyzes your existing monolith and produces actionable migration reports. It combines static code analysis with runtime tracing to identify service boundaries with evidence.

> **Status:** v0.1.0-alpha — Publishable preview. Ready for experimentation and feedback.

---

## Quickstart

```bash
composer require carvephp/carve --dev

# Publish config
php artisan carve:install

# Run environment check
php artisan carve:doctor

# Static scan your codebase
php artisan carve:scan --pretty

# Build dependency graph (combines static + runtime traces)
php artisan carve:analyze

# Detect candidate service boundaries
php artisan carve:boundaries --report=carve-boundaries.md

# Generate full migration report
php artisan carve:report --output=carve-report.md
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
| Automatic service generation | ❌ (planned) |
| Graph-based modularity clustering | 🔄 (stub) |
| Event sourcing trace analysis | ❌ (planned) |
| Shadow / diff mode for extraction validation | 🔄 (stub) |
| One-command microservice extraction | ❌ (never planned — you stay in control) |

---

## Commands

| Command | Description |
|---------|-------------|
| `carve:doctor` | Check environment readiness |
| `carve:scan` | Run static analysis on the monolith |
| `carve:analyze` | Build dependency graph from static scan + traces |
| `carve:boundaries` | Detect candidate service boundaries |
| `carve:report` | Generate comprehensive migration report |
| `carve:trace-install` | Guide runtime tracing setup |
| `carve:install` | Publish config |
| `carve:generate:service` | Generate service stub (experimental) |
| `carve:shadow` | Run shadow requests for diff testing (experimental) |

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

## Requirements

- PHP 8.2+
- Laravel 10, 11, or 12
- Composer 2.x

## Runtime Tracing (Optional)

For better boundary detection, enable runtime tracing:

```bash
php artisan carve:trace-install
# Follow the instructions, then:
echo "CARVE_TRACE_ENABLED=true" >> .env
php artisan migrate
```

This captures query and queue events. More traces → more accurate boundaries.

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## Security

See [SECURITY.md](SECURITY.md).

## License

MIT — see [LICENSE](LICENSE) for details.
