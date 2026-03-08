> Status: Operational guidance
> Category: Runtime diagnostics
> Purpose: Helps developers and operators diagnose issues encountered when running systems built on the Laravel Stakeholder RBAC Infrastructure Artefact.


# Troubleshooting

## Purpose

This document provides guidance for diagnosing and resolving issues that may occur when operating systems built using the Laravel Stakeholder RBAC Infrastructure Artefact.

The goal is to assist developers and operators in identifying common runtime problems related to:

* authentication and guard resolution

* dashboard routing

* session behaviour

* configuration drift

* route availability

This document focuses on **practical debugging steps** rather than system design.

***

# 1. Authentication and Guard Issues

## Problem

A user logs in successfully but is redirected to an unexpected location or login page.

## Possible Causes

* incorrect guard detection

* missing dashboard route configuration

* misconfigured login routes

* missing portal route definitions

## Diagnostic Steps

Check the configured guards in:
```php
config/auth.php
```
Verify that all expected guards are defined and use the correct driver:
```
'guards' => [
    'web' => [...],
    'student' => [...],
    'employer' => [...],
]
```
Ensure the guard uses the **session driver** for interactive portals.

***

## Verify Dashboard Route Configuration

Check:
```php
config/nka.php
```
Example configuration:
```php
'nka' => [
    'auth' => [
        'dashboard_routes' => [
            'web' => [
                'roles' => [
                    Roles::SYSTEM_ADMIN     => 'sysadmin.dashboard',
                    Roles::SUPER_ADMIN      => 'admin.dashboard',
                    Roles::ADMIN            => 'admin.dashboard',
                ],
                'default' => 'auth.reset',
            ],

            'student'  => 'student.dashboard',
            'employer' => 'employer.dashboard',
        ],
    ],
]
```
Ensure that:

* route names exist

* role names match actual role names

* default routes are defined.

***

# 2. Dashboard Redirects Failing

## Problem

After login the system redirects to the `auth.reset` page.

## Explanation

The infrastructure intentionally redirects to **`auth.reset`** when:

* a configured dashboard route does not exist

* configuration references an invalid route

* the resolved route fails validation.

This behaviour acts as a **safe recovery mechanism**.

***

## Diagnostic Steps

Check whether the configured dashboard route exists.

Example check:
```bash
php artisan route:list
```
Look for the route name referenced in:
```php
nka.auth.dashboard_routes
```
Example:
```route
student.dashboard
employer.dashboard
admin.dashboard
```
If the route does not exist, update either:

* the route definition, or

* the configuration entry.

***

# 3. Guard Detection Problems

## Problem

The system behaves as if the wrong guard is authenticated.

Example symptoms:

* student login redirects to web dashboard

* portal routes redirect to incorrect login page.

***

## How Guard Detection Works

The infrastructure detects the active authentication context using deterministic guard resolution.

Guards are evaluated in the order defined by:
```php
Guards::resolutionOrder()
```
The **first authenticated guard** is treated as the active session context.

***

## Diagnostic Steps

Check which guard is currently authenticated.

Temporary debugging example:
```laravel
Auth::guard('web')->check();
Auth::guard('student')->check();
Auth::guard('employer')->check();
```
Only **one guard should be authenticated** in normal operation.

If multiple guards appear authenticated, session handling may be misconfigured.

***

# 4. Login Route Not Found

## Problem

Unauthenticated requests redirect to an unexpected route or `/`.

## Possible Causes

* missing login routes

* incorrect guard naming

* incorrect portal route definitions

***

## Expected Login Route Structure
```route
login
student.login
employer.login
```
Check available routes:

```bash
php artisan route:list
```
Verify that login routes exist and match the configured guard names.

***

# 5. Configuration Drift

## Problem

The system behaves inconsistently across environments.

Example symptoms:

* routes work locally but fail in production

* dashboard redirects differ between environments.

***

## Possible Causes

* cached configuration

* outdated route cache

* environment configuration mismatch

***

## Recommended Fix

Clear Laravel caches:
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```
If using production cache:
```bash
php artisan config:cache
php artisan route:cache
```

***

# 6. Route Configuration Errors

## Problem

Dashboard routing or login routing behaves unpredictably.

***

## Diagnostic Steps

Verify that the routes referenced in configuration exist.

Example:
```laravel
Route::name('student.dashboard')
```
Ensure route names match exactly with configuration entries.

***

# 7. Session Issues

## Problem

Users appear to lose authentication or are redirected to login unexpectedly.

***

## Possible Causes

* session storage misconfiguration

* expired session cookies

* incorrect session domain configuration.

***

## Diagnostic Steps

Check session configuration:
```php
config/session.php
```
Verify:

* session driver

* cookie domain

* lifetime settings.

***

# 8. When to Use `auth.reset`

The `auth.reset` route acts as a **controlled recovery path**.

It is triggered when:

* dashboard routing cannot safely determine a valid route

* configuration references invalid routes.

This route should:

* remain protected by authentication middleware

* provide a safe fallback screen allowing the user to re-establish session context.

***

# Related Documentation

- [Authentication & Guards](../../docs/architecture/auth-and-guards.md)
- [Dashboards Feature](../../docs/features/dashboards.md)
- [Session Management](../../docs/operations/session-management.md)
- [Reference Environment](../../docs/operations/environments.md)
- [Deployment](../../docs/operations/deployment.md)
