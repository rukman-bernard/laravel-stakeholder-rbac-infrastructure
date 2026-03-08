# ADR-004: Stakeholder-Specific Theming

## Status
Accepted

## Context

The system serves multiple stakeholder domains (students, employers, and internal users), each authenticated under a distinct guard.

A key requirement is to:

- Provide visually distinct interfaces per stakeholder
- Avoid duplicating layouts or AdminLTE blade templates
- Remain upgrade-safe for future AdminLTE updates
- Support both simple CSS skins and SCSS-based visual designs

Coupling themes directly to layouts or duplicating AdminLTE templates would increase maintenance cost and introduce upgrade risk.

---

## Decision

The system implements stakeholder-specific theming using **skins layered on top of a shared AdminLTE layout**.

- **AdminLTE** provides the base layout and default styling.
- **Skins** (CSS or SCSS) override selected visual styles after the base theme.
- The active skin is resolved dynamically at runtime based on the authenticated guard.
- CSS-based skins load after AdminLTE to override selected UI properties.
- SCSS-based skins are scoped using a theme switch class applied to the `<body>` element to activate overrides within a controlled context.


AdminLTE base styles are always loaded first. If no skin is configured or resolved, the interface remains styled using the base AdminLTE theme through normal CSS cascade behaviour.

Skins are strictly limited to visual overrides and must not alter layout structure.

---

## Rationale

This decision provides:

- Clear separation between layout structure and visual styling
- A single shared AdminLTE layout across all stakeholders
- Minimal coupling between stakeholder identity and UI implementation
- Deterministic stylesheet ordering (base first, skin second)
- Support for both lightweight CSS overrides and expressive SCSS-based themes
- Long-term upgrade safety

The approach aligns with AdminLTE extension patterns and modern frontend theming practices.

---

## Consequences

- Slightly increased configuration complexity (skin registration and optional body class switching)
- Skins must remain restricted to visual overrides to preserve layout consistency
- Asset ordering must remain deterministic to avoid unintended cascade issues
- Adding new stakeholder skins requires Vite configuration updates

---

## Related Documents

- [Theming Strategy](../architecture/theming-strategy.md)
- [Authentication & Guards](../architecture/auth-and-guards.md)
- [Authorisation (RBAC)](../architecture/authorisation-rbac.md)
- [ADR-003: Vite Theme Loading](ADR-003-vite-theme-loading.md)