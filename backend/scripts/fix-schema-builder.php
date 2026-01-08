<?php

/**
 * Fix Laravel Schema Builder bug where getTablePrefix() returns an array.
 * This script patches the vendor file directly.
 */

$file = __DIR__ . '/../vendor/laravel/framework/src/Illuminate/Database/Schema/Builder.php';

if (!file_exists($file)) {
    echo "File not found: $file\n";
    exit(1);
}

$content = file_get_contents($file);

// Check if already patched
if (strpos($content, '// FIXED: Ensure prefix is always a string') !== false) {
    echo "File already patched.\n";
    exit(0);
}

// Find the hasTable method and patch it
$pattern = '/public function hasTable\(\$table\)\s*\{[^}]*\$table = \$this->connection->getTablePrefix\(\)\.\$table;/s';

$replacement = <<<'PHP'
public function hasTable($table)
    {
        // FIXED: Ensure prefix is always a string (Laravel bug fix)
        $prefix = $this->connection->getTablePrefix();
        if (is_array($prefix)) {
            $prefix = '';
        } elseif (!is_string($prefix)) {
            $prefix = (string) ($prefix ?? '');
        }
        $table = $prefix . $table;
PHP;

$newContent = preg_replace($pattern, $replacement, $content);

if ($newContent === $content) {
    echo "Could not find pattern to patch.\n";
    exit(1);
}

file_put_contents($file, $newContent);
echo "Successfully patched Schema Builder.\n";
