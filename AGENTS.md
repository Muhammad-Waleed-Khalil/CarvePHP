# CarvePHP — Agent Rules

## Product Goal

CarvePHP is a Laravel-first monolith-to-microservices migration toolkit. It provides static analysis, runtime tracing, graph building, boundary detection, reports, service skeleton generation, OpenAPI stubs, client SDK generation, contract tests, and safe strangler-pattern tooling.

## What CarvePHP Is

- A discovery and extraction *assistant* for Laravel monoliths
- A toolkit that gets teams 70-85% of the way toward a safe extraction workflow
- Explainable: every automated decision is documented in generated reports
- Safe by default: scan/report first, generate into `carve-output/` unless configured otherwise

## What CarvePHP Is NOT

- A push-button monolith-to-microservices converter
- A replacement for human architecture decisions
- A code migration tool that mutates your monolith automatically

## Safety Rules

1. **Never delete or mutate existing monolith files** unless the user explicitly runs a generator command into a chosen output directory.
2. **Always scan/report before suggesting changes.**
3. **Generate into `carve-output/`** by default; never overwrite without `--force`.
4. **Explain every automated decision** — no black-box suggestions.
5. **Mark uncertainty** — if a schema is guessed, a route is unresolved, or a table name is inferred by convention, note it with a warning.
6. **Do not log secrets** — never capture request bodies, SQL bindings, or authorization headers by default.
7. **Runtime tracing is disabled by default** — opt-in only.

## Build Order

Follow this order for implementation:

1. Package foundation (ServiceProvider, config, migrations, base commands)
2. Static scanner (source finder, PHP parser, route/controller/model analyzers)
3. Runtime tracer (middleware, listeners, trace stores)
4. Graph builder (nodes, edges, co-occurrence)
5. Boundary detection (clustering, scoring, naming)
6. Reports (Markdown, JSON)
7. Generators (service skeleton, OpenAPI, client SDK, configs, tests)
8. Shadow/diff stubs
9. Docs, examples, CI, polish

## Laravel/PHP Coding Standards

- PHP 8.2+ typed classes with `declare(strict_types=1);`
- Use interfaces for analyzers, graph exporters, trace stores, boundary algorithms, and generators
- Use dependency injection through the Laravel service provider
- Keep command classes thin; place real logic in services
- Use clear namespaces under `Carve\`
- Laravel 10, 11, and 12 compatibility
- MySQL first-class DB target; abstract for Postgres later

## Static Scan Before Mutation

- Every implementation phase starts with static analysis of the target monolith
- Results are written to JSON before any generation
- Generation decisions are based on analyzed data, not guesswork

## Tests Required for Implementation

- Unit tests for: table name inference, raw SQL extraction, validation rule mapping, graph operations, clustering algorithms, scoring, stub rendering, response diffing
- Feature tests for: package boot, command registration, config publish, migration run, middleware recording, scan output, boundary reports, generator output
- Use Orchestra Testbench for package tests
- Fixture app for end-to-end testing of scan/analyze/boundaries/generate flow
