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

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : 'all';

$search_sql = mysqli_real_escape_string($conn, $search);
$status_sql = mysqli_real_escape_string($conn, $status_filter);

$query = "SELECT p.*, l.harga_akhir, u.nama_lengkap, b.nama_barang
          FROM tb_pembayaran p
          LEFT JOIN tb_lelang l ON p.id_lelang = l.id_lelang
          LEFT JOIN tb_user u ON p.id_user = u.id_user
          LEFT JOIN tb_barang b ON l.id_barang = b.id_barang
          WHERE 1=1";

if ($search !== '') {
    $query .= " AND (
        u.nama_lengkap LIKE '%$search_sql%' OR
        b.nama_barang LIKE '%$search_sql%' OR
        p.metode_pembayaran LIKE '%$search_sql%' OR
        p.id_pembayaran LIKE '%$search_sql%'
    )";
}

if ($status_filter !== '' && $status_filter !== 'all') {
    $query .= " AND p.status_pembayaran = '$status_sql'";
}

$query .= " ORDER BY p.created_at DESC, p.id_pembayaran DESC";
$pembayaran = mysqli_query($conn, $query);

$stats = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status_pembayaran = 'pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status_pembayaran = 'dibayar' THEN 1 ELSE 0 END) AS dibayar,
        SUM(CASE WHEN status_pembayaran = 'selesai' THEN 1 ELSE 0 END) AS selesai,
        SUM(CASE WHEN status_pembayaran = 'selesai' THEN jumlah ELSE 0 END) AS total_nilai
     FROM tb_pembayaran"
));

$total_pembayaran = (int) ($stats['total'] ?? 0);
$pembayaran_pending = (int) ($stats['pending'] ?? 0);
$pembayaran_dibayar = (int) ($stats['dibayar'] ?? 0);
$pembayaran_selesai = (int) ($stats['selesai'] ?? 0);
$total_nilai = (float) ($stats['total_nilai'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Petugas</title>
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
                    <a href="pembayaran.php" class="flex items-center px-4 py-3 bg-biscuit text-coffee rounded-lg shadow-sm border border-biscuit-dark">
                        <i class="fas fa-money-bill w-5"></i><span class="ml-3">Pembayaran</span>
                    </a>
                    <a href="laporan.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-file-alt w-5"></i><span class="ml-3">Laporan</span>
                    </a>
                </nav>
            </div>
        </aside>

        <main class="flex-1 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-coffee font-serif"><i class="fas fa-credit-card mr-3"></i>Data Pembayaran</h1>
                <p class="text-gray-600 mt-2">Halaman ini hanya menampilkan data pembayaran yang sudah masuk.</p>
            </div>

            <div class="bg-white rounded-xl shadow-warm p-6 mb-8 border border-biscuit">
                <form method="GET" action="pembayaran.php" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-coffee-light"></i>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari ID, pembeli, barang, atau metode..." class="w-full pl-10 pr-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee">
                        </div>
                    </div>
                    <div class="w-full md:w-48">
                        <select name="status" class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="dibayar" <?php echo $status_filter === 'dibayar' ? 'selected' : ''; ?>>Dibayar</option>
                            <option value="selesai" <?php echo $status_filter === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-coffee hover:bg-coffee-dark text-biscuit px-6 py-2 rounded-lg font-semibold transition duration-200 shadow-warm">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                        <a href="pembayaran.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg font-semibold transition duration-200 shadow-warm">
                            <i class="fas fa-redo mr-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-coffee">
                    <p class="text-gray-500 text-sm">Total Pembayaran</p>
                    <p class="text-3xl font-bold text-coffee mt-2"><?php echo $total_pembayaran; ?></p>
                </div>
                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-yellow-700">
                    <p class="text-gray-500 text-sm">Pending</p>
                    <p class="text-3xl font-bold text-coffee mt-2"><?php echo $pembayaran_pending; ?></p>
                </div>
                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-coffee-light">
                    <p class="text-gray-500 text-sm">Dibayar</p>
                    <p class="text-3xl font-bold text-coffee mt-2"><?php echo $pembayaran_dibayar; ?></p>
                </div>
                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-amber-700">
                    <p class="text-gray-500 text-sm">Total Nilai Selesai</p>
                    <p class="text-2xl font-bold text-coffee mt-2"><?php echo formatRupiah($total_nilai); ?></p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-warm overflow-hidden border border-biscuit">
                <div class="p-6 border-b border-biscuit bg-biscuit">
                    <h2 class="text-xl font-bold text-coffee font-serif">Daftar Pembayaran</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-biscuit-light">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Pembeli</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Barang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Metode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-biscuit">
                            <?php if ($pembayaran && mysqli_num_rows($pembayaran) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($pembayaran)): ?>
                                <tr class="hover:bg-biscuit-light transition duration-200">
                                    <td class="px-6 py-4 font-medium text-coffee">#<?php echo $row['id_pembayaran']; ?></td>
                                    <td class="px-6 py-4 text-coffee"><?php echo htmlspecialchars($row['nama_lengkap'] ?? '-'); ?></td>
                                    <td class="px-6 py-4 text-coffee"><?php echo htmlspecialchars($row['nama_barang'] ?? '-'); ?></td>
                                    <td class="px-6 py-4 font-semibold text-coffee-light"><?php echo formatRupiah($row['jumlah']); ?></td>
                                    <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($row['metode_pembayaran'] ?? '-'); ?></td>
                                    <td class="px-6 py-4 text-gray-600"><?php echo !empty($row['created_at']) ? date('d/m/Y', strtotime($row['created_at'])) : '-'; ?></td>
                                    <td class="px-6 py-4">
                                        <?php if ($row['status_pembayaran'] === 'pending'): ?>
                                            <span class="px-3 py-1 bg-yellow-50 text-yellow-700 rounded-full text-xs font-semibold border border-yellow-200">Pending</span>
                                        <?php elseif ($row['status_pembayaran'] === 'dibayar'): ?>
                                            <span class="px-3 py-1 bg-biscuit text-coffee rounded-full text-xs font-semibold border border-biscuit-dark">Dibayar</span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold border border-green-200">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">Tidak ada data pembayaran untuk filter ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
