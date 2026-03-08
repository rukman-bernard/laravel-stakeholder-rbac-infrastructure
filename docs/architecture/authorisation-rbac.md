# Authorisation (RBAC)

This document describes how the system implements **authorisation** using **role-based access control (RBAC)**. It complements `Authentication & Guards.md`, which focuses on **authentication** (multi-guard login contexts).

In this project:
- **Authentication** is handled using Laravel guards (`web`, `student`, `employer`).
- **Authorisation** is applied to **internal users authenticated under the `web` guard** using **Spatie Roles & Permissions**.

---

## Scope

RBAC is currently used to control access for the following internal user roles:

- **sysadmin**
- **superadmin**
- **admin**

RBAC is not currently applied to student or employer portals. Guard isolation provides sufficient separation for those stakeholders at this stage. If future requirements introduce role hierarchies within those portals, RBAC can be extended per guard without architectural change.

---

## RBAC Model

### Roles

Roles represent high-level responsibility and access scope within the `web` guard.

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

Typical permission naming follows a consistent verb–resource pattern:

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

### Internal Users (`web` guard)

- All internal users authenticate under the `web` guard.
- Differentiation between internal users is achieved using Spatie roles and permissions.
- Dashboard routing, menu visibility, and feature access are role- and permission-driven.

### Portal Stakeholders (`student`, `employer`)

- Students and employers authenticate under separate guards.
- Their portals are isolated at the guard level.
- RBAC is not currently required for these stakeholders but remains extensible.

---

## How RBAC Is Enforced

Authorisation is enforced at multiple layers to ensure defence-in-depth.

### 1️⃣ Route and Middleware Protection

Sensitive routes are protected using:

- `auth:web`
- Role or permission checks where required

This ensures unauthorised access cannot occur via direct URL navigation.

---

### 2️⃣ Livewire Component Enforcement

Livewire components enforce permissions using a standardised trait:

- `App\Traits\AuthorizesWithPermissions`

This trait centralises permission checks and ensures consistent behaviour across components.

Typical enforcement patterns include:

- `$this->authorizePermission('permission-key');`
- Laravel’s built-in `authorize()` mechanisms where appropriate

This prevents duplication of permission logic and improves readability.

---

### 3️⃣ Blade UI Visibility

UI elements (menus, buttons, sections) are conditionally displayed using:

- `@can`
- `@cannot`

UI checks are treated as a usability layer only.  
Server-side route and component enforcement remains the authoritative security boundary.

---

## Dashboard Resolution Strategy (Web Guard)

Internal user dashboard resolution is role-aware:

- `sysadmin` → sysadmin dashboard
- `admin` / `superadmin` → admin dashboard

Dashboard resolution is handled centrally by:

- `App\Services\Auth\DashboardResolver`

Route mappings are defined in:

- `config/nka.php` → `dashboard_routes`

Runtime AdminLTE configuration is applied by:

- `App\Services\AdminLTE\AdminLTESettingsService`

This ensures dashboard URLs, UI titles, and layout behaviour remain consistent and configuration-driven.

---

## Database Tables (Authorisation)

Spatie Roles & Permissions uses the following tables:

- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

Internal user accounts are stored in:

- `users` (authenticated under the `web` guard)

---

## Extensibility

The RBAC architecture supports extension without structural change:

- Additional roles can be introduced per guard.
- Permissions can be expanded using the same naming convention.
- Enforcement remains consistent through the shared trait and service-based resolution.

---

## Summary

- Guards isolate authentication contexts (who is logged in).
- Spatie RBAC controls authorisation (what a user can do) within the `web` guard.
- Internal users share the `web` guard and are differentiated using roles and permissions.
- Permission enforcement is centralised and consistent.
- The design supports future expansion without guard-role coupling.