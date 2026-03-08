# Architecture Decision Records (ADRs)

This section presents the Architecture Decision Records (ADRs) for the Laravel Stakeholder RBAC Infrastructure Artefact.

The ADRs document significant technical and architectural decisions, including the context in which each decision was made, the selected approach, and the associated trade-offs.  
They are intended for reviewers, maintainers, and future contributors who need to understand why key architectural choices were made.

---

## Purpose of ADRs

Architecture Decision Records exist to:

- Capture _why_ decisions were made
- Preserve historical context as the system evolves
- Avoid repeating past discussions
- Support onboarding of new contributors
- Provide transparency for reviewers and maintainers

ADRs complement architectural and feature documentation by explaining intent and rationale.

---

## When an ADR Is Created

An ADR is typically written when:

- A design choice has long-term impact
- Multiple alternatives were considered
- A decision affects security, scalability, or maintainability
- Reversing the decision would be costly

Examples include authentication strategy, session handling, theming approach, and infrastructure choices.

---

## ADR Lifecycle

Each ADR includes a status, such as:

- Proposed
- Accepted
- Deferred
- Deprecated
- Superseded

This makes it clear whether a decision is current or historical.

---

## ADR Index

The following ADRs are currently documented:

- **ADR-001** — Separation of Authentication, Authorisation, and Presentation  
- **ADR-002** — Multi-Guard Authentication Strategy  
- **ADR-003** — Vite-Based Theme Loading  
- **ADR-004** — Stakeholder-Specific Theming  
- **ADR-005** — Student Portal Layout Strategy  
- **ADR-006** — Docker Adoption  
- **ADR-007** — Password Confirmation (Deferred)

Each ADR is stored as an individual Markdown file in this directory.

---

## Summary

- ADRs provide durable architectural memory
- They reduce future ambiguity and rework
- They improve maintainability and reviewability
- They reflect deliberate and traceable architectural decisions