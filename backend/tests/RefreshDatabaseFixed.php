<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase as BaseRefreshDatabase;

trait RefreshDatabaseFixed
{
    use BaseRefreshDatabase {
        refreshTestDatabase as baseRefreshTestDatabase;
    }

    /**
     * Refresh the test database and fix prefix issue.
     */
    protected function refreshTestDatabase(): void
    {
        // Ensure prefix is set correctly in config before any connection is made
        $config = app('config');
        $connections = $config->get('database.connections', []);
        
        foreach ($connections as $name => $connection) {
            if (isset($connection['prefix'])) {
                $prefix = $connection['prefix'];
                if (is_array($prefix) || (!is_string($prefix) && $prefix !== null)) {
                    $connections[$name]['prefix'] = '';
                }
            } else {
                $connections[$name]['prefix'] = '';
            }
        }
        
        $config->set('database.connections', $connections);
        
        // Clear any existing connections to force recreation with fixed prefix
        \Illuminate\Support\Facades\DB::purge();
        
        // Call parent method
        $this->baseRefreshTestDatabase();
    }

}
