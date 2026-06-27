# Strangler Pattern

CarvePHP supports the strangler fig pattern through generated configuration and optional middleware.

## Generated Config

```
carve-output/monolith/config/carve_services.php
```

This config maps route patterns to target service URLs with per-route enabled/shadow toggles.

## Shadow Traffic

When shadow mode is enabled, requests matching a route pattern are forwarded to the new service in the background. The monolith response is still returned to the user. Differences are logged for analysis.

## Phases

1. **Coexistence** — Both monolith and service run; service is shadow-only
2. **Read migration** — Service handles reads; monolith handles writes
3. **Write migration** — Writes are dual-written; monolith is source of truth
4. **Full cutover** — Service handles all traffic; monolith is decommissioned

## Safety

- Shadow mode must not break user-facing requests
- Timeouts prevent shadow requests from blocking the main response
- Route patterns must be explicitly enabled
