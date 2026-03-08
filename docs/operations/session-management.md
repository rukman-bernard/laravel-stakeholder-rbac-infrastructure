# Session Management

This document describes how authentication sessions are managed across the Laravel Stakeholder RBAC Infrastructure Artefact.

It focuses on runtime behaviour, deterministic guard resolution, and user experience guarantees within the multi-guard authentication infrastructure.

---

## Scope

Session management covers:

- How authenticated sessions are created and terminated
- How session-based guards interact within a browser context
- How the system enforces a single active authentication context
- How dashboard and portal routing are resolved deterministically

This document applies to all stakeholder portals (`web`, `student`, `employer`).

---

## Design Principles

The session model is designed to:

- Enforce a single active authentication context per browser session
- Prevent ambiguous multi-portal states
- Provide deterministic dashboard routing
- Ensure predictable portal resolution
- Simplify operational reasoning and debugging

---

## Single Active Authentication Context

### Overview

The system enforces a single active authentication context per browser session.

At any given time:

- Only one session-based guard is treated as authoritative
- Routing, layout resolution, theming, and recovery behaviour are derived from the active guard
- Other authenticated guards (if present due to edge cases or interruption) are ignored for resolution purposes

All session guards (`web`, `student`, `employer`) share the same Laravel session store.  
Guard isolation is logical rather than storage-based.

---

## Guard Resolution

### Deterministic Resolution Order

Guard resolution follows a centrally defined deterministic order.

Guards are evaluated in resolution order, and the first authenticated guard is treated as active.

This behaviour is implemented through:

- `GuardResolver::detect()`
- `Guards::resolutionOrder()`

The system is designed to prevent multiple authenticated guards through:

- Logout-before-login enforcement
- Guest-route redirection
- Middleware guard checks

Resolution order exists solely to ensure predictable behaviour during edge cases and recovery scenarios.

It does not imply support for multiple concurrent authenticated guards.

---

## Active Identity Resolution

The active authentication context is resolved using `GuardResolver::identity()`.

This returns:

```php
[
  'guard' => $guard, 
  'user'  => $user, 
]
```
This structure is used consistently by:

* Portal hub routing
* Layout resolution
* Theming selection
* Dashboard routing
* Recovery flows

***

## Portal Hub Behaviour

The portal hub functions as a neutral entry point.

### Behaviour

1. Resolve the active guard via deterministic resolution.

2. Resolve the appropriate dashboard route.

3. Redirect the user to the authoritative portal.

This guarantees:

* One authoritative portal per session
* No cross-portal ambiguity
* Deterministic routing behaviour

***

## Logout Behaviour

Logout is handled via the central logout controller.

When a user logs out:

* The active guard session is terminated
* The Laravel session is refreshed
* The user is redirected to the correct stakeholder login entry point
* If no guard is active, the portal hub is used as a neutral fallback

Logout behaviour supports the single-session model and prevents unintended cross-portal states.

***

## User Experience Guarantees

The single active session model ensures:

* Users always interact with one portal at a time
* Dashboard routing is predictable
* Theming and layout are consistent
* Browser tab behaviour remains intuitive
* Authentication boundaries remain clear

***

## Security Considerations

The session model contributes to security by:

* Preventing ambiguous privilege overlap between guards
* Avoiding unintended cross-portal access
* Reducing risks associated with stale authentication contexts
* Enforcing explicit re-authentication when switching portals

Session enforcement complements:

* CSRF protection
* Guard isolation
* Role-based authorisation within the `web` guard

***

## Recovery Behaviour

If an authenticated user reaches an unauthorised route or if a safe dashboard cannot be resolved:

1. A user-friendly recovery page is shown.

2. If a dashboard can be resolved safely, a “Go to my dashboard” option is provided.

3. If resolution is not possible, the user is offered a session reset option (`auth.reset`).

The session reset flow allows the user to:

* Log out via the standard logout route
* Re-authenticate cleanly
* Restore a valid authentication context

***

## Operational Characteristics

* Session behaviour is deterministic and testable
* Guard resolution order is centrally defined
* All session guards share a common Laravel session store
* Debugging is simplified due to single active context enforcement
* No portal relies on implicit or ambiguous session state

***

## Related Documentation

* [Authentication & Guards](../architecture/auth-and-guards.md)
* [Authorisation (RBAC)](../architecture/authorisation-rbac.md)
* [Dashboards Feature](../features/dashboards.md)
* [ADR-002: Multi-Guard Authentication](../decisions/ADR-002-multi-guard-auth.md)

***

## Summary

* The system enforces a single active authentication context per session
* Guard resolution follows deterministic resolution order
* Portal routing is predictable and controlled
* Security and usability are reinforced by session isolation
* The design supports clear operational reasoning and maintenance

```