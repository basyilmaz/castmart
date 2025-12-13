<?php
/**
 * Laravel Development Router
 * 
 * This file is used to properly serve static files with PHP's built-in server.
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Check if request is for a static file
$publicPath = __DIR__;

// List of static file extensions
$staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'ico', 'svg', 'webp', 'woff', 'woff2', 'ttf', 'eot', 'map'];

// Get file extension
$extension = strtolower(pathinfo($uri, PATHINFO_EXTENSION));

// If this is a real file, serve it
if ($uri !== '/' && file_exists($publicPath . $uri)) {
    // Set proper content type for static files
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
    ];
    
    if (isset($mimeTypes[$extension])) {
        header('Content-Type: ' . $mimeTypes[$extension]);
        readfile($publicPath . $uri);
        return true;
    }
    
    return false;
}

// If requesting storage files, redirect to actual storage path
if (strpos($uri, '/storage/') === 0) {
    $storagePath = str_replace('/storage/', '/app/public/', $uri);
    $fullPath = dirname($publicPath) . '/storage' . $storagePath;
    
    if (file_exists($fullPath)) {
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
        ];
        
        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
        }
        
        readfile($fullPath);
        return true;
    }
}

// Route to Laravel
require_once $publicPath . '/index.php';
