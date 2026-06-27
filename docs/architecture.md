# Architecture

CarvePHP is organized into these subsystems:

1. **Static Scanner** — Parses PHP source files to extract routes, controllers, models, migrations, DB usage, events, jobs, and configuration references.
2. **Runtime Tracer** — Laravel middleware and event listeners that record actual request/response behavior including DB queries, events, and queue jobs.
3. **Graph Builder** — Combines static and runtime data into a weighted directed graph where nodes are code artifacts and edges represent relationships.
4. **Boundary Detection** — Clusters graph nodes into candidate service boundaries using configurable algorithms.
5. **Report Generator** — Produces Markdown and JSON reports from analysis data.
6. **Code Generators** — Generate service skeletons, OpenAPI specs, client SDKs, feature flag configs, and contract tests.
7. **Shadow/Diff** — Shadow traffic management and response comparison for strangler pattern validation.

All subsystems are designed with interfaces for extensibility.
