<?php

namespace App\Services\Auth;

use App\Constants\Guards;

final class PasswordBrokerResolver
{
    /**
     * Resolve the password broker key for a given guard.
     *
     * Resolution order:
     * 1) config('nka.auth.password_brokers.<guard>')
     * 2) config('auth.defaults.passwords')
     *
     * Safety:
     * - If the resolved broker is not defined in auth.passwords,
     *   falls back to the default broker.
     */
    public function broker(string $guard): string
    {
        $guard = Guards::normalize($guard) ?? Guards::default();

        $map = config('nka.auth.password_brokers', []);
        $defaultBroker = $this->defaultBroker();

        $broker = is_array($map) && isset($map[$guard])
            ? (string) $map[$guard]
            : $defaultBroker;

        // Ensure the broker actually exists in auth.passwords
        if (! $this->brokerExists($broker)) {
            return $defaultBroker;
        }

        return $broker;
    }

    /**
     * Get default broker from Laravel config.
     */
    private function defaultBroker(): string
    {
        $default = config('auth.defaults.passwords', 'users');

        return is_string($default) && $default !== ''
            ? $default
            : 'users';
    }

    /**
     * Check whether a broker key exists in auth.passwords.
     */
    private function brokerExists(string $broker): bool
    {
        return is_array(config("auth.passwords.$broker"));
    }
}