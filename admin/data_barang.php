<?php
session_start();
require_once('../config/config.php');

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

checkLevel([1]);

if (isset($_GET['delete'])) {
    $id_barang = (int) $_GET['delete'];

    $check = mysqli_query($conn, "SELECT 1 FROM tb_lelang WHERE id_barang = $id_barang LIMIT 1");
    if ($check && mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "Tidak dapat menghapus barang karena sudah digunakan di lelang.";
        header('Location: data_barang.php');
        exit;
    }

    $gambar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT gambar FROM tb_barang WHERE id_barang = $id_barang"));
    if (!empty($gambar['gambar']) && file_exists('../uploads/barang/' . $gambar['gambar'])) {
        unlink('../uploads/barang/' . $gambar['gambar']);
    }

    if (mysqli_query($conn, "DELETE FROM tb_barang WHERE id_barang = $id_barang")) {
        $_SESSION['success'] = "Barang berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus barang: " . mysqli_error($conn);
    }

    header('Location: data_barang.php');
    exit;
}

$barang = mysqli_query(
    $conn,
    "SELECT b.*,
            (SELECT COUNT(*) FROM tb_lelang WHERE id_barang = b.id_barang) AS jumlah_lelang,
            (SELECT COUNT(*) FROM tb_lelang WHERE id_barang = b.id_barang AND status = 'dibuka') AS lelang_aktif
     FROM tb_barang b
     ORDER BY b.id_barang DESC"
);

$total_barang = mysqli_num_rows($barang);
mysqli_data_seek($barang, 0);

$barang_dibuka = 0;
$barang_ditutup = 0;
$barang_pending = 0;

while ($temp = mysqli_fetch_assoc($barang)) {
    if ($temp['status_barang'] === 'dibuka') {
        $barang_dibuka++;
    } elseif ($temp['status_barang'] === 'ditutup') {
        $barang_ditutup++;
    } else {
        $barang_pending++;
    }
}

mysqli_data_seek($barang, 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang - Admin</title>
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
        .shadow-warm { box-shadow: 0 10px 25px -5px rgba(111, 78, 55, 0.1), 0 8px 10px -6px rgba(111, 78, 55, 0.1); }
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
                    <a href="../auth/logout.php" class="bg-biscuit text-coffee px-4 py-2 rounded-lg transition duration-200 shadow-md hover:bg-biscuit-dark">
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
                    <p class="text-lg font-bold text-coffee font-serif">Administrator</p>
                </div>

                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-home w-5"></i>
                        <span class="ml-3">Beranda</span>
                    </a>
                    <a href="data_barang.php" class="flex items-center px-4 py-3 bg-biscuit text-coffee rounded-lg shadow-sm border border-biscuit-dark">
                        <i class="fas fa-database w-5"></i>
                        <span class="ml-3">Data Barang</span>
                    </a>
                    <a href="laporan.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-file-alt w-5"></i>
                        <span class="ml-3">Laporan</span>
                    </a>
                </nav>
            </div>
        </aside>

        <main class="flex-1 p-8">
            <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-700 mr-2"></i>
                    <span><?php echo htmlspecialchars($_SESSION['success']); ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-700"><i class="fas fa-times"></i></button>
            </div>
            <?php unset($_SESSION['success']); endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-700 mr-2"></i>
                    <span><?php echo htmlspecialchars($_SESSION['error']); ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-700"><i class="fas fa-times"></i></button>
            </div>
            <?php unset($_SESSION['error']); endif; ?>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-coffee font-serif">Data Barang</h1>
                    <p class="text-gray-600 mt-2">Kelola semua data barang dalam sistem.</p>
                </div>
                <a href="barang_form.php" class="inline-flex items-center bg-coffee hover:bg-coffee-dark text-biscuit px-6 py-3 rounded-lg font-semibold shadow-warm transition duration-200">
                    <i class="fas fa-plus-circle mr-2"></i>Tambah Barang
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-coffee">
                    <p class="text-gray-500 text-sm">Total Barang</p>
                    <p class="text-3xl font-bold text-coffee mt-2"><?php echo $total_barang; ?></p>
                    <p class="text-xs text-gray-500 mt-1">Semua Barang</p>
                </div>
                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-coffee-light">
                    <p class="text-gray-500 text-sm">Barang Dibuka</p>
                    <p class="text-3xl font-bold text-coffee mt-2"><?php echo $barang_dibuka; ?></p>
                    <p class="text-xs text-gray-500 mt-1">Sedang Lelang</p>
                </div>
                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-amber-700">
                    <p class="text-gray-500 text-sm">Barang Ditutup</p>
                    <p class="text-3xl font-bold text-coffee mt-2"><?php echo $barang_ditutup; ?></p>
                    <p class="text-xs text-gray-500 mt-1">Sudah Selesai</p>
                </div>
                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-yellow-700">
                    <p class="text-gray-500 text-sm">Barang Pending</p>
                    <p class="text-3xl font-bold text-coffee mt-2"><?php echo $barang_pending; ?></p>
                    <p class="text-xs text-gray-500 mt-1">Belum Dilelang</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-warm p-6 mb-6 border border-biscuit">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1 relative">
                        <i class="fas fa-search absolute left-3 top-3 text-coffee-light"></i>
                        <input type="text" id="searchInput" placeholder="Cari nama barang..." class="w-full pl-10 pr-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white">
                    </div>
                    <select id="filterStatus" class="px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="dibuka">Dibuka</option>
                        <option value="ditutup">Ditutup</option>
                    </select>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-warm overflow-hidden border border-biscuit">
                <div class="p-6 border-b border-biscuit bg-biscuit flex justify-between items-center">
                    <h2 class="text-xl font-bold text-coffee font-serif">Daftar Barang</h2>
                    <span class="bg-coffee text-biscuit px-4 py-2 rounded-lg text-sm font-semibold">
                        <i class="fas fa-box mr-2"></i>Total: <?php echo $total_barang; ?> Barang
                    </span>
                </div>

                <?php if ($total_barang > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-biscuit-light">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Gambar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Nama Barang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Harga Awal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Total Lelang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Lelang Aktif</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-biscuit" id="tableBody">
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($barang)): ?>
                            <tr class="hover:bg-biscuit-light barang-row transition duration-200" data-status="<?php echo htmlspecialchars($row['status_barang']); ?>">
                                <td class="px-6 py-4 text-coffee"><?php echo $no++; ?></td>
                                <td class="px-6 py-4">
                                    <div class="w-16 h-16 bg-biscuit rounded-lg flex items-center justify-center overflow-hidden border border-biscuit">
                                        <?php if (!empty($row['gambar']) && file_exists('../uploads/barang/' . $row['gambar'])): ?>
                                            <img src="../uploads/barang/<?php echo htmlspecialchars($row['gambar']); ?>" alt="<?php echo htmlspecialchars($row['nama_barang']); ?>" class="w-full h-full object-cover">
                                        <?php elseif (!empty($row['gambar']) && file_exists('../uploads/' . $row['gambar'])): ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($row['gambar']); ?>" alt="<?php echo htmlspecialchars($row['nama_barang']); ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <i class="fas fa-image text-coffee text-2xl"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 barang-name">
                                    <div class="font-medium text-coffee"><?php echo htmlspecialchars($row['nama_barang']); ?></div>
                                    <div class="text-xs text-gray-500 mt-1">ID: #<?php echo $row['id_barang']; ?></div>
                                </td>
                                <td class="px-6 py-4 text-gray-600"><?php echo formatTanggal($row['tgl']); ?></td>
                                <td class="px-6 py-4 text-coffee-light font-semibold"><?php echo formatRupiah($row['harga_awal']); ?></td>
                                <td class="px-6 py-4">
                                    <?php if ($row['status_barang'] === 'dibuka'): ?>
                                        <span class="px-3 py-1 bg-biscuit text-coffee rounded-full text-xs font-semibold border border-biscuit-dark">Dibuka</span>
                                    <?php elseif ($row['status_barang'] === 'ditutup'): ?>
                                        <span class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-semibold">Ditutup</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center text-coffee"><?php echo $row['jumlah_lelang']; ?></td>
                                <td class="px-6 py-4 text-center text-coffee-light"><?php echo $row['lelang_aktif']; ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-3">
                                        <a href="barang_form.php?id=<?php echo $row['id_barang']; ?>" class="text-coffee hover:text-coffee-light transition duration-200" title="Edit">
                                            <i class="fas fa-edit text-lg"></i>
                                        </a>
                                        <a href="data_barang.php?delete=<?php echo $row['id_barang']; ?>" onclick="return confirm('Yakin ingin menghapus barang <?php echo htmlspecialchars(addslashes($row['nama_barang'])); ?>?')" class="text-red-600 hover:text-red-800 transition duration-200" title="Hapus">
                                            <i class="fas fa-trash text-lg"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="p-12 text-center">
                    <i class="fas fa-inbox text-yellow-700 text-6xl mb-4"></i>
                    <h3 class="text-xl font-bold text-coffee font-serif mb-2">Belum Ada Barang</h3>
                    <p class="text-gray-500 mb-6">Mulai tambahkan barang untuk dilelang.</p>
                    <a href="barang_form.php" class="inline-flex items-center bg-coffee hover:bg-coffee-dark text-biscuit px-6 py-3 rounded-lg font-semibold shadow-warm">
                        <i class="fas fa-plus mr-2"></i>Tambah Barang
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('searchInput').addEventListener('keyup', filterTable);
        document.getElementById('filterStatus').addEventListener('change', filterTable);

        function filterTable() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const statusValue = document.getElementById('filterStatus').value;
            const rows = document.querySelectorAll('.barang-row');

            rows.forEach((row) => {
                const barangName = row.querySelector('.barang-name').textContent.toLowerCase();
                const status = row.getAttribute('data-status');
                const matchSearch = barangName.includes(searchValue);
                const matchStatus = statusValue === '' || status === statusValue;
                row.style.display = matchSearch && matchStatus ? '' : 'none';
            });
        }
    </script>
</body>
</html>
