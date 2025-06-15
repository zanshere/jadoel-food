<?php
// Ambil protocol: http:// atau https://
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

// Ambil host: contoh localhost atau 127.0.0.1 atau domain
$host = $_SERVER['HTTP_HOST'];

// Ambil path ke folder proyek dari DOCUMENT_ROOT (misal /git-project/jadoel-food)
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT']);
$currentDir   = realpath(__DIR__ . '/..'); // karena file ini di /config/, naik 1 level ke root project
$relativePath = str_replace('\\', '/', str_replace($documentRoot, '', $currentDir));

// Pastikan slash di depan dan di akhir
$relativePath = '/' . trim($relativePath, '/') . '/';

// Gabungkan jadi BASE_URL
define('BASE_URL', $protocol . $host . $relativePath);

?>