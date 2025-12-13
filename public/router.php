<?php
/**
 * Laravel Development Router
 * 
 * This file is used to properly serve static files with PHP's built-in server.
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

$publicPath = __DIR__;

// MIME types for static files
$mimeTypes = [
    'css' => 'text/css',
    'js' => 'application/javascript',
    'json' => 'application/json',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'svg' => 'image/svg+xml',
    'webp' => 'image/webp',
    'ico' => 'image/x-icon',
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf' => 'font/ttf',
    'eot' => 'application/vnd.ms-fontobject',
    'map' => 'application/json',
    'pdf' => 'application/pdf',
    'mp4' => 'video/mp4',
    'mp3' => 'audio/mpeg',
];

// Get file extension
$extension = strtolower(pathinfo($uri, PATHINFO_EXTENSION));

// Function to serve a file
function serveFile($path, $mimeType = null) {
    if (!file_exists($path)) {
        return false;
    }
    
    if ($mimeType) {
        header('Content-Type: ' . $mimeType);
    }
    
    header('Content-Length: ' . filesize($path));
    header('Cache-Control: public, max-age=31536000');
    
    readfile($path);
    return true;
}

// Try to serve from public directory directly
$requestedFile = $publicPath . $uri;
if ($uri !== '/' && file_exists($requestedFile) && is_file($requestedFile)) {
    $mimeType = $mimeTypes[$extension] ?? null;
    if (serveFile($requestedFile, $mimeType)) {
        return true;
    }
    // Let PHP built-in server handle it
    return false;
}

// Handle /storage/ paths - symlink should exist, but fallback to direct path
if (strpos($uri, '/storage/') === 0) {
    // First try the symlink path (public/storage/...)
    $symlinkPath = $publicPath . $uri;
    if (file_exists($symlinkPath) && is_file($symlinkPath)) {
        $mimeType = $mimeTypes[$extension] ?? null;
        if (serveFile($symlinkPath, $mimeType)) {
            return true;
        }
    }
    
    // Fallback: try direct storage path
    $relativePath = substr($uri, 9); // Remove '/storage/'
    $storagePath = dirname($publicPath) . '/storage/app/public/' . $relativePath;
    if (file_exists($storagePath) && is_file($storagePath)) {
        $mimeType = $mimeTypes[$extension] ?? null;
        if (serveFile($storagePath, $mimeType)) {
            return true;
        }
    }
}

// Not a static file, route to Laravel
require_once $publicPath . '/index.php';

