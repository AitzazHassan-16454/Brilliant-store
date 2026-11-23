<?php

// Ensure writable directories exist in Vercel's /tmp environment
$tmpDirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/storage/app/private',
    '/tmp/storage/app/public',
    '/tmp/storage/app/public/products',
    '/tmp/storage/app/public/categories',
    '/tmp/storage/app/public/branding',
    '/tmp/storage/app/public/avatars',
    '/tmp/storage/app/public/payment_slips',
];

foreach ($tmpDirs as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Intercept requests for /storage/... and serve from /tmp if available
$uri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($uri, PHP_URL_PATH);
if ($path && str_starts_with($path, '/storage/')) {
    $relativePath = substr($path, 9);
    $tmpFile = '/tmp/storage/app/public/'.$relativePath;
    $publicFile = __DIR__.'/../public/storage/'.$relativePath;

    $fileToServe = null;
    if (file_exists($tmpFile) && is_file($tmpFile)) {
        $fileToServe = $tmpFile;
    } elseif (file_exists($publicFile) && is_file($publicFile)) {
        $fileToServe = $publicFile;
    }

    if ($fileToServe) {
        $ext = strtolower(pathinfo($fileToServe, PATHINFO_EXTENSION));
        $mimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
        ];
        header('Content-Type: '.($mimes[$ext] ?? 'application/octet-stream'));
        header('Content-Length: '.filesize($fileToServe));
        header('Cache-Control: public, max-age=86400');
        readfile($fileToServe);
        exit;
    }
}

// Forward Vercel request to Laravel's public entrypoint
require __DIR__.'/../public/index.php';
