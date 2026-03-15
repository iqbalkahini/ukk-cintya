<?php
session_start();
require_once('../config/config.php');
checkLevel([3]);

$id_user = $_SESSION['id_user'];

// Get statistics
$total_penawaran = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM history_lelang WHERE id_user = $id_user"))['total'];
$total_menang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_lelang WHERE id_user = $id_user AND status = 'ditutup'"))['total'];

// Get active lelang - TAMPILKAN GAMBAR
$lelang_data = mysqli_query($conn, "SELECT l.*, b.nama_barang, b.harga_awal, b.gambar, b.deskripsi_barang,
                                    (SELECT MAX(penawaran_harga) FROM history_lelang WHERE id_lelang = l.id_lelang) as harga_tertinggi
                                    FROM tb_lelang l 
                                    JOIN tb_barang b ON l.id_barang = b.id_barang 
                                    WHERE l.status = 'dibuka'
                                    ORDER BY l.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Masyarakat</title>
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
                <div class="hidden md:flex items-center space-x-6">
                    <a href="dashboard.php" class="text-biscuit hover:text-biscuit-dark font-semibold">Beranda</a>
                    <a href="barang_lelang.php" class="text-biscuit hover:text-biscuit-dark">Barang Lelang</a>
                    <a href="penawaran_saya.php" class="text-biscuit hover:text-biscuit-dark">Penawaran Saya</a>
                    <a href="pembayaran.php" class="text-biscuit hover:text-biscuit-dark">Pembayaran</a>
                    <div class="flex items-center space-x-3">
                        <div class="text-right">
                            <p class="text-biscuit text-sm"><?php echo $_SESSION['nama_lengkap']; ?></p>
                            <p class="text-biscuit opacity-80 text-xs">Masyarakat</p>
                        </div>
                        <a href="../auth/logout.php" class="bg-biscuit hover:bg-biscuit-dark text-coffee px-4 py-2 rounded-lg transition duration-200 shadow-md">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="text-biscuit focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-coffee-dark px-4 py-3">
            <a href="dashboard.php" class="block text-biscuit py-2 hover:bg-coffee-light px-3 rounded">Beranda</a>
            <a href="barang_lelang.php" class="block text-biscuit py-2 hover:bg-coffee-light px-3 rounded">Barang Lelang</a>
            <a href="penawaran_saya.php" class="block text-biscuit py-2 hover:bg-coffee-light px-3 rounded">Penawaran Saya</a>
            <a href="pembayaran.php" class="block text-biscuit py-2 hover:bg-coffee-light px-3 rounded">Pembayaran</a>
            <div class="border-t border-coffee-light my-2"></div>
            <div class="text-biscuit py-2 px-3">
                <p class="font-semibold"><?php echo $_SESSION['nama_lengkap']; ?></p>
                <p class="text-sm opacity-80">Masyarakat</p>
            </div>
            <a href="../auth/logout.php" class="block bg-biscuit text-coffee py-2 px-3 rounded mt-2">Keluar</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-coffee text-biscuit py-16 shadow-warm">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center">
                <h1 class="text-4xl font-bold mb-4 font-serif">Selamat Datang Di Sistem Lelang Online</h1>
                <p class="text-xl text-biscuit opacity-90 mb-8">Platform lelang online untuk memenangkan barang impian Anda dengan mudah</p>
                <a href="barang_lelang.php" class="bg-biscuit hover:bg-biscuit-dark text-coffee font-bold px-8 py-3 rounded-lg inline-block transition duration-200 shadow-warm">
                    LIHAT BARANG LELANG
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Welcome Card -->
        <div class="bg-coffee rounded-xl shadow-warm p-6 mb-8 text-biscuit border border-coffee-light">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2 font-serif">Selamat Datang, <?php echo $_SESSION['nama_lengkap']; ?>!</h2>
                    <p class="text-biscuit opacity-80">Telusuri lelang dan buat penawaran terbaik. Semoga beruntung!</p>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-coffee hover:shadow-lg transition duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Penawaran</p>
                        <p class="text-3xl font-bold text-coffee mt-2"><?php echo $total_penawaran; ?></p>
                        <p class="text-xs text-gray-500 mt-1">Penawaran Anda</p>
                    </div>
                    <div class="bg-biscuit p-4 rounded-full">
                        <i class="fas fa-hand-holding-usd text-coffee text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-coffee-light hover:shadow-lg transition duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Lelang Dimenangkan</p>
                        <p class="text-3xl font-bold text-coffee mt-2"><?php echo $total_menang; ?></p>
                        <p class="text-xs text-gray-500 mt-1">Total Kemenangan</p>
                    </div>
                    <div class="bg-biscuit p-4 rounded-full">
                        <i class="fas fa-trophy text-coffee-light text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-amber-700 hover:shadow-lg transition duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Waktu Sekarang</p>
                        <p class="text-3xl font-bold text-coffee mt-2">
                            <?php echo date('H:i'); ?>
                        </p>
                        <p class="text-xs text-gray-500 mt-1"><?php echo date('d F Y'); ?></p>
                    </div>
                    <div class="bg-biscuit p-4 rounded-full">
                        <i class="fas fa-clock text-amber-700 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barang Lelang Terbaru -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-coffee font-serif">
                    <i class="fas fa-fire text-coffee-light mr-2"></i>Barang Lelang Terbaru
                </h2>
                <a href="barang_lelang.php" class="text-coffee hover:text-coffee-light font-semibold">
                    Lihat Semua <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php while($row = mysqli_fetch_assoc($lelang_data)): ?>
                <div class="bg-white rounded-xl shadow-warm overflow-hidden hover:shadow-xl transition duration-300 border border-biscuit">
                    <!-- GAMBAR - DIPERBAIKI -->
                    <div class="h-48 bg-biscuit flex items-center justify-center relative overflow-hidden">
                        <?php 
                        // Cek apakah ada gambar di folder uploads/barang/
                        if(!empty($row['gambar']) && file_exists('../uploads/barang/' . $row['gambar'])): 
                        ?>
                            <img src="../uploads/barang/<?php echo $row['gambar']; ?>" 
                                 alt="<?php echo htmlspecialchars($row['nama_barang']); ?>" 
                                 class="barang-image">
                        <?php 
                        // Cek apakah ada gambar di folder uploads/ (folder lama)
                        elseif(!empty($row['gambar']) && file_exists('../uploads/' . $row['gambar'])): 
                        ?>
                            <img src="../uploads/<?php echo $row['gambar']; ?>" 
                                 alt="<?php echo htmlspecialchars($row['nama_barang']); ?>" 
                                 class="barang-image">
                        <?php else: ?>
                            <i class="fas fa-image text-coffee-light text-4xl"></i>
                        <?php endif; ?>
                        
                        <div class="absolute top-4 right-4">
                            <span class="px-3 py-1 bg-coffee text-biscuit rounded-full text-sm font-semibold shadow-lg">
                                <i class="fas fa-circle text-xs mr-1 animate-pulse"></i>Aktif
                            </span>
                        </div>
                    </div>

                    <div class="p-6">
                        <h3 class="font-bold text-xl text-coffee mb-2 font-serif"><?php echo htmlspecialchars($row['nama_barang']); ?></h3>
                        <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars(substr($row['deskripsi_barang'], 0, 80) . '...'); ?></p>
                        
                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-gray-500">Harga Awal</span>
                                <span class="font-semibold text-gray-700"><?php echo formatRupiah($row['harga_awal']); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Harga Saat Ini</span>
                                <span class="font-bold text-coffee-light text-lg">
                                    <?php echo formatRupiah($row['harga_tertinggi'] ?? $row['harga_awal']); ?>
                                </span>
                            </div>
                        </div>

                        <a href="detail_lelang.php?id=<?php echo $row['id_lelang']; ?>" 
                           class="block w-full bg-coffee hover:bg-coffee-dark text-biscuit text-center font-semibold py-3 rounded-lg transition duration-200 shadow-warm">
                            Tawar <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Jika tidak ada lelang -->
            <?php 
            mysqli_data_seek($lelang_data, 0);
            if(mysqli_num_rows($lelang_data) == 0): 
            ?>
            <div class="bg-white rounded-xl shadow-warm p-12 text-center border border-biscuit">
                <i class="fas fa-inbox text-biscuit text-6xl mb-4"></i>
                <h3 class="text-xl font-bold text-coffee font-serif mb-2">Belum Ada Lelang Aktif</h3>
                <p class="text-gray-500 mb-6">Saat ini tidak ada barang yang sedang dilelang. Silakan cek kembali nanti.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Riwayat Penawaran -->
        <div class="bg-white rounded-xl shadow-warm p-6 border border-biscuit">
            <h2 class="text-xl font-bold text-coffee font-serif mb-4">
                <i class="fas fa-history mr-2"></i>Riwayat Penawaran Terbaru
            </h2>
            <?php
            $history = mysqli_query($conn, "SELECT h.*, b.nama_barang, l.status
                                           FROM history_lelang h
                                           JOIN tb_barang b ON h.id_barang = b.id_barang
                                           JOIN tb_lelang l ON h.id_lelang = l.id_lelang
                                           WHERE h.id_user = $id_user
                                           ORDER BY h.created_at DESC LIMIT 5");
            ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-biscuit-light">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Penawaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-biscuit">
                        <?php while($h = mysqli_fetch_assoc($history)): ?>
                        <tr class="hover:bg-biscuit-light transition duration-200">
                            <td class="px-6 py-4 font-medium text-coffee"><?php echo htmlspecialchars($h['nama_barang']); ?></td>
                            <td class="px-6 py-4 text-coffee-light font-semibold"><?php echo formatRupiah($h['penawaran_harga']); ?></td>
                            <td class="px-6 py-4 text-gray-600"><?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?></td>
                            <td class="px-6 py-4">
                                <?php if($h['status'] == 'dibuka'): ?>
                                    <span class="px-2 py-1 bg-coffee text-biscuit rounded-full text-xs">Sedang Berlangsung</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">Ditutup</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if(mysqli_num_rows($history) == 0): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-3xl mb-2 block"></i>
                                Belum ada riwayat penawaran
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
</body>
</html>