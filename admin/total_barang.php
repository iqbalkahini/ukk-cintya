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

// Get all barang with statistics
$barang = mysqli_query($conn, "SELECT b.*, 
                               (SELECT COUNT(*) FROM tb_lelang WHERE id_barang = b.id_barang) as jumlah_lelang,
                               (SELECT COUNT(*) FROM tb_lelang WHERE id_barang = b.id_barang AND status = 'dibuka') as lelang_aktif
                               FROM tb_barang b 
                               ORDER BY b.id_barang DESC");

// Get statistics
$total_barang = mysqli_num_rows($barang);
mysqli_data_seek($barang, 0);
$barang_dibuka = 0;
$barang_ditutup = 0;
$barang_pending = 0;

while($temp = mysqli_fetch_assoc($barang)) {
    if($temp['status_barang'] == 'dibuka') $barang_dibuka++;
    elseif($temp['status_barang'] == 'ditutup') $barang_ditutup++;
    else $barang_pending++;
}
mysqli_data_seek($barang, 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Barang - Admin</title>
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
                    <a href="dashboard.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-home w-5"></i>
                        <span class="ml-3">Beranda</span>
                    </a>
                    <a href="total_barang.php" class="flex items-center px-4 py-3 bg-biscuit text-coffee rounded-lg shadow-sm border border-biscuit-dark">
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
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-coffee font-serif">Total Barang</h1>
                <p class="text-gray-600 mt-2">Lihat semua barang yang terdaftar dalam sistem</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-coffee hover:shadow-lg transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Barang</p>
                            <p class="text-3xl font-bold text-coffee mt-2"><?php echo $total_barang; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Semua Barang</p>
                        </div>
                        <div class="bg-biscuit p-4 rounded-full">
                            <i class="fas fa-box text-coffee text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-coffee-light hover:shadow-lg transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Barang Dibuka</p>
                            <p class="text-3xl font-bold text-coffee mt-2"><?php echo $barang_dibuka; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Sedang Lelang</p>
                        </div>
                        <div class="bg-biscuit p-4 rounded-full">
                            <i class="fas fa-check-circle text-coffee-light text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-amber-700 hover:shadow-lg transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Barang Ditutup</p>
                            <p class="text-3xl font-bold text-coffee mt-2"><?php echo $barang_ditutup; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Sudah Selesai</p>
                        </div>
                        <div class="bg-biscuit p-4 rounded-full">
                            <i class="fas fa-times-circle text-amber-700 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-yellow-700 hover:shadow-lg transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Barang Pending</p>
                            <p class="text-3xl font-bold text-coffee mt-2"><?php echo $barang_pending; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Belum Dilelang</p>
                        </div>
                        <div class="bg-biscuit p-4 rounded-full">
                            <i class="fas fa-clock text-yellow-700 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="mb-6">
                <a href="data_barang.php" class="inline-block bg-coffee hover:bg-coffee-dark text-biscuit px-6 py-3 rounded-lg font-semibold shadow-warm transition duration-200">
                    <i class="fas fa-plus mr-2"></i>Tambah Barang Baru
                </a>
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-xl shadow-warm p-6 mb-6 border border-biscuit">
                <div class="flex flex-col md:flex-row gap-4">
                    <input type="text" id="searchInput" placeholder="Cari nama barang..." 
                           class="flex-1 px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white">
                    <select id="filterStatus" class="px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="dibuka">Dibuka</option>
                        <option value="ditutup">Ditutup</option>
                    </select>
                </div>
            </div>

            <!-- Grid View -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php while($row = mysqli_fetch_assoc($barang)): ?>
                <div class="bg-white rounded-xl shadow-warm overflow-hidden hover:shadow-xl transition duration-300 border border-biscuit barang-card" 
                     data-status="<?php echo $row['status_barang']; ?>">
                    <!-- Image -->
                    <div class="h-48 bg-biscuit flex items-center justify-center relative overflow-hidden">
                        <?php 
                        // Cek apakah ada gambar di folder uploads/barang/
                        if(!empty($row['gambar']) && file_exists('../uploads/barang/' . $row['gambar'])): 
                        ?>
                            <img src="../uploads/barang/<?php echo $row['gambar']; ?>" 
                                 alt="<?php echo $row['nama_barang']; ?>" 
                                 class="barang-image">
                        <?php 
                        // Cek apakah ada gambar di folder uploads/ (folder lama)
                        elseif(!empty($row['gambar']) && file_exists('../uploads/' . $row['gambar'])): 
                        ?>
                            <img src="../uploads/<?php echo $row['gambar']; ?>" 
                                 alt="<?php echo $row['nama_barang']; ?>" 
                                 class="barang-image">
                        <?php else: ?>
                            <i class="fas fa-image text-coffee-light text-4xl"></i>
                        <?php endif; ?>
                        
                        <div class="absolute top-4 right-4">
                            <?php if($row['status_barang'] == 'dibuka'): ?>
                                <span class="px-3 py-1 bg-coffee text-biscuit rounded-full text-xs font-semibold">
                                    <i class="fas fa-circle text-xs mr-1"></i>Dibuka
                                </span>
                            <?php elseif($row['status_barang'] == 'ditutup'): ?>
                                <span class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-semibold">
                                    <i class="fas fa-times-circle mr-1"></i>Ditutup
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">
                                    <i class="fas fa-clock mr-1"></i>Pending
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <h3 class="font-bold text-xl text-coffee font-serif mb-2 barang-name"><?php echo $row['nama_barang']; ?></h3>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                            <?php echo substr($row['deskripsi_barang'], 0, 80) . '...'; ?>
                        </p>

                        <!-- Price -->
                        <div class="border-t border-b border-biscuit py-3 mb-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-coffee">Harga Awal</span>
                                <span class="font-bold text-coffee-light text-lg">
                                    <?php echo formatRupiah($row['harga_awal']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                            <div class="text-center bg-biscuit-light rounded-lg py-2 border border-biscuit">
                                <p class="text-coffee font-bold"><?php echo $row['jumlah_lelang']; ?></p>
                                <p class="text-coffee-light text-xs">Total Lelang</p>
                            </div>
                            <div class="text-center bg-biscuit rounded-lg py-2 border border-biscuit-dark">
                                <p class="text-coffee-light font-bold"><?php echo $row['lelang_aktif']; ?></p>
                                <p class="text-coffee text-xs">Lelang Aktif</p>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="text-xs text-coffee mb-4">
                            <p><i class="fas fa-calendar mr-2"></i><?php echo formatTanggal($row['tgl']); ?></p>
                            <p class="mt-1"><i class="fas fa-tag mr-2"></i>ID: #<?php echo $row['id_barang']; ?></p>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <a href="data_barang.php?edit=<?php echo $row['id_barang']; ?>" 
                               class="flex-1 bg-coffee hover:bg-coffee-dark text-biscuit text-center py-2 rounded-lg transition duration-200 shadow-sm">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                            <a href="data_barang.php?delete=<?php echo $row['id_barang']; ?>" 
                               onclick="return confirm('Yakin ingin menghapus?')"
                               class="flex-1 bg-biscuit hover:bg-biscuit-dark text-coffee text-center py-2 rounded-lg transition duration-200 shadow-sm border border-biscuit-dark">
                                <i class="fas fa-trash mr-1"></i>Hapus
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <?php 
            mysqli_data_seek($barang, 0);
            if($total_barang == 0): 
            ?>
            <div class="bg-white rounded-xl shadow-warm p-12 text-center border border-biscuit">
                <i class="fas fa-inbox text-biscuit text-6xl mb-4"></i>
                <h3 class="text-xl font-bold text-coffee font-serif mb-2">Belum Ada Barang</h3>
                <p class="text-coffee mb-6">Mulai tambahkan barang untuk dilelang</p>
                <a href="data_barang.php" class="inline-block bg-coffee hover:bg-coffee-dark text-biscuit px-6 py-3 rounded-lg font-semibold shadow-warm">
                    <i class="fas fa-plus mr-2"></i>Tambah Barang
                </a>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            filterCards();
        });

        // Filter by status
        document.getElementById('filterStatus').addEventListener('change', function() {
            filterCards();
        });

        function filterCards() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const statusValue = document.getElementById('filterStatus').value;
            const cards = document.querySelectorAll('.barang-card');

            cards.forEach(card => {
                const barangName = card.querySelector('.barang-name').textContent.toLowerCase();
                const status = card.getAttribute('data-status');

                const matchSearch = barangName.includes(searchValue);
                const matchStatus = statusValue === '' || status === statusValue;

                if (matchSearch && matchStatus) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>