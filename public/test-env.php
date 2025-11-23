<?php
// TEST FILE - Check if environment variables are working
// DELETE THIS FILE AFTER TESTING!

echo "<h1>Environment Variable Test</h1>";
echo "<pre>";

echo "APP_NAME: " . getenv('APP_NAME') . "\n";
echo "APP_KEY: " . (getenv('APP_KEY') ? 'SET ✓' : 'NOT SET ✗') . "\n";
echo "APP_URL: " . getenv('APP_URL') . "\n";
echo "DB_DATABASE: " . getenv('DB_DATABASE') . "\n";
echo "DB_HOST: " . getenv('DB_HOST') . "\n";

echo "\n--- PHP Info ---\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Path: " . __FILE__ . "\n";

echo "\n--- Check Files ---\n";
echo "vendor folder exists: " . (is_dir(__DIR__ . '/../vendor') ? 'YES ✓' : 'NO ✗') . "\n";
echo "storage folder exists: " . (is_dir(__DIR__ . '/../storage') ? 'YES ✓' : 'NO ✗') . "\n";
echo "bootstrap/cache exists: " . (is_dir(__DIR__ . '/../bootstrap/cache') ? 'YES ✓' : 'NO ✗') . "\n";

echo "\n--- Check Permissions ---\n";
echo "storage writable: " . (is_writable(__DIR__ . '/../storage') ? 'YES ✓' : 'NO ✗') . "\n";
echo "bootstrap/cache writable: " . (is_writable(__DIR__ . '/../bootstrap/cache') ? 'YES ✓' : 'NO ✗') . "\n";

echo "\n--- All Environment Variables ---\n";
print_r(getenv());

echo "</pre>";

echo "<h2>Next Steps:</h2>";
echo "<ul>";
echo "<li>If APP_KEY shows 'NOT SET', mod_env is not enabled or .htaccess not working</li>";
echo "<li>If vendor folder shows 'NO', run: composer install</li>";
echo "<li>If storage shows 'NO' or not writable, fix permissions: chmod -R 755 storage</li>";
echo "<li>DELETE THIS FILE after testing: public/test-env.php</li>";
echo "</ul>";
?>

