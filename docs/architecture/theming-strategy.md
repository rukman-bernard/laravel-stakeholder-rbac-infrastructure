# Theming Strategy

This system supports **stakeholder-specific theming** while maintaining a single shared AdminLTE layout.

The strategy enables different visual appearances per stakeholder **without duplicating layouts or maintaining multiple AdminLTE variants**.

Authentication context and guard behaviour are defined in:

→ [Authentication & Guards](./auth-and-guards.md)

---

## Overview

The system applies theming based on the **resolved authentication context**.

Visual customisation is achieved through:

- a shared AdminLTE base theme  
- optional stakeholder-specific skins  
- runtime configuration via service layer  

Theming is **configuration-driven and service-driven**, not layout-driven.

---

## Terminology

- **Base theme (AdminLTE)**  
  Default AdminLTE styling loaded via Vite and configured through `config/adminlte.php`.

- **Skin**  
  A stakeholder-specific CSS or SCSS file that overrides visual styles **without altering layout structure**.

- **Theme switch class**  
  A CSS class applied to the `<body>` element (e.g., `glassmorphism-theme`) to activate scoped overrides.

In this document, **skin** refers strictly to a visual override layer applied on top of the AdminLTE base theme.

---

## Architectural Approach

1. AdminLTE base styles are loaded first via Vite  
2. The active authentication context is resolved (see Authentication & Guards)  
3. Runtime configuration is applied using a service layer  
4. Optional skin assets are loaded after the base theme  
5. If no skin is applied, AdminLTE base styles remain effective  

This ensures:

- consistent layout across all stakeholders  
- isolated visual customisation  
- predictable CSS cascade behaviour  

---

## Base AdminLTE Theme

AdminLTE base styling is loaded first through Vite.

Configuration sources:

- `config/adminlte.php`  
- `resources/css/app.css`  

AdminLTE may inject classes such as:

```html
<body class="layout-top-nav sidebar-mini dark-mode">
```
These classes control layout and default styling behaviour.

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

Skin selection is based on the **resolved authentication guard**.

Guard resolution is performed centrally by:

* `AppServicesAuthGuardResolver`

***

## Runtime Configuration (Service-Driven)

Theming behaviour is applied at runtime using:

* `AppServicesAdminLTEAdminLTESettingsService`

This service:

* resolves the active authentication context
* applies guard-specific AdminLTE configuration
* sets dashboard-related configuration
* optionally injects theme switch classes

Example:

```
Config::set('adminlte.classes_body', 'glassmorphism-theme');
```

This class activates scoped styling rules within skin files.

If no class is applied:

* no overrides are activated
* AdminLTE base theme remains unchanged

***

## Skin Asset Files

Current skin assets include:

* `resources/scss/skins/student/student.scss`
* `resources/css/skins/employer.css`

Internal users (`web` guard) use the default AdminLTE theme unless explicitly overridden.

***

## Asset Bundling (Vite)

All CSS and SCSS assets are served using Vite.

* Development: supports dynamic loading (HMR)
* Production: skin assets must be included in `vite.config.js`

This ensures:

* consistent asset compilation
* predictable stylesheet loading

***

## Layout Consistency

All stakeholders share a single layout structure:

* `resources/views/components/layouts/adminlte-app.blade.php`
* `resources/views/components/layouts/app.blade.php`

No duplicate layouts exist per stakeholder.

Visual differences are applied through:

* runtime configuration
* skin assets
* optional body classes

***

## Benefits

* **Upgrade-friendly AdminLTE integration**
  AdminLTE is extended through configuration rather than modification
* **No layout duplication**
  All stakeholders share a single layout structure
* **Deterministic behaviour**
  Guard resolution and runtime configuration ensure predictable theming
* **Consistent stylesheet layering**
  Base styles load first, skins apply only when configured

***

## Notes

* Internal users (`web` guard) use the default AdminLTE theme
* Student and employer portals may apply guard-specific skins
* Theming affects visual appearance only; layout structure remains consistent

***

## Related Documents

- [Authentication & Guards](./auth-and-guards.md)
- [Stakeholders](./stakeholders.md)
