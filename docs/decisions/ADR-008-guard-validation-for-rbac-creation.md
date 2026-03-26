# ADR-008: Guard Validation for RBAC Entity Creation

## Status
Accepted

## Context

The infrastructure uses Laravel multi-guard authentication to isolate stakeholder domains such as internal users, students, and employers.

Roles and permissions are scoped by guard within the RBAC layer. This means each role or permission is associated with a specific authentication guard and is expected to operate only within that guard context.

During evaluation of external RBAC implementations ([Redis-Backed RBAC Evaluation](../case-studies/redis-rbac-evaluation.md)), it was observed that guard-scoped entities could be created using arbitrary guard names, even when those guards were not defined in Laravel's `config/auth.php` configuration. A similar gap was identified within this infrastructure artefact.

If roles or permissions are created with non-existent guards:

- the database may contain invalid RBAC records
- authorisation behaviour may become ambiguous
- debugging becomes more difficult because guard metadata no longer reflects the actual authentication system
- future integrations and UI workflows may present invalid choices to developers or administrators

Because guards are part of the core authentication architecture, RBAC entity creation must remain aligned with the configured authentication model.

---

## Decision

Guard names used during role and permission creation are validated against the configured Laravel authentication guards.

Only values returned from `array_keys(config('auth.guards'))` are considered valid for RBAC entity creation and update operations.

This validation applies to:

- permission creation
- role creation
- any form, Livewire component, seeder, or service that accepts guard input for RBAC entities

User-facing selection controls restrict available options to configured guards rather than allowing free-form guard input.

---

## Rationale

Roles and permissions are meaningful only when tied to a valid authentication context.

Validating guard names against Laravel's configured guards provides:

- consistency between the RBAC layer and the authentication layer
- prevention of invalid or orphaned RBAC records
- clearer reasoning about authorisation scope
- safer administration workflows
- stronger support for multi-guard boundary enforcement

This decision keeps RBAC configuration aligned with the infrastructure's multi-guard design and avoids treating guard names as arbitrary metadata.

---

## Consequences

- RBAC creation flows become stricter and reject invalid guard names
- forms and administrative interfaces must source guard options from `config('auth.guards')`
- seeders and automated setup routines must use configured guards only
- existing records created with invalid guards may require review or cleanup
- the RBAC layer becomes more predictable and aligned with Laravel's authentication configuration

---

## Related Documents

- [Authentication & Guards](../architecture/auth-and-guards.md)
- [Authorisation (RBAC)](../architecture/authorisation-rbac.md)
- [ADR-002: Multi-Guard Authentication](./ADR-002-multi-guard-auth.md)
- [Redis-Backed RBAC Evaluation](../case-studies/redis-rbac-evaluation.md)