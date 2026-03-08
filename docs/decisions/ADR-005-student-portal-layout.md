# ADR-005: Student Portal Layout (Top Navigation)

## Status
Accepted

## Context
Internal staff users (e.g., sysadmin, superadmin, admin) require a feature-rich administrative interface with comprehensive navigation and management screens.

Students, however, require a simpler and more focused portal experience:
- fewer navigation options at a time,
- quicker access to student-centric content,
- and a cleaner UI that does not resemble an internal admin tool.

Using the full staff-style dashboard layout (e.g., sidebar-heavy navigation) for students would increase cognitive load and reduce usability. At the same time, introducing a fully separate UI framework or duplicating layouts would increase maintenance effort and reduce upgrade safety.

## Decision
The student portal uses the **AdminLTE top navigation layout** instead of the full staff-style layout.

- Students continue to use the shared AdminLTE foundation to keep the UI consistent and upgrade-friendly.
- The student portal is configured to use a top-nav layout (rather than sidebar navigation) to provide a simpler student experience.
- Layout behaviour is resolved dynamically based on the active authentication guard, ensuring consistent stakeholder separation without duplicating templates.

## Rationale
This decision provides:

- A cleaner, student-appropriate user experience
- Reduced cognitive load compared to staff-style dashboards
- A consistent UI foundation shared across stakeholders (AdminLTE)
- Minimal duplication of templates and assets
- Upgrade safety when AdminLTE updates or configuration changes

It also aligns with the broader architecture goals of isolating stakeholders while maintaining a single maintainable UI platform.

## Consequences
- Some layout behaviour must be configured per guard (e.g., enabling top-nav for students)
- Student navigation patterns must be designed carefully to avoid hidden features
- Lower long-term maintenance cost compared to maintaining a separate student UI framework
- A clear foundation for future student-specific visual skins and UX refinements

## Related Documents
- [Stakeholders](../architecture/stakeholders.md)
- [Theming Strategy](../architecture/theming-strategy.md)
- [Authentication & Guards](../architecture/auth-and-guards.md)
- [ADR-004: Stakeholder-Specific Theming](./ADR-004-stakeholder-theming.md)
