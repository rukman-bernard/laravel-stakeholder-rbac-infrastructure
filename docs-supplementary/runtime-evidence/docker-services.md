> Status: Implemented and verified in the reference environment  
> Category: Runtime evidence  
> Purpose: Demonstrates container service behaviour and Redis state verification for the infrastructure artefact

# Docker Services

## Purpose

This document describes the Docker services used by the reference implementation and how they interact within the containerised environment.

Each service has a **single responsibility** and communicates over a Docker-managed network.

---

## Core Services

### Laravel Application Container

**Responsibility**
- Runs the Laravel application
- Handles authentication, role-based authorisation, and session logic
- Serves HTTP requests

**Key characteristics**
- PHP runtime container
- Mounts application source code
- Reads environment configuration from `.env`

This service is stateless and can be rebuilt safely.

---

### Database Service (MariaDB)

**Responsibility**
- Persistent storage for all application data

MariaDB operates in a MySQL-compatible configuration.

**Key characteristics**
- Data stored in Docker volumes
- Backed up externally
- Never rebuilt without backup

This is the **most critical service**.

---

### Cache / Session Service (Redis)

**Responsibility**
- Session storage (single-session enforcement)
- Cache storage
- Queue backend (if enabled)

**Key characteristics**
- Provides centralised session storage
- Enables cross-container session consistency
- Improves state management performance

---
## Redis Runtime Verification

The reference implementation uses Redis for:
- Cache (DB 1)
- Queue (DB 2)
- Sessions (DB 0)

---
**The following procedures demonstrate that Redis is correctly configured as the system state layer for cache, queue transport, and session storage.**

### 1) Confirm configuration values (Laravel-side)

```bash
docker compose exec laravel php artisan tinker --execute 'dump([
  "CACHE_STORE" => config("cache.default"),
  "CACHE_PREFIX" => config("cache.prefix"),
  "CACHE_CONN" => config("cache.stores.redis.connection"),
  "CACHE_DB" => config("database.redis.cache.database"),

  "QUEUE_CONNECTION" => config("queue.default"),
  "QUEUE_CONN" => config("queue.connections.redis.connection"),
  "QUEUE_DB" => config("database.redis.".config("queue.connections.redis.connection").".database"),
  "QUEUE_NAME" => config("queue.connections.redis.queue"),

  "SESSION_DRIVER" => config("session.driver"),
  "SESSION_CONN" => config("session.connection"),
  "SESSION_PREFIX" => config("session.prefix"),
  "SESSION_DB" => config("database.redis.". (config("session.connection") ?: "default") .".database"),
]);'
```

Expected:
```text
array:12 [
  "CACHE_STORE" => "redis"
  "CACHE_PREFIX" => "nka_hub_cache_"
  "CACHE_CONN" => "cache"
  "CACHE_DB" => "1"
  "QUEUE_CONNECTION" => "redis"
  "QUEUE_CONN" => "queue"
  "QUEUE_DB" => "2"
  "QUEUE_NAME" => "default"
  "SESSION_DRIVER" => "redis"
  "SESSION_CONN" => "default"
  "SESSION_PREFIX" => "nka_hub_session_"
  "SESSION_DB" => "0"
] 
```

---

### 2) Cache proof (Laravel writes → Redis contains key)

#### 2.1 Write a cache key via Laravel

```bash
docker compose exec laravel php artisan tinker --execute '
Cache::store("redis")->put("redis_manual_cache_test", "ok", 600);
echo Cache::store("redis")->get("redis_manual_cache_test").PHP_EOL;
'
```
Expected output:

* `ok`

#### 2.2 Verify the key exists in Redis DB 1

Laravel applies a cache prefix. Confirm the prefix first:

```bash
docker compose exec laravel php artisan tinker --execute 'echo config("cache.prefix").PHP_EOL;'
```
Now check Redis DB 1:
```bash
docker compose exec redis redis-cli -n 1 KEYS "*redis_manual_cache_test*"
```
Expected output includes something like:

* `nka_hub_cache_redis_manual_cache_test`

Read the raw value using the _prefixed_ key:

```bash
docker compose exec redis redis-cli -n 1 GET "nka_hub_cache_redis_manual_cache_test"
```

Expected:

* a serialized value like `"s:2:\"ok\";"` (this is normal)

This proves: **Laravel cache store is Redis and keys land in Redis DB 1**

***

### 3) Queue proof (Job appears in Redis → worker processes it)

This project uses:

* Redis connection: `queue`

* Redis DB index: `2`

* Queue name: defined by `REDIS_QUEUE` (default: `default`)


#### 3.1 Dispatch a probe job (Laravel → Redis queue list)

Enter tinker:
```bash
docker compose exec laravel php artisan tinker
```
Run:
```php
$token = now()->format("YmdHis")."_".bin2hex(random_bytes(3));
dispatch(new \App\Jobs\RedisDoctorProbeJob($token))->onQueue("redis-manual");
$token;
```
Copy the printed token.

#### 3.2 Confirm the job exists in Redis DB 2

```bash
docker compose exec redis redis-cli -n 2 LLEN "queues:redis-manual"
docker compose exec redis redis-cli -n 2 LRANGE "queues:redis-manual" 0 0
```
Expected:

* `LLEN` should be `>= 1`

* `LRANGE` shows JSON payload for the queued job

#### 3.3 Process the queue

```bash
docker compose exec laravel php artisan queue:work redis --queue=redis-manual --stop-when-empty -v
```
Expected:

* `App\Jobs\RedisDoctorProbeJob ... DONE`

#### 3.4 Confirm the job executed (it writes a cache result key)

Laravel writes the result into the Redis **cache store** (DB 1) using the cache prefix.

Get the cache prefix:
```bash
docker compose exec laravel php artisan tinker --execute 'echo config("cache.prefix").PHP_EOL;'
```
Now verify the result key exists via Redis CLI (DB 1):

Replace `<TOKEN>` with your token and `<CACHE_PREFIX>` with the prefix you printed.

```bash
docker compose exec redis redis-cli -n 1 GET "<CACHE_PREFIX>redis_doctor:probe:<TOKEN>"
```
Expected:

* a serialized string containing `processed`

This proves:

* jobs are stored in Redis (DB 2)
* a worker can process them
* the job code ran successfully (writes proof key into Redis cache DB 1)
---
### 4) Session proof (HTTP request creates session → Redis DB 0 contains session keys)

This system stores sessions in:
- Redis connection: `default`
- Redis DB index: `0`
- Key prefix: `<APP_NAME>_session_`


#### 4.1 Confirm session driver is Redis
```bash
docker compose exec laravel php artisan tinker --execute 'echo config("session.driver").PHP_EOL;'
```
Expected:

* `redis`

#### 4.2 Create a session via browser (simplest method)

1. Open the app in your browser (e.g., login page or dashboard).

2. Refresh once.

Now find the session prefix Laravel uses:
```bash
docker compose exec laravel php artisan tinker --execute 'echo config("session.prefix").PHP_EOL;'
```
Check Redis DB 0 for session keys:

Replace `<SESSION_PREFIX>` with the printed prefix:
```bash
docker compose exec redis redis-cli -n 0 KEYS "<SESSION_PREFIX>*"
docker compose exec redis redis-cli -n 0 DBSIZE
```
Expected:

* `KEYS` shows session keys

* `DBSIZE` is `> 0` once sessions exist

This proves: **sessions are stored in Redis DB 0**

---
### Notes / Common confusion: Prefixes

* Cache keys are stored as: `<CACHE_PREFIX><your-key>`
* Session keys are stored as: `<SESSION_PREFIX><session-id>`
* Queue lists are stored as: `queues:<queue-name>` (no cache prefix)

If a Redis `GET` returns `(nil)`, check whether you queried the _unprefixed_ key.

---

### Node / Vite Service (Asset Build)

**Responsibility**
- Build frontend assets
- Compile CSS/SCSS skins
- Support Vite HMR in development

**Key characteristics**
- Used primarily in development
- Not required at runtime in production
- Outputs built assets consumed by Laravel

---

### Reverse Proxy (Optional)

**Responsibility**
- HTTP routing
- TLS termination
- Port exposure

This service is optional and depends on deployment strategy.

---

## Service Communication

All services communicate via:
- Docker internal networking
- Service names as hostnames

No service communicates via `localhost` internally.

---

## Session Integrity and Docker

Single-session enforcement is preserved because:

- session storage is centralised (Redis or DB),
- containers are stateless,
- restarting containers does not invalidate sessions unexpectedly.

This aligns with the system’s single-session enforcement model.

---
## Scaling Considerations

> Status: Architectural capability (not demonstrated in this runtime evidence)

The system supports:

- horizontal scaling of application containers,
- shared session store,
- shared database backend.

No architectural changes are required to scale.

---

## Operational Safety Rules

> Status: Operational guidance (not enforced by the current infrastructure)

- Never rebuild the database container without backup
- Never expose internal service ports publicly
- Always validate `.env` consistency after changes
- Restart services in dependency order if needed

---

## Related Documentation

- [Docker Setup](./docker-setup.md)
- [Deployment](../../docs/operations/deployment.md)
- [Session Management](../../docs/operations/session-management.md)
- [Backups and Recovery](../engineering-notes/backups-and-recovery.md)
