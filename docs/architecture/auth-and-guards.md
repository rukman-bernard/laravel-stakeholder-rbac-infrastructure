# Authentication & Guards

This system uses Laravel’s **multi-guard authentication** to ensure that only authenticated users can access the appropriate portal. Guards are enforced across routes, middleware, controllers, and Livewire components to isolate authentication contexts for different user groups.

**Authentication (guards)** and **authorisation (roles/permissions)** are intentionally separate concerns. Guards determine **who** is authenticated, while authorisation is handled using **role-based access control (RBAC)** via Spatie Roles & Permissions to determine **what** an authenticated user is allowed to access.

---

## Guards

### `web` — internal users (web guard)
The `web` guard is used for all **internal user accounts**, including users assigned the **sysadmin**, **superadmin**, and **admin** roles. These users share the same authentication context (`users` table) but are differentiated using Spatie roles and permissions.

Dashboard access for `web` users is **role-gated**. A user must hold an appropriate role (and related permissions) to access a dashboard. In the current design, dashboards are resolved using role-based routing (e.g., **sysadmin dashboard** vs. **other internal user dashboards**).


**Implementation note (dashboard + UI configuration):**  
For AdminLTE-based portals, dashboard routing and selected layout behaviour are applied at runtime using:
- `App\Services\AdminLTE\AdminLTESettingsService` (sets `adminlte.dashboard_url`, `adminlte.title_postfix`, and other AdminLTE options per guard/role)
- `config/nka.php` → `dashboard_routes`
- Dashboard URLs are resolved exclusively through `App\Services\Auth\DashboardResolver`, which maps guards and (for `web`) roles to route names defined in `config/nka.php`.

---

### `student` — student users
All students authenticate using the `student` guard. This guard is fully isolated from internal users (`web` guard) and uses its own user provider (Student model and `students` table). Student access is restricted to the student portal and related features.

**Implementation note (AdminLTE layout behaviour):**  
When the active guard is `student`, AdminLTE settings can be adjusted (e.g., top-nav layout) via `AdminLTESettingsService`.

---

### `employer` — employer users
All employers authenticate using the `employer` guard. This guard is isolated from both internal users (`web` guard) and student accounts (`student` guard) and uses its own user provider (Employer model and `employers` table). Employer access is limited to employer-specific portals and functionality.

**Implementation note (AdminLTE layout behaviour):**  
When the active guard is `employer`, AdminLTE settings can be adjusted (e.g., top-nav layout) via `AdminLTESettingsService`.

---

## Password Reset (Per Guard)

Password reset is implemented per authentication context.

Guard-to-broker mapping is handled by `App\Services\Auth\PasswordBrokerResolver`, which resolves the appropriate password broker based on the active guard using configuration defined in `config/nka.php`.

Each guard resolves to a dedicated password broker configuration, ensuring token isolation and reset flow separation per authentication context.
This ensuring reset tokens remain isolated between:

- Internal users (`web`)
- Students (`student`)
- Employers (`employer`)

Reset emails are sent using `StakeholderResetPasswordNotification`, which derives guard-aware labels and subjects from configuration.

---

## Authentication Architecture (Overview)

The system implements a multi-guard authentication architecture to support distinct user portals (`web`, `student`, `employer`) while maintaining a single active session per client. Each guard represents an authentication context only, with isolation enforced through dedicated providers and password reset brokers. Session-based guards are resolved deterministically via `GuardResolver`, which evaluates configured session guards in the priority order defined by `App\Constants\Guards::priority()`. This allows middleware and shared routes to operate consistently across all portals. Guard classification and resolution logic are centralised to prevent string duplication and configuration drift, while guard-aware middleware (e.g. email verification and logout) ensures correct routing and enforcement per portal. This design aligns with Laravel best practices and supports extensibility without introducing role or model coupling into the authentication layer.

---

## Design Rationale

- **Clear separation of authentication contexts**  
  Each guard maps to a dedicated user provider and database table (e.g., `users`, `students`, `employers`).  
  Password reset brokers and token storage are separated per guard, further isolating reset flows and reducing the risk of cross-portal token misuse.

- **Reduced cross-portal access risk**  
  Laravel’s guard mechanism prevents users authenticated under one guard from accessing another portal without explicitly authenticating in that context.

- **Scalable authorisation model**  
  By combining guards with Spatie RBAC, the system supports fine-grained access control within a guard (particularly for internal users under `web`) without introducing complex conditional logic.

- **Future compatibility with external authentication (SSO)**  
  The guard-based design provides a clean foundation for future SSO integration (e.g., Socialite, SAML2, OAuth2/OpenID Connect) while preserving portal isolation.

---

## Stakeholder Identity Isolation

Each portal stakeholder type in the system (internal users, students, employers) is treated as a **separate authentication and security domain**.  
This is enforced through isolated authentication guards and user providers.

As a result:
- A single account cannot belong to multiple stakeholder domains.
- Authentication credentials, password reset policies, session rules, UI theming, and permissions are isolated per stakeholder type.
- Cross-domain privilege leakage (e.g., a student account being treated as an internal user) is structurally prevented.

Multi-domain access is supported **only through explicit separate registration** under the relevant portal.  
This keeps identity boundaries intentional, auditable, and aligned with the principle of least privilege.

While a single individual may create multiple accounts under different portals, each account remains isolated within its own authentication context. Deterministic guard resolution and single-session enforcement ensure predictable behaviour when multiple sessions exist.

---
## Guard Detection

The system performs deterministic guard resolution using the `App\Services\Auth\GuardResolver` service.

`GuardResolver` inspects configured session guards (defined in `config/auth.php`) and resolves the active authentication context in priority order as defined in `App\Constants\Guards::priority()`.

### Guard Priority (Deterministic Resolution)

The session guard priority order is:

- `web`
- `student`
- `employer`

When multiple session guards are unexpectedly authenticated within the same browser context, the highest-priority guard is treated as effective. This ensures consistent behaviour across:

- Dashboard resolution
- Redirect handling
- Logout enforcement
- Runtime UI configuration

Guard resolution is centralised within `GuardResolver` to prevent string duplication and configuration drift.

---

## Authentication Services (Implementation)

The system uses small service classes to keep authentication-related logic consistent and reusable:

- `App\Services\Auth\GuardRedirectService`  
  Resolves which login route to redirect to when a request is unauthenticated (e.g., redirecting to `student.login` / `employer.login` vs internal user `login`) while respecting intended URLs.

- `App\Services\Auth\GuardLogoutService`  
  Logs out the first active guard (or a supplied set of guards), invalidates the session, and regenerates the CSRF token.

---

## Middleware Integration

Unauthenticated requests are intercepted by the `App\Http\Middleware\Authenticate` middleware.

Behaviour summary:

- The active guard is resolved via `GuardResolver`.
- If authenticated and the request is a **GET**, `AdminLTESettingsService::apply()` is invoked to configure runtime AdminLTE settings.
- If unauthenticated:
  - JSON requests receive `401`
  - Web requests are redirected to the appropriate login route using `GuardRedirectService`

---

## Single-Session Behaviour (Per Browser Session)

The system enforces **a single active authentication context per browser session** to provide a predictable and safe user experience across multiple portals (student, employer, internal users).

This design prevents:
* Concurrent multi-guard authentication states within a single session
* Ambiguous dashboard resolution
* Redirect loops between guest and protected routes

This behaviour is enforced using **three coordinated mechanisms**.

---

### 1️⃣ Redirect authenticated users away from guest routes

All guest-only pages (welcome, login, register, password reset, portal.hub) are protected by the `redirect.loggedin` middleware.

**Behaviour**
* If **no guard is authenticated**, the request proceeds normally.
* If **any session-based guard is authenticated**, the user is redirected to the correct dashboard for that guard.
  
This ensures that:
* Logged-in users never see guest pages
* The browser session always has a single active portal context
  
**Implementation**
* Middleware: `App\Http\Middleware\RedirectLoggedInToDashboard`
* Service: `App\Services\Auth\GuardResolver`
* Service: `App\Services\Auth\DashboardResolver`

---

### 2️⃣ Logout-before-login safety mechanism

Before processing a login attempt, the system explicitly logs out **any existing authenticated guard** in the same browser session.

**Purpose**
* Prevent mixed-guard sessions (e.g. student + internal user)
* Ensure login always starts from a clean authentication state
* Enforce “one user per browser session” consistently

**Behaviour**
* The first authenticated guard found is logged out
* The session is invalidated once
* A fresh login attempt proceeds normally

**Implementation**
* Service: `App\Services\Auth\GuardLogoutService`
* Method: `logoutAnyAuthenticatedGuard()`
* Used by: `App\Livewire\Shared\Auth\LoginForm`

---

### 3️⃣ Redirect-loop recovery route (`auth.reset`)

To safely recover from redirect loops or unresolved dashboard states, the system defines a **guard-agnostic recovery route** named `auth.reset`.
This route is available to authenticated users under any session-based guard and is explicitly excluded from the `redirect.loggedin` middleware to prevent recursive redirection.

**When it is used**
* A user is authenticated
* The system cannot resolve a valid dashboard route
* Redirecting would otherwise loop back to a protected route

**Behaviour**
* The user is redirected to `auth.reset`
* A dedicated **Session Reset** page is displayed with clear, non-technical guidance
* The page instructs the user to log out and sign in again
* Logout is performed using the standard `POST /logout` route
* The logout controller logs out the active guard and redirects the user to:
  * the corresponding stakeholder login page (`student.login`, `employer.login`), or
  * the internal user login page for `web` users
* If no active guard is detected, the user is redirected to the portal hub

This ensures recovery is deterministic and always returns the user to the correct authentication entry point.

**Implementation**
* Route name: `auth.reset`
* Exempted in: `RedirectLoggedInToDashboard`
* Logout handled by: `CommonLogoutController`

---

### Implementation References

* **Middleware**
  * `App\Http\Middleware\RedirectLoggedInToDashboard`

* **Services**
  * `App\Services\Auth\GuardLogoutService`
  * `App\Services\Auth\GuardRedirectService`

* **Controllers**
  * `App\Http\Controllers\Auth\CommonLogoutController`

* **Livewire**
  * `App\Livewire\Shared\Auth\LoginForm`
    
---

## Authorisation (Spatie Roles & Permissions)

Spatie Roles & Permissions is used to control access **within** an authenticated guard, primarily for `web` guard users (sysadmin, superadmin, admin). Roles define high-level responsibility (e.g. system administrator vs other internal users), while permissions control fine-grained access to features and actions across the system. This RBAC layer operates independently of authentication guards and is only evaluated after a user has been authenticated under the appropriate guard.

For full details on the RBAC model, role hierarchy, permission conventions, and enforcement layers, see:
- **[Authorisation (RBAC)](./authorisation-rbac.md)**

---

## Stakeholder Access & Routing Flow

```mermaid
flowchart TB
    U[User] --> A{Authenticated?}

    A -->|No| L[Login Page]
    A -->|Yes| G{Active Guard}

    G -->|student| S[Student Portal]
    G -->|employer| E[Employer Portal]
    G -->|web| W[Internal User Portal]

    S --> SD[/student/dashboard/]
    E --> ED[/employer/dashboard/]

    W --> R{Spatie Role}

    R -->|sysadmin| SYD[/sysadmin/dashboard/]
    R -->|admin / superadmin| AD[/admin/dashboard/]

    S --> SL[Student Layout + Student Theme]
    E --> EL[Employer Layout + Employer Theme]
    W --> WL[AdminLTE Layout + Internal Theme]

 ```
 > In this diagram, **internal users** refers collectively to all users authenticated under the `web` guard, including sysadmin, superadmin, and admin roles.

***

## Related Architecture Documents

Authentication determines **who** is logged in and **under which guard**.
Visual presentation and UI behaviour are resolved **after authentication** based on the active guard and role.

For details on how stakeholder-specific UI skins are resolved and applied on top of AdminLTE, see:

* **[Theming Strategy](theming-strategy.md)** — explains how skins are loaded per guard, how SCSS theme switching works, and how the system safely falls back to the default AdminLTE theme.

> Note: Theming is not part of authentication itself.
> Authentication resolves the active guard; theming is resolved afterwards using that guard.
