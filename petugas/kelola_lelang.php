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

checkLevel([2]);

if (!isset($_SESSION['success'])) {
    $_SESSION['success'] = null;
}
if (!isset($_SESSION['error'])) {
    $_SESSION['error'] = null;
}

// Handle buka/tutup lelang (dengan sanitasi)
if(isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if($action == 'buka') {
        $cek_kunci = mysqli_fetch_assoc(mysqli_query($conn, "SELECT l.id_barang,
                    EXISTS(
                        SELECT 1
                        FROM tb_pembayaran p
                        WHERE p.id_lelang = l.id_lelang
                          AND p.id_user = l.id_user
                          AND p.status_pembayaran IN ('dibayar', 'selesai')
                    ) AS sudah_dibayar
                FROM tb_lelang l
                WHERE l.id_lelang = $id"));

        if ($cek_kunci && (int) $cek_kunci['sudah_dibayar'] === 1) {
            $_SESSION['error'] = "Lelang tidak bisa dibuka lagi karena pemenang sudah melakukan pembayaran.";
        } else {
            mysqli_query($conn, "UPDATE tb_lelang SET status = 'dibuka' WHERE id_lelang = $id");
            mysqli_query($conn, "UPDATE tb_barang SET status_barang = 'dibuka' WHERE id_barang = (SELECT id_barang FROM tb_lelang WHERE id_lelang = $id)");
            $_SESSION['success'] = "Lelang berhasil dibuka.";
        }
    } elseif($action == 'tutup') {
        mysqli_query($conn, "UPDATE tb_lelang SET status = 'ditutup' WHERE id_lelang = $id");
        mysqli_query($conn, "UPDATE tb_barang SET status_barang = 'ditutup' WHERE id_barang = (SELECT id_barang FROM tb_lelang WHERE id_lelang = $id)");
        $_SESSION['success'] = "Lelang berhasil ditutup.";
    }
    
    header('Location: kelola_lelang.php');
    exit;
}

// Handle create lelang (dengan sanitasi)
if(isset($_POST['create_lelang'])) {
    $id_barang = intval($_POST['id_barang']);
    $tgl_lelang = mysqli_real_escape_string($conn, $_POST['tgl_lelang']);
    $id_petugas = intval($_SESSION['id_user']);

    $barang_terkunci = mysqli_fetch_assoc(mysqli_query($conn, "SELECT EXISTS(
            SELECT 1
            FROM tb_lelang l
            JOIN tb_pembayaran p ON p.id_lelang = l.id_lelang AND p.id_user = l.id_user
            WHERE l.id_barang = $id_barang
              AND p.status_pembayaran IN ('dibayar', 'selesai')
        ) AS terkunci"));
    
    if ($barang_terkunci && (int) $barang_terkunci['terkunci'] === 1) {
        $_SESSION['error'] = "Barang ini sudah dimenangkan dan dibayar, sehingga tidak bisa dibuka lagi.";
    } else {
        // Get harga awal
        $barang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT harga_awal FROM tb_barang WHERE id_barang = $id_barang"));
        
        $query = "INSERT INTO tb_lelang (id_barang, tgl_lelang, harga_akhir, id_petugas, status) 
                  VALUES ($id_barang, '$tgl_lelang', {$barang['harga_awal']}, $id_petugas, 'dibuka')";
        mysqli_query($conn, $query);
        
        mysqli_query($conn, "UPDATE tb_barang SET status_barang = 'dibuka' WHERE id_barang = $id_barang");
        $_SESSION['success'] = "Lelang baru berhasil dibuat.";
    }
    
    header('Location: kelola_lelang.php');
    exit;
}

// Ambil data lelang
$lelang = mysqli_query($conn, "SELECT l.*, b.nama_barang, b.harga_awal, u.nama_lengkap as pemenang,
                               EXISTS(
                                   SELECT 1
                                   FROM tb_pembayaran p
                                   WHERE p.id_lelang = l.id_lelang
                                     AND p.id_user = l.id_user
                                     AND p.status_pembayaran IN ('dibayar', 'selesai')
                               ) AS sudah_dibayar
                               FROM tb_lelang l 
                               JOIN tb_barang b ON l.id_barang = b.id_barang
                               LEFT JOIN tb_user u ON l.id_user = u.id_user
                               ORDER BY l.id_lelang DESC");

// Ambil barang yang tersedia untuk lelang
$barang_available = mysqli_query($conn, "SELECT *
    FROM tb_barang
    WHERE id_barang NOT IN (
        SELECT l.id_barang
        FROM tb_lelang l
        JOIN tb_pembayaran p ON p.id_lelang = l.id_lelang AND p.id_user = l.id_user
        WHERE p.status_pembayaran IN ('dibayar', 'selesai')
    )
    AND (
        status_barang = 'pending'
        OR id_barang NOT IN (SELECT id_barang FROM tb_lelang WHERE status = 'dibuka')
    )");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Lelang - Petugas</title>
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
        <!-- Sidebar -->
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
                    <a href="kelola_lelang.php" class="flex items-center px-4 py-3 bg-biscuit text-coffee rounded-lg shadow-sm border border-biscuit-dark">
                        <i class="fas fa-gavel w-5"></i><span class="ml-3">Kelola Lelang</span>
                    </a>
                    <a href="pembayaran.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-money-bill w-5"></i><span class="ml-3">Pembayaran</span>
                    </a>
                    <a href="laporan.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-file-alt w-5"></i><span class="ml-3">Laporan</span>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-coffee font-serif mb-8">Kelola Barang & Penawaran</h1>

            <?php if(!empty($_SESSION['success'])): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
            <?php endif; ?>

            <?php if(!empty($_SESSION['error'])): ?>
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>

            <!-- Form Buka Lelang Baru -->
            <div class="bg-white rounded-xl shadow-warm p-6 mb-8 border border-biscuit">
                <h2 class="text-xl font-bold text-coffee font-serif mb-4">
                    <i class="fas fa-plus-circle mr-2"></i>Buka Lelang Baru
                </h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-coffee font-semibold mb-2">Pilih Barang</label>
                        <select name="id_barang" required class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white">
                            <option value="">-- Pilih Barang --</option>
                            <?php while($b = mysqli_fetch_assoc($barang_available)): ?>
                            <option value="<?php echo $b['id_barang']; ?>">
                                <?php echo htmlspecialchars($b['nama_barang']); ?> (<?php echo formatRupiah($b['harga_awal']); ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-coffee font-semibold mb-2">Tanggal Lelang</label>
                        <input type="date" name="tgl_lelang" required 
                               class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" name="create_lelang" 
                                class="w-full bg-coffee hover:bg-coffee-dark text-biscuit px-6 py-2 rounded-lg transition duration-200 shadow-warm">
                            <i class="fas fa-play mr-2"></i>Buka Lelang
                        </button>
                    </div>
                </form>
            </div>

            <!-- Daftar Lelang -->
            <div class="bg-white rounded-xl shadow-warm overflow-hidden border border-biscuit">
                <div class="p-6 border-b border-biscuit bg-biscuit">
                    <h2 class="text-xl font-bold text-coffee font-serif">Daftar Lelang</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-biscuit-light">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Nama Barang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Harga Awal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Harga Akhir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Pemenang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Aksi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Detail Penawaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-biscuit">
                            <?php while($row = mysqli_fetch_assoc($lelang)): ?>
                            <tr class="hover:bg-biscuit-light transition duration-200">
                                <td class="px-6 py-4 font-medium text-coffee"><?php echo $row['id_lelang']; ?></td>
                                <td class="px-6 py-4 font-medium text-coffee"><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                                <td class="px-6 py-4 text-gray-600"><?php echo formatRupiah($row['harga_awal']); ?></td>
                                <td class="px-6 py-4 text-coffee-light font-semibold"><?php echo formatRupiah($row['harga_akhir']); ?></td>
                                <td class="px-6 py-4 text-coffee"><?php echo htmlspecialchars($row['pemenang'] ?? '-'); ?></td>
                                <td class="px-6 py-4">
                                    <?php if($row['status'] == 'dibuka'): ?>
                                        <span class="px-3 py-1 bg-biscuit text-coffee rounded-full text-xs font-semibold border border-biscuit-dark">Dibuka</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold border border-gray-200">Ditutup</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if($row['status'] == 'dibuka'): ?>
                                        <a href="?action=tutup&id=<?php echo $row['id_lelang']; ?>" 
                                           onclick="return confirm('Yakin ingin menutup lelang?')"
                                           class="text-coffee hover:text-coffee-light transition duration-200">
                                            <i class="fas fa-stop-circle mr-1"></i> Tutup
                                        </a>
                                    <?php elseif((int) $row['sudah_dibayar'] === 1): ?>
                                        <span class="text-gray-400 cursor-not-allowed">
                                            <i class="fas fa-lock mr-1"></i> Terkunci
                                        </span>
                                    <?php else: ?>
                                        <a href="?action=buka&id=<?php echo $row['id_lelang']; ?>" 
                                           class="text-coffee-light hover:text-coffee-dark transition duration-200">
                                            <i class="fas fa-play-circle mr-1"></i> Buka
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="detail_lelang_petugas.php?id=<?php echo $row['id_lelang']; ?>" class="text-coffee hover:text-coffee-light transition duration-200">
                                        <i class="fas fa-eye mr-1"></i> Lihat
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
