# Laravel Stakeholder RBAC Infrastructure Artefact

[![GitHub Repository](https://img.shields.io/badge/GitHub-Repository-black?logo=github)](https://github.com/rukman-bernard/laravel-stakeholder-rbac-infrastructure)
[![DOI](https://zenodo.org/badge/DOI/10.5281/zenodo.18910977.svg)](https://doi.org/10.5281/zenodo.18910977)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
![Laravel](https://img.shields.io/badge/Laravel-11-red)


A Dockerised **Laravel 11 infrastructure** demonstrating:

- multi-guard authentication  
- role-based access control (RBAC)  
- deterministic guard resolution  
- stakeholder-aware dashboard routing  

This repository serves as:

- a **working Laravel infrastructure implementation**, and  
- a **reference technical artefact** supporting associated academic research.

Although the reference implementation is evaluated in an academic systems context, the infrastructure itself is **domain-agnostic** and reusable in any system that requires stakeholder-aware authentication and authorisation.

## Citation

If you use this infrastructure artefact in research or academic work, please cite:

Bernard, R. (2026).  
*Laravel Stakeholder RBAC Infrastructure Artefact*.  
Zenodo.  
https://doi.org/10.5281/zenodo.18910977


---

## Documentation

Detailed architecture and operational documentation is available in:

`docs/`

Additional engineering notes and runtime verification materials are located in:

`docs-supplementary/`

Start with:

`docs/README.md`

---

## Quick Start (Docker)

### Prerequisites

- Docker  
- Docker Compose v2  

---

## First Run (Fresh Clone)

The system can be instantiated reproducibly using Docker.
```bash
cp .env.example .env
docker compose up -d --build
docker compose exec laravel composer install
docker compose exec laravel php artisan key:generate --force
docker compose exec laravel php artisan storage:link
docker compose exec laravel php artisan migrate --seed
```

---
## Access Points
| Service     | URL                     |
| ----------- | ----------------------- |
| Application | `http://localhost:8000` |
| phpMyAdmin  | `http://localhost:8080` |

All containers communicate through Docker's internal network.

Internal services never rely on `localhost`.

---

## Infrastructure Overview

This repository provides a reusable Laravel infrastructure baseline for systems that require:

* multi-guard authentication

* role-based access control (RBAC)

* deterministic guard resolution

* single-session enforcement

* stakeholder-aware dashboard routing

* containerised reproducibility

* documentation-driven engineering practices

The infrastructure is demonstrated through a reference implementation originally developed in the context of an academic management system, but the architectural patterns are intended for broader reuse.

---

## Architectural Principles

### Separation of Concerns

Authentication, session storage, caching, queue handling, and database persistence are isolated at the infrastructure level.

---

### Stateless Application Layer

The Laravel container is designed to be stateless to support horizontal scalability and container restart safety.

---

### Deterministic Multi-Guard Authentication

Each stakeholder type operates under a dedicated guard with explicit authentication and access boundaries.

---

### Single Active Authentication Context

The infrastructure enforces a single active authentication context per browser session.

---

### Infrastructure Reproducibility

Docker and Docker Compose provide a reproducible runtime environment across development machines.

---

### Documentation-First Development

Architectural decisions and implementation details are documented alongside the codebase.

---

## Technical Stack
| Layer | Technology |
|-------|------------|
| Framework | Laravel 11 (PHP 8.2) |
| Authentication & Authorisation | Multi-guard architecture + Spatie Laravel Permission |
| UI Framework | AdminLTE 3 |
| Frontend Tooling | Vite + Node.js |
| Database | MariaDB (Dockerised) |
| State Layer | Redis (phpredis extension) |
| Environment | Docker + Docker Compose |

## Development Environment

The system runs inside a fully containerised development environment.

### Core Services

* Laravel application container (PHP runtime)

* Nginx (HTTP server)

* MariaDB (persistent data storage)

* Redis (state management)

* Node / Vite (asset compilation)

* phpMyAdmin (optional development utility)

Start the environment:
```bash
docker compose up -d --build
```
---

## Environment Configuration
| File | Purpose |
|------|---------|
| .env | Local development configuration |
| .env.example | Configuration template |

Sensitive values are excluded from version control.

---

# Redis State Architecture

Redis functions as the **centralised state layer** responsible for:

* HTTP session storage

* application caching

* queue transport

To ensure predictable behaviour, Redis uses separate logical databases.

| Function | Redis DB |
|----------|----------|
| Sessions | DB 0 |
| Cache | DB 1 |
| Queue | DB 2 |

Key design characteristics:

* dedicated cache key prefix (`CACHE_PREFIX`)

* dedicated session key prefix (`SESSION_PREFIX`)

* queue lists stored as `queues:<queue-name>`

* Docker service name `redis` used as Redis hostname

This structure enables:

* deterministic queue processing

* container restart safety

* key collision prevention

* cross-container session consistency

---

# Repository Structure

| Directory | Purpose |
|-----------|---------|
| app/ | Core application logic |
| config/ | Framework configuration |
| database/ | Migrations and seeders |
| docker/ | Container configuration |
| resources/ | Views and frontend assets |
| routes/ | Route definitions |
| storage/ | Logs and runtime files |
| docs/ | System architecture documentation |
| docs-supplementary/ | Engineering notes and runtime evidence |

---

# Runtime Verification

Operational verification procedures for Redis, container services, and runtime configuration are documented in:

`docs-supplementary/runtime-evidence/docker-services.md`

These documents include CLI-level verification procedures used during system construction.

---

# Research Context

This repository accompanies research exploring:

* multi-guard RBAC architecture in Laravel

* stakeholder-aware infrastructure design

* reusable authentication and authorisation patterns for domain-driven systems

The repository therefore functions as a **reproducible research artefact** supporting architectural evaluation and further extension.

For detailed research background see:

`docs/resources/research-context.md`

The source code for the infrastructure artefact is available at:

https://github.com/rukman-bernard/laravel-stakeholder-rbac-infrastructure

---

# License

This project is licensed under the **MIT License**.

See the `LICENSE` file for details.
