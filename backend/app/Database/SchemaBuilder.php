<?php

namespace App\Database;

use Illuminate\Database\Schema\MySqlBuilder as BaseBuilder;

class SchemaBuilder extends BaseBuilder
{
    /**
     * Determine if the given table exists.
     * 
     * Override to fix Laravel bug where getTablePrefix() returns an array.
     *
     * @param  string  $table
     * @return bool
     */
    public function hasTable($table)
    {
        $prefix = $this->connection->getTablePrefix();
        
        // Fix: Ensure prefix is always a string
        if (is_array($prefix)) {
            $prefix = '';
        } elseif (!is_string($prefix)) {
            $prefix = $prefix !== null ? (string) $prefix : '';
        }
        
        // Handle edge case where table might be an array
        if (is_array($table)) {
            // If table is an array, try to get the first element or use empty string
            $table = !empty($table) ? (string) reset($table) : '';
        } elseif (!is_string($table)) {
            $table = (string) $table;
        }
        
        $table = $prefix . $table;

        foreach ($this->getTables(false) as $value) {
            if (strtolower($table) === strtolower($value['name'])) {
                return true;
            }
        }

        return false;
    }
}
