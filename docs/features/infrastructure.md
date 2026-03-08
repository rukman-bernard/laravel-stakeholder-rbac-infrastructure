# Infrastructure Layer

This document describes the **infrastructure layer** of the system, including runtime environment, asset pipeline, containerisation strategy, and supporting services.

The infrastructure is designed to support:

- Secure multi-guard authentication
- Deterministic single-session behaviour
- Role-based dashboards
- Stakeholder-specific theming
- Reproducible development and production environments

---

## Purpose

The infrastructure layer provides:

- A consistent runtime environment across development and production
- Isolation between services (application, database, cache)
- Reliable asset compilation and delivery
- Deterministic guard resolution and session handling
- A clean foundation for scaling and deployment

---

## Runtime Environment

### Application Stack

- **Backend Framework**: Laravel
- **PHP Runtime**: PHP 8.x
- **Web Server**: Nginx (containerised)
- **Frontend/UI**: AdminLTE 3 (Bootstrap-based)
- **Reactive UI Layer**: Livewire
- **Asset Bundler**: Vite
- **Database**: MySQL / MariaDB
- **Cache / Queue (optional)**: Redis

---

## Containerisation (Docker)

The system uses **Docker** to ensure consistent environments across:

- Local development
- Testing
- Staging / production

### Why Docker is used

- Eliminates environment drift
- Isolates system dependencies (PHP, Node, MySQL, Redis)
- Simplifies contributor onboarding
- Enables reproducible builds and deployments

Docker is treated strictly as **infrastructure tooling**, not application logic.  
Core architectural behaviour remains valid even if containerisation is replaced.

---

## Container Responsibilities

| Container | Responsibility |
|------------|----------------|
| app        | Laravel application runtime |
| web        | Nginx reverse proxy |
| db         | Database (MySQL/MariaDB) |
| redis      | Cache / session / queue backend (optional) |
| node       | Vite asset compilation (development/build) |

---

## Asset Pipeline (Vite)

### Development Mode

- Vite serves assets dynamically
- CSS/SCSS/JS files are resolved on demand
- Hot Module Replacement (HMR) is enabled

### Production Mode

- Assets are prebuilt
- All CSS/SCSS/JS entries must be declared in `vite.config.js`
- Static compiled assets are served by the web container
- Deterministic stylesheet ordering is guaranteed

AdminLTE base styles load first.  
Stakeholder skins are layered afterward using standard CSS cascade behaviour.

---

## AdminLTE Integration

- AdminLTE assets are bundled through Vite
- Layouts are extended via Laravel view inheritance
- Custom styles are injected through the `adminlte_css` extension point
- No direct modification of vendor CSS occurs

This design ensures:

- Upgrade safety
- Predictable override hierarchy
- Clear separation between base theme and skins

---

## Session & Guard Infrastructure

The infrastructure supports a **single active authentication context per session**, enforced at runtime.

While Laravel allows multiple guards to be authenticated concurrently, the system:

- Resolves the active guard deterministically
- Applies guard priority rules
- Invalidates conflicting authentication states
- Treats transitional multi-guard states as invalid

Session storage may be backed by Redis in scalable deployments.

Logout operations invalidate the full session to prevent guard overlap.

---

## Environment Configuration

Environment-specific behaviour is controlled through:

- `.env` configuration
- Laravel config files (`config/auth.php`, `config/adminlte.php`, `config/nka.php`)
- Feature toggles for:
  - Debug logging
  - Authentication tracing
  - Skin activation

Infrastructure logic is not hardcoded in controllers or views.

---

## Security Considerations

Infrastructure design enforces:

- Guard-isolated authentication contexts
- Secure session handling
- No cross-container privilege leakage
- Separation of build-time and runtime assets
- Environment-based secret management

Sensitive configuration remains externalised and is not committed to version control.

---

## Infrastructure Flow (Simplified)

```mermaid
flowchart TB
    User --> Web[Web Server (Nginx)]
    Web --> App[Laravel Application]
    App --> DB[(Database)]
    App --> Session[(Session Store)]
    App --> Assets[Vite Assets]

    Assets --> UI[AdminLTE Base + Optional Skins]
    UI --> User
```

## Related Documentation

- [Authentication & Guards](../architecture/auth-and-guards.md)
- [Authorisation (RBAC)](../architecture/authorisation-rbac.md)
- [Theming Strategy](../architecture/theming-strategy.md)
- [Dashboards Feature](dashboards.md)
- [ADR-006: Docker Adoption](../decisions/ADR-006-docker-adoption.md)

