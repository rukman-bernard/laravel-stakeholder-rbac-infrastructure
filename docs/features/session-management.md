# Session Management (Single-Session Enforcement Model)

This system implements a **single-session authentication model** to ensure predictable behaviour, improved user experience, and reduced ambiguity in a multi-guard environment.

---

## Problem Statement

In a multi-guard Laravel application, a single browser session may technically authenticate multiple guards simultaneously (e.g., `web`, `student`, `employer`).

This can lead to:

- Ambiguous dashboard routing
- Incorrect UI theming
- Confusing logout behaviour
- Unintended context leakage between portals

---

## Design Decision

The system enforces a **single effective authentication context per browser session**.

While Laravel permits multiple session-based guards to exist simultaneously, the application:

- Expects only one active guard per session
- Treats mixed-guard states as invalid
- Resolves ambiguous states deterministically
- Provides controlled recovery mechanisms when required

Single-session behaviour is enforced proactively through login handling, guest-route redirection, and logout logic.

---

## Guard Resolution Strategy

The system determines the effective guard for each request by:

1. Inspecting authenticated session-based guards
2. Applying a deterministic resolution strategy
3. Treating conflicting guard states as transitional or invalid

Guard resolution is applied consistently across:

- Dashboard routing
- AdminLTE configuration
- Theming decisions
- Redirect handling
- Logout and recovery behaviour

Under normal operation, only one guard is active.  
Deterministic resolution exists as a defensive safeguard rather than a primary operating mode.

---

## Logout-Before-Login Enforcement

Before processing any new login attempt, the system explicitly logs out any existing authenticated guard within the same browser session.

This ensures:

- Mixed-guard sessions cannot persist
- Each login begins from a clean authentication state
- Single-session guarantees are enforced proactively

This mechanism prevents ambiguous states rather than merely resolving them.

---

## Logout Behaviour

When logout is triggered:

- The effective authenticated guard is logged out
- The session is invalidated
- The CSRF token is regenerated
- Logout handling is centralised

After logout, users are redirected to the appropriate login entry point for the guard that was active:

- Internal users (`web`) → `/login`
- Students (`student`) → `/student/login`
- Employers (`employer`) → `/employer/login`

If no guard can be resolved, the user is redirected to the portal hub.

Logout behaviour is deterministic and never routes users to an unrelated portal.

---

## Recovery UX: Unresolvable States

In rare cases, an authenticated user may reach a state where:

- A route cannot be accessed due to authorisation failure, or
- A dashboard cannot be safely resolved

To prevent redirect loops or dead-end navigation states, the system provides a recovery route named `auth.reset`.

### Recovery Flow

1. The system detects an unresolvable or invalid authentication state.
2. A user-friendly recovery page is displayed.
3. The user is instructed to log out and sign in again.
4. The session is fully reset.
5. The user returns to the correct stakeholder login page.

The recovery route does not attempt to guess dashboards and does not expose internal guard or role concepts.

---

## Benefits

- Deterministic routing and UI behaviour
- Clear and predictable logout semantics
- Controlled recovery from edge cases
- Reduced session-related ambiguity and cross-portal risk
- Cleaner mental model for developers
- Safer foundation for future SSO integration

---

## Related Documentation

- [Authentication & Guards](../architecture/auth-and-guards.md)
- [Authorisation (RBAC)](../architecture/authorisation-rbac.md)
- [Security Posture](./security-posture.md)