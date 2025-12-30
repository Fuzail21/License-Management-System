<?php

/**
 * Azure App Service - Laravel Root Redirector
 *
 * This file allows Laravel to work on Azure App Service without
 * needing to configure the document root to /public
 */

// Define the public directory path
define('PUBLIC_PATH', __DIR__ . '/public');

// Get the request URI
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$request_path = parse_url($request_uri, PHP_URL_PATH);

// If requesting a static file in public directory, serve it directly
if ($request_path !== '/' && $request_path !== '/index.php') {
    $file_path = PUBLIC_PATH . $request_path;

    if (file_exists($file_path) && is_file($file_path)) {
        // Serve static files with appropriate content type
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        $mime_types = [
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'json' => 'application/json',
            'xml'  => 'application/xml',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            'ico'  => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2'=> 'font/woff2',
            'ttf'  => 'font/ttf',
            'eot'  => 'application/vnd.ms-fontobject',
            'otf'  => 'font/otf',
        ];

        if (isset($mime_types[$extension])) {
            header('Content-Type: ' . $mime_types[$extension]);
        }

        readfile($file_path);
        exit;
    }
}

// For all other requests, load Laravel's public/index.php
require_once PUBLIC_PATH . '/index.php';
