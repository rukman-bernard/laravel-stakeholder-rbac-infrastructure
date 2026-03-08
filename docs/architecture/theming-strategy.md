# Theming Strategy

This system supports **stakeholder-specific theming** (Student and Employer) while keeping the AdminLTE layout consistent, upgrade-friendly, and centrally managed. The strategy enables different visual appearances per stakeholder **without duplicating layouts or maintaining multiple AdminLTE variants**.

---

## Terminology

- **Base theme (AdminLTE)**  
  The default AdminLTE styling loaded via Vite and configured through `config/adminlte.php`.

- **Skin**  
  A stakeholder-specific CSS or SCSS file that overrides selected visual styles **without altering layout structure**.

- **Theme switch class**  
  A CSS class applied to the `<body>` element (e.g., `glassmorphism-theme`) that activates scoped SCSS overrides.

> In this document, **skin** refers strictly to a visual override layer applied on top of the AdminLTE base theme.

---

## Goal

- Provide different **skins per stakeholder** without duplicating layouts.
- Keep AdminLTE upgrades safe and isolated.
- Allow new skins to be introduced with minimal configuration changes.
- Ensure deterministic behaviour through stylesheet ordering, allowing the AdminLTE base theme to remain effective when no stakeholder skin is applied.

---

## Architectural Approach

1. **AdminLTE base styles load first** via Vite using `config/adminlte.php`.
2. Stakeholder-specific configuration is applied at runtime via `AdminLTESettingsService`.
3. Optional skin assets (CSS or SCSS) are loaded after the base theme.
4. If no skin or theme switch class is configured, no visual overrides occur. The browser naturally applies the AdminLTE base styles through standard CSS cascade behaviour.

Theming decisions are **service-driven**, not layout-driven.

---

## Base AdminLTE Theme

AdminLTE base styling is loaded first through Vite.

Configuration:

- `config/adminlte.php`
- `resources/css/app.css` (AdminLTE entry point)

AdminLTE dark mode is controlled via configuration and may inject a `dark-mode` class into the `<body>` element.

Example body output:

```html
<body class="layout-top-nav sidebar-mini dark-mode">
```
***

## Stakeholder Skin Configuration

Stakeholder skins are defined in:

* `config/nka.php`

Example:
```php
'skins' => [
    'student'  => 'resources/scss/skins/student/student.scss',
    'employer' => 'resources/css/skins/employer.css',
],
```
The skin entry is selected based on the **active authentication guard**.

Guard resolution is performed centrally by:

* `App\Services\Auth\GuardResolver`

***
## Runtime Configuration (Service-Driven)

Theming behaviour is applied at runtime by:

* `App\Services\AdminLTE\AdminLTESettingsService`

This service:

* Resolves the active guard

* Applies dashboard routing configuration

* Applies guard-specific AdminLTE options

* Optionally injects a theme switch class into `adminlte.classes_body`

Example (student guard):
```php
Config::set('adminlte.classes_body', 'glassmorphism-theme');
```
The `glassmorphism-theme` class activates SCSS overrides scoped under that class.

If no class is injected, no scoped overrides are activated. The AdminLTE base theme remains in effect without requiring additional fallback logic.
***

## Skin Asset Files

Current skin assets include:

* `resources/scss/skins/student/student.scss`

* `resources/css/skins/employer.css`

Internal users authenticated under the `web` guard use the default AdminLTE theme unless explicitly overridden.

***

## Asset Bundling (Vite)

All CSS and SCSS assets are served using **Vite**.

* In development, Vite supports dynamic asset loading (HMR).

* In production, skin files must be included in `vite.config.js` under the `input` array to ensure compilation.

File:

* `vite.config.js`

***

## Layout Consistency

All stakeholders share a single AdminLTE layout:

* `resources/views/components/layouts/adminlte-app.blade.php`

* `resources/views/components/layouts/app.blade.php`

No duplicate layouts exist per stakeholder.

Visual differentiation is achieved purely through:

* Runtime configuration

* Skin assets

* Optional body classes

***

## Benefits

* **Upgrade-friendly AdminLTE integration**
  AdminLTE is extended via configuration and service injection, not modified directly.

* **No layout duplication**
  All stakeholders share the same structural layout.

* **Deterministic behaviour**
  Guard resolution and runtime configuration ensure predictable theming.

* **Deterministic stylesheet layering**
  AdminLTE base styles are always loaded first. Stakeholder skins are layered afterwards. If no skin is loaded, the base theme remains effective through normal CSS cascade resolution.

***

## Notes

* Internal users (`web` guard) use the default AdminLTE theme.

* Student and employer portals may apply guard-specific skins.

* Theming customises visual appearance only; layout structure remains consistent across stakeholders.
