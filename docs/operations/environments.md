# Reference Environment

## Purpose

This document discloses the validated runtime environment used for the reference implementation of the Laravel Stakeholder RBAC Infrastructure Artefact.

It supports:

- Reproducibility of evaluation
- Transparent dependency disclosure
- Controlled version alignment

Architectural behaviour remains independent of these specific versions.

---

## Validated Runtime Stack

### Backend
- Laravel 11.x
- PHP 8.2.x

Required PHP extensions:
- OpenSSL
- PDO
- Mbstring
- Tokenizer
- XML
- Ctype
- JSON
- BCMath
- Redis (phpredis)

### Database
- MariaDB 10.x (MySQL-compatible)

### Cache / Session Backend
- Redis 7.x
- Redis client: `phpredis`

### Frontend Tooling
- Node.js 20.x
- Vite (laravel-vite-plugin)

### UI Layer
- AdminLTE 3.x
- Bootstrap 4.x
- Livewire 3.x

### Authorisation
- Spatie Laravel Permission 6.x

---

## Containerised Validation

The system was validated using:

- Docker Engine 24.x
- Docker Compose v2

Container separation was used to isolate:

- Application runtime
- Database
- Cache / session backend
- Asset build tooling

The architecture itself does not require Docker but was validated under containerised execution.

---

## Session Configuration Model

The validated environment used:

- Session driver: Redis
- Cache driver: Redis
- Queue driver: Redis (optional)
- Single-session enforcement enabled

The architecture remains compatible with alternative session drivers.

---

## Version Variability

The architecture is not tightly coupled to these specific minor versions.

Minor updates within:

- Laravel 11.x
- PHP 8.2.x
- Redis 7.x
- MariaDB 10.x

are expected to remain compatible unless upstream breaking changes occur.

Major upgrades should be validated independently.

---

## Architectural Independence

The following architectural behaviours are independent of runtime version:

- Deterministic guard resolution
- Single active authentication context
- Guard-scoped dashboard routing
- Role-aware dashboard resolution
- Environment-driven configuration

---

## Summary

This reference environment provides:

- Transparent dependency disclosure
- Reproducibility baseline
- Operational clarity without architectural coupling

The authentication infrastructure remains version-agnostic within compatible runtime ranges.