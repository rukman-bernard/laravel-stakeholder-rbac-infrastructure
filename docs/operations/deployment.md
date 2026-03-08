# Deployment

## Purpose

This document describes the runtime deployment assumptions of the Laravel Stakeholder RBAC Infrastructure Artefact.

The system is designed to operate predictably across environments using container-based execution and environment-driven configuration.

This document focuses only on deployment characteristics required for correct system operation.

---

## Container-Based Deployment

The artefact is designed to run in containerised environments.

Key characteristics:

- Application code runs inside an immutable container
- Configuration is injected via environment variables
- Database and cache services are externalised
- Application containers remain stateless

Containerisation ensures:

- Environment parity
- Deterministic startup behaviour
- Isolation between environments

Refer to:
- [ADR-006: Docker Adoption](../decisions/ADR-006-docker-adoption.md)

---

## Environment Configuration

All environment-specific behaviour is defined via environment variables.

Examples:

- `APP_ENV`
- `APP_DEBUG`
- Database connection settings
- Session driver
- Cache driver

No secrets are stored in source control.

Authentication behaviour, guard definitions, and session handling are environment-aware but configuration-driven.

---

## Asset Build (Vite)

The system uses Vite for frontend asset management.

### Development Mode

- Assets served dynamically
- Hot module replacement enabled
- No compiled artefacts required

### Production Mode

- Assets must be prebuilt
- Only compiled artefacts are served
- No runtime compilation

Production builds must include:

- AdminLTE base assets
- Stakeholder-specific styles
- JavaScript bundles

---

## Database Migrations

Deployment must include schema migrations.

Guidelines:

- Migrations are idempotent
- Schema must be up to date before accepting traffic
- Authentication tables must exist before login is enabled

The artefact assumes migrations are executed as part of deployment.

---

## Session and Environment Isolation

Each environment must isolate:

- Session storage
- Cache storage
- Database connections

The single-session enforcement model applies at runtime regardless of environment.

Refer to:
- [Session Management](./session-management.md)

---

## Operational Guarantees

When deployed according to these principles, the system guarantees:

- Deterministic guard resolution
- Single active authentication context
- Role-aware and guard scoped dashboard routing
- Environment-driven configuration

---

## Related Documentation

- [Operations Overview](./overview.md)
- [Session Management (Single-Session Enforcement Model)](../features/session-management.md)
- [ADR-006: Docker Adoption](../decisions/ADR-006-docker-adoption.md)