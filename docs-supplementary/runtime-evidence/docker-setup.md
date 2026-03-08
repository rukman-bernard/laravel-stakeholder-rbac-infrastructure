> Status: Implemented and verified in the reference environment  
> Category: Runtime evidence  
> Purpose: Describes the containerised runtime setup used to reproduce the infrastructure environment

# Docker Setup

## Purpose

This document describes how Docker is used to run and manage the system in a **consistent, reproducible, and isolated environment**.

Docker is treated as an **operational concern**, not a core architectural dependency. The architecture does not require Docker, but the reference implementation
is validated using a containerised runtime.

---

## Why Docker Is Used

Docker is adopted to solve the following operational problems:

- Environment drift between developers and servers
- Dependency conflicts (PHP, Node, extensions)
- Difficult local setup for complex stacks
- Inconsistent production behaviour

Docker ensures the system runs the same way across:
- local development,
- staging,
- production environments.

---

## Docker Architecture (High Level)

The system is containerised using **Docker Compose** and follows a _service-per-responsibility_ pattern.

Typical services include:

- Application runtime (Laravel / PHP)
- Database (MariaDB – MySQL-compatible configuration)
- Cache / session store (Redis)
- Asset build tooling (Node / Vite)
- Reverse proxy (optional)

---

## Environment Parity

Docker enables **environment parity** by:

- pinning PHP versions,
- controlling extensions,
- standardising database versions,
- isolating runtime dependencies.

This significantly reduces:
- “works on my machine” issues,
- deployment surprises,
- debugging complexity.

---

## Configuration Management

Environment-specific configuration is handled via:

- `.env` files (not committed)
- Docker Compose overrides
- runtime environment variables

Secrets are never committed to source control.

---

## When Docker Is Recommended

Docker is **recommended** when:

- running the full stack locally,
- onboarding new developers,
- deploying to staging/production,
- reproducing bugs from production.

Docker is **optional** when:

- running the app on a managed PaaS,
- using traditional VM-based deployments,
- integrating into an existing infrastructure.

---

## Operational Responsibility

Docker configuration is owned by:
- system administrators,
- DevOps / platform engineers,
- senior developers.

Application developers interact with Docker via:
- `docker compose up`
- `docker compose down`
- `docker compose exec`

without needing deep container internals knowledge.

---
## Docker Runtime Lifecycle

The system follows a two-phase Docker lifecycle:

### 1. Image Build Phase

During `docker compose build`:

- The Dockerfile constructs the Laravel runtime image.
- PHP extensions and system dependencies are installed.
- The `entrypoint.sh` script is copied into the image.
- No Laravel application logic is executed at this stage.

This phase produces a reusable container image.

---

### 2. Container Run Phase

During `docker compose up`:

- A container is created from the image.
- The `entrypoint.sh` script executes.
- The script:
  - ensures `.env` exists (copied from `.env.example` if missing),
  - creates required Laravel runtime directories (`storage`, `bootstrap/cache`),
  - applies safe writable permissions,
  - starts PHP-FPM.

---

### Bind Mount Behaviour

The Laravel application directory is bind-mounted:

```yaml
volumes:
  - .:/var/www/html
```
This means:

* The project directory from the host overrides the image filesystem.

* The `vendor/` directory is generated on the host when Composer is executed inside the Laravel container. Dependency installation is intentionally performed manually to preserve deterministic container startup.

* Dependencies installed during image build would be overridden by the bind mount.

For this reason, Composer is executed **after container startup**.

---
## Dependency Storage Strategy (vendor vs node_modules)

The system intentionally stores dependencies differently:

- `vendor/` (PHP) is written to the project directory via bind mount (`.:/var/www/html`).
  This keeps Composer installs visible on the host and simplifies IDE support.

- `node_modules/` (Node) is stored in a Docker named volume (`node_modules:/var/www/html/node_modules`).
  This prevents large dependency trees from polluting the repository and avoids cross-platform filesystem issues.

The Node container uses `docker/node/entrypoint.sh` to install dependencies into the volume when empty and then start the Vite dev server.

---

### Composer Installation Strategy

Composer dependency installation is intentionally executed manually:

```bash
docker compose exec laravel composer install
```
This design:

* preserves deterministic container startup behaviour,
* avoids unnecessary dependency resolution on each restart,
* keeps dependency changes explicit and version-controlled,
* maintains transparency in development workflows.

For operational setup instructions (container bootstrap and first-run workflow),
refer to:

[Application README – Quick start (Docker)](../../README.md#quick-start-docker)

---
## Reproducibility Note

The Docker configuration ensures that any contributor can reproduce the
runtime environment with a deterministic container stack.

Environment parity is treated as an operational requirement for reproducible development environments.

---

## Related Documentation

- [Docker Services](./docker-services.md)
- [Deployment](../../docs/operations/deployment.md)
- [Environments](../../docs/operations/environments.md)
- [ADR-006: Docker Adoption](../../docs/decisions/ADR-006-docker-adoption.md)
