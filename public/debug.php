<?php
// SUPER SIMPLE DEBUG - No Laravel needed
// DELETE THIS FILE AFTER FIXING!

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug - PrintItMat</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .pass { color: green; font-weight: bold; }
        .fail { color: red; font-weight: bold; }
        .warn { color: orange; font-weight: bold; }
        h1 { color: #333; }
        pre { background: #f9f9f9; padding: 10px; overflow-x: auto; }
        .cmd { background: #000; color: #0f0; padding: 10px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>🔍 PrintItMat Debug Tool</h1>
    
    <?php
    $issues = [];
    $warnings = [];
    
    echo '<div class="box">';
    echo '<h2>1. PHP Information</h2>';
    echo '<pre>';
    echo "PHP Version: " . phpversion() . "\n";
    echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
    echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
    echo "Script Path: " . __FILE__ . "\n";
    echo '</pre>';
    echo '</div>';
    
    echo '<div class="box">';
    echo '<h2>2. Environment Variables (.htaccess)</h2>';
    echo '<pre>';
    
    $envVars = [
        'APP_KEY' => 'Critical',
        'APP_URL' => 'Important',
        'DB_HOST' => 'Critical',
        'DB_DATABASE' => 'Critical',
        'DB_USERNAME' => 'Critical',
        'DB_PASSWORD' => 'Critical',
    ];
    
    foreach ($envVars as $var => $level) {
        $value = getenv($var);
        if ($value) {
            echo "<span class='pass'>✓</span> {$var}: ";
            if ($var === 'DB_PASSWORD' || $var === 'APP_KEY') {
                echo "SET (hidden)\n";
            } else {
                echo "{$value}\n";
            }
        } else {
            echo "<span class='fail'>✗</span> {$var}: NOT SET\n";
            $issues[] = "{$var} not set in .htaccess";
        }
    }
    
    echo '</pre>';
    echo '</div>';
    
    echo '<div class="box">';
    echo '<h2>3. File System Checks</h2>';
    echo '<pre>';
    
    $baseDir = dirname(__DIR__);
    
    // Check .env
    $envFile = $baseDir . '/.env';
    if (file_exists($envFile)) {
        $size = filesize($envFile);
        if ($size < 200) {
            echo "<span class='pass'>✓</span> .env exists ({$size} bytes - minimal, good!)\n";
        } else {
            echo "<span class='warn'>⚠</span> .env exists ({$size} bytes - might override .htaccess)\n";
            $warnings[] = ".env file is large, might override .htaccess values";
        }
    } else {
        echo "<span class='fail'>✗</span> .env file MISSING\n";
        $issues[] = ".env file missing (Laravel needs it even if empty)";
    }
    
    // Check vendor
    $vendorFile = $baseDir . '/vendor/autoload.php';
    if (file_exists($vendorFile)) {
        echo "<span class='pass'>✓</span> vendor/autoload.php exists\n";
    } else {
        echo "<span class='fail'>✗</span> vendor/autoload.php MISSING\n";
        $issues[] = "vendor folder missing - CRITICAL!";
    }
    
    // Check bootstrap
    $bootstrapFile = $baseDir . '/bootstrap/app.php';
    if (file_exists($bootstrapFile)) {
        echo "<span class='pass'>✓</span> bootstrap/app.php exists\n";
    } else {
        echo "<span class='fail'>✗</span> bootstrap/app.php MISSING\n";
        $issues[] = "bootstrap/app.php missing";
    }
    
    // Check storage
    $storageDirs = [
        'storage' => $baseDir . '/storage',
        'storage/framework' => $baseDir . '/storage/framework',
        'storage/framework/sessions' => $baseDir . '/storage/framework/sessions',
        'storage/framework/views' => $baseDir . '/storage/framework/views',
        'storage/framework/cache' => $baseDir . '/storage/framework/cache',
        'storage/logs' => $baseDir . '/storage/logs',
        'bootstrap/cache' => $baseDir . '/bootstrap/cache',
    ];
    
    foreach ($storageDirs as $name => $path) {
        if (!file_exists($path)) {
            echo "<span class='fail'>✗</span> {$name}: Doesn't exist\n";
            $issues[] = "{$name} directory missing";
        } elseif (!is_writable($path)) {
            echo "<span class='fail'>✗</span> {$name}: Not writable\n";
            $issues[] = "{$name} not writable (permissions issue)";
        } else {
            echo "<span class='pass'>✓</span> {$name}: OK\n";
        }
    }
    
    echo '</pre>';
    echo '</div>';
    
    // Database test
    if (getenv('DB_HOST') && getenv('DB_DATABASE')) {
        echo '<div class="box">';
        echo '<h2>4. Database Connection Test</h2>';
        echo '<pre>';
        
        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%s;dbname=%s',
                    getenv('DB_HOST'),
                    getenv('DB_PORT') ?: '3306',
                    getenv('DB_DATABASE')
                ),
                getenv('DB_USERNAME'),
                getenv('DB_PASSWORD')
            );
            echo "<span class='pass'>✓</span> Database connection successful\n";
            
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($tables) > 0) {
                echo "<span class='pass'>✓</span> Found " . count($tables) . " tables\n";
                
                $required = ['users', 'customers', 'quotations', 'invoices', 'settings'];
                $missing = array_diff($required, $tables);
                
                if (empty($missing)) {
                    echo "<span class='pass'>✓</span> All required tables exist\n";
                } else {
                    echo "<span class='fail'>✗</span> Missing tables: " . implode(', ', $missing) . "\n";
                    $issues[] = "Database missing tables - import database_import.sql";
                }
            } else {
                echo "<span class='fail'>✗</span> Database is empty (no tables)\n";
                $issues[] = "Database empty - import database_import.sql";
            }
        } catch (PDOException $e) {
            echo "<span class='fail'>✗</span> Database connection failed\n";
            echo "Error: " . $e->getMessage() . "\n";
            $issues[] = "Database connection failed - check credentials";
        }
        
        echo '</pre>';
        echo '</div>';
    }
    
    // Summary
    echo '<div class="box">';
    echo '<h2>📊 Summary</h2>';
    
    if (empty($issues)) {
        echo '<h3 class="pass">✓✓✓ ALL CHECKS PASSED! ✓✓✓</h3>';
        echo '<p>Your application should work now!</p>';
        echo '<p><a href="/" style="font-size: 18px; color: blue;">→ Go to Homepage</a></p>';
        
        if (!empty($warnings)) {
            echo '<h4 class="warn">⚠ Warnings:</h4>';
            echo '<ul>';
            foreach ($warnings as $warning) {
                echo '<li>' . htmlspecialchars($warning) . '</li>';
            }
            echo '</ul>';
        }
        
        echo '<hr>';
        echo '<p style="color: red;"><strong>⚠️ DELETE THIS FILE NOW for security!</strong></p>';
        echo '<p>File to delete: <code>public/debug.php</code></p>';
    } else {
        echo '<h3 class="fail">✗✗✗ ISSUES FOUND ✗✗✗</h3>';
        echo '<ul>';
        foreach ($issues as $issue) {
            echo '<li>' . htmlspecialchars($issue) . '</li>';
        }
        echo '</ul>';
        
        // Show fixes
        echo '<h3>🔧 How to Fix:</h3>';
        
        if (in_array('.env file missing (Laravel needs it even if empty)', $issues)) {
            echo '<div class="box">';
            echo '<h4>Fix 1: Create .env file</h4>';
            echo '<p>Create file: <code>public_html/tarek/.env</code></p>';
            echo '<p>Content:</p>';
            echo '<pre>APP_NAME=
APP_ENV=
APP_KEY=
APP_DEBUG=
APP_URL=
DB_CONNECTION=
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
SESSION_DRIVER=
QUEUE_CONNECTION=
CACHE_DRIVER=</pre>';
            echo '<p>Use file: <code>env_template_for_htaccess.txt</code></p>';
            echo '</div>';
        }
        
        if (in_array('vendor folder missing - CRITICAL!', $issues)) {
            echo '<div class="box">';
            echo '<h4>Fix 2: Install Composer Dependencies (CRITICAL!)</h4>';
            echo '<p><strong>Via Terminal:</strong></p>';
            echo '<div class="cmd">cd ~/public_html/tarek<br>composer install --no-dev --optimize-autoloader</div>';
            echo '<p><strong>Or without Terminal:</strong></p>';
            echo '<ol>';
            echo '<li>Install Composer on your local computer</li>';
            echo '<li>Run <code>composer install --no-dev</code> locally</li>';
            echo '<li>Upload the entire <code>vendor</code> folder via FTP to <code>public_html/tarek/vendor/</code></li>';
            echo '</ol>';
            echo '</div>';
        }
        
        foreach ($issues as $issue) {
            if (strpos($issue, 'not writable') !== false || strpos($issue, 'directory missing') !== false) {
                echo '<div class="box">';
                echo '<h4>Fix 3: Create Directories & Fix Permissions</h4>';
                echo '<p><strong>Via Terminal:</strong></p>';
                echo '<div class="cmd">cd ~/public_html/tarek<br>';
                echo 'mkdir -p storage/framework/sessions<br>';
                echo 'mkdir -p storage/framework/views<br>';
                echo 'mkdir -p storage/framework/cache/data<br>';
                echo 'mkdir -p storage/logs<br>';
                echo 'mkdir -p bootstrap/cache<br>';
                echo 'chmod -R 755 storage bootstrap/cache</div>';
                echo '<p><strong>Or via File Manager:</strong></p>';
                echo '<ol>';
                echo '<li>Create missing directories</li>';
                echo '<li>Right-click folders → Permissions → 755 (recursive)</li>';
                echo '</ol>';
                echo '</div>';
                break;
            }
        }
        
        foreach ($issues as $issue) {
            if (strpos($issue, 'Database') !== false) {
                echo '<div class="box">';
                echo '<h4>Fix 4: Import Database</h4>';
                echo '<ol>';
                echo '<li>Go to phpMyAdmin</li>';
                echo '<li>Select database: <code>u624844894_tarek</code></li>';
                echo '<li>Click "Import" tab</li>';
                echo '<li>Choose file: <code>database_import.sql</code></li>';
                echo '<li>Click "Go"</li>';
                echo '</ol>';
                echo '</div>';
                break;
            }
        }
    }
    
    echo '</div>';
    ?>
    
    <div class="box">
        <h3>📝 Quick Reference</h3>
        <ul>
            <li>Project Path: <code><?php echo $baseDir; ?></code></li>
            <li>This File: <code><?php echo __FILE__; ?></code></li>
            <li>Visit: <a href="https://tarek.arvixi.net">https://tarek.arvixi.net</a></li>
        </ul>
    </div>
    
</body>
</html>

