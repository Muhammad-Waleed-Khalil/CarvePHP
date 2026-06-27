# Commands

All commands are registered under the `carve:*` namespace.

| Command | Description |
|---------|-------------|
| `carve:install` | Publish config, migrations, and optional stubs |
| `carve:doctor` | Check environment readiness |
| `carve:scan` | Run static analysis on the monolith |
| `carve:trace-install` | Guide setup of runtime tracing middleware/listeners |
| `carve:analyze` | Build a dependency graph from static scan + runtime traces |
| `carve:boundaries` | Suggest candidate microservice boundaries |
| `carve:report` | Generate full migration report (JSON/Markdown) |
| `carve:generate-service` | Generate service skeleton for a boundary |
| `carve:generate-openapi` | Generate OpenAPI spec for a boundary |
| `carve:generate-client` | Generate monolith client SDK for a boundary |
| `carve:shadow` | Manage shadow traffic configuration |
| `carve:diff` | Compare monolith vs service responses |

Run `php artisan help carve:<command>` for each command's options.
