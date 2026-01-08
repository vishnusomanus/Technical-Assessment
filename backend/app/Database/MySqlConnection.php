<?php

namespace App\Database;

use Illuminate\Database\MySqlConnection as BaseMySqlConnection;

class MySqlConnection extends BaseMySqlConnection
{
    /**
     * Get the table prefix for the connection.
     * 
     * Override to ensure prefix is always a string, never an array.
     *
     * @return string
     */
    public function getTablePrefix()
    {
        $prefix = parent::getTablePrefix();
        
        // Fix Laravel bug: ensure prefix is always a string
        if (is_array($prefix)) {
            return '';
        }
        
        if (!is_string($prefix)) {
            return (string) ($prefix ?? '');
        }
        
        return $prefix;
    }
    
    /**
     * Get a schema builder instance for the connection.
     *
     * @return \App\Database\SchemaBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new \App\Database\SchemaBuilder($this);
    }
    
    /**
     * Set the table prefix and return the connection.
     *
     * @param  string  $prefix
     * @return $this
     */
    public function setTablePrefix($prefix)
    {
        // Ensure prefix is always a string
        if (is_array($prefix)) {
            $prefix = '';
        } elseif (!is_string($prefix)) {
            $prefix = (string) ($prefix ?? '');
        }
        
        $this->tablePrefix = $prefix;

        return $this;
    }
}
