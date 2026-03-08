# Security Posture

This document outlines the security protections implemented across authentication, authorisation, session handling, and presentation layers.

---

## Authentication Security

- Laravel multi-guard authentication isolates stakeholder login contexts.
- Each guard uses a dedicated user provider and database table.
- Guard boundaries prevent cross-portal access.
- Only one effective authentication context is permitted per browser session.

---

## Session Security

- The system enforces a single active authentication context per session.
- Mixed-guard states are treated as invalid and resolved deterministically.
- Sessions are invalidated and CSRF tokens regenerated on logout.
- Guard-aware redirection prevents cross-portal redirect leakage.

---

## CSRF Protection

- Laravel CSRF middleware protects all state-changing web routes.
- CSRF tokens are regenerated during login and logout transitions.
- API routes are isolated and require explicit authentication mechanisms.

---

## Authorisation (RBAC)

- Spatie Roles & Permissions enforces fine-grained access control.
- Roles define responsibility scope.
- Permissions define action-level access.
- Server-side authorisation checks are the source of truth.
- UI visibility checks never grant access.

---

## Presentation Layer Safety

- Layout or theme selection does not grant access.
- AdminLTE theming is decoupled from authentication and authorisation logic.
- Skin activation is purely visual and does not influence access control.

---

## Infrastructure-Level Safeguards

- Container isolation prevents cross-service privilege sharing.
- Sensitive configuration is externalised via environment variables.
- Build-time and runtime assets are clearly separated.
- Deterministic dashboard resolution prevents ambiguous navigation states.

---

## Extensibility Considerations

The architecture supports future security enhancements such as:

- Multi-factor authentication (MFA)
- Single Sign-On (OAuth2 / SAML / OpenID Connect)
- Guard-specific role expansion
- Audit logging and access reviews

---

## Related Documentation

- [Authentication & Guards](../architecture/auth-and-guards.md)
- [Authorisation (RBAC)](../architecture/authorisation-rbac.md)
- [Session Management](./session-management.md)