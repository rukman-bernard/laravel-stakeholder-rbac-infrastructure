> Status: Engineering guidance (not implemented by the infrastructure artefact)  
> Category: Operational considerations  
> Purpose: Describes recommended maintenance and upgrade practices for deployments using the infrastructure

# Maintenance and Upgrades

## Purpose

This document outlines maintenance and upgrade practices for systems deployed using the Laravel Stakeholder RBAC Infrastructure Artefact to ensure:

- stability,
- security,
- and continuity of service.

Maintenance activities are designed to minimise downtime while keeping the system
aligned with evolving requirements and dependencies.

---

## Maintenance Scope

Regular maintenance applies to:

- application code,
- database schema,
- authentication infrastructure and session management,
- roles and permissions,
- UI themes and layouts,
- infrastructure and deployment tooling.

---

## Routine Maintenance

### Dependency Updates

Dependencies should be reviewed periodically, including:

- Laravel framework
- Spatie Roles & Permissions
- AdminLTE
- Livewire
- Frontend tooling (Vite, npm packages)

Recommended practice:
- Apply updates incrementally
- Review changelogs before upgrading
- Avoid skipping major versions

---

### Database Maintenance

- Review migrations for consistency
- Clean up obsolete records where applicable
- Prune old operational records where applicable (e.g., login attempt records if implemented)
- Validate indexes and foreign keys

Database changes should always be applied using migrations.

---

### Log and Session Cleanup

- Rotate application logs
- Prune expired sessions
- Remove stale authentication records
- Review unusual authentication patterns

These actions help maintain performance and security.

---

## Upgrade Strategy

### Application Upgrades

Recommended process:
1. Backup database and configuration
2. Apply code updates in a non-production environment
3. Run automated tests (if available)
4. Apply database migrations
5. Deploy to production
6. Monitor logs and behaviour

---

### Authentication and Session Changes

Because authentication and session handling are security-sensitive:

- Changes must be reviewed carefully
- Deterministic guard resolution behaviour must remain consistent
- Single-session enforcement should be revalidated
- Cross-guard behaviour should be tested explicitly

---

### UI and Theme Upgrades

When updating AdminLTE or stakeholder skins:

- Verify layout compatibility with the updated AdminLTE version
- Validate that custom skin overrides still apply correctly
- Test pages across different layouts and UI components
- Confirm that CSS overrides do not unintentionally affect unrelated components

Stakeholder skins are implemented as **non-invasive CSS override layers** applied on top of the base AdminLTE styles.

This approach relies on normal **CSS cascade behaviour**, meaning the AdminLTE base theme remains intact unless explicitly overridden by the skin stylesheet.

The infrastructure intentionally avoids modifying AdminLTE source styles, allowing framework upgrades to be performed with minimal impact on custom UI layers.

---

## Downtime Considerations

Most maintenance tasks can be performed with:
- zero or minimal downtime,
- rolling deployments,
- or scheduled maintenance windows.

High-impact changes (e.g., schema refactors) should be scheduled during low-traffic periods.

---

## Rollback Strategy

If an upgrade introduces issues:

1. Revert application code
2. Restore database backup (if schema changed)
3. Reapply known-good configuration
4. Validate authentication and dashboards

Rollback procedures should be tested periodically.

---

## Security Updates

Security-related updates take priority and may include:

- dependency security patches
- authentication logic changes
- RBAC rule refinements
- session handling improvements

Security fixes should be deployed promptly and reviewed post-deployment.

---

## Change Documentation

Significant changes should be recorded using:

- Architecture Decision Records (ADR)
- Updated documentation in `architecture/` or `features/`
- Version tags or release notes

This ensures traceability and institutional knowledge.

---

## Operational Readiness Checklist

Before completing an upgrade:

- Backups verified
- Migrations reviewed
- Authentication flows tested
- RBAC permissions validated
- Logs monitored post-deployment
---
## Related Documentation

- [Backups and Recovery](./backups-and-recovery.md)
- [Logging and Monitoring](./logging-and-monitoring.md)
- [Session Management](../../docs/operations/session-management.md)
- [Reference Environment](../../docs/operations/environments.md)
- [Authentication & Guards](../../docs/architecture/auth-and-guards.md)
- [Deployment](../../docs/operations/deployment.md)
- [Operations Overview](../../docs/operations/overview.md)