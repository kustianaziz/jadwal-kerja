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
