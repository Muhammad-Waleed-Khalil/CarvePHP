# Static Analysis

`php artisan carve:scan` extracts a static map of the monolith.

## What is extracted

- Routes (method, URI, name, controller action, middleware)
- Controllers (methods, constructor dependencies, model/DB usage)
- Models (table name, primary key, fillable, casts, relationships)
- Migrations (tables created, modified, dropped, columns, foreign keys)
- FormRequests (validation rules mapped to JSON schema)
- API Resources (output shape from `toArray()`)
- Events, jobs, listeners
- DB facade usage and raw SQL table references
- Service provider registrations

## Output

JSON file with meta, routes, classes, models, tables, migrations, db_usages, edges, and warnings.

## Uncertainty

When a table name is inferred by convention rather than explicit declaration, or when raw SQL parsing is uncertain, a warning is added to the output.
