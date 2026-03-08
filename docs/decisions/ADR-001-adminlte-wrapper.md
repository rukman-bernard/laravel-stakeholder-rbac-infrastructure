# ADR-001: Separation of Authentication, Authorisation, and Presentation Concerns 


## Status
Accepted

## Context
The system serves multiple stakeholder groups (students, employers, and internal staff),
each requiring different access rules, dashboards, and UI presentation.

A common risk in multi-role systems is tightly coupling:
- authentication logic,
- authorisation rules,
- and UI rendering decisions.

This leads to fragile code, poor extensibility, and security risks.

## Decision
The system separates concerns into three distinct layers:

1. **Authentication**
   - Implemented using Laravel multi-guard authentication.
   - Guards define _who_ is authenticated and _which portal_ they belong to.

2. **Authorisation**
   - Implemented using Spatie Roles & Permissions.
   - Roles and permissions define *what* an authenticated user is allowed to do.
   - Currently applied primarily to the `web` (staff) guard.

3. **Presentation (Theming & Layout)**
   - Implemented using AdminLTE layouts and stakeholder-specific skins.
   - UI presentation is resolved dynamically after authentication, primarily based on the active guard, with optional role-aware configuration for staff users.
   - The theming mechanism is guard-driven by default and can be extended in future to support role-specific variations within the `web` guard if required.

## Rationale
Separating these concerns provides:

- Clear security boundaries
- Easier reasoning about access control
- Independent evolution of authentication, RBAC, and UI
- Safer future extensions (new stakeholders, SSO, additional roles)

This structure aligns with Laravel’s guard-based authentication model and established RBAC design patterns in multi-portal systems.

## Consequences
- Slightly more configuration is required upfront due to explicit separation of concerns.
- Improved long-term maintainability through clear responsibility boundaries.
- Reduced coupling between authentication, authorisation, and UI layers.
- A stable foundation for scaling stakeholders, roles, and authentication mechanisms without structural refactoring.

## Related Documents
- [Authentication & Guards](../architecture/auth-and-guards.md)
- [Authorisation (RBAC)](../architecture/authorisation-rbac.md)
- [Theming Strategy](../architecture/theming-strategy.md)
