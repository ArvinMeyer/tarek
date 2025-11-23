<?php
// Enable ALL error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Error Check Page</h1>";
echo "<p>If you see this, PHP is working.</p>";

echo "<h2>Checking Laravel...</h2>";
echo "<pre>";

try {
    echo "1. Loading autoloader...\n";
    
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        die("✗ FATAL: vendor/autoload.php NOT FOUND!\n\nYou MUST run: composer install\n");
    }
    
    require __DIR__ . '/../vendor/autoload.php';
    echo "✓ Autoloader loaded\n\n";
    
    echo "2. Loading Laravel app...\n";
    
    if (!file_exists(__DIR__ . '/../bootstrap/app.php')) {
        die("✗ FATAL: bootstrap/app.php NOT FOUND!\n");
    }
    
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "✓ Laravel app loaded\n\n";
    
    echo "3. Checking environment...\n";
    echo "APP_KEY: " . (getenv('APP_KEY') ? 'SET ✓' : 'NOT SET ✗') . "\n";
    echo "DB_HOST: " . (getenv('DB_HOST') ?: 'NOT SET ✗') . "\n";
    echo "DB_DATABASE: " . (getenv('DB_DATABASE') ?: 'NOT SET ✗') . "\n\n";
    
    echo "4. Testing database connection...\n";
    try {
        $pdo = new PDO(
            'mysql:host=' . (getenv('DB_HOST') ?: 'localhost') . ';dbname=' . (getenv('DB_DATABASE') ?: ''),
            getenv('DB_USERNAME') ?: '',
            getenv('DB_PASSWORD') ?: ''
        );
        echo "✓ Database connection successful!\n\n";
    } catch (PDOException $e) {
        echo "✗ Database connection FAILED: " . $e->getMessage() . "\n\n";
    }
    
    echo "5. Checking storage permissions...\n";
    $storageWritable = is_writable(__DIR__ . '/../storage');
    $cacheWritable = is_writable(__DIR__ . '/../bootstrap/cache');
    
    echo "storage writable: " . ($storageWritable ? '✓ YES' : '✗ NO - Run: chmod -R 755 storage') . "\n";
    echo "bootstrap/cache writable: " . ($cacheWritable ? '✓ YES' : '✗ NO - Run: chmod -R 755 bootstrap/cache') . "\n\n";
    
    echo "✓ ALL CHECKS PASSED!\n\n";
    echo "If Laravel still shows blank page, check:\n";
    echo "1. Is APP_DEBUG set to true?\n";
    echo "2. Check storage/logs/laravel.log for errors\n";
    echo "3. Make sure database was imported (database_import.sql)\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";

echo "<hr>";
echo "<p style='color: red; font-weight: bold;'>⚠️ DELETE THIS FILE AFTER TESTING!</p>";
?>

