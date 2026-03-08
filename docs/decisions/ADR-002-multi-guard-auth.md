# ADR-002: Multi-Guard Authentication

## Status
Accepted

## Context

The system supports multiple stakeholder domains, including:

- Students
- Employers
- Internal users (administrative and system roles)

Each stakeholder domain requires:

- A distinct login flow
- Isolated authentication state
- Separate user models and database tables
- Restricted access to its respective portal

Using a single authentication context with role-based differentiation alone would increase the risk of cross-portal access, complicate session handling, and blur identity boundaries between stakeholder domains.

---

## Decision

The system uses **Laravel multi-guard authentication** to isolate authentication contexts.

Each stakeholder domain authenticates using its own guard:

- `web` — internal users (sysadmin, superadmin, admin)
- `student` — students
- `employer` — employers

Authentication guards determine:

- _Who_ is authenticated
- _Which portal_ the user belongs to
- _Which authentication provider_ (model/table) is used

Guard resolution is centralised using a dedicated resolver service, ensuring consistent and deterministic behaviour across the system.

Although Laravel technically allows multiple guards to be authenticated within the same browser session, the system enforces a **single active authentication context** per session. If multiple guards are detected, resolution follows a deterministic priority order defined in `App\Constants\Guards::priority()`.

Invalid or transitional states are resolved through controlled redirection and session reset mechanisms.

---

## Rationale

Using multiple guards provides:

- Strong isolation between stakeholder authentication contexts
- Reduced risk of accidental cross-portal access
- Cleaner routing and middleware enforcement
- Independent user providers and password reset resolution
- A scalable foundation for introducing new stakeholder domains

This approach aligns with Laravel best practices for multi-portal systems and ensures authentication concerns remain separate from role-based authorisation logic.

---

## Consequences

- Increased configuration complexity due to multiple guards and providers
- Clear separation of authentication responsibilities
- Deterministic identity resolution per request
- Simplified reasoning about portal access boundaries
- Easier future integration of external authentication mechanisms (e.g., OAuth, SSO)

---

## Related Documents

- [Authentication & Guards](../architecture/auth-and-guards.md)
- [Authorisation (RBAC)](../architecture/authorisation-rbac.md)
- [Stakeholders](../architecture/stakeholders.md)