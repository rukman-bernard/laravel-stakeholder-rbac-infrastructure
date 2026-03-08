# Authorisation Feature (RBAC)

This document describes how authorisation is implemented in the Laravel Stakeholder RBAC Infrastructure Artefact using **role-based access control (RBAC)**. It complements the authentication feature by defining what an authenticated user is allowed to do after login.

> Authentication determines **who** a user is and **which portal** they access.  
> Authorisation determines **what actions** that user can perform.

For architectural details, see:
- [Authentication & Guards](../architecture/auth-and-guards.md)
- [Authorisation (RBAC)](../architecture/authorisation-rbac.md)

---

## Feature Scope

Authorisation is currently applied primarily to **staff users** authenticated under the `web` guard.

RBAC is used to control:
- Role-based dashboard resolution within the `web` guard
- Menu visibility
- CRUD operations on users, roles, permissions, and academic entities
- Access to sensitive or system-level features

Student and employer portals rely mainly on **guard isolation** at this stage.
RBAC can be extended to those portals in the future if required.

---
## Roles

Roles represent **high-level responsibility** and access scope.  
In this system, roles apply primarily to authenticated `web` guard users.

### Web guard roles (persisted Spatie roles)

These roles are **stored in Spatie** and used for authorisation checks.

- `sysadmin`
  - System-level administration
  - Security-sensitive operations
  - Role and permission management

- `superadmin`
  - Full administrative access within the staff scope
  - Excludes sysadmin-only operations

- `admin`
  - Standard administrative access within the staff scope

### Staff (logical grouping, not a role)

This grouping exists **only in code and configuration**, never in the database.

- `staff`
  - Represents any authenticated `web` user who is **not** a `sysadmin`
  - Includes: `superadmin`, `admin` (and any future staff roles)
  - Used for:
    - dashboard routing
    - applying shared staff-only behaviour
  - **Not stored** in the roles table and never assigned via Spatie

**Rule:** `sysadmin` is a `web` role, but **not part of** the logical `staff` group.

##### Role & Access Matrix (Web Guard)
| Role         | Web Guard | Part of `staff` group | Dashboard Route      | Scope Summary                          |
|--------------|-----------|------------------------|----------------------|----------------------------------------|
| `sysadmin`   | ✅        | ❌                     | `sysadmin.dashboard` | System-level administration & security |
| `superadmin` | ✅        | ✅                     | `admin.dashboard`    | Full staff-level administration        |
| `admin`      | ✅        | ✅                     | `admin.dashboard`    | Standard staff administration          |

Dashboard routes are not permission-gated; permissions apply to features *within* dashboards, not to dashboard entry itself.

### Interpretation Rules

- **`sysadmin`**
  - Authenticates via the `web` guard
  - Explicitly **excluded** from the logical `staff` grouping
  - Routed to a dedicated system dashboard
  - Holds permissions not available to staff users

- **`staff` (logical grouping)**
  - Not a Spatie role
  - Represents *any* authenticated `web` user **except** `sysadmin`
  - Includes `superadmin`, `admin`, and future staff roles
  - Used internally for:
    - dashboard resolution
    - shared middleware logic
    - permission grouping

---

## Permissions

Permissions define **fine-grained access** to system features and actions.
They are assigned to roles and optionally to individual users.

### Permission structure
Permissions follow a clear, readable naming convention:

- `view <resource>`
- `create <resource>`
- `edit <resource>`
- `delete <resource>`
- `assign <resource> to <resource>`

Examples:
- `view users`
- `create programmes`
- `edit modules`
- `assign students to programmes`
- `view sysadmin dashboard`

This convention improves readability, maintainability, and consistency across the codebase.

---

## Enforcement Layers

Authorisation is enforced at multiple layers to ensure security and usability.

### 1) Route and middleware protection
Sensitive routes are protected using authentication and role/permission checks.

Typical patterns:
- `auth:web` for staff-only routes
- Role-based routing to resolve dashboards
- Permission-based middleware for restricted features

This ensures unauthorised users cannot access protected endpoints directly.

---

### 2) Livewire component authorisation
Livewire components enforce permissions at the component level using Laravel
authorisation helpers.

This prevents bypassing access rules via direct URL access and ensures server-side
enforcement remains the source of truth.

---

### 3) UI visibility (Blade)
Menus, buttons, and UI sections are conditionally displayed using permission checks
(e.g. `@can`, `@cannot`).

> UI visibility improves usability but is **not** treated as a security boundary.
> All critical enforcement happens at route and component level.

---

## Dashboard Resolution Flow (Guard + Role)

Dashboard routing for staff users is resolved using both **guard** and **role**. Authorisation checks are applied *after* routing at the feature and action level.

- All staff users authenticate under the `web` guard
- The user’s role determines which dashboard is accessible

```mermaid
flowchart TB
    U[Authenticated User] --> G{Guard}
    G -->|web| R{Role}

    R -->|sysadmin| SD[Sysadmin Dashboard]
    R -->|admin| AD[Admin Dashboard]
    R -->|superadmin| AD

    G -->|student| SP[Student Portal]
    G -->|employer| EP[Employer Portal]
