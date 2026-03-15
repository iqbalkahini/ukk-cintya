<?php
session_start();
require_once('../config/config.php');

// Fungsi untuk format rupiah
if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

// Fungsi untuk format tanggal
if (!function_exists('formatTanggal')) {
    function formatTanggal($tanggal) {
        return date('d M Y', strtotime($tanggal));
    }
}

checkLevel([1]); // Hanya admin

// Get statistics
$total_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_user"))['total'];
$total_barang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_barang"))['total'];
$total_lelang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_lelang"))['total'];

// Get recent lelang dengan gambar
$recent_lelang = mysqli_query($conn, "SELECT l.*, b.nama_barang, b.gambar 
                                       FROM tb_lelang l 
                                       JOIN tb_barang b ON l.id_barang = b.id_barang 
                                       ORDER BY l.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Lelang Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap');
        
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        h1, h2, h3, h4, h5, h6, .font-serif {
            font-family: 'Playfair Display', serif;
        }
        
        .bg-coffee {
            background-color: #6F4E37;
        }
        .bg-coffee-light {
            background-color: #8B6B4D;
        }
        .bg-coffee-dark {
            background-color: #4A3729;
        }
        .bg-biscuit {
            background-color: #F7E1C0;
        }
        .bg-biscuit-light {
            background-color: #FEF3E2;
        }
        .bg-biscuit-dark {
            background-color: #E5C9A8;
        }
        .text-coffee {
            color: #6F4E37;
        }
        .text-coffee-light {
            color: #8B6B4D;
        }
        .text-biscuit {
            color: #F7E1C0;
        }
        .border-coffee {
            border-color: #6F4E37;
        }
        .border-biscuit {
            border-color: #E5C9A8;
        }
        .hover\:bg-coffee-dark:hover {
            background-color: #4A3729;
        }
        .hover\:bg-biscuit-dark:hover {
            background-color: #E5C9A8;
        }
        .shadow-warm {
            box-shadow: 0 10px 25px -5px rgba(111, 78, 55, 0.1), 0 8px 10px -6px rgba(111, 78, 55, 0.1);
        }
        .barang-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-biscuit-light">
    <!-- Navbar -->
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
                        <?php echo $_SESSION['nama_lengkap']; ?>
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
                    <p class="text-lg font-bold text-coffee font-serif">Administrator</p>
                </div>
                
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-3 bg-biscuit text-coffee rounded-lg shadow-sm border border-biscuit-dark">
                        <i class="fas fa-home w-5"></i>
                        <span class="ml-3">Beranda</span>
                    </a>
                    <a href="total_barang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-box w-5"></i>
                        <span class="ml-3">Total Barang</span>
                    </a>
                    <a href="data_barang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-database w-5"></i>
                        <span class="ml-3">Data Barang</span>
                    </a>
                    <a href="total_lelang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-gavel w-5"></i>
                        <span class="ml-3">Total Lelang</span>
                    </a>
                    <a href="laporan.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-file-alt w-5"></i>
                        <span class="ml-3">Laporan</span>
                    </a>
                    <a href="data_user.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-users w-5"></i>
                        <span class="ml-3">Data Pengguna</span>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-coffee mb-8 font-serif">Beranda Menu</h1>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total User -->
                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-coffee hover:shadow-lg transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Pengguna</p>
                            <p class="text-3xl font-bold text-coffee mt-2"><?php echo $total_user; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Semua Pengguna Sistem</p>
                        </div>
                        <div class="bg-biscuit p-4 rounded-full">
                            <i class="fas fa-users text-coffee text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Lelang -->
                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-coffee-light hover:shadow-lg transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Lelang</p>
                            <p class="text-3xl font-bold text-coffee mt-2"><?php echo $total_lelang; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Semua Sesi Lelang</p>
                        </div>
                        <div class="bg-biscuit p-4 rounded-full">
                            <i class="fas fa-gavel text-coffee-light text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Barang -->
                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-yellow-700 hover:shadow-lg transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Barang</p>
                            <p class="text-3xl font-bold text-coffee mt-2"><?php echo $total_barang; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Barang di Sistem</p>
                        </div>
                        <div class="bg-biscuit p-4 rounded-full">
                            <i class="fas fa-box text-yellow-700 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Lelang -->
            <div class="bg-white rounded-xl shadow-warm p-6">
                <h2 class="text-xl font-bold text-coffee mb-4 font-serif">
                    <i class="fas fa-clock mr-2"></i>Lelang Terbaru
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-biscuit">
                                <th class="text-left py-3 px-4 text-coffee">Barang</th>
                                <th class="text-left py-3 px-4 text-coffee">Tanggal Lelang</th>
                                <th class="text-left py-3 px-4 text-coffee">Harga Akhir</th>
                                <th class="text-left py-3 px-4 text-coffee">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($recent_lelang)): ?>
                            <tr class="border-b border-biscuit hover:bg-biscuit transition duration-200">
                                <td class="py-3 px-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-biscuit rounded mr-3 flex items-center justify-center overflow-hidden">
                                            <?php 
                                            // Cek gambar
                                            if(!empty($row['gambar']) && file_exists('../uploads/barang/' . $row['gambar'])): 
                                            ?>
                                                <img src="../uploads/barang/<?php echo $row['gambar']; ?>" 
                                                     alt="<?php echo $row['nama_barang']; ?>" 
                                                     class="w-full h-full object-cover">
                                            <?php elseif(!empty($row['gambar']) && file_exists('../uploads/' . $row['gambar'])): ?>
                                                <img src="../uploads/<?php echo $row['gambar']; ?>" 
                                                     alt="<?php echo $row['nama_barang']; ?>" 
                                                     class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <i class="fas fa-image text-coffee"></i>
                                            <?php endif; ?>
                                        </div>
                                        <span class="font-medium text-gray-700"><?php echo $row['nama_barang']; ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-600"><?php echo formatTanggal($row['tgl_lelang']); ?></td>
                                <td class="py-3 px-4 font-semibold text-coffee-light">
                                    <?php echo formatRupiah($row['harga_akhir']); ?>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if($row['status'] == 'dibuka'): ?>
                                        <span class="px-3 py-1 bg-biscuit text-coffee rounded-full text-sm border border-biscuit-dark">Dibuka</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm">Ditutup</span>
                                    <?php endif; ?>
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