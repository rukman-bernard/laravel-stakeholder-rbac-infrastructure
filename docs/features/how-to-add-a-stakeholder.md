# Adding a New Stakeholder (Implementation Guide)

This section describes the controlled extension process used to introduce a new stakeholder (e.g. `teacher`, `partner`, `vendor`) into the **Laravel Stakeholder RBAC Infrastructure Artefact**.

The design follows **separation of concerns**, **guard-based authentication**, and **configuration-driven routing**, ensuring the solution remains extensible, maintainable, and suitable for academic evaluation and extension.

---

## Step 1: Guard Definition

Each stakeholder is represented by a dedicated authentication guard.

A guard constant is first defined to ensure consistent referencing throughout the system:

```php
// App\Constants\Guards.php
public const TEACHER = 'teacher';
```

The guard is then registered in `config/auth.php`, including:

* Session-based guard configuration
* Provider mapping to the stakeholder model
* Optional password broker configuration

**Rationale:**
Using separate guards prevents cross-stakeholder session leakage and enforces clear authentication boundaries between system actors.

***

## Step 2: Stakeholder Model and Persistence Layer

Each stakeholder has its own:

* Eloquent model
* Database table
* Authentication provider

For example:

* `Teacher` model
* `teachers` table
* `teachers` provider

**Rationale:**
This approach ensures data isolation, improves schema clarity, and avoids overloading a single user table with unrelated concerns.

***

## Step 3: Dashboard Route Mapping

Stakeholder dashboards are resolved through a central configuration mapping:
```php
// config/nka.php (configuration namespace used by the reference implementation)
'auth' => [
    'dashboard_routes' => [
        'teacher' => 'teacher.dashboard',
    ],
],
```
**Rationale:**
Centralising dashboard resolution avoids hard-coded redirects, simplifies future changes, and supports consistent post-authentication behaviour.

***

## Step 4: Theming Configuration (Optional)

Where visual separation is required, a stakeholder-specific skin may be registered:
```php
// config/nka.php (example)
'ui' => [
    'skins' => [
        'teacher' => 'resources/css/skins/teacher.css',
    ],
],
```
If layout behaviour must differ (e.g., top-nav), configure guard-specific AdminLTE overrides:
```php
// config/nka.php (example)
'ui' => [
    'adminlte_overrides_by_guard' => [
        'teacher' => [
            'adminlte.layout_topnav' => true,
        ],
    ],
],
```
**Rationale:**
Theming is treated as a presentation concern and remains decoupled from authentication and authorisation logic.

***
## Step 5: Route and Controller Registration

The following routes are created for the stakeholder:

* Login
* Logout
* Dashboard

All protected routes are secured using guard-specific middleware:
```php
auth:teacher
```

Shared authentication controllers are reused where possible, with stakeholder-specific logic limited to dashboard and feature controllers.

**Rationale:**
This minimises duplication while preserving clear responsibility boundaries.

***

## Step 6: Role-Based Access Control (Optional)

If finer-grained access control is required:

* Roles are defined under the stakeholder guard

* Permissions are assigned to roles

* Enforcement occurs at route, controller, or Livewire component level

**Rationale:**
RBAC is introduced only when justified by functional requirements, avoiding unnecessary complexity.

***

## Outcome

Following this process, the new stakeholder:

* Operates under an isolated authentication guard

* Shares common infrastructure with existing stakeholders

* Requires minimal additional code

* Integrates cleanly with routing, dashboards, and theming

* Maintains architectural consistency and evaluation traceability

## Related Documentation

- [Authentication & Guards](../architecture/auth-and-guards.md)
- [Authorisation (RBAC)](../architecture/authorisation-rbac.md)
- [Session Management](./session-management.md)
- [Theming Strategy](../architecture/theming-strategy.md)
- [Admin Portal](./admin-portal.md)
- [Student Portal](./student-portal.md)
- [Employer Portal](./employer-portal.md)
  