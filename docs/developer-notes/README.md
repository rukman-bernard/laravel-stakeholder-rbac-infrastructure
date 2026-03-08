# Developer Notes

This directory contains implementation notes that clarify non-obvious behaviour encountered when integrating frameworks and components used in the infrastructure.

These notes document interactions between:

- Laravel framework components
- AdminLTE layout and theming
- Vite asset compilation
- guard-based authentication
- RBAC enforcement

They exist to explain **why the system behaves the way it does**, and to prevent common misunderstandings when extending or modifying the system.

Developer notes are intentionally separated from architectural and operational documentation.

---

## Relationship to Other Documentation

| Documentation Layer | Purpose |
|--------------------|--------|
Architecture | Describes system design principles and structure |
Decisions (ADRs) | Records architectural decisions and their rationale |
Operations | Describes runtime behaviour and deployment context |
Developer Notes | Explains framework interactions and implementation caveats |

Developer notes do **not** introduce new architectural decisions. Instead, they clarify the behaviour that arises from those decisions when implemented using the chosen frameworks and tooling.

---

## Contents

- **AdminLTE CSS Loading Behaviour**  
  Explains stylesheet layering between AdminLTE base styles and stakeholder-specific skins.

- **Vite: Dev vs Build Differences**  
  Describes how Vite development and production modes resolve assets differently and why explicit build inputs are required.

- **Implementation Caveats**  
  Highlights framework interaction constraints encountered when working with guard resolution, RBAC enforcement, and runtime theming.

---

## Scope

Developer notes focus on:

- framework behaviour
- integration caveats
- implementation constraints

This section contains development observations and implementation notes recorded during system construction.

These notes complement — but do not replace — the formal architecture and operations documentation.