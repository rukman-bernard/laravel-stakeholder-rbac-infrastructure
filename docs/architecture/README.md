# Architecture Documentation

This section presents the architectural design of the Laravel Stakeholder RBAC Infrastructure Artefact.

It explains how the system is structured, how responsibilities are separated, and how major technical concerns interact within the Laravel infrastructure. While the focus remains on design intent and architectural rationale, the documentation references the concrete services that enforce these behaviours.

---

## Scope

**Architecture documentation covers:**

- Multi-guard authentication architecture (Laravel session guards)
- Role-based authorisation using Spatie RBAC
- Deterministic guard resolution and dashboard routing
- AdminLTE runtime configuration and theming strategy
- Single-session enforcement model
- Infrastructure boundaries and deployment separation

**This section answers questions such as:**

- How are internal users and portal stakeholders isolated?
- How does authentication differ from authorisation at runtime?
- How are dashboards resolved deterministically?
- How is UI presentation handled without duplicating layouts?
- How does the infrastructure prevent cross-portal privilege leakage?
- How do these architectural choices support long-term maintainability and extensibility?

---

## Architectural Principles

The system architecture is guided by the following principles:

### Separation of Concerns
Authentication, authorisation, and presentation are explicitly decoupled:

- Authentication context is resolved via `GuardResolver`
- Dashboard routing is handled by `DashboardResolver`
- Login redirection is managed by `GuardRedirectService`
- Logout enforcement is centralised in `GuardLogoutService`
- Password broker mapping is handled by `PasswordBrokerResolver`
- Runtime UI configuration is applied via `AdminLTESettingsService`

No layer directly couples authentication logic with RBAC or presentation logic.

---

### Deterministic Behaviour

Guard resolution and routing behave predictably across sessions:

- Session guards are resolved deterministically via `GuardResolver`, with priority applied only in defensive edge cases.
- Dashboard routes are mapped using `config/nka.php`
- Guest-route protection is enforced by `RedirectLoggedInToDashboard`
- Redirect recovery is handled through the `auth.reset` route

This ensures that only one authentication context is active per browser session.

---

### Upgrade Safety

Third-party frameworks (e.g., AdminLTE) are extended rather than modified:

- AdminLTE configuration is applied at runtime using `AdminLTESettingsService`
- No vendor files are edited
- Layout behaviour is controlled through configuration and service injection

This preserves framework upgrade compatibility.

---

### Extensibility

The architecture supports the addition of new portal guards without structural refactoring:

- Guard labels and priority are defined centrally (`App\Constants\Guards`)
- Dashboard routing is defined in `config/nka.php`
- Portal-specific UI overrides are isolated in `AdminLTESettingsService`
- Password broker mapping is configurable per guard

New guards can be introduced without modifying core authentication flow.

---

## Prototype Naming Legacy

Some configuration namespaces and identifiers in the reference implementation
use the prefix `nka` (for example `config/nka.php`).

This prefix originates from the prototype system used during the research
phase of this infrastructure. It is retained in the reference implementation
to preserve compatibility with the original evaluation environment.

The prefix does not represent a functional dependency on any specific
institution or domain. Systems adopting this infrastructure may freely rename
the configuration namespace if desired.

---

## Documents in This Section

- [Authentication & Guards](./auth-and-guards.md)  
  Describes the multi-guard authentication model, deterministic guard resolution (`GuardResolver`), dashboard mapping (`DashboardResolver`), and single-session enforcement.

- [Authorisation (RBAC)](./authorisation-rbac.md)  
  Explains how roles and permissions (Spatie) are enforced within an authenticated guard context.

- [Theming Strategy](./theming-strategy.md)  
  Describes how runtime UI configuration is applied via `AdminLTESettingsService`, including guard-aware layout and body class overrides (e.g., `glassmorphism-theme`).

---

## Summary

- The architecture defines clear security and identity boundaries between internal users (`web` guard) and portal stakeholders (`student`, `employer`).
- Authentication context resolution, dashboard routing, and UI configuration are deterministic and centrally managed.
- Authorisation is layered on top of authentication and remains guard-scoped.
- The infrastructure is modular, upgrade-safe, and designed for long-term evolution without compromising authentication isolation or presentation integrity.
