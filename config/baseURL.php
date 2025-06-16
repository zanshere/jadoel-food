<?php
// Deteksi protokol: http atau https
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

// Ambil nama host, contoh: localhost, vercel.app, dll
$host = $_SERVER['HTTP_HOST'];

// Tentukan BASE_URL, tanpa perlu menebak folder
define('BASE_URL', $protocol . $host . '/');
?>
