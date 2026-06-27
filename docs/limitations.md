# Limitations

## v0.1

- **No automatic code migration** — Generated service skeletons contain placeholder controller logic, not migrated business logic
- **Raw SQL parsing is best-effort** — Dynamic queries, string interpolation, and complex JOINs may produce uncertain results
- **API Resource inference is partial** — Only simple `toArray()` returns with direct property access are analyzed
- **Event payload schemas** are placeholder stubs; actual event structure must be documented manually
- **No UI** — All interaction is through Artisan commands and generated files
- **No Kubernetes/container orchestration generation** — Dockerfile output is minimal
- **No gRPC** — OpenAPI/REST only
- **No OpenTelemetry integration** — Traces use CarvePHP's own stores
- **Boundary suggestions are statistical** — They require human review before acting on them
- **Laravel-specific** — Not designed for non-Laravel PHP projects
- **MySQL-focused** — Postgres support is planned but not yet first-class
