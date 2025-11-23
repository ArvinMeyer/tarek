<?php
// Quick PHP diagnostic
// DELETE THIS FILE AFTER TESTING!

echo "<h1>PHP is Working!</h1>";
echo "<p>If you see this, PHP is running correctly.</p>";

echo "<h2>Quick Tests:</h2>";
echo "<pre>";

echo "PHP Version: " . phpversion() . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n\n";

// Check vendor
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "✓ vendor/autoload.php EXISTS\n";
    require __DIR__ . '/../vendor/autoload.php';
    echo "✓ vendor/autoload.php LOADED\n";
} else {
    echo "✗ vendor/autoload.php MISSING!\n";
    echo "  RUN: composer install\n";
}

// Check Laravel
if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
    echo "✓ bootstrap/app.php EXISTS\n";
} else {
    echo "✗ bootstrap/app.php MISSING!\n";
}

// Check storage
echo "\nStorage writable: " . (is_writable(__DIR__ . '/../storage') ? "✓ YES" : "✗ NO") . "\n";

// Check environment variables
echo "\nEnvironment Variables:\n";
echo "APP_KEY: " . (getenv('APP_KEY') ? '✓ SET' : '✗ NOT SET') . "\n";
echo "DB_HOST: " . (getenv('DB_HOST') ? getenv('DB_HOST') : '✗ NOT SET') . "\n";

echo "</pre>";

echo "<hr>";
echo "<h2>Full PHP Info:</h2>";
phpinfo();

echo "<hr>";
echo "<p style='color: red; font-weight: bold;'>⚠️ DELETE THIS FILE AFTER TESTING: public/phpinfo.php</p>";
?>

