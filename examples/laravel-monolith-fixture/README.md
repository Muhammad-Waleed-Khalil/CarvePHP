# Laravel Monolith Fixture

A minimal Laravel monolith for testing CarvePHP.

## Domain

- **Billing**: invoices, payments
- **Identity**: users
- **Support**: tickets

## Routes

- `GET /api/invoices`
- `POST /api/invoices`
- `POST /api/payments`
- `GET /api/tickets`
- `POST /api/tickets`

## Expected Boundaries

- **Billing**: invoices + payments
- **Support**: tickets
