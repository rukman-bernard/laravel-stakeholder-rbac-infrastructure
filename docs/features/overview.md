# Features Overview

This section provides a high-level overview of the **functional features** implemented in the system. It focuses on _what the system does_ from a user and stakeholder perspective, while detailed design and implementation decisions are documented separately in the **Architecture** and **Decisions (ADR)** sections.

The system is designed to support multiple stakeholder groups using a shared platform, while maintaining strong separation of authentication, authorisation, and presentation concerns.

---

## Feature Scope

The system currently provides the following core capabilities:

- Multi-guard authentication for isolated stakeholder access
- Role-based authorisation for internal staff
- Stakeholder-specific dashboards
- Configurable theming and visual skins
- Modular UI built using Livewire
- Upgrade-friendly AdminLTE integration
- Containerised development and deployment environment

Each feature is documented at an appropriate level of abstraction and linked to supporting architectural decisions where relevant.

---

## Stakeholder-Based Features

### Students
- Dedicated authentication guard (`student`)
- Isolated student portal
- Student-specific dashboard layout
- Stakeholder-specific visual skin
- Navigation optimised for student workflows

📄 See: [`student-portal.md`](student-portal.md)

---

### Employers
- Dedicated authentication guard (`employer`)
- Isolated employer portal
- Employer-specific dashboard
- Independent visual skin
- Separation from student and staff access

📄 See: [`employer-portal.md`](employer-portal.md)

---

### Admin & Super Admin (Staff)
- Shared authentication guard (`web`)
- Role-based dashboard routing
- Permission-driven feature access
- AdminLTE-based layout
- Centralised administrative workflows

📄 See: [`admin-portal.md`](admin-portal.md)

---

### Sysadmin
- Shared `web` guard with elevated role
- Access to system-level and security-sensitive features
- Dedicated sysadmin dashboard
- Strong separation from standard superadmin and admin privileges

📄 See: [`sysadmin-features.md`](sysadmin-features.md)

---

## Cross-Cutting Features

### Authentication
- Multi-guard authentication using Laravel
- Guard-based portal isolation
- Single active authentication context per browser session (UX)
- Centralised login redirection logic

📄 See: [`authentication.md`](authentication.md)  
📐 Architecture: `architecture/Authentication & Guards.md`

---

### Authorisation (RBAC)
- Role-based access control using Spatie Roles & Permissions
- Fine-grained permission enforcement for staff users
- UI visibility aligned with permission checks

📄 See: [`authorisation.md`](authorisation.md)  
📐 Architecture: `architecture/Authorisation (RBAC).md`

---

### Dashboards & Routing
- Guard- and role-aware dashboard resolution
- Centralised dashboard routing strategy
- Shared layouts with role-specific entry points

📄 See: [`dashboards.md`](dashboards.md)

---
### Theming & Presentation
- Stakeholder-specific visual skins
- CSS and SCSS skin support
- Runtime theme switching with layered override strategy
- AdminLTE-compatible override strategy

📄 See: [`theming-features.md`](theming-features.md)  
📐 Architecture: `architecture/Theming Strategy.md`

---

## Infrastructure & Operations

- Docker-based development and deployment environment
- Environment parity across development and production
- Vite-powered asset bundling and optimisation

📄 See: [`infrastructure.md`](infrastructure.md)  
📐 Decision: `decisions/ADR-006-docker-adoption.md`

---

## Relationship to Architecture & Decisions

This Features section intentionally avoids implementation detail.  
For deeper technical reasoning, refer to:

- **Architecture** — explains system structure and design patterns
- **ADR (Architecture Decision Records)** — explains _why_ specific design choices were made

Together, these sections provide a complete and maintainable documentation set for both current and future contributors.

---

## Summary

- Features describe _system behaviour_
- Architecture explains _system structure_
- ADRs explain _design decisions_

This separation ensures clarity, scalability, and long-term maintainability of the documentation and the system itself.
