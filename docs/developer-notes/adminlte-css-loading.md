# AdminLTE CSS Loading Behaviour

This document explains how **AdminLTE stylesheets**, **custom skins**, and **Vite asset bundling**
interact in the system, and clarifies common points of confusion encountered during development.

It is intended as a **developer reference**, not an operational troubleshooting guide.

---

## Purpose

AdminLTE provides a robust base layout and theme system, but when combined with:

- Laravel Vite
- custom CSS/SCSS skins
- runtime guard-based theming

it can appear as though styles are being “auto-loaded” or applied unexpectedly\[^1].

This document explains **why that happens**, and how the system is designed to behave.

---

## AdminLTE Base Styles

AdminLTE base styles are loaded via Vite using the configuration in:

- `config/adminlte.php`
- `resources/css/app.css`

When `laravel_asset_bundling` is set to `vite`, AdminLTE injects its base CSS and JS using:

```blade
@vite([
config('adminlte.laravel_css_path', 'resources/css/app.css'),
config('adminlte.laravel_js_path', 'resources/js/app.js')
])
```
These base styles define:

* layout structure

* component styling

* light/dark mode support

AdminLTE dark mode is enabled via configuration and results in the `dark-mode` class being applied to the `<body>` element\[^2].

---

## Custom Skins (Post-AdminLTE Overrides)

Stakeholder-specific skins are loaded **after** AdminLTE base styles using the `adminlte_css` Blade section.

Example:
```blade
@section('adminlte_css')
    @vite($skinEntry)
@endsection
```
This ensures:

* AdminLTE loads first

* stakeholder skin overrides second

* no layout duplication occurs

Skins are designed to override:

* colour palette

* background styles

* typography accents

They **do not** alter layout structure\[^3].

---

## CSS vs SCSS Skin Behaviour

### CSS Skins

* Apply immediately when loaded

* No runtime switch required

* Suitable for simple palette overrides

Example:
```php
'student' => 'resources/css/skins/student.css',
```
---
### SCSS Skins

SCSS skins are **scoped** using a body switch class to avoid global overrides.

Example switch class:
```html
<body class="glassmorphism-theme dark-mode">
```
The switch class is applied at runtime via:
```php
Config::set('adminlte.classes_body', 'glassmorphism-theme'); //app/Services/AdminLTE/AdminLTESettingsService.php
```
This ensures:

* SCSS styles only apply when explicitly enabled

* default AdminLTE theme remains intact if misconfigured\[^4].

---

## Vite Development vs Production Behaviour

### Development Mode

In development:

* Vite resolves assets dynamically

* Imported or referenced CSS/SCSS may appear to load even if not listed in `vite.config.js`

* This can give the impression of “auto-loading”

This behaviour is **intentional** and improves developer experience\[^5].

---

### Production Builds

In production:

* Only assets listed in `vite.config.js` `input` array are compiled

* Missing entries result in missing styles

* No dynamic resolution occurs

Therefore **all skins must be explicitly declared** in `vite.config.js`.

Example:
```php
input: [
'resources/css/app.css', //Adminlte default styles
'resources/js/app.js',  //Adminlte default js
'resources/js/shared/library.js',  //This contains the shared custom js
'resources/css/skins/employer.css',     
'resources/scss/skins/student/student.scss',
],
```
---
## Implementation Caveats

### “My SCSS skin compiles but has no effect”

Cause:

* Missing body switch class

Fix:

* Ensure `adminlte.classes_body` is set at runtime

---

### “Removing Vite inputs does not remove styles in dev”

Cause:

* Vite dev server dynamic resolution

Fix:

* Test behaviour using a production build\[^6]

---

### “AdminLTE styles override my skin”

Cause:

* Skin loaded before AdminLTE base CSS

Fix:

* Always load skins using `adminlte_css` (post-AdminLTE)

---

## Design Intent Summary

Stylesheets are intentionally loaded in a deterministic order to leverage normal CSS cascade behaviour.

AdminLTE base styles load first, followed by stakeholder-specific skins.  This allows skins to override colour palettes and visual accents without modifying
AdminLTE’s structural layout rules.

This approach supports:

* safe AdminLTE upgrades
* runtime theming
* guard-based UI separation
* clear override boundaries between base styles and skins

If a stakeholder skin is not loaded, the interface simply renders using the default AdminLTE styles because no override stylesheet is applied.

---
## Related Documentation

- [Theming Strategy](../architecture/theming-strategy.md)
- [ADR-003: Vite Theme Loading](../decisions/ADR-003-vite-theme-loading.md)
- [ADR-004: Stakeholder-Specific Theming](../decisions/ADR-004-stakeholder-theming.md)
- [Vite: Dev vs Build Differences](./vite-dev-vs-build.md)

---

## Footnotes

[^1]: In development mode, Vite dynamically resolves imported and referenced assets, which can make styles appear present even when not explicitly declared. This is often mistaken for implicit or automatic loading.

[^2]: The `dark-mode` class is injected by AdminLTE based on configuration, not by manual template logic.

[^3]: Layout structure (grids, navigation, component placement) remains controlled entirely by AdminLTE to preserve compatibility and simplify upgrades.

[^4]: Using a body-level switch class scopes SCSS overrides and prevents accidental global styling changes when a skin is missing or misconfigured.

[^5]: Vite prioritises fast feedback in development by resolving assets dynamically; this behaviour does not apply to production builds.

[^6]: Production builds rely on a static asset manifest and will not include CSS or SCSS files unless they are explicitly declared in `vite.config.js`.
