# Sysadmin Features

This document describes the **System Administrator (`sysadmin`)** feature set and responsibilities within the Laravel Stakeholder RBAC Infrastructure Artefact.

Sysadmins operate under the `web` guard and are distinguished from other internal users through **Spatie roles and permissions**. Their responsibilities focus on authentication architecture, authorisation control, and system-level configuration.

***

## Scope and Purpose

The `sysadmin` role exists to:

* Maintain authentication and authorisation integrity
* Enforce guard isolation and session boundaries
* Manage roles and permissions
* Oversee internal user access control

Sysadmins do **not** perform routine academic or stakeholder operations unless explicitly granted permission.

***

## Authentication Context

* **Guard:** `web`
* **Role:** `sysadmin`
* **Authentication model:** Single active authentication context per session
* **Dashboard:** `/sysadmin/dashboard`

There is no separate guard for sysadmins. They authenticate under `web` and are routed to the sysadmin dashboard through deterministic role resolution.

***

## Core Features

### 1) Sysadmin Dashboard

The sysadmin dashboard is permission-gated and restricted to users with:
```php
Permissions::VIEW_SYSTEM_ADMIN_DASHBOARD
```
Access control is enforced during component initialisation:
```php
$this->authorize(Permissions::VIEW_SYSTEM_ADMIN_DASHBOARD);
```
The dashboard itself is currently minimal and serves as an access-controlled entry point for system-level operations.

Dashboard routing is resolved via:
```php
App\Services\Auth\DashboardResolver
```
Resolution is deterministic and configuration-driven.

***

### 2) Internal User Management

Sysadmins can:

* View internal user accounts
* Create and manage internal users
* Assign or revoke roles
* Reset credentials (where permitted)

All actions are protected by explicit permissions and enforced server-side.

***

### 3) Role & Permission Management

Sysadmins maintain control over RBAC configuration, including:

* Creating roles
* Assigning permissions to roles
* Assigning roles to users
  
RBAC enforcement occurs at:

* Route middleware
* Livewire/controller level
* Blade visibility layer

The `AuthorizesWithPermissions` trait standardises component-level permission enforcement.

***

### 4) Guard & Session Architecture Oversight

Sysadmins operate within a multi-guard architecture that enforces:

* Deterministic guard resolution
* Single active authentication context per session
* Guard isolation between `web`, `student`, and `employer`

Controlled session recovery is available via:
```php
auth.reset
```
Sysadmins do not bypass guard boundaries.

***

## What Sysadmins Cannot Do (By Design)

To prevent privilege escalation:

* Sysadmins do not automatically inherit academic administrator permissions
* Sysadmins do not manage student or employer content unless explicitly granted
* Sysadmins do not override guard isolation
* Sysadmins do not bypass RBAC enforcement

***

## Extensibility

Future extensions may introduce:

* Audit logging
* Advanced permission analysis
* Guard/session diagnostics

Any such extensions will remain compatible with:

* The `web` guard
* Deterministic guard resolution
* The single-session authentication model

***

## Summary

* Sysadmins authenticate via the `web` guard
* Access is enforced via Spatie RBAC permissions
* The sysadmin dashboard is permission-gated
* Authentication boundaries remain intact
* The role exists to maintain infrastructure-level integrity

The `sysadmin` role provides controlled administrative authority without altering stakeholder authentication boundaries.

```
```
