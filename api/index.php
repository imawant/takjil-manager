<?php

// Load Laravel's autoloader
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// --- TAMBAHAN PENTING ---
// Memaksa Laravel menggunakan folder /tmp untuk storage
// karena Vercel read-only filesystem.
$app->useStoragePath('/tmp/storage');
// ------------------------

// Run the application
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
