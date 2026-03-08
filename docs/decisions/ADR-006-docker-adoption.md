# ADR-006: Docker Adoption for Development and Deployment Consistency

## Status
Accepted

## Context
The system requires multiple supporting services in addition to the Laravel application runtime, including a relational database and caching infrastructure. During development, environment instability (notably database crashes and local configuration drift) increased the cost of maintenance and slowed down progress when returning to the project after time gaps.

A consistent and reproducible environment is required to:
- reduce “works on my machine” issues,
- ensure service parity across developer machines,
- support reliable local development and testing,
- and provide a clean foundation for deployment.

Managing these dependencies manually on each machine (or relying on platform-specific stacks) would make the project harder to maintain and less portable.

## Decision
The project adopts Docker (via Docker Compose) to standardise **development and local execution** environments.

- The Laravel application and its supporting services are run in containers.
- Service dependencies (e.g., database, Redis, phpMyAdmin) are declared and versioned in `docker-compose.yml`.
- Persistent data (e.g., database storage) is managed using Docker volumes to protect against container restarts.
- The Docker-based approach is used to improve reproducibility and reduce platform-specific setup complexity.

Docker is used to provide development and environment parity. The architecture remains container-agnostic, and production deployment strategies may evolve independently.

## Rationale
Using Docker provides:

- A repeatable environment across development machines
- Clear, version-controlled infrastructure configuration
- Reduced risk of local service corruption and configuration drift
- Easier onboarding for collaborators and future maintainers
- A scalable path toward consistent deployment practices

This approach supports long-term maintainability and improves operational confidence.

## Consequences
- Slightly higher initial setup cost for developers unfamiliar with Docker
- Requires basic Docker knowledge for troubleshooting containers and volumes
- Improved stability and reproducibility across machines and environments
- Easier recovery from local environment failures (containers can be rebuilt cleanly)

## Related Documents
- [Operational Overview](../operations/overview.md)
- [Docker Setup](../operations/docker-setup.md)
- [Environments](../operations/environments.md)
