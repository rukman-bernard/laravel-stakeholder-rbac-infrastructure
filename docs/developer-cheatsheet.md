# Laravel Stakeholder RBAC Infrastructure
## Developer Cheat Sheet

This cheat sheet provides quick reference guidelines to help developers stay aligned with the system’s architectural design.

---

### 🧠 Core Principles

- **Authentication ≠ Authorisation**
  - Guards → identify **who*- is authenticated
  - RBAC → define **what*- they can do

- **Single Source of Truth**
  - Guards → `App\Constants\Guards`
  - Roles → `App\Constants\Roles`
  - Permissions → `App\Constants\Permissions`

- **No duplication of logic**
  - Never re-implement guard logic
  - Always use provided services and constants

---

### 🔐 Guards (Authentication)

#### Use ONLY the Guards abstraction

```php
use App\Constants\Guards;
```
#### Get configured guards

```php
Guards::configured();
```

#### Normalize configured guard input

```php
Guards::normalizeConfigured($input);
```

#### Guard rules

- ❌ Do NOT use:

```php
config('auth.guards')
```

- ❌ Do NOT hardcode:

```php
'web', 'student', 'employer'
```

- ✅ Always use:

```php
Guards::WEB
Guards::STUDENT
Guards::EMPLOYER
Guards::API
```

---

### 🔄 Guard Resolution

Use:

```php
use App\Services\Auth\GuardResolver;
```

#### Detect active guard

```php
$guard = app(GuardResolver::class)->detect();
```

#### Key rules

- First authenticated guard in resolution order wins
- Resolution order is deterministic
- Only configured session guards participate


---

### 👥 RBAC (Roles & Permissions)

#### Roles and permissions are guard-scoped
- The same role name can exist across different guards
- The same permission name can exist across different guards
- Always validate uniqueness within guard context


#### Validate role uniqueness

```php
Rule::unique('roles', 'name')
    ->where(fn ($q) => $q->where('guard_name', $guard))
```

#### Validate permission uniqueness

```php
Rule::unique('permissions', 'name')
    ->where(fn ($q) => $q->where('guard_name', $guard))
```

---

### 🛡️ Guard Validation (CRITICAL)

Always validate guards using:

```php
Rule::in(Guards::configured())
```

Never trust raw user input.

---

### 🧩 Livewire Components

#### Always validate guard input

```php
'guard_name' => [
    'required',
    Rule::in(Guards::configured()),
]
```

#### Filter data by guard

```php
Permission::where('guard_name', $this->guard_name)
```

```php
Role::where('guard_name', $this->guard_name)
```
---

### 🧠 Services (Use, don’t reimplement)

| Responsibility    | Service                   |
| ----------------- | ------------------------- |
| Guard detection   | `GuardResolver`           |
| Dashboard routing | `DashboardResolver`       |
| Login redirect    | `GuardRedirectService`    |
| Logout handling   | `GuardLogoutService`      |
| Password reset    | `PasswordBrokerResolver`  |
| UI config         | `AdminLTESettingsService` |

---

### 🖥️ Theming

- Do NOT modify AdminLTE directly
- Use:

```php
AdminLTESettingsService
```

- Skins defined in:

```php
config('nka.ui.skins')
```

---

### 🧭 Routing

#### Use guard-aware middleware

#### or:
```php
'auth:' . implode(',', Guards::session())
```

---

### 🚫 Common Mistakes

#### ❌ Bypassing Guards abstraction

```php
config('auth.guards') // avoid in feature-level logic
```

---
#### ❌ Confusing guards with roles

Guard → defines the authentication context (who you are logged in as)  
Role → defines permissions within that context (what you can do)

---

#### ❌ Ignoring guard context in RBAC lookups

```php
Role::findByName('admin') // ambiguous without explicit guard context
```

---

#### ❌ Trusting frontend guard input

Always validate:

```php
Guards::configured()
```

---

### ✅ Correct Patterns

#### Create Role

```php
Role::create([
    'name' => $name,
    'guard_name' => $guard,
]);
```

---

#### Assign Role

```php
$user->assignRole('admin');
```

---

#### Check Permission

```php
$user->hasPermissionTo('edit users');
```

---

### 🧪 Testing Tips

- Always test:

  - multiple guards
  - same role name across guards
  - same permission name across guards
  - guard switching scenarios

---

### 📚 Reference Docs

- [Authentication](docs/architecture/auth-and-guards.md)
- [RBAC](docs/architecture/authorisation-rbac.md)
- [Stakeholders](docs/architecture/stakeholders.md)
- [Theming](docs/architecture/theming-strategy.md)
- [Decisions](docs/decisions/README.md)
- [Case Study](docs/case-studies/redis-rbac-evaluation.md)
---

### 🧭 Mental Model

```text
User → Guard → Identity → Role → Permission → UI → Theme
```

---

### 🚀 Final Rule

> If you find yourself writing guard logic manually → STOP
> There is already a service or constant for it.
