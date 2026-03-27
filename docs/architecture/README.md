# Architecture Documentation

This section describes the architectural design of the Laravel Stakeholder RBAC Infrastructure Artefact.

It provides a structured view of how the system is organised, how responsibilities are separated, and how core technical concerns interact within the Laravel environment.

Each document in this section focuses on a specific architectural concern.  
Detailed behaviour is defined within those documents and referenced here.

---

## Scope

The architecture documentation covers:

- multi-guard authentication model  
- role-based authorisation (RBAC)  
- deterministic authentication context resolution  
- stakeholder domain isolation  
- runtime UI configuration and theming  
- infrastructure service layer design  

This section answers questions such as:

- How are authentication contexts isolated?
- How does authentication differ from authorisation?
- How is a single active authentication context enforced?
- How are dashboards and UI behaviour resolved?
- How does the system avoid layout duplication?
- How does the architecture support extensibility?

---

## Architectural Principles

### Separation of Concerns

Authentication, authorisation, and presentation are explicitly decoupled.

Core responsibilities are handled by dedicated services:

- authentication context → `GuardResolver`  
- dashboard routing → `DashboardResolver`  
- login redirection → `GuardRedirectService`  
- logout enforcement → `GuardLogoutService`  
- password broker mapping → `PasswordBrokerResolver`  
- runtime UI configuration → `AdminLTESettingsService`  

Each concern operates independently, avoiding cross-layer coupling.

---

### Deterministic Behaviour

Authentication context resolution is deterministic.

- Guards are resolved using a predefined resolution order  
- Only configured session guards participate in resolution  
- A single authentication context is active per session  

Routing and UI configuration are derived from this resolved context.

---

### Upgrade Safety

Third-party frameworks (e.g., AdminLTE) are extended rather than modified.

- configuration is applied at runtime  
- vendor files are not edited  
- layout behaviour is controlled through services  

This preserves compatibility with framework updates.

---

### Extensibility

The architecture supports the introduction of new stakeholder domains without structural refactoring.

- guards are defined and validated through `App\Constants\Guards`  
- routing is configured via `config/nka.php`  
- UI behaviour is resolved dynamically  
- RBAC remains guard-scoped and extensible  

New domains can be introduced by configuration and integration, not redesign.

---

## Prototype Naming Legacy

Some configuration namespaces use the prefix `nka` (e.g., `config/nka.php`).

This originates from the prototype system used during the research phase and is retained for compatibility.

It does not represent a dependency on a specific institution.  
Adopting systems may rename this namespace if required.

---

## Architecture Documents

The architecture is divided into focused documents:

- [System Overview](./overview.md)  
  High-level system structure and component relationships  

- [Authentication & Guards](./auth-and-guards.md)  
  Guard model, classification, and deterministic resolution  

- [Authorisation (RBAC)](./authorisation-rbac.md)  
  Role and permission model and enforcement  

- [Stakeholders](./stakeholders.md)  
  Stakeholder domains and isolation boundaries  

- [Theming Strategy](./theming-strategy.md)  
  Runtime UI configuration and visual differentiation  

---

## Summary

- Authentication contexts are isolated using Laravel guards  
- Authorisation is layered on top of authentication using RBAC  
- Stakeholder domains are separated structurally and behaviourally  
- Runtime behaviour is driven by centralised service classes  
- UI presentation is consistent and configurable without layout duplication  
- The system is designed for maintainability, extensibility, and upgrade safety  