<?php
// Vercel serverless entrypoint for Laravel
putenv('APP_CONFIG_CACHE=/tmp/nonexistent_config.php');
$_ENV['APP_CONFIG_CACHE'] = '/tmp/nonexistent_config.php';
$_SERVER['APP_CONFIG_CACHE'] = '/tmp/nonexistent_config.php';

putenv('APP_SERVICES_CACHE=/tmp/nonexistent_services.php');
$_ENV['APP_SERVICES_CACHE'] = '/tmp/nonexistent_services.php';
$_SERVER['APP_SERVICES_CACHE'] = '/tmp/nonexistent_services.php';

putenv('APP_PACKAGES_CACHE=/tmp/nonexistent_packages.php');
$_ENV['APP_PACKAGES_CACHE'] = '/tmp/nonexistent_packages.php';
$_SERVER['APP_PACKAGES_CACHE'] = '/tmp/nonexistent_packages.php';

putenv('APP_EVENTS_CACHE=/tmp/nonexistent_events.php');
$_ENV['APP_EVENTS_CACHE'] = '/tmp/nonexistent_events.php';
$_SERVER['APP_EVENTS_CACHE'] = '/tmp/nonexistent_events.php';

putenv('APP_ROUTES_CACHE=/tmp/nonexistent_routes.php');
$_ENV['APP_ROUTES_CACHE'] = '/tmp/nonexistent_routes.php';
$_SERVER['APP_ROUTES_CACHE'] = '/tmp/nonexistent_routes.php';

// FORCE Critical Environment Variables for Vercel
// This overrides any faulty settings in the Vercel Dashboard that might cause timeouts
putenv('APP_ENV=production');
putenv('APP_DEBUG=false');
putenv('SESSION_DRIVER=cookie');
putenv('SESSION_FILES_PATH=/tmp/storage/framework/sessions');
putenv('CACHE_STORE=file');
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
putenv('DB_CONNECTION=pgsql');
putenv('DB_HOST=aws-0-ap-northeast-1.pooler.supabase.com'); // IPv4 pooler
putenv('DB_PORT=6543'); // Transaction pooler port

$_ENV['APP_ENV'] = 'production';
$_ENV['APP_DEBUG'] = 'false';
$_ENV['SESSION_DRIVER'] = 'cookie';
$_ENV['CACHE_STORE'] = 'file';
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
$_ENV['DB_HOST'] = 'aws-0-ap-northeast-1.pooler.supabase.com';
$_ENV['DB_PORT'] = '6543';

$_SERVER['APP_ENV'] = 'production';
$_SERVER['APP_DEBUG'] = 'false';
$_SERVER['SESSION_DRIVER'] = 'cookie';
$_SERVER['CACHE_STORE'] = 'file';
$_SERVER['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
$_SERVER['DB_HOST'] = 'aws-0-ap-northeast-1.pooler.supabase.com';
$_SERVER['DB_PORT'] = '6543';

// Ensure writable directories exist in Vercel serverless /tmp space
foreach ([
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions'
] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

require __DIR__ . '/../public/index.php';
