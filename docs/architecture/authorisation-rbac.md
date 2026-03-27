# Authorisation (RBAC)

This document describes how the system implements **authorisation** using **role-based access control (RBAC)**.

It complements:

→ [Authentication & Guards](./auth-and-guards.md)

which defines:

- guard classification  
- authentication context isolation  
- deterministic guard resolution  

---

## Scope

RBAC is currently used to control access for internal users authenticated under the `web` guard.

The internal roles supported are:

- **sysadmin**
- **superadmin**
- **admin**

RBAC is not currently applied to student or employer portals. Guard isolation provides sufficient separation for those stakeholders at this stage.

If future requirements introduce role hierarchies within those portals, RBAC can be extended per guard without structural changes.

---

## RBAC Model

### Roles

Roles represent high-level responsibility and access scope within a guard.

The internal user roles defined in this system are:

- `sysadmin` — system-level administration and security-sensitive operations  
- `superadmin` — full administrative access (excluding sysadmin-only functions)  
- `admin` — standard administrative access  

Role keys are centralised in:

- `App\Constants\Roles`

This avoids string duplication and ensures consistency across the codebase.

---

### Permissions

Permissions represent fine-grained access to features and actions.  
Permissions are assigned to roles and may optionally be assigned directly to users.

Permission keys are centralised in:

- `App\Constants\Permissions`

Naming convention:

- `view <resource>`  
- `create <resource>`  
- `edit <resource>`  
- `delete <resource>`  
- `assign <resource>`  

Examples:

- `view users`  
- `create users`  
- `edit roles`  
- `assign permissions`  

This naming convention improves clarity, maintainability, and enforcement consistency.

---

## Guard Policy

Guard behaviour, classification, and validation are defined in:

→ [Authentication & Guards](./auth-and-guards.md)

### Internal Users (`web` guard)

- All internal users authenticate under the `web` guard  
- Differentiation is achieved using roles and permissions  
- Dashboard routing, menu visibility, and feature access are role- and permission-driven  

### Portal Stakeholders (`student`, `employer`)

- Students and employers authenticate under separate guards  
- Access is isolated at the authentication level  
- RBAC is not currently required but remains extensible  

---

## How RBAC Is Enforced

Authorisation is enforced at multiple layers to ensure defence-in-depth.

---

### 1️⃣ Route and Middleware Protection

Sensitive routes are protected using:

- `auth:web`  
- permission checks where required  

This ensures unauthorised access cannot occur via direct URL navigation.

---

### 2️⃣ Livewire Component Enforcement

Livewire components enforce permissions using:

- `App\Traits\AuthorizesWithPermissions`

Typical usage:

```php
$this->authorizePermission('permission-key');
```
This centralises permission checks and ensures consistent enforcement across components.

***

### 3️⃣ Blade UI Visibility

UI elements (menus, buttons, sections) are conditionally displayed using:

* `@can`
* `@cannot`

UI checks are a usability layer only.\
Server-side enforcement remains the authoritative security boundary.

***

## Dashboard Resolution Strategy (Web Guard)

Internal user dashboard resolution is role-aware:

* `sysadmin` → sysadmin dashboard
* `admin` / `superadmin` → admin dashboard

Dashboard resolution is handled by:

* `App\Services\Auth\DashboardResolver`

Configuration:

* `config/nka.php` → `dashboard_routes`

Runtime UI behaviour:

* `App\Services\AdminLTE\AdminLTESettingsService`

***

## Guard Validation (RBAC Creation)

Guard validation for RBAC entities follows the system’s guard model.

Only guards returned by:

```
Guards::configured()
```

are accepted during:

* role creation
* permission creation

See:

→ [ADR-008: Guard Validation for RBAC Entity Creation](../decisions/ADR-008-guard-validation-for-rbac-creation.md)

***

## Database Tables (Authorisation)

Spatie Roles & Permissions uses:

* `roles`
* `permissions`
* `model_has_roles`
* `model_has_permissions`
* `role_has_permissions`

Internal users:

* `users` table (under `web` guard)

***

## Extensibility

The RBAC architecture supports extension without structural changes:

* additional roles can be introduced per guard
* permissions can be expanded using the same naming convention
* enforcement remains consistent through shared traits and services

***

## Summary

* Guards define authentication context (who is logged in)
* RBAC defines access control (what a user can do)
* Internal users share the `web` guard and are differentiated using roles and permissions
* Guard validation ensures RBAC remains aligned with authentication architecture
* The system supports future extension without coupling guards and roles

```