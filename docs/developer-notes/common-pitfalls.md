# Implementation Caveats

This document highlights implementation caveats encountered when integrating AdminLTE, Vite asset bundling, guard-based authentication, and RBAC enforcement within the infrastructure.

These caveats arise from the interaction between framework behaviour and the architectural design choices used in this infrastructure.

This document exists as a **developer reference**, not an operational troubleshooting guide.

---

## 1. Vite Development vs Production Behaviour

### Description

Assets may appear to load correctly during development but fail after a production build.

### Explanation

Vite development mode resolves assets dynamically using the dev server. Production builds rely on a static manifest generated during `vite build`.

Assets that are not declared in `vite.config.js` are therefore available in development but absent in production builds.

### Architectural Constraint

Any asset expected to exist in production must be explicitly declared in the Vite `input` configuration.

---

## 2. SCSS Skin Loaded but Styles Not Applied

### Description

A stakeholder SCSS skin compiles successfully but produces no visible visual changes.

### Explanation

SCSS skins are intentionally scoped behind a body switch class to prevent global style overrides.

If the switch class is not applied to the `<body>` element, the compiled styles remain inactive.

### Architectural Constraint

The runtime configuration must apply the correct body class for the skin:
```php
Config::set('adminlte.classes_body', 'glassmorphism-theme'); //app/Services/AdminLTE/AdminLTESettingsService.php
```
---

## 3. AdminLTE Styles Override Custom Skin

### Description

Custom skin styles appear to be overridden by AdminLTE base styles.

### Explanation

CSS cascade order determines which rules take precedence.

If the custom skin is loaded before AdminLTE base styles, AdminLTE will override those rules.

### Architectural Constraint

Stakeholder skins must load **after** AdminLTE base styles using the `adminlte_css` extension point:
```blade
@section('adminlte_css')
    @vite($skinEntry)
@endsection
```
This preserves the intended stylesheet layering.
***

## 4. Guard Context Mismatch Causes Redirect Loops

### Description

A user authenticates successfully but experiences repeated redirects when attempting to access a protected portal route.

### Explanation

The system uses multi-guard authentication with a **single active authentication context per browser session**.

Each protected route is guard-scoped (for example, `auth:web`, `auth:student`, `auth:employer`).  
If a request targets a route protected by a guard that does not match the active authentication context, the request is rejected and the user is redirected into the appropriate guard-scoped authentication flow.

This typically occurs when:

- a route is protected by the wrong guard middleware for its portal context, or
- a user attempts to access a portal route while authenticated under a different guard.

### Architectural Constraint

Routes must enforce the correct guard middleware for the portal context, using `auth:<guard>` consistently.

Dashboard resolution is **guard-scoped**:

- `student` and `employer` resolve dashboard routing using guard-scoped dashboard routes
- `web` resolves dashboard routing using **role-aware routing scoped to the `web` guard** (for example, `sysadmin` vs `admin/superadmin`)

If a dashboard cannot be resolved to a valid configured route, the system uses the **controlled recovery route**:

- `auth.reset` — a session reset page protected by multi-guard auth middleware (`auth:` + all session guards)

This ensures recovery remains deterministic when a safe dashboard redirect cannot be produced.

***

## 5. UI Visibility Does Not Match Backend Enforcement

### Description

A menu item is visible but the action is denied, or the action works but the menu item does not appear.

### Explanation

UI visibility checks and backend permission enforcement operate independently.

Blade directives such as `@can` control visual presentation only and do not provide security guarantees.

### Architectural Constraint

Permissions must always be enforced at the route or component level.

UI visibility checks should be treated as convenience indicators rather than security controls.

***
## Related Documentation
- [Vite: Dev vs Build Differences](./vite-dev-vs-build.md)
- [AdminLTE CSS Loading Behaviour](./adminlte-css-loading.md)
- [Theming Strategy](../architecture/theming-strategy.md)
- [Deployment](../operations/deployment.md)
- [Session Management](../operations/session-management.md)

```