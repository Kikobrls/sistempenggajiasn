<?php
/**
 * GajiPro - Router Server PHP Bawaan
 * Jalankan: php -S 0.0.0.0:8080 router.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files from /public
if (preg_match('/^\/public\//', $uri)) {
    $filePath = __DIR__ . $uri;
    if (file_exists($filePath) && is_file($filePath)) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'map' => 'application/json',
        ];
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        readfile($filePath);
        return true;
    }
}

// Route to appropriate PHP file
$filePath = __DIR__ . $uri;

// Check if it's a directory, look for index.php
if (is_dir($filePath)) {
    $filePath = rtrim($filePath, '/') . '/index.php';
}

// If it's a PHP file, serve it
if (file_exists($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
    require $filePath;
    return true;
}

// Default: index.php
if ($uri === '/' || $uri === '') {
    require __DIR__ . '/index.php';
    return true;
}

// 404
http_response_code(404);
echo '<h1>404 - Page Not Found</h1><p>The page you requested was not found.</p><a href="/">Go to Dashboard</a>';
return true;
