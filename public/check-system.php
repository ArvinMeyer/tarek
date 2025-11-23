<?php
// Updated diagnostic - checks for .htaccess configuration
// DELETE THIS FILE AFTER TESTING!

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Complete System Check</h1>";
echo "<pre>";

echo "=== STEP 1: Check .env File ===\n";
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    echo "✓ .env file exists\n";
    $envSize = filesize($envPath);
    echo "  File size: {$envSize} bytes\n";
    if ($envSize < 100) {
        echo "  ✓ Minimal .env (good - using .htaccess)\n";
    } else {
        echo "  ⚠ Large .env (might override .htaccess values)\n";
    }
} else {
    echo "✗ .env file MISSING!\n";
    echo "  Create minimal .env file (see .env.minimal)\n";
}

echo "\n=== STEP 2: Check Environment Variables (.htaccess) ===\n";
$requiredVars = [
    'APP_KEY' => 'Application key',
    'APP_URL' => 'Application URL',
    'DB_HOST' => 'Database host',
    'DB_DATABASE' => 'Database name',
    'DB_USERNAME' => 'Database user',
    'DB_PASSWORD' => 'Database password',
];

foreach ($requiredVars as $var => $desc) {
    $value = getenv($var);
    if ($value) {
        if ($var === 'DB_PASSWORD' || $var === 'APP_KEY') {
            echo "✓ {$var}: SET (hidden for security)\n";
        } else {
            echo "✓ {$var}: {$value}\n";
        }
    } else {
        echo "✗ {$var}: NOT SET\n";
        echo "  Check .htaccess has: SetEnv {$var} value\n";
    }
}

echo "\n=== STEP 3: Check Composer Dependencies ===\n";
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "✓ vendor/autoload.php exists\n";
    require __DIR__ . '/../vendor/autoload.php';
    echo "✓ Autoloader loaded successfully\n";
} else {
    echo "✗ vendor/autoload.php MISSING!\n";
    echo "  CRITICAL: Run 'composer install'\n";
    die("\nCannot continue without vendor folder!\n");
}

echo "\n=== STEP 4: Check Laravel Bootstrap ===\n";
if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
    echo "✓ bootstrap/app.php exists\n";
    try {
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        echo "✓ Laravel application loaded\n";
    } catch (Exception $e) {
        echo "✗ Laravel failed to load: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ bootstrap/app.php MISSING!\n";
}

echo "\n=== STEP 5: Check Database Connection ===\n";
try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s',
        getenv('DB_HOST') ?: 'localhost',
        getenv('DB_PORT') ?: '3306',
        getenv('DB_DATABASE') ?: ''
    );
    
    $pdo = new PDO(
        $dsn,
        getenv('DB_USERNAME') ?: '',
        getenv('DB_PASSWORD') ?: ''
    );
    
    echo "✓ Database connection successful\n";
    
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "✓ Database has " . count($tables) . " tables\n";
        
        $requiredTables = ['users', 'customers', 'quotations', 'invoices', 'settings'];
        $missingTables = [];
        
        foreach ($requiredTables as $table) {
            if (!in_array($table, $tables)) {
                $missingTables[] = $table;
            }
        }
        
        if (empty($missingTables)) {
            echo "✓ All required tables exist\n";
        } else {
            echo "⚠ Missing tables: " . implode(', ', $missingTables) . "\n";
            echo "  Import database_import.sql in phpMyAdmin\n";
        }
    } else {
        echo "⚠ Database is empty - no tables found\n";
        echo "  Import database_import.sql in phpMyAdmin\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database connection FAILED\n";
    echo "  Error: " . $e->getMessage() . "\n";
    echo "  Check DB credentials in .htaccess\n";
}

echo "\n=== STEP 6: Check Storage Permissions ===\n";
$storageChecks = [
    'storage' => __DIR__ . '/../storage',
    'storage/framework' => __DIR__ . '/../storage/framework',
    'storage/framework/sessions' => __DIR__ . '/../storage/framework/sessions',
    'storage/framework/views' => __DIR__ . '/../storage/framework/views',
    'storage/framework/cache' => __DIR__ . '/../storage/framework/cache',
    'storage/logs' => __DIR__ . '/../storage/logs',
    'bootstrap/cache' => __DIR__ . '/../bootstrap/cache',
];

$permissionIssues = [];
foreach ($storageChecks as $name => $path) {
    if (!file_exists($path)) {
        echo "✗ {$name}: Directory doesn't exist\n";
        $permissionIssues[] = $name;
    } elseif (!is_writable($path)) {
        echo "✗ {$name}: Not writable\n";
        $permissionIssues[] = $name;
    } else {
        echo "✓ {$name}: OK\n";
    }
}

if (!empty($permissionIssues)) {
    echo "\n⚠ Fix permissions:\n";
    echo "  chmod -R 755 storage bootstrap/cache\n";
    echo "  Or create missing directories\n";
}

echo "\n=== STEP 7: System Information ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Current Path: " . __FILE__ . "\n";

echo "\n=== FINAL RESULT ===\n";

$issues = [];
if (!file_exists(__DIR__ . '/../.env')) $issues[] = 'Missing .env file';
if (!getenv('APP_KEY')) $issues[] = 'APP_KEY not set';
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) $issues[] = 'Missing vendor folder';
if (!empty($permissionIssues)) $issues[] = 'Permission issues';

if (empty($issues)) {
    echo "✓✓✓ ALL CHECKS PASSED! ✓✓✓\n\n";
    echo "Your application should work!\n";
    echo "Visit: https://tarek.arvixi.net\n\n";
    echo "If still showing blank page:\n";
    echo "1. Clear browser cache\n";
    echo "2. Check storage/logs/laravel.log for errors\n";
    echo "3. Make sure APP_DEBUG=true in .htaccess\n";
} else {
    echo "✗✗✗ ISSUES FOUND ✗✗✗\n\n";
    foreach ($issues as $issue) {
        echo "- {$issue}\n";
    }
    echo "\nFix these issues and test again!\n";
}

echo "\n";
echo "</pre>";

echo "<hr>";
echo "<h2>Quick Fixes:</h2>";
echo "<ul>";
echo "<li><strong>Missing .env:</strong> Upload .env.minimal file as .env</li>";
echo "<li><strong>Missing vendor:</strong> Run 'composer install' or upload vendor folder</li>";
echo "<li><strong>APP_KEY not set:</strong> Check .htaccess has SetEnv APP_KEY line</li>";
echo "<li><strong>Permission issues:</strong> Run 'chmod -R 755 storage bootstrap/cache'</li>";
echo "<li><strong>Database empty:</strong> Import database_import.sql in phpMyAdmin</li>";
echo "</ul>";

echo "<hr>";
echo "<p style='color: red; font-weight: bold;'>⚠️ DELETE THIS FILE AFTER TESTING: public/check-system.php</p>";
?>

