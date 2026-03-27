# Authentication & Guards

This system uses Laravel’s **multi-guard authentication** to ensure that only authenticated users can access the appropriate portal. Guards are enforced across routes, middleware, controllers, and Livewire components to isolate authentication contexts for different user groups.

**Authentication (guards)** and **authorisation (roles/permissions)** are intentionally separate concerns. Guards determine **who** is authenticated, while authorisation is handled using **role-based access control (RBAC)** via Spatie Roles & Permissions to determine **what** an authenticated user is allowed to access.

---

## Guards

### `web` — internal users (web guard)
The `web` guard is used for all **internal user accounts**, including users assigned the **sysadmin**, **superadmin**, and **admin** roles. These users share the same authentication context (`users` table) but are differentiated using Spatie roles and permissions.

Dashboard access for `web` users is **role-gated**. A user must hold an appropriate role (and related permissions) to access a dashboard. Dashboards are resolved using role-based routing (e.g., **sysadmin dashboard** vs. **other internal user dashboards**).

**Implementation note (dashboard + UI configuration):**  
For AdminLTE-based portals, dashboard routing and layout behaviour are applied at runtime using:

- `App\Services\AdminLTE\AdminLTESettingsService`
- `config/nka.php` → `dashboard_routes`
- `App\Services\Auth\DashboardResolver`

---

### `student` — student users
All students authenticate using the `student` guard. This guard is fully isolated from internal users (`web`) and uses its own user provider (`students` table).

Student access is restricted to the student portal and related features.

**Implementation note:**  
AdminLTE layout behaviour (e.g., top-nav layout) is dynamically adjusted via `AdminLTESettingsService`.

---

### `employer` — employer users
All employers authenticate using the `employer` guard. This guard is isolated from both `web` and `student` guards and uses its own provider (`employers` table).

Employer access is limited to employer-specific functionality.

**Implementation note:**  
AdminLTE layout behaviour is dynamically adjusted via `AdminLTESettingsService`.

---

## Password Reset (Per Guard)

Password reset is implemented per authentication context.

Guard-to-broker mapping is handled by:

- `App\Services\Auth\PasswordBrokerResolver`

Each guard resolves to a dedicated password broker defined in `config/nka.php`.

This ensures reset tokens remain isolated between:

- internal users (`web`)
- students (`student`)
- employers (`employer`)

Reset emails are sent using `StakeholderResetPasswordNotification`, which applies guard-aware labels and messaging.

---

## Authentication Architecture (Overview)

The system implements a multi-guard authentication architecture to support distinct user portals while maintaining a single active session per client.

Each guard represents an authentication context only, with isolation enforced through:

- dedicated user providers  
- separate password reset brokers  
- guard-aware middleware and routing  

Guard classification, validation, and resolution are centralised within the application guard abstraction:

- `App\Constants\Guards`

This abstraction ensures:

- consistency between Laravel configuration and application behaviour  
- elimination of string duplication across the codebase  
- controlled adoption of new guards into the system  

Deterministic guard resolution ensures a single active authentication context per request.

---

## Guard Classification Model

Guard classification is derived from Laravel authentication configuration (`config/auth.php`), not hardcoded lists.

The system distinguishes between:

- session-based guards (driver = `session`)
- non-session guards (e.g. API or token-based)

Classification is computed at runtime through the application guard abstraction (`App\Constants\Guards`).

A guard is considered valid only when it is:

1. configured in Laravel authentication configuration  
2. recognised by the application guard registry  

This two-step model ensures that guards must be explicitly adopted into the application architecture rather than becoming implicitly available through configuration alone.

---

## Deterministic Guard Resolution

The system enforces a deterministic guard resolution strategy to ensure a single active authentication context per request.

Guard resolution is based on an application-defined order:

- defined in `App\Constants\Guards::resolutionOrder()`

This order represents the **final resolution policy**, not a partial preference list.

Only guards that are:

- configured in Laravel  
- session-based  
- recognised by the application guard registry  

participate in resolution.

No additional guards are appended at runtime.

The first authenticated guard in this ordered list is treated as the active authentication context.

This ensures:

- predictable behaviour across requests  
- strict control over authentication context selection  
- elimination of ambiguity in multi-guard scenarios  

---

## Stakeholder Identity Isolation

Each stakeholder type (internal users, students, employers) is treated as a **separate authentication and security domain**.

This is enforced through isolated authentication guards and providers.

As a result:

- accounts cannot span multiple stakeholder domains  
- credentials, sessions, UI, and permissions are isolated  
- cross-domain privilege leakage is structurally prevented  

Multi-domain access is supported only through explicit separate registration.

---

## Authentication Services (Implementation)

The system uses small service classes to centralise authentication logic:

- `App\Services\Auth\GuardResolver`  
  Resolves the active guard using deterministic resolution order  

- `App\Services\Auth\GuardRedirectService`  
  Resolves correct login redirection per guard  

- `App\Services\Auth\GuardLogoutService`  
  Handles logout and session invalidation  

- `App\Services\Auth\DashboardResolver`  
  Resolves dashboard routes per guard and role  

---

## Middleware Integration

Unauthenticated requests are handled by:

- `App\Http\Middleware\Authenticate`

Behaviour:

- Active guard is resolved via `GuardResolver`
- Authenticated GET requests trigger AdminLTE runtime configuration
- Unauthenticated:
  - JSON → `401`
  - Web → redirected via `GuardRedirectService`

---

## Single-Session Behaviour (Per Browser Session)

The system enforces a **single active authentication context per browser session**.

This prevents:

- concurrent multi-guard sessions  
- ambiguous dashboard routing  
- redirect loops  

### 1️⃣ Redirect authenticated users away from guest routes

Middleware:  
`App\Http\Middleware\RedirectLoggedInToDashboard`

Behaviour:

- If no guard → request proceeds  
- If any configured session guard is authenticated → redirect to dashboard  

---

### 2️⃣ Logout-before-login safety mechanism

Before login:

- first authenticated guard in resolution order is logged out  
- session is invalidated  
- new login proceeds cleanly  

---

### 3️⃣ Redirect-loop recovery (`auth.reset`)

Fallback route for unresolved states.

Ensures:

- deterministic recovery  
- correct redirection to login context  

---

## Authorisation (RBAC)

Spatie Roles & Permissions is used to control access **within a guard**, primarily for `web` users.

Roles define responsibilities, permissions define access.

RBAC is evaluated only after authentication.

See:
- [Authorisation (RBAC)](./authorisation-rbac.md)

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