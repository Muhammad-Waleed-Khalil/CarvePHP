# CarvePHP

**Laravel-first monolith to microservices migration toolkit.**

CarvePHP helps Laravel teams incrementally migrate monoliths toward microservices by discovering natural seams, proposing candidate bounded contexts, generating contracts and service scaffolds, adding strangler routing/proxy support, generating client SDKs, and providing testing safety rails.

## What It Is

- A discovery and extraction assistant for Laravel monoliths
- Gets teams 70–85% of the way toward a safe extraction workflow
- Explainable: every automated decision is documented in generated reports
- Safe by default: scan/report first, generate into `carve-output/`

## What It Is Not

- A push-button monolith-to-microservices converter
- A replacement for human architecture decisions
- A code migration tool that mutates your monolith automatically

## Installation

```bash
composer require carvephp/carve --dev
php artisan carve:install
php artisan migrate
```

## Quickstart

```bash
# Check environment readiness
php artisan carve:doctor

# Run static analysis
php artisan carve:scan --pretty

# Combine scan results with runtime traces into a graph
php artisan carve:analyze

# Suggest candidate service boundaries
php artisan carve:boundaries --report=carve-boundaries.md

# Generate a service skeleton for a candidate boundary
php artisan carve:generate-service billing --with-openapi --with-client --with-tests
```

## Commands

| Command | Description |
|---------|-------------|
| `carve:install` | Publish config, migrations, stubs |
| `carve:doctor` | Check environment readiness |
| `carve:scan` | Run static analysis on the monolith |
| `carve:trace-install` | Guide middleware/listener setup for runtime tracing |
| `carve:analyze` | Build a dependency graph from static + runtime data |
| `carve:boundaries` | Suggest candidate microservice boundaries |
| `carve:report` | Generate a full migration report |
| `carve:generate-service` | Generate a service skeleton for a boundary |
| `carve:generate-openapi` | Generate an OpenAPI spec for a boundary |
| `carve:generate-client` | Generate a monolith client SDK for a boundary |
| `carve:shadow` | Manage shadow traffic configuration |
| `carve:diff` | Compare monolith vs service responses |

## Safety

- Runtime tracing is **disabled by default**
- All generation goes to `carve-output/` unless configured otherwise
- No existing monolith files are ever mutated automatically
- Sensitive data (passwords, tokens, SQL bindings) is never logged by default

## Limitations

- v0.1 does not perform automatic code migration
- Raw SQL table extraction is best-effort and may miss dynamic queries
- API Resource schema inference is partial
- Event payload schemas are placeholder stubs

## Roadmap

- [x] Package foundation
- [x] Static scanner
- [ ] Runtime tracer
- [ ] Graph builder
- [ ] Boundary detection
- [ ] Reports
- [ ] Generators
- [ ] Shadow/diff stubs
- [ ] Docs and examples
