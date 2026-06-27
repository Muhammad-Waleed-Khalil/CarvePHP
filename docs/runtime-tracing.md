# Runtime Tracing

Runtime tracing records real behavioral coupling during actual request execution.

## Setup

```bash
php artisan carve:trace-install
```

This guides you through adding `CarveTraceMiddleware` to your HTTP kernel and registering the event listeners.

## What is recorded

- Request ID, route URI/name, HTTP method
- Controller action and duration
- DB tables touched, SQL operation types
- Events emitted (framework events filtered by default)
- Queue jobs dispatched/processed/failed
- Status code and exceptions
- Authenticated user ID (optional, disabled by default)

## Stores

- **Database**: Writes to `carve_traces` and `carve_trace_events` tables
- **JSONL**: Appends to a JSONL file
- **Null**: Discards all traces (for testing)

## Configuration

Runtime tracing is **disabled by default**. Enable it in `config/carve.php`:
`'enabled' => env('CARVE_TRACE_ENABLED', true)`

## Security

- SQL bindings are not captured by default
- Request bodies are never captured
- Authorization headers are never logged
- Sensitive value masking is enabled by default
