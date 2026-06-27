# Data Migration

CarvePHP does not automatically split databases. Instead, it generates a migration plan for each boundary.

## Migration Modes

### Shared Database Transitional Mode

The new service connects to the same database as the monolith. Tables are logically owned by the service but physically co-located. Simplest starting point.

### Outbox/Event Sync Mode

The monolith writes to an outbox table. A publisher sends events to the service. The service maintains its own read models. Suitable when the service needs eventual consistency.

### Database-per-Service Mode

Each service owns its database. Data is migrated via scripts. Cross-service queries use API calls.

## Output

For each boundary, a data ownership proposal is generated with owned tables, read-only external tables, risk level, recommended migration mode, and step-by-step instructions.
