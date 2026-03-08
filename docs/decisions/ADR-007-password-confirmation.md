# ADR-007: Password Confirmation (Deferred)

## Status
Deferred

## Context

Laravel provides a password confirmation mechanism (`password.confirm` middleware and `ConfirmPasswordController`) to protect high-risk actions by requiring recent password verification.

In a multi-guard environment, such confirmation must be implemented in a guard-aware manner to avoid cross-portal inconsistencies.

At the current stage of the infrastructure:

- No break-glass workflows exist
- No root escalation flows are implemented
- No financial or grading override actions are present

Introducing password confirmation prematurely would increase architectural complexity without immediate security benefit.

## Decision

Password confirmation flows are deferred.

They will be implemented when introducing:

- Root escalation mechanisms
- Break-glass access workflows
- Financial or grading override operations
- Other high-risk administrative actions

## Consequences

- Reduced complexity in the initial infrastructure baseline
- Clear architectural placeholder for future security hardening
- Avoidance of premature guard-aware confirmation logic

## Related Documents

- [Authentication & Guards](../architecture/auth-and-guards.md)
- [Security Posture](../features/security-posture.md)