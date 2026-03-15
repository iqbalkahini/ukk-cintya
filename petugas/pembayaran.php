<?php
session_start();
require_once('../config/config.php');

// Definisi fungsi format rupiah
if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

// Definisi fungsi format tanggal
if (!function_exists('formatTanggal')) {
    function formatTanggal($tanggal) {
        return date('d M Y', strtotime($tanggal));
    }
}

checkLevel([1, 2]); // Admin dan Petugas

$is_admin = $_SESSION['id_level'] == 1;

// Handle update status
if(isset($_POST['update_status'])) {
    $id_pembayaran = intval($_POST['id_pembayaran']);
    $status = mysqli_real_escape_string($conn, $_POST['status_pembayaran']);
    
    mysqli_query($conn, "UPDATE tb_pembayaran SET status_pembayaran = '$status' WHERE id_pembayaran = $id_pembayaran");
    header('Location: pembayaran.php');
    exit;
}

// Handle delete pembayaran
if(isset($_GET['delete'])) {
    $id_pembayaran = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM tb_pembayaran WHERE id_pembayaran = $id_pembayaran");
    header('Location: pembayaran.php');
    exit;
}

// Get search parameters with sanitasi
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Build query with filters
$query = "SELECT p.*, l.harga_akhir, u.nama_lengkap, b.nama_barang
          FROM tb_pembayaran p
          JOIN tb_lelang l ON p.id_lelang = l.id_lelang
          JOIN tb_user u ON p.id_user = u.id_user
          JOIN tb_barang b ON l.id_barang = b.id_barang
          WHERE 1=1";

if(!empty($search)) {
    $query .= " AND (u.nama_lengkap LIKE '%$search%' OR b.nama_barang LIKE '%$search%' OR p.metode_pembayaran LIKE '%$search%')";
}

if(!empty($status_filter) && $status_filter != 'all') {
    $query .= " AND p.status_pembayaran = '$status_filter'";
}

$query .= " ORDER BY p.created_at DESC";

$pembayaran = mysqli_query($conn, $query);

// Hitung statistik untuk cards (menggunakan query terpisah agar tidak mengganggu result set)
$stats_query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status_pembayaran = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status_pembayaran = 'dibayar' THEN 1 ELSE 0 END) as dibayar,
                    SUM(CASE WHEN status_pembayaran = 'selesai' THEN jumlah ELSE 0 END) as total_nilai,
                    SUM(CASE WHEN status_pembayaran = 'selesai' THEN 1 ELSE 0 END) as selesai
                FROM tb_pembayaran";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));
$total_pembayaran = $stats['total'];
$pembayaran_pending = $stats['pending'];
$pembayaran_dibayar = $stats['dibayar'];
$pembayaran_selesai = $stats['selesai'];
$total_nilai = $stats['total_nilai'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Admin/Petugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... (style sama) ... */
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
    <script>
        function confirmDelete(id, nama) {
            if(confirm(`Apakah Anda yakin ingin menghapus pembayaran #${id} untuk ${nama}?`)) {
                window.location.href = `pembayaran.php?delete=${id}`;
            }
        }
    </script>
</head>
<body class="bg-biscuit-light">
    <!-- Navigation -->
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
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-warm min-h-screen border-r border-biscuit">
            <div class="p-4">
                <div class="bg-biscuit rounded-lg p-4 mb-4 border border-biscuit-dark">
                    <p class="text-sm text-coffee">Beranda</p>
                    <p class="text-lg font-bold text-coffee font-serif"><?php echo $is_admin ? 'Administrator' : 'Petugas'; ?></p>
                </div>
                
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-home w-5"></i><span class="ml-3">Beranda</span>
                    </a>
                    <?php if($is_admin): ?>
                    <a href="total_barang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-box w-5"></i><span class="ml-3">Total Barang</span>
                    </a>
                    <a href="data_barang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-database w-5"></i><span class="ml-3">Data Barang</span>
                    </a>
                    <a href="total_lelang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-gavel w-5"></i><span class="ml-3">Total Lelang</span>
                    </a>
                    <?php else: ?>
                    <a href="data_barang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-box w-5"></i><span class="ml-3">Data Barang</span>
                    </a>
                    <a href="kelola_lelang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-gavel w-5"></i><span class="ml-3">Kelola Lelang</span>
                    </a>
                    <?php endif; ?>
                    <a href="pembayaran.php" class="flex items-center px-4 py-3 bg-biscuit text-coffee rounded-lg shadow-sm border border-biscuit-dark">
                        <i class="fas fa-money-bill w-5"></i><span class="ml-3">Pembayaran</span>
                    </a>
                    <a href="laporan.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-file-alt w-5"></i><span class="ml-3">Laporan</span>
                    </a>
                    <?php if($is_admin): ?>
                    <a href="data_user.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-users w-5"></i><span class="ml-3">Data User</span>
                    </a>
                    <?php endif; ?>
                </nav>
            </div>
        </aside>

        <main class="flex-1 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-coffee font-serif">
                    <i class="fas fa-credit-card mr-3"></i>Manajemen Pembayaran
                </h1>
                <p class="text-gray-600 mt-2">Kelola dan konfirmasi pembayaran lelang</p>
            </div>

            <!-- Search and Filter Section -->
            <div class="bg-white rounded-xl shadow-warm p-6 mb-8 border border-biscuit">
                <form method="GET" action="pembayaran.php" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-coffee-light"></i>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Cari pembeli, barang, atau metode..." 
                                   class="w-full pl-10 pr-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee">
                        </div>
                    </div>
                    <div class="w-full md:w-48">
                        <select name="status" class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee">
                            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="dibayar" <?php echo $status_filter == 'dibayar' ? 'selected' : ''; ?>>Dibayar</option>
                            <option value="selesai" <?php echo $status_filter == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
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

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-coffee hover:shadow-lg transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Pembayaran</p>
                            <p class="text-3xl font-bold text-coffee mt-2"><?php echo $total_pembayaran; ?></p>
                        </div>
                        <div class="bg-biscuit p-4 rounded-full">
                            <i class="fas fa-file-invoice text-coffee text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-yellow-700 hover:shadow-lg transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Pending</p>
                            <p class="text-3xl font-bold text-coffee mt-2"><?php echo $pembayaran_pending; ?></p>
                        </div>
                        <div class="bg-biscuit p-4 rounded-full">
                            <i class="fas fa-clock text-yellow-700 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-coffee-light hover:shadow-lg transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Selesai</p>
                            <p class="text-3xl font-bold text-coffee mt-2"><?php echo $pembayaran_selesai; ?></p>
                        </div>
                        <div class="bg-biscuit p-4 rounded-full">
                            <i class="fas fa-check-circle text-coffee-light text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-amber-700 hover:shadow-lg transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Nilai</p>
                            <p class="text-2xl font-bold text-coffee mt-2"><?php echo formatRupiah($total_nilai); ?></p>
                        </div>
                        <div class="bg-biscuit p-4 rounded-full">
                            <i class="fas fa-money-bill-wave text-amber-700 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Pembayaran -->
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-biscuit">
                            <?php while($row = mysqli_fetch_assoc($pembayaran)): ?>
                            <tr class="hover:bg-biscuit-light transition duration-200">
                                <td class="px-6 py-4 font-medium text-coffee">#<?php echo $row['id_pembayaran']; ?></td>
                                <td class="px-6 py-4 text-coffee"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td class="px-6 py-4 text-coffee"><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                                <td class="px-6 py-4 font-semibold text-coffee-light"><?php echo formatRupiah($row['jumlah']); ?></td>
                                <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($row['metode_pembayaran'] ?? 'Transfer Bank'); ?></td>
                                <td class="px-6 py-4 text-gray-600"><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                <td class="px-6 py-4">
                                    <?php if($row['status_pembayaran'] == 'pending'): ?>
                                        <span class="px-3 py-1 bg-yellow-50 text-yellow-700 rounded-full text-xs font-semibold border border-yellow-200">
                                            <i class="fas fa-clock mr-1"></i>Pending
                                        </span>
                                    <?php elseif($row['status_pembayaran'] == 'dibayar'): ?>
                                        <span class="px-3 py-1 bg-biscuit text-coffee rounded-full text-xs font-semibold border border-biscuit-dark">
                                            <i class="fas fa-hourglass-half mr-1"></i>Menunggu
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-biscuit text-coffee-light rounded-full text-xs font-semibold border border-biscuit-dark">
                                            <i class="fas fa-check-circle mr-1"></i>Selesai
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if($row['status_pembayaran'] != 'selesai'): ?>
                                    <button onclick="openModal(<?php echo $row['id_pembayaran']; ?>, '<?php echo $row['status_pembayaran']; ?>')"
                                            class="text-coffee hover:text-coffee-light font-semibold transition duration-200">
                                        <i class="fas fa-edit mr-1"></i>Update
                                    </button>
                                    <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($total_pembayaran == 0): ?>
                <div class="p-12 text-center">
                    <i class="fas fa-inbox text-biscuit text-6xl mb-4"></i>
                    <h3 class="text-xl font-bold text-coffee mb-2">Belum Ada Pembayaran</h3>
                    <p class="text-gray-500">Belum ada pembayaran yang masuk</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal Update Status -->
    <div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4 shadow-2xl border border-biscuit">
            <h3 class="text-2xl font-bold text-coffee font-serif mb-4">Update Status Pembayaran</h3>
            <form method="POST">
                <input type="hidden" name="id_pembayaran" id="modal_id_pembayaran">
                
                <div class="mb-6">
                    <label class="block text-coffee font-semibold mb-2">Status Pembayaran</label>
                    <select name="status_pembayaran" id="modal_status" class="w-full px-4 py-3 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee">
                        <option value="pending">Pending</option>
                        <option value="dibayar">Dibayar - Menunggu Konfirmasi</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>

                <div class="flex gap-3">
                    <button type="submit" name="update_status" class="flex-1 bg-coffee hover:bg-coffee-dark text-biscuit px-6 py-3 rounded-lg font-semibold transition duration-200 shadow-warm">
                        <i class="fas fa-save mr-2"></i>Update
                    </button>
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-400 hover:bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold transition duration-200 shadow-warm">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id, status) {
            document.getElementById('modal_id_pembayaran').value = id;
            document.getElementById('modal_status').value = status;
            document.getElementById('updateModal').classList.remove('hidden');
            document.getElementById('updateModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('updateModal').classList.add('hidden');
            document.getElementById('updateModal').classList.remove('flex');
        }
    </script>
</body>
</html>