#!/usr/bin/env php
<?php
/**
 * Simple Migration Runner
 * Run a specific SQL migration file
 */

if ($argc < 2) {
    echo "Usage: php run_migration.php <migration_file.sql>\n";
    exit(1);
}

$migrationFile = $argv[1];

if (!file_exists($migrationFile)) {
    echo "Error: Migration file not found: $migrationFile\n";
    exit(1);
}

// Load application bootstrap
require_once __DIR__ . '/../app/bootstrap.php';

echo "Running migration: $migrationFile\n";

// Read the SQL file
$sql = file_get_contents($migrationFile);

if ($sql === false) {
    echo "Error: Could not read migration file\n";
    exit(1);
}

// Split into individual statements (simple approach)
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function($stmt) {
        return !empty($stmt) && !preg_match('/^--/', $stmt);
    }
);

try {
    foreach ($statements as $statement) {
        if (trim($statement)) {
            db()->execute($statement);
            echo "✓ Executed statement\n";
        }
    }
    echo "\n✅ Migration completed successfully!\n";
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
