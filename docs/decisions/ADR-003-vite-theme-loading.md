#  ADR-003: Vite Theme Loading (AdminLTE Base + Stakeholder Skins)

## Status
Accepted

## Context

The system supports stakeholder-specific skins (CSS/SCSS) to provide different visual appearances per portal (student, employer, and internal users), resolved primarily by the active authentication guard.

A reliable asset pipeline is required to:

- Compile and serve AdminLTE base assets
- Compile and serve stakeholder skins (CSS and SCSS)
- Support fast developer feedback during UI development
- Produce deterministic, deployable build artefacts for production

The project requires strict control over stylesheet ordering:

- AdminLTE base styles must load first
- Stakeholder skins must load after base styles to override selected visual styles

---

## Decision

The system uses **Vite** (via `laravel-vite-plugin`) as the asset bundler for all CSS/SCSS and JavaScript assets.

- AdminLTE base assets are served using Vite-managed entries.
- Stakeholder skins are defined as Vite-managed assets and injected **after** the AdminLTE base styles using the `adminlte_css` layout extension point.
- All required skin assets are explicitly included in the Vite `input` array to ensure they are compiled into production build artefacts.

The theming mechanism is **guard-driven**, meaning the active authentication guard determines which skin entry (if any) is loaded for the request.

---

## Rationale

Using Vite provides:

- Fast development workflow with Hot Module Replacement (HMR)
- Modern compilation support for both CSS and SCSS
- Deterministic production builds via precompiled artefacts
- Centralised control over stylesheet ordering (base first, skins second)
- A scalable approach for introducing additional stakeholder skins

This approach avoids manual stylesheet linking and ensures that all UI assets remain under a controlled build pipeline.

---

## Consequences

- Vite configuration must explicitly include all required production assets (base entries and skins).
- Development and production behaviours differ:
  - In development, Vite can dynamically serve assets referenced by `@vite(...)`.
  - In production, assets must be present in the build manifest, requiring inclusion in the `input` array.
- The system achieves predictable stylesheet ordering and deterministic UI behaviour across environments.

---

## Related Documents

- [Theming Strategy](../architecture/theming-strategy.md)
- [Authentication & Guards](../architecture/auth-and-guards.md)
- [Stakeholders](../architecture/stakeholders.md)