<?php
/**
 * Konfigurasi Aplikasi
 */

define('APP_NAME', 'GajiPro');
define('APP_VERSION', '1.0.0');
define('APP_URL', '');
define('CURRENCY', 'Rp');
define('CURRENCY_CODE', 'IDR');
define('COMPANY_NAME', 'PT. Maju Sejahtera');
define('COMPANY_ADDRESS', 'Jl. Sudirman No. 123, Jakarta Selatan');
define('COMPANY_PHONE', '(021) 555-0123');
define('COMPANY_EMAIL', 'hr@majusejahtera.co.id');

// Konfigurasi session (hanya atur sebelum session dimulai)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
}

// Zona Waktu
date_default_timezone_set('Asia/Jakarta');

// Pelaporan error (set ke 0 di produksi)
error_reporting(E_ALL);
ini_set('display_errors', 0);

/**
 * Format mata uang
 */
function formatRupiah($amount) {
    return CURRENCY . ' ' . number_format($amount, 0, ',', '.');
}

/**
 * Format tanggal ke Indonesia
 */
function formatDate($date) {
    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $d = new DateTime($date);
    return $d->format('d') . ' ' . $months[(int)$d->format('m')] . ' ' . $d->format('Y');
}

/**
 * Generate ID unik
 */
function generateId($prefix = 'KRY') {
    return $prefix . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
}

/**
 * Sanitasi input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Respon JSON
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Cek apakah pengguna sudah login
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect jika belum login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /pages/auth/login.php');
        exit;
    }
}

/**
 * Dapatkan URL dasar
 */
function baseUrl($path = '') {
    return '/' . ltrim($path, '/');
}

/**
 * URL Aset
 */
function asset($path) {
    return '/public/' . ltrim($path, '/');
}
