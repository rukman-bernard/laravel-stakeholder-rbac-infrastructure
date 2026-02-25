<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

/**
 * RedisDoctorProbeJob
 * ===================
 *
 * PURPOSE
 * -------
 * This job is intentionally minimal and exists solely as a
 * **queue execution health probe** for Redis-backed queues.
 *
 * It allows the system to verify that:
 *   1. The job was successfully pushed to Redis.
 *   2. The queue worker picked it up.
 *   3. The job executed without failure.
 *   4. The Redis cache store is writable.
 *
 * HOW IT WORKS
 * ------------
 * - A unique token is generated when dispatching the job.
 * - When executed, the job writes a short-lived key to Redis.
 * - External health checks can confirm execution by reading that key.
 *
 * DESIGN INTENT
 * -------------
 * - This job contains NO business logic.
 * - It must remain lightweight and deterministic.
 * - The queue name is NOT forced here; dispatcher decides via ->onQueue().
 *
 * This job supports:
 * - System diagnostics (e.g., /sysadmin/redis-doctor)
 * - CI/CD environment verification
 * - Production queue health checks
 */
final class RedisDoctorProbeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Unique probe token used to correlate dispatch with execution.
     */
    public function __construct(
        public readonly string $token
    ) {}

    /**
     * Execute the probe.
     */
    public function handle(): void
    {
        // Mark probe as processed in Redis cache.
        // TTL = 5 minutes (sufficient for health verification window).
        Cache::store('redis')->put(
            $this->resultKey(),
            'processed',
            now()->addMinutes(5)
        );
    }

    /**
     * Build the deterministic Redis result key.
     */
    private function resultKey(): string
    {
        return "redis_doctor:probe:{$this->token}";
    }
}