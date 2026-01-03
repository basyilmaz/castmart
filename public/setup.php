<?php
/**
 * CastMart Setup Script
 * Run this once after deployment to setup the application
 * DELETE THIS FILE AFTER USE!
 */

// Security check - only allow from specific IPs or with secret key
$secretKey = 'castmart_setup_2026';
if (!isset($_GET['key']) || $_GET['key'] !== $secretKey) {
    die('Access denied. Use ?key=' . $secretKey);
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<html><head><title>CastMart Setup</title></head><body>";
echo "<h1>CastMart Setup</h1>";
echo "<pre style='background:#000;color:#0f0;padding:20px;'>";

try {
    // 1. Run migrations
    echo "📦 Running migrations...\n";
    $kernel->call('migrate', ['--force' => true]);
    echo Artisan::output();
    echo "✅ Migrations completed!\n\n";
    
    // 2. Create storage link
    echo "🔗 Creating storage link...\n";
    $kernel->call('storage:link', ['--force' => true]);
    echo Artisan::output();
    echo "✅ Storage link created!\n\n";
    
    // 3. Clear and cache config
    echo "🧹 Clearing caches...\n";
    $kernel->call('config:clear');
    $kernel->call('cache:clear');
    $kernel->call('view:clear');
    echo "✅ Caches cleared!\n\n";
    
    // 4. Create config cache
    echo "⚡ Creating config cache...\n";
    $kernel->call('config:cache');
    echo "✅ Config cached!\n\n";
    
    echo "\n========================================\n";
    echo "🎉 SETUP COMPLETED SUCCESSFULLY!\n";
    echo "========================================\n";
    echo "\n⚠️  DELETE THIS FILE NOW: public/setup.php\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
}

echo "</pre>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Delete this file (public/setup.php)</li>";
echo "<li>Visit <a href='/'>Homepage</a></li>";
echo "<li>Visit <a href='/admin'>Admin Panel</a></li>";
echo "</ol>";
echo "</body></html>";
