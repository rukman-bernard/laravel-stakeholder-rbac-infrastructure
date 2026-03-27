# System Overview

## Purpose

This repository provides a **reusable multi-guard authentication and role-based access control (RBAC) infrastructure** built on Laravel.

It establishes a deterministic and extensible foundation that:

- isolates authentication contexts using Laravel guards  
- differentiates internal users using role-based access control  
- enforces a single active authentication context per session  
- supports multiple stakeholder domains without layout duplication  
- applies runtime UI configuration in a centralised, upgrade-safe manner  

This repository represents the **infrastructure layer**, designed to be adapted to different domains without modifying its core architectural principles.

---

## Core Concepts

The system is built around the following core concepts:

- **Authentication (Guards)** → defines who is authenticated  
- **Authorisation (RBAC)** → defines what a user can access  
- **Stakeholder Domains** → define system boundaries  
- **Service-Driven Infrastructure** → centralises runtime behaviour  
- **Theming Strategy** → applies visual differentiation without layout duplication  

See:

- [Authentication & Guards](./auth-and-guards.md)  
- [Authorisation (RBAC)](./authorisation-rbac.md)  
- [Stakeholders](./stakeholders.md)  
- [Theming Strategy](./theming-strategy.md)  

---

## Stakeholder Domains

The system supports multiple stakeholder domains, each operating within an isolated authentication context.

Stakeholder definitions and responsibilities are described in:

→ [Stakeholders](./stakeholders.md)

---

## High-Level Architecture

### Laravel Backend

Laravel provides the core execution environment, including:

- multi-guard authentication  
- middleware-driven request handling  
- service container and dependency injection  
- secure session and password management  
- structured configuration system  

---

### Livewire UI Layer

Livewire is used to build server-driven UI components.

This enables:

- centralised permission enforcement  
- seamless integration with Laravel authorisation  
- SPA-like interactivity without client-side complexity  

---

### AdminLTE Layout Framework

AdminLTE 3 provides the base layout framework.

Key characteristics:

- layout structure is shared across all stakeholders  
- AdminLTE is extended through configuration, not modified  
- visual differentiation is applied through theming layers  

See:

→ [Theming Strategy](./theming-strategy.md)

---

### Service-Driven Infrastructure

Core behaviour is centralised within service classes, including:

- GuardResolver  
- DashboardResolver  
- PasswordBrokerResolver  
- GuardLogoutService  
- AdminLTESettingsService  

This approach:

- prevents configuration drift  
- eliminates guard logic duplication  
- enforces deterministic behaviour  
- improves maintainability and testability  

---

### Vite Asset Pipeline

Vite is used for asset bundling and stylesheet management.

It ensures:

- deterministic stylesheet ordering  
- efficient production builds  
- controlled layering of base styles and stakeholder skins  

---

## Simplified Request Flow

```text
User
  ↓
Authentication Context (GuardResolver)
  ↓
Route Group (per guard)
  ↓
Livewire Component
  ↓
Authorisation (RBAC where applicable)
  ↓
Runtime Configuration (AdminLTESettingsService)
  ↓
Layout Rendering (AdminLTE base)
  ↓
Optional Stakeholder Skin (CSS cascade)
```


## Related Documents
- [Authentication & Guards](./auth-and-guards.md)
- [Authorisation (RBAC)](./authorisation-rbac.md)
-  [Stakeholders](./stakeholders.md)
- [Theming Strategy](./theming-strategy.md)

```