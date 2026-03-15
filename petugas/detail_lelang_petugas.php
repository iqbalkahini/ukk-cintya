<?php
session_start();
require_once('../config/config.php');

// Definisi fungsi format rupiah
if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

// Definisi fungsi format tanggal (jika diperlukan)
if (!function_exists('formatTanggal')) {
    function formatTanggal($tanggal) {
        return date('d M Y', strtotime($tanggal));
    }
}

checkLevel([2]);

// Sanitasi ID lelang
$id_lelang = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_lelang <= 0) {
    header('Location: kelola_lelang.php');
    exit;
}

// Ambil detail lelang dan barang
$lelang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT l.*, b.nama_barang, b.deskripsi_barang FROM tb_lelang l JOIN tb_barang b ON l.id_barang = b.id_barang WHERE id_lelang = $id_lelang"));
if (!$lelang) {
    header('Location: kelola_lelang.php');
    exit;
}

// Ambil history penawaran
$history = mysqli_query($conn, "SELECT h.*, u.nama_lengkap FROM history_lelang h JOIN tb_user u ON h.id_user = u.id_user WHERE h.id_lelang = $id_lelang ORDER BY h.penawaran_harga DESC, h.created_at ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Detail Penawaran Lelang - Petugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... (style sama seperti sebelumnya) ... */
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap');
        * { font-family: 'Poppins', sans-serif; }
        h1, h2, h3, h4, h5, h6, .font-serif { font-family: 'Playfair Display', serif; }
        .bg-coffee { background-color: #6F4E37; }
        .bg-coffee-light { background-color: #8B6B4D; }
        .bg-coffee-dark { background-color: #4A3729; }
        .bg-biscuit { background-color: #F7E1C0; }
        .bg-biscuit-light { background-color: #FEF3E2; }
        .bg-biscuit-dark { background-color: #E5C9A8; }
        .text-coffee { color: #6F4E37; }
        .text-coffee-light { color: #8B6B4D; }
        .text-biscuit { color: #F7E1C0; }
        .border-coffee { border-color: #6F4E37; }
        .border-biscuit { border-color: #E5C9A8; }
        .hover\:bg-coffee-dark:hover { background-color: #4A3729; }
        .hover\:bg-biscuit-dark:hover { background-color: #E5C9A8; }
        .shadow-warm { box-shadow: 0 10px 25px -5px rgba(111,78,55,0.1), 0 8px 10px -6px rgba(111,78,55,0.1); }
    </style>
</head>
<body class="bg-biscuit-light">
<div class="max-w-4xl mx-auto py-8 px-4">
    <a href="kelola_lelang.php" class="text-coffee hover:text-coffee-light mb-4 inline-block transition duration-200">
        <i class="fas fa-arrow-left mr-2"></i>Kembali
    </a>
    <div class="bg-white rounded-xl shadow-warm p-8 mb-4 border border-biscuit">
        <h2 class="text-2xl font-bold text-coffee font-serif mb-2"><?php echo htmlspecialchars($lelang['nama_barang']); ?></h2>
        <p class="text-gray-500 mb-4"><?php echo htmlspecialchars($lelang['deskripsi_barang']); ?></p>
        <h3 class="text-lg font-semibold text-coffee font-serif mb-3">Riwayat Penawaran</h3>
        <div class="overflow-x-auto">
            <table class="w-full bg-white border border-biscuit rounded-lg">
                <thead class="bg-coffee">
                    <tr>
                        <th class="text-left px-4 py-3 text-biscuit text-sm font-semibold">No</th>
                        <th class="text-left px-4 py-3 text-biscuit text-sm font-semibold">Nama Penawar</th>
                        <th class="text-left px-4 py-3 text-biscuit text-sm font-semibold">Nominal Penawaran</th>
                        <th class="text-left px-4 py-3 text-biscuit text-sm font-semibold">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no=1;
                    while($h = mysqli_fetch_assoc($history)): ?>
                    <tr class="hover:bg-biscuit-light transition duration-200">
                        <td class="px-4 py-3 border-b border-biscuit text-coffee"><?php echo $no++; ?></td>
                        <td class="px-4 py-3 border-b border-biscuit text-coffee"><?php echo htmlspecialchars($h['nama_lengkap']); ?></td>
                        <td class="px-4 py-3 border-b border-biscuit text-coffee-light font-semibold"><?php echo formatRupiah($h['penawaran_harga']); ?></td>
                        <td class="px-4 py-3 border-b border-biscuit text-gray-600"><?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($no === 1): // jika tidak ada data ?>
                    <tr>
                        <td colspan="4" class="text-center px-4 py-8 text-coffee">Belum ada penawaran</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>