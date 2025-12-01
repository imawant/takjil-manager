<?php

// Load Laravel's autoloader
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// --- VERCEL CONFIGURATION (START) ---

// 1. Paksa storage path ke /tmp (karena folder lain read-only)
$app->useStoragePath('/tmp/storage');

// 2. Paksa cache path ke /tmp lewat Environment Variable PHP
// Ini memperbaiki error "bootstrap/cache directory must be present and writable"
$env_cache_path = '/tmp/packages.php';
$env_services_path = '/tmp/services.php';

putenv("APP_PACKAGES_CACHE={$env_cache_path}");
putenv("APP_SERVICES_CACHE={$env_services_path}");

$_SERVER['APP_PACKAGES_CACHE'] = $env_cache_path;
$_SERVER['APP_SERVICES_CACHE'] = $env_services_path;

// 3. Buat struktur folder storage di /tmp jika belum ada
// (Supaya tidak error saat menulis log/session)
if (!is_dir('/tmp/storage')) {
    mkdir('/tmp/storage', 0777, true);
    mkdir('/tmp/storage/framework/views', 0777, true);
    mkdir('/tmp/storage/framework/cache', 0777, true);
    mkdir('/tmp/storage/framework/sessions', 0777, true);
    mkdir('/tmp/storage/logs', 0777, true);
}

// --- VERCEL CONFIGURATION (END) ---

// Run the application
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
