# Evaluating Redis-Backed RBAC in Laravel: Guard Boundary Behaviour

---

## Context

As part of developing the Laravel Stakeholder RBAC Infrastructure Artefact, I evaluated a Redis-backed roles and permissions package to better understand its behaviour in multi-guard environments.

The objective of this evaluation was to assess:

- correctness of authorization behaviour  
- handling of multiple authentication guards  
- suitability for multi-stakeholder systems  
- performance-oriented design trade-offs  

This evaluation was conducted independently in a controlled local environment.

Guard behaviour, classification, and resolution in this system are defined in:

→ [Authentication & Guards](../architecture/auth-and-guards.md)

---

## Environment Setup

The package was tested using a lightweight containerised setup:

- Laravel 11  
- Docker (PHP-FPM, Nginx, Redis)  
- SQLite (for simplified testing)  
- Predis (Redis client)  
- laravel-permissions-redis (v1.1.1)  

**Date of evaluation:** 25 March 2026  

The package was installed and configured according to its documentation, including cache warming.

---

## Test Scenario

To evaluate guard isolation, roles and permissions were created with identical names across multiple guards.

```php
Permission::findOrCreate('posts.edit', 'web');
Permission::findOrCreate('posts.edit', 'api');

Role::findOrCreate('editor', 'web');
Role::findOrCreate('editor', 'api');
```
A permission was assigned to the `web` role, and the role was assigned to a user:

```php
$role = Role::findByName('editor', 'web');
$permission = Permission::findByName('posts.edit', 'web');

$role->permissions()->sync([$permission->id]);
$user->assignRole('editor'); // web
```

***

## Observations

The following authorization checks were performed:

```php
// Permission checks
$user->hasPermissionTo('posts.edit', 'web'); // true
$user->hasPermissionTo('posts.edit', 'api'); // true

// Role checks
$user->hasRole('editor', 'web'); // true
$user->hasRole('editor', 'api'); // true
```

Despite only assigning roles and permissions under the `web` guard, checks against the `api` guard also returned `true`.

***

## Analysis

The observed behaviour indicates that permission and role resolution is primarily based on the identifier (name), rather than a combination of `name + guard`.

As a result, guard boundaries are not strictly enforced when identical names exist across multiple guards.

This behaviour may lead to unintended authorization outcomes in systems where:

* multiple guards are used (e.g., `web`, `student`, `employer`, `api`)
* permission and role names overlap across contexts

This is particularly relevant in multi-stakeholder systems where strict separation between authentication contexts is required.

***

## Validation

Redis cache inspection confirmed that roles and permissions were being stored and resolved correctly at the data level.

Example Redis keys:

* `auth:user:{userId}:roles`
* `auth:user:{userId}:permissions`
* `auth:role:{roleId}:permissions`
* `auth:role:{roleId}:users`

This suggests that the issue is not related to caching, but to how authorization resolution is performed.

***

## Impact

In multi-guard environments, this behaviour can:

* weaken logical separation between authentication contexts
* introduce ambiguity in authorization checks
* lead to incorrect access decisions when identifiers overlap

For systems designed around stakeholder-specific guards, this represents an important architectural consideration.

***

## Reflection

This evaluation highlighted the importance of strict guard boundary enforcement in RBAC systems.

While analysing this behaviour, I identified a similar gap in my own RBAC infrastructure.

To align RBAC behaviour with the system’s guard model, I introduced the following improvements:

* restricting guard assignment to configured application guards (`Guards::configured()`)
* enforcing guard validation at input level
* aligning authorization behaviour with the application guard abstraction

These changes ensure that only guards explicitly recognised by the system are accepted within RBAC workflows.

***

## Contribution

The findings were shared with the package author via:

* a LinkedIn discussion
* a GitHub issue with reproducible test steps

**GitHub issue (reproduction and discussion):**
<https://github.com/scabarcas17/laravel-permissions-redis/issues/1>

The behaviour was acknowledged by the maintainer, and a fix for proper guard isolation is planned.

***

## Related Internal Decision

This evaluation directly influenced an architectural refinement in this artefact:

* [ADR-008: Guard Validation for RBAC Entity Creation](../decisions/ADR-008-guard-validation-for-rbac-creation.md)

***

## Key Takeaways

* Redis-backed authorization can significantly improve performance
* Guard handling is a critical aspect of RBAC design
* Name-based resolution alone is insufficient in multi-guard systems
* Edge-case testing is essential for validating system correctness
* Evaluating third-party packages can uncover improvements in internal system design

***

## Note

This case study is based on an independent evaluation of the open-source package:

**laravel-permissions-redis** by Sebastian Cabarcas
**Version tested:** v1.1.1
**Date of evaluation:** 25 March 2026

<https://packagist.org/packages/scabarcas/laravel-permissions-redis>

**Source repository:**
<https://github.com/scabarcas17/laravel-permissions-redis>

All testing was conducted locally and is intended to support architectural understanding.
