# ADR-008: Guard Validation for RBAC Entity Creation

## Status
Accepted

---

## Context

The system uses multi-guard authentication and guard-scoped RBAC entities.

Guard behaviour, classification, and resolution are defined in:

→ [Authentication & Guards](../architecture/auth-and-guards.md)

During evaluation of external RBAC implementations  
([Redis-Backed RBAC Evaluation](../case-studies/redis-rbac-evaluation.md)), it was observed that guard-scoped entities could be created using arbitrary guard names, including values not defined in the authentication configuration.

A similar gap was identified within this infrastructure artefact.

If roles or permissions are created with invalid or non-existent guards:

- RBAC records may not reflect actual authentication contexts
- authorisation behaviour may become ambiguous
- debugging and reasoning about access control becomes difficult
- UI and administrative workflows may expose invalid options

RBAC entity creation must therefore be aligned with the system’s guard model.

---

## Decision

Guard names used during role and permission creation are validated using the application guard abstraction.

Only values returned from:

`App\Constants\Guards::configured()`

are considered valid for RBAC entity creation and update operations.

This validation applies to:

- permission creation
- role creation
- any form, Livewire component, seeder, or service that accepts guard input for RBAC entities

User-facing selection controls must restrict available options to configured guards rather than allowing free-form input.

---

## Rationale

RBAC entities are meaningful only when associated with valid authentication contexts.

The system follows a two-layer guard model:

1. Laravel configuration (`config/auth.php`) defines available guards at runtime  
2. The application guard registry (`App\Constants\Guards`) defines which guards are recognised by the infrastructure  

This ensures that newly introduced guards are explicitly adopted into the application model rather than becoming implicitly usable through configuration alone.

Validating guard input through `Guards::configured()`:

- aligns RBAC behaviour with authentication configuration
- prevents creation of invalid or orphaned guard-scoped entities
- maintains consistency across validation, UI, and service layers
- supports strict multi-guard boundary enforcement

---

## Consequences

- RBAC creation flows reject invalid or unrecognised guard names
- UI components must source guard options from `Guards::configured()`
- seeders and automated workflows must use configured guards only
- existing data may require validation or cleanup if invalid guards were previously used
- the RBAC layer becomes more predictable and aligned with system architecture

---

## Related Documents

- [Authentication & Guards](../architecture/auth-and-guards.md)
- [Authorisation (RBAC)](../architecture/authorisation-rbac.md)
- [ADR-002: Multi-Guard Authentication](./ADR-002-multi-guard-auth.md)
- [Redis-Backed RBAC Evaluation](../case-studies/redis-rbac-evaluation.md)