<?php
// Database Configuration
define('DB_HOST', 'mysql');
define('DB_USER', 'root');
define('DB_PASS', 'root123');
define('DB_NAME', 'sistem_lelang_online_cintya');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Base URL dibuat root-relative agar aman untuk localhost maupun ngrok/proxy.
define('BASE_URL', '/ukk-cintya/');

// Untuk path file fisik di server
define('BASE_PATH', __DIR__ . '/');

// Function untuk format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Function untuk format tanggal Indonesia
function formatTanggal($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $pecah = explode('-', $tanggal);
    return $pecah[2] . ' ' . $bulan[(int)$pecah[1]] . ' ' . $pecah[0];
}

function resolveUploadFile($filename, $type = 'barang') {
    if (empty($filename)) {
        return null;
    }

    $project_root = dirname(__DIR__);
    $clean = ltrim(str_replace('\\', '/', $filename), '/');
    $candidates = [];

    if (strpos($clean, 'uploads/') === 0) {
        $candidates[] = $clean;
    }

    if (strpos($clean, 'barang/') === 0 || strpos($clean, 'bukti_bayar/') === 0) {
        $candidates[] = 'uploads/' . $clean;
    }

    if ($type === 'barang' && strpos($clean, 'uploads/') !== 0 && strpos($clean, 'barang/') !== 0) {
        $candidates[] = 'uploads/barang/' . $clean;
    }

    if (strpos($clean, 'uploads/') !== 0) {
        $candidates[] = 'uploads/' . $clean;
    }

    $candidates = array_values(array_unique($candidates));

    foreach ($candidates as $relative_path) {
        $absolute_path = $project_root . '/' . $relative_path;
        if (is_file($absolute_path)) {
            return [
                'path' => $absolute_path,
                'relative' => $relative_path,
                'url' => BASE_URL . ltrim($relative_path, '/')
            ];
        }
    }

    return null;
}

function getUploadUrl($filename, $type = 'barang') {
    $resolved = resolveUploadFile($filename, $type);
    return $resolved ? $resolved['url'] : '';
}

function deleteUploadFile($filename, $type = 'barang') {
    $resolved = resolveUploadFile($filename, $type);
    if (!$resolved) {
        return false;
    }

    return @unlink($resolved['path']);
}

// Function untuk cek login
function checkLogin() {
    if (!isset($_SESSION['id_user'])) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }
}

// Function untuk cek level akses
function checkLevel($allowed_levels = []) {
    if (!isset($_SESSION['id_level'])) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }
    
    if (!in_array($_SESSION['id_level'], $allowed_levels)) {
        header('Location: ' . BASE_URL);
        exit;
    }
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
