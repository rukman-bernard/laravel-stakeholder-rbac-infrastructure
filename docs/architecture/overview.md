# System Overview

## Purpose

This repository provides a **reusable multi-guard authentication and role-based access control (RBAC) infrastructure** built on Laravel.

Its primary objective is to establish a deterministic and extensible foundation that:

- Isolates authentication contexts using Laravel guards
- Differentiates internal users using role-based access control
- Enforces a single active authentication context per session
- Supports multiple stakeholder domains without layout duplication
- Applies runtime UI configuration in a centralised, upgrade-safe manner

This repository represents the **infrastructure layer**, designed to be adapted to various institutional or organisational contexts without altering its core architectural principles.

---

## Stakeholder Domains

The system defines three primary authentication domains:

### Students
Students authenticate using the `student` guard.  
This guard operates independently from other domains and provides isolated access to the student portal.

### Employers
Employers authenticate using the `employer` guard.  
This guard is isolated from both students and internal users.

### Internal Users
Internal users authenticate using the `web` guard.

Within this guard, users are differentiated using Spatie roles:

- `sysadmin`
- `superadmin`
- `admin`

Role-based access control determines feature access, dashboard routing, and UI visibility within this authentication domain.

Each stakeholder domain represents a distinct authentication context with its own provider and session boundary.

---

## High-Level Architecture

### Laravel Backend

The system is built on Laravel, which provides:

- Multi-guard authentication
- Middleware-driven request handling
- Service container and dependency injection
- Secure password hashing and session management
- Structured configuration management

Laravel forms the core execution environment for authentication, authorisation, routing, and runtime configuration.

---

### Livewire UI Layer

Livewire is used to build interactive, server-driven UI components.

This enables:

- Centralised permission enforcement
- Consistent integration with Laravel’s authorisation system
- Reduced frontend complexity while preserving SPA-like behaviour

---

### AdminLTE Layout Framework

AdminLTE 3 provides the base layout framework.

Architectural characteristics:

- AdminLTE is extended, not modified
- Layout behaviour is configured at runtime
- All stakeholders share a single structural layout
- Visual differentiation is achieved through layered skins

Layout structure remains invariant across stakeholders.

---

### Service-Driven Infrastructure

Core infrastructure behaviour is centralised within dedicated service classes, including:

- GuardResolver
- DashboardResolver
- PasswordBrokerResolver
- GuardLogoutService
- AdminLTESettingsService

This service-driven approach:

- Prevents configuration drift
- Avoids guard logic duplication
- Enforces deterministic request handling
- Improves maintainability and testability

---

### Vite Asset Pipeline

Vite is used for asset bundling and stylesheet management.

It ensures:

- Deterministic stylesheet ordering
- Efficient production builds
- Controlled layering of CSS and SCSS skins

AdminLTE base styles load first.  
Optional stakeholder skins are layered afterward through normal CSS cascade behaviour.

---

## Simplified Request Flow

```text
User
  ↓
Authentication Guard (GuardResolver)
  ↓
Route Group (per guard)
  ↓
Livewire Component
  ↓
Role / Permission Enforcement (if web guard)
  ↓
AdminLTESettingsService (runtime configuration)
  ↓
Layout Rendering (AdminLTE base)
  ↓
Optional Stakeholder Skin (CSS cascade)
```

```