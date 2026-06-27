# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability in CarvePHP, please report it privately via email to **muhammadwaleedkhalil@gmail.com**.

Please do **not** open a public issue for security vulnerabilities.

## What to Include

- A brief description of the issue
- Steps to reproduce
- Affected versions
- Any potential impact

## Response

You will receive a response within 48 hours. We will work with you to understand the issue and release a fix as soon as possible.

## Scope

CarvePHP is a development tool that runs locally. It does not expose network services or handle user data in production. However, security best practices apply to:

- Runtime trace data (disable SQL binding capture in production)
- Configuration files (do not commit secrets)
- Generated reports (review before sharing)

## Supported Versions

| Version | Supported |
|---------|-----------|
| 0.1.x   | ✅ Active |
| < 0.1   | ❌        |
