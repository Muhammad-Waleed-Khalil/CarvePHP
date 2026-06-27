# Contributing to CarvePHP

CarvePHP is an open-source project and contributions are welcome!

## Getting Started

1. Fork the repository
2. Clone your fork
3. Install dependencies:

```bash
composer install
```

4. Run tests to verify your setup:

```bash
vendor/bin/phpunit --no-coverage
```

## Development Workflow

1. Create a feature branch: `git checkout -b feat/my-feature`
2. Make your changes
3. Run quality gates before committing:

```bash
composer validate --no-check-lock
vendor/bin/pint --test
vendor/bin/phpstan analyse --no-progress
vendor/bin/phpunit --no-coverage
```

4. Commit with a descriptive message
5. Push and open a pull request

## Coding Standards

- Follow PSR-12 as enforced by Laravel Pint.
- Use strict types everywhere: `declare(strict_types=1);`
- All public methods should have return type hints.
- Favor readonly properties and immutable value objects.
- No docblock comments unless necessary for PHPStan.

## Testing

- Unit tests go in `tests/Unit/`, feature tests in `tests/Feature/`.
- Use Orchestra Testbench for Laravel feature tests.
- Use in-memory SQLite for database tests.
- Aim for full coverage of new functionality.

## What Needs Help

See [issues labeled "help wanted"](https://github.com/Muhammad-Waleed-Khalil/CarvePHP/issues?q=is%3Aissue+is%3Aopen+label%3A%22help+wanted%22).

## Questions

Open a [discussion](https://github.com/Muhammad-Waleed-Khalil/CarvePHP/discussions) or an issue.
