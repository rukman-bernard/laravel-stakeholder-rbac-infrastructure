# Theming Architecture

This document describes how stakeholder-specific visual theming (skins) is implemented within the Laravel Stakeholder RBAC Infrastructure Artefact.

The theming architecture enables visual differentiation between stakeholders while preserving a single shared AdminLTE layout structure. This design provides controlled visual variation without introducing layout duplication, upgrade risk, or fragile global CSS overrides.

---

## Scope and Purpose

The theming feature is responsible for:

- Applying stakeholder-specific visual styles (skins)
- Preserving a single shared AdminLTE layout
- Supporting both CSS and SCSS-based skins
- Loading skins deterministically based on guard
- Allowing future skins to be added without modifying layout structure

Theming is a presentation concern only.  
It does not affect authentication, authorisation, routing, or session behaviour.

---

## Design Principles

- **Single layout, multiple skins**  
  All portals use the same AdminLTE layout.

- **Skin-based overrides only**  
  Skins modify colour palette and minor visual styling, not structure.

- **Guard-driven resolution**  
  The active skin is resolved based on the authenticated guard.

- **Deterministic base behaviour**  
  If no skin is configured for a guard, only the AdminLTE base theme is applied.

---

## Supported Stakeholders

The system currently defines skins for:

- `student`
- `employer`

The `web` guard (Admin / Superadmin / Sysadmin) does not define a dedicated skin.  
It uses the default AdminLTE base theme.

---

## Skin Types

### 1) CSS Skins

CSS skins:

- Are loaded after AdminLTE base styles
- Apply globally once loaded
- Do not require a body switch class

Example:
`resources/css/skins/employer.css`
`resources/css/skins/testuser.css`

CSS skins are suitable for palette and surface-level styling changes.

---

### 2) SCSS Skins (Scoped)

SCSS skins:

- Are compiled via Vite
- Are scoped using a switch class on `<body>`
- Apply only when the switch class is present

Example:
```php
resources/scss/skins/student/student.scss
```

SCSS rules are intentionally scoped to prevent accidental global overrides.

---

## Skin Resolution Flow

1. User authenticates under a guard (`student`, `employer`, `web`, etc.)
2. The active guard is resolved via:
```php
App\Services\Auth\GuardResolver
````
3. The corresponding skin entry is read from `config/nka.php`
4. AdminLTE base theme loads first
5. The skin asset (if configured) is loaded afterward
6. For SCSS skins, a switch class may be applied to `<body>`

The UI renders using CSS cascade behaviour (base first, then overrides).

---

## Configuration

### Skin Mapping

Skin entries are defined in:

```php
// config/nka.php
'ui'=> [
    'skins' => [
     'student'  => 'resources/scss/skins/student/student.scss',
     'employer' => 'resources/css/skins/employer.css',
    ],
],
````

The array key corresponds to the guard name.

If no entry exists for a guard, only the AdminLTE base theme is applied.

***

### Layout Injection

Skins are loaded via the AdminLTE layout extension point:
```php
@section('adminlte_css')
    @if (!empty($skinEntry))
        @vite($skinEntry)
    @endif
@endsection
```
This ensures skin assets load after AdminLTE base styles.

***

### SCSS Switch Class

For SCSS skins, a switch class is injected at runtime:
```php
Config::set('adminlte.classes_body', 'glassmorphism-theme'); //AdminLTESettingsService.php
```
Resulting example:
```php
<body class="layout-top-nav sidebar-mini glassmorphism-theme dark-mode">
```
SCSS rules are scoped to this class and therefore only apply when it is present.

***

## Asset Bundling (Vite)

All skins are compiled and served via Vite.

Development:

* Assets are served via the Vite dev server using configured inputs.

Production:

* Skin entries must be declared in `vite.config.js` inputs.

Example:
```php
input: [
'resources/css/app.css', //Adminlte default styles
'resources/js/app.js',  //Adminlte default js
'resources/js/shared/library.js',  //This contains the custom js 
'resources/css/skins/employer.css',     
'resources/scss/skins/student/student.scss',
],
```
***

## What Theming Does Not Do

Theming does not:

* Modify layout structure
* Alter routing or access control
* Replace AdminLTE components
* Change authentication or session behaviour

AdminLTE remains the single source of layout structure.

***

## Extensibility

Adding a new skin requires:

1. Creating a CSS or SCSS file

2. Registering the file in `config/nka.php`

3.Defining a body switch class for SCSS

4. Including the asset in Vite inputs

No layout duplication or controller changes are required.

***

## Summary

* Theming is guard-driven and layout-agnostic
* AdminLTE remains the structural foundation
* Skins override appearance, not structure
* SCSS skins are safely scoped via body switch classes
* Base theme behaviour is deterministic when no skin is configured

This approach preserves upgrade safety and architectural consistency.
