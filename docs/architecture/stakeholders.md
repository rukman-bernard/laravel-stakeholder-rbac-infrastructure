# Stakeholders

This document describes the system stakeholders, their authentication context, entry points, and associated UI configuration.

Each stakeholder type is isolated through a dedicated authentication guard.  
Authorisation (where applicable) is applied independently of authentication.

---

## Student

- Guard: `student`
- Spatie Role: _Not applicable_
- Login Route: `/student/login`
- Logout Route: `/logout`
- Registration Route: `/student/register`
- Entry Route: `/student/dashboard`
- Layout: `resources/views/components/layouts/app.blade.php`
- CSS Skin: `resources/scss/skins/student/student.scss`

Students authenticate under a dedicated guard and operate within an isolated portal context.  
No role-based access control is currently applied within this guard.

---

## Employer

- Guard: `employer`
- Spatie Role: _Not applicable_
- Login Route: `/employer/login`
- Logout Route: `/logout`
- Registration Route: `/employer/register`
- Entry Route: `/employer/dashboard`
- Layout: `resources/views/components/layouts/app.blade.php`
- CSS Skin: `resources/css/skins/employer.css`

Employers authenticate under a dedicated guard separate from students and internal users.  
No role-based access control is currently applied within this guard.

---

## Sysadmin

- Guard: `web`
- Spatie Role: `sysadmin`
- Login Route: `/login`
- Logout Route: `/logout`
- Registration Route: `/register`
- Entry Route: `/sysadmin/dashboard`
- Layout: `resources/views/components/layouts/app.blade.php`
- CSS Skin: AdminLTE default (dark mode enabled)

Sysadmin users perform system-level administration and security-sensitive operations.

---

## Superadmin

- Guard: `web`
- Spatie Role: `superadmin`
- Login Route: `/login`
- Logout Route: `/logout`
- Registration Route: `/register`
- Entry Route: `/admin/dashboard`
- Layout: `resources/views/components/layouts/app.blade.php`
- CSS Skin: AdminLTE default (dark mode enabled)

Superadmin users have full administrative capabilities within the application domain, excluding sysadmin-only functions.

---

## Admin

- Guard: `web`
- Spatie Role: `admin`
- Login Route: `/login`
- Logout Route: `/logout`
- Registration Route: `/register`
- Entry Route: `/admin/dashboard`
- Layout: `resources/views/components/layouts/app.blade.php`
- CSS Skin: AdminLTE default (dark mode enabled)

Admin users perform standard administrative functions within the application.

---

> **Note:** Sysadmin, Superadmin, and Admin users share the same authentication guard (`web`).  
> They are differentiated strictly through Spatie roles and permissions.  
> No additional logical grouping or implicit role abstraction is used within the system.

---

## Stakeholder Access & Routing Flow

```mermaid
flowchart TB
    U[User] --> G{Authenticated Guard?}

    G -->|student| S[Student Portal]
    G -->|employer| E[Employer Portal]
    G -->|web| W[Internal User Portal]

    S --> SD[/student/dashboard/]
    E --> ED[/employer/dashboard/]

    W --> R{Spatie Role}
    R -->|sysadmin| SYD[/sysadmin/dashboard/]
    R -->|superadmin| AD[/admin/dashboard/]
    R -->|admin| AD[/admin/dashboard/]

    S --> SL[app.blade.php + student skin]
    E --> EL[app.blade.php + employer skin]
    W --> WL[app.blade.php + AdminLTE default]