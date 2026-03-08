# Vite: Development vs Build Differences

This document explains the behavioural differences between Vite development mode and production build mode as relevant to the theming architecture used by the infrastructure. These differences are subtle but critical when working with AdminLTE, stakeholder-specific skins, and dynamic asset loading.

This note exists to prevent repeated confusion when behaviour appears “correct” in development but fails in production\[^1].

---

## Why This Matters

Vite behaves **very differently** in:

- `vite dev` (development / HMR mode)
- `vite build` (production, precompiled assets)

Understanding this difference is essential when:
- Loading stakeholder-specific CSS/SCSS skins
- Injecting assets dynamically using `@vite()`
- Working with AdminLTE’s layout and extension points

---

## Development Mode (`vite dev`)

In development mode:

- Vite runs a **dev server**
- Assets are resolved **on demand**
- Files referenced by `@vite()` **do not need to be listed** in `vite.config.js`
- SCSS imports and CSS files may appear to “just work”\[^2]


### Behaviour in Development Mode


- CSS/SCSS files load even if **not present** in the `input` array
- Dynamic `@vite($skinEntry)` calls resolve correctly
- AdminLTE + stakeholder skins appear to load without issues
- Missing Vite inputs are silently tolerated

### Why This Happens

Vite’s dev server:
- Compiles assets **in memory**
- Resolves dependencies dynamically
- Serves files directly from the filesystem

This makes development fast — but **can hide production issues**\[^3].

---

## Production Build Mode (`vite build`)

In production mode:

- Assets are **precompiled**
- Only files listed in the `input` array are included
- Dynamic runtime resolution is **not available**
- Missing entries result in missing CSS/JS in production

### Behaviour in Production Builds

- Stakeholder skins **fail to load** if not in `vite.config.js`
- `@vite($skinEntry)` silently resolves to nothing\[^4]
- AdminLTE loads, but overrides do not
- AdminLTE base styles remain active because no override stylesheet was compiled

---

## Key Rule (Critical)

> **If an asset must exist in production, it must be declared in `vite.config.js`.**\[^5]

This includes:
- Stakeholder CSS skins
- SCSS-based theme files
- Any dynamically injected assets

---

## Correct Configuration Pattern

### `vite.config.js`

```js
input: [
'resources/css/app.css', //Adminlte default styles
'resources/js/app.js',  //Adminlte default js
'resources/js/shared/library.js',  //This contains the shared custom js
'resources/css/skins/employer.css',     
'resources/scss/skins/student/student.scss',
],
```
## Runtime usage (safe)
```blade
@vite($skinEntry)
```
Where `$skinEntry` is guaranteed to be:

* Present in `vite.config.js`

* Compiled during `vite build`

---


## SCSS-Specific Considerations

For SCSS skins:

* SCSS is compiled **once at build time**

* Runtime switching is achieved via a **body switch class**

* The SCSS file must still be present in Vite inputs

Example switch class:
```html
<body class="glassmorphism-theme dark-mode">
```
If:

* The SCSS file is missing → no styles

* The switch class is missing → no effect

* Both are present → correct theme

---
## Common Pitfalls
| Issue                               | Cause                                 |
|-------------------------------------|---------------------------------------|
| Skin works in dev but not prod      | Missing Vite input entry              |
| AdminLTE loads but skin doesn't     | Skin injected post-AdminLTE but not compiled |
| SCSS has no effect                  | Body switch class missing             |
| Dynamic @vite() silently fails      | Asset not part of build               |

---
## Recommended Practice

* Always treat **dev mode as permissive**

* Validate behaviour using `vite build`

* Keep Vite inputs explicit

* Document dynamic asset usage clearly

* Prefer predictable over “magic” loading

---
## Summary

* Vite dev mode is **forgiving**

* Vite build mode is **strict**

* AdminLTE + dynamic skins amplify this difference

* Explicit Vite inputs are mandatory for production safety
---

## Related Documentation
- [ADR-003: Vite Theme Loading](../decisions/ADR-003-vite-theme-loading.md)
- [Theming Strategy](../architecture/theming-strategy.md)  
- [AdminLTE CSS Loading Behaviour](./adminlte-css-loading.md)  
- [Deployment](../operations/deployment.md)  
  

---

## Footnotes

[^1]: Vite’s development server dynamically resolves and serves assets at runtime, whereas production relies on a precompiled asset manifest generated during `vite build`.

[^2]: In development mode, Vite compiles and serves CSS/SCSS on demand, even when files are not explicitly declared as build inputs.

[^3]: Because the dev server resolves assets dynamically and in memory, missing production configuration is often not detected until a build is performed.

[^4]: When an asset is not part of the production build manifest, `@vite()` fails silently by design and does not emit runtime errors.

[^5]: Production builds are static and deterministic; only assets explicitly declared in `vite.config.js` are compiled and made available.
