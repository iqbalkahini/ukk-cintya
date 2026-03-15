<?php
session_start();
require_once('../config/config.php');
checkLevel([2]);

if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        return 'Rp ' . number_format((float) $angka, 0, ',', '.');
    }
}

if (!function_exists('formatTanggal')) {
    function formatTanggal($tanggal) {
        return date('d M Y', strtotime($tanggal));
    }
}

$tanggal_dari = $_GET['dari'] ?? date('Y-m-01');
$tanggal_sampai = $_GET['sampai'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_dari)) $tanggal_dari = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_sampai)) $tanggal_sampai = date('Y-m-d');

$tanggal_dari_sql = mysqli_real_escape_string($conn, $tanggal_dari);
$tanggal_sampai_sql = mysqli_real_escape_string($conn, $tanggal_sampai);

$laporan = mysqli_query(
    $conn,
    "SELECT l.*, b.nama_barang, b.harga_awal, u.nama_lengkap AS pemenang
     FROM tb_lelang l
     JOIN tb_barang b ON l.id_barang = b.id_barang
     LEFT JOIN tb_user u ON l.id_user = u.id_user
     WHERE l.tgl_lelang BETWEEN '$tanggal_dari_sql' AND '$tanggal_sampai_sql'
     ORDER BY l.tgl_lelang DESC"
);

$rows = [];
$total_lelang = 0;
$total_nilai = 0;
$lelang_selesai = 0;
while ($row = mysqli_fetch_assoc($laporan)) {
    $rows[] = $row;
    $total_lelang++;
    if ($row['status'] === 'ditutup') {
        $lelang_selesai++;
        $total_nilai += (float) $row['harga_akhir'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Lelang - Petugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        .border-biscuit { border-color: #E5C9A8; }
        .shadow-warm { box-shadow: 0 10px 25px -5px rgba(111,78,55,0.1), 0 8px 10px -6px rgba(111,78,55,0.1); }
    </style>
</head>
<body class="bg-biscuit-light">
    <nav class="bg-coffee shadow-warm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-gavel text-biscuit text-2xl mr-3"></i>
                    <span class="text-biscuit text-xl font-bold font-serif">Sistem Lelang Online</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-biscuit">
                        <i class="fas fa-user-circle mr-2"></i>
                        <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
                    </span>
                    <a href="../auth/logout.php" class="bg-biscuit hover:bg-biscuit-dark text-coffee px-4 py-2 rounded-lg transition duration-200 shadow-md">
                        <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <aside class="w-64 bg-white shadow-warm min-h-screen border-r border-biscuit">
            <div class="p-4">
                <div class="bg-biscuit rounded-lg p-4 mb-4 border border-biscuit-dark">
                    <p class="text-sm text-coffee">Beranda</p>
                    <p class="text-lg font-bold text-coffee font-serif">Petugas</p>
                </div>
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-home w-5"></i><span class="ml-3">Beranda</span>
                    </a>
                    <a href="data_barang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-box w-5"></i><span class="ml-3">Data Barang</span>
                    </a>
                    <a href="kelola_lelang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-gavel w-5"></i><span class="ml-3">Kelola Lelang</span>
                    </a>
                    <a href="pembayaran.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-money-bill w-5"></i><span class="ml-3">Pembayaran</span>
                    </a>
                    <a href="laporan.php" class="flex items-center px-4 py-3 bg-biscuit text-coffee rounded-lg shadow-sm border border-biscuit-dark">
                        <i class="fas fa-file-alt w-5"></i><span class="ml-3">Laporan</span>
                    </a>
                </nav>
            </div>
        </aside>

        <main class="flex-1 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-coffee font-serif"><i class="fas fa-chart-bar mr-2"></i>Laporan Lelang</h1>
                <p class="text-gray-600 mt-2">Filter laporan lalu export ke PDF atau Excel.</p>
            </div>

            <div class="bg-white rounded-xl shadow-warm p-6 mb-8 border border-biscuit">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-coffee font-semibold mb-2">Tanggal Dari</label>
                        <input type="date" name="dari" value="<?php echo htmlspecialchars($tanggal_dari); ?>" class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee">
                    </div>
                    <div>
                        <label class="block text-coffee font-semibold mb-2">Tanggal Sampai</label>
                        <input type="date" name="sampai" value="<?php echo htmlspecialchars($tanggal_sampai); ?>" class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-coffee hover:bg-coffee-dark text-biscuit px-6 py-2 rounded-lg transition duration-200 shadow-warm">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="window.print()" class="w-full bg-coffee-light hover:bg-coffee-dark text-biscuit px-6 py-2 rounded-lg transition duration-200 shadow-warm">
                            <i class="fas fa-print mr-2"></i>Cetak
                        </button>
                    </div>
                </form>

                <div class="flex flex-col md:flex-row gap-3 mt-4">
                    <a href="export_laporan.php?format=pdf&dari=<?php echo urlencode($tanggal_dari); ?>&sampai=<?php echo urlencode($tanggal_sampai); ?>" class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg transition duration-200 shadow-warm">
                        <i class="fas fa-file-pdf mr-2"></i>Export PDF
                    </a>
                    <a href="export_laporan.php?format=excel&dari=<?php echo urlencode($tanggal_dari); ?>&sampai=<?php echo urlencode($tanggal_sampai); ?>" class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg transition duration-200 shadow-warm">
                        <i class="fas fa-file-excel mr-2"></i>Export Excel
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-warm overflow-hidden border border-biscuit">
                <div class="p-6 border-b border-biscuit bg-biscuit">
                    <h2 class="text-xl font-bold text-coffee font-serif">Data Lelang Periode <?php echo formatTanggal($tanggal_dari); ?> s/d <?php echo formatTanggal($tanggal_sampai); ?></h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-biscuit-light">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Nama Barang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Harga Awal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Harga Akhir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Pemenang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-biscuit">
                            <?php if (count($rows) > 0): ?>
                                <?php foreach ($rows as $index => $row): ?>
                                <tr class="hover:bg-biscuit-light transition duration-200">
                                    <td class="px-6 py-4 text-coffee"><?php echo $index + 1; ?></td>
                                    <td class="px-6 py-4 text-gray-600"><?php echo formatTanggal($row['tgl_lelang']); ?></td>
                                    <td class="px-6 py-4 font-medium text-coffee"><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                                    <td class="px-6 py-4 text-gray-600"><?php echo formatRupiah($row['harga_awal']); ?></td>
                                    <td class="px-6 py-4 text-coffee-light font-semibold"><?php echo formatRupiah($row['harga_akhir']); ?></td>
                                    <td class="px-6 py-4 text-coffee"><?php echo htmlspecialchars($row['pemenang'] ?: '-'); ?></td>
                                    <td class="px-6 py-4">
                                        <?php if ($row['status'] === 'dibuka'): ?>
                                            <span class="px-3 py-1 bg-biscuit text-coffee rounded-full text-xs font-semibold border border-biscuit-dark">Dibuka</span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 bg-coffee text-biscuit rounded-full text-xs font-semibold">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">Tidak ada data laporan untuk periode ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="bg-biscuit-light">
                            <tr class="font-bold">
                                <td colspan="4" class="px-6 py-4 text-right text-coffee">Total Nilai Lelang Selesai:</td>
                                <td class="px-6 py-4 text-coffee-light"><?php echo formatRupiah($total_nilai); ?></td>
                                <td colspan="2" class="px-6 py-4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="p-6 border-t border-biscuit bg-biscuit">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-white rounded-lg shadow border border-biscuit">
                            <p class="text-coffee text-sm">Total Lelang</p>
                            <p class="text-3xl font-bold text-coffee"><?php echo $total_lelang; ?></p>
                        </div>
                        <div class="text-center p-4 bg-white rounded-lg shadow border border-biscuit">
                            <p class="text-coffee text-sm">Lelang Selesai</p>
                            <p class="text-3xl font-bold text-coffee-light"><?php echo $lelang_selesai; ?></p>
                        </div>
                        <div class="text-center p-4 bg-white rounded-lg shadow border border-biscuit">
                            <p class="text-coffee text-sm">Total Nilai Transaksi</p>
                            <p class="text-3xl font-bold text-amber-700"><?php echo formatRupiah($total_nilai); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        @media print {
            aside, nav, form, a, button { display: none !important; }
            main { padding: 20px !important; width: 100% !important; }
        }
    </style>
</body>
</html>
