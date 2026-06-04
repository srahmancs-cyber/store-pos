<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Namecheap Shared Hosting Deployment
|--------------------------------------------------------------------------
| On Namecheap, the Laravel root lives OUTSIDE public_html for security:
|
|   ~/laravel/store-pos/          ← Laravel root
|   ~/public_html/store-pos/      ← Web-accessible (this file lives here)
|
| When deployed, update the path below to match your actual server path.
| Example: /home/yourusername/laravel/store-pos
|
| During local development, __DIR__.'/../' resolves correctly as-is.
*/

// ── Local development (default) ──────────────────────────────────────────────
$laravelRoot = __DIR__ . '/../';

// ── Production: uncomment and set your actual server path ────────────────────
// $laravelRoot = '/home/yourusername/laravel/store-pos/';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravelRoot . 'storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelRoot . 'vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once $laravelRoot . 'bootstrap/app.php')
    ->handleRequest(Request::capture());
