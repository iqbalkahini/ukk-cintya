<?php
session_start();
require_once('../config/config.php');
checkLevel([3]);

// Query yang lebih baik - pastikan hanya menampilkan lelang aktif dengan barang yang valid
$lelang_data = mysqli_query($conn, "SELECT l.*, 
                                    b.nama_barang, 
                                    b.harga_awal, 
                                    b.gambar, 
                                    b.deskripsi_barang,
                                    b.status_barang,
                                    (SELECT MAX(penawaran_harga) FROM history_lelang WHERE id_lelang = l.id_lelang) as harga_tertinggi,
                                    (SELECT COUNT(*) FROM history_lelang WHERE id_lelang = l.id_lelang) as jumlah_penawaran,
                                    (SELECT COUNT(*) FROM history_lelang WHERE id_lelang = l.id_lelang AND id_user = " . $_SESSION['id_user'] . ") as user_has_bid
                                    FROM tb_lelang l 
                                    JOIN tb_barang b ON l.id_barang = b.id_barang 
                                    WHERE l.status = 'dibuka' 
                                    AND b.status_barang = 'dibuka'
                                    ORDER BY l.created_at DESC");

// Debug: Cek apakah query error
if (!$lelang_data) {
    die("Query Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Lelang - Sistem Lelang Online</title>
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
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
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
                <div class="flex items-center space-x-6">
                    <a href="dashboard.php" class="text-biscuit hover:text-biscuit-dark">Beranda</a>
                    <a href="barang_lelang.php" class="text-biscuit hover:text-biscuit-dark font-semibold">Barang Lelang</a>
                    <a href="penawaran_saya.php" class="text-biscuit hover:text-biscuit-dark">Penawaran Saya</a>
                    <a href="pembayaran.php" class="text-biscuit hover:text-biscuit-dark">Pembayaran</a>
                    <div class="flex items-center space-x-3">
                        <div class="text-right hidden lg:block">
                            <p class="text-biscuit text-sm font-semibold"><?php echo $_SESSION['nama_lengkap']; ?></p>
                            <p class="text-biscuit opacity-80 text-xs">Masyarakat</p>
                        </div>
                        <a href="../auth/logout.php" class="bg-biscuit hover:bg-biscuit-dark text-coffee px-4 py-2 rounded-lg transition duration-200 shadow-md">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-coffee font-serif mb-2">
                <i class="fas fa-th-large mr-3"></i>Barang Lelang Terbaru
            </h1>
            <p class="text-gray-600">Temukan barang impian Anda dan menangkan lelang!</p>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-xl shadow-warm p-6 mb-8 border border-biscuit">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" id="searchInput" placeholder="Cari barang..." 
                           class="w-full px-4 py-3 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white">
                </div>
                <button onclick="searchBarang()" class="bg-coffee hover:bg-coffee-dark text-biscuit px-6 py-3 rounded-lg transition duration-200 shadow-warm">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
            </div>
        </div>

        <!-- Grid Barang -->
        <?php if(mysqli_num_rows($lelang_data) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="barangGrid">
            <?php while($row = mysqli_fetch_assoc($lelang_data)): ?>
            <div class="bg-white rounded-xl shadow-warm overflow-hidden hover:shadow-xl transition duration-300 transform hover:-translate-y-1 border border-biscuit barang-card">
                <!-- Image -->
                <div class="h-56 bg-biscuit flex items-center justify-center relative overflow-hidden">
                    <?php 
                    // Cek apakah ada gambar
                    if(!empty($row['gambar']) && file_exists('../uploads/barang/' . $row['gambar'])): 
                    ?>
                        <img src="../uploads/barang/<?php echo $row['gambar']; ?>" 
                             alt="<?php echo htmlspecialchars($row['nama_barang']); ?>" 
                             class="barang-image">
                    <?php elseif(!empty($row['gambar']) && file_exists('../uploads/' . $row['gambar'])): ?>
                        <img src="../uploads/<?php echo $row['gambar']; ?>" 
                             alt="<?php echo htmlspecialchars($row['nama_barang']); ?>" 
                             class="barang-image">
                    <?php else: ?>
                        <i class="fas fa-image text-coffee-light text-5xl"></i>
                    <?php endif; ?>
                    
                    <div class="absolute top-4 right-4">
                        <span class="px-3 py-1 bg-coffee text-biscuit rounded-full text-sm font-semibold shadow-lg">
                            <i class="fas fa-circle text-xs mr-1 animate-pulse"></i>Aktif
                        </span>
                    </div>

                    <?php if($row['user_has_bid'] > 0): ?>
                    <div class="absolute top-4 left-4">
                        <span class="px-3 py-1 bg-biscuit text-coffee rounded-full text-sm font-semibold shadow-lg border border-coffee">
                            <i class="fas fa-check-circle mr-1"></i>Anda Menawar
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <h3 class="font-bold text-xl text-coffee font-serif mb-2 barang-name"><?php echo htmlspecialchars($row['nama_barang']); ?></h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                        <?php echo htmlspecialchars(substr($row['deskripsi_barang'], 0, 100) . '...'); ?>
                    </p>

                    <!-- Price Info -->
                    <div class="border-t border-b border-biscuit py-4 mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-500">Harga Awal</span>
                            <span class="font-semibold text-gray-700">
                                <?php echo formatRupiah($row['harga_awal']); ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Harga Saat Ini</span>
                            <span class="font-bold text-coffee-light text-lg">
                                <?php echo formatRupiah($row['harga_tertinggi'] ?? $row['harga_awal']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="flex items-center justify-between mb-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-users mr-2 text-coffee"></i>
                            <span><?php echo $row['jumlah_penawaran']; ?> Penawaran</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock mr-2 text-coffee"></i>
                            <span><?php echo formatTanggal($row['tgl_lelang']); ?></span>
                        </div>
                    </div>

                    <!-- Button -->
                    <a href="detail_lelang.php?id=<?php echo $row['id_lelang']; ?>" 
                       class="block w-full bg-coffee hover:bg-coffee-dark text-biscuit text-center font-semibold py-3 rounded-lg transition duration-200 shadow-warm">
                        <i class="fas fa-gavel mr-2"></i>Tawar Sekarang
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <!-- Empty State -->
        <div class="bg-white rounded-xl shadow-warm p-12 text-center border border-biscuit">
            <i class="fas fa-inbox text-biscuit text-6xl mb-4"></i>
            <h3 class="text-xl font-bold text-coffee font-serif mb-2">Belum Ada Lelang Aktif</h3>
            <p class="text-gray-500 mb-6">Saat ini tidak ada barang yang sedang dilelang. Silakan cek kembali nanti.</p>
            <a href="dashboard.php" class="inline-block bg-coffee hover:bg-coffee-dark text-biscuit px-6 py-3 rounded-lg font-semibold transition duration-200 shadow-warm">
                <i class="fas fa-home mr-2"></i>Kembali ke Dashboard
            </a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function searchBarang() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.barang-card');
            
            cards.forEach(card => {
                const barangName = card.querySelector('.barang-name').textContent.toLowerCase();
                if (barangName.includes(searchValue)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Real-time search
        document.getElementById('searchInput').addEventListener('keyup', function() {
            searchBarang();
        });
    </script>
</body>
</html>