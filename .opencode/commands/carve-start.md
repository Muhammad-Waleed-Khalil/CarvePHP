---
description: >-
  Initialize the CarvePHP expert team for a new analysis session.
  Delegates to repo-cartographer, laravel-analyzer, and boundary-architect first.
  Requires test-safety and final-reviewer before final completion.
agent: carve-chief
arguments:
  type: string
  description: Arguments to pass to the carve-chief orchestrator
  required: false
---

# /carve-start — Initialize CarvePHP Expert Team

This command initializes the full CarvePHP expert agent team in the correct order.

## Workflow

1. **carve-chief** reads current context:
   - `git status --short` for dirty/clean state
   - `git branch --show-current` for current branch
   - `php -v` and `composer --version` if available

2. **repo-cartographer** maps the full repository tree (read-only).

3. **laravel-analyzer** analyzes Laravel/PHP code structure (read-only).

4. **boundary-architect** suggests candidate boundaries (read-only).

5. If generation is requested, **extractor-builder** produces output (edits with approval).

6. **test-safety** runs tests/checks (with approval).

7. **final-reviewer** reviews all output (read-only).

8. **carve-chief** summarises everything for the user.

## Safety rules

- **No edits during discovery** (steps 1–4 are read-only).
- **No file deletion** at any point.
- **No automatic git push**.
- **No automatic `composer install`** without user approval.
- **All generation goes to `carve-output/`** by default.
- **test-safety** and **final-reviewer** must approve before final completion.

## Example

```
/carve-start scan the current Laravel app
/carve-start generate billing service skeleton
/carve-start run boundaries and report
```

## Arguments

Accepts `$ARGUMENTS` passed to carve-chief for interpretation.
