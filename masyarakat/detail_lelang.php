<?php
session_start();
require_once('../config/config.php');
checkLevel([3]);

$id_lelang = $_GET['id'] ?? 0;
$id_user = $_SESSION['id_user'];

// Handle penawaran
if(isset($_POST['submit_penawaran'])) {
    $penawaran_harga = $_POST['penawaran_harga'];
    $id_barang = $_POST['id_barang'];
    
    // Get harga tertinggi saat ini
    $current = mysqli_fetch_assoc(mysqli_query($conn, "SELECT harga_akhir FROM tb_lelang WHERE id_lelang = $id_lelang"));
    
    if($penawaran_harga > $current['harga_akhir']) {
        // Insert history
        mysqli_query($conn, "INSERT INTO history_lelang (id_lelang, id_barang, id_user, penawaran_harga) 
                            VALUES ($id_lelang, $id_barang, $id_user, $penawaran_harga)");
        
        // Update lelang
        mysqli_query($conn, "UPDATE tb_lelang SET harga_akhir = $penawaran_harga, id_user = $id_user WHERE id_lelang = $id_lelang");
        
        $success = "Penawaran berhasil diajukan!";
    } else {
        $error = "Penawaran harus lebih tinggi dari harga saat ini!";
    }
}

// Get lelang detail
$lelang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT l.*, b.* 
                                                  FROM tb_lelang l 
                                                  JOIN tb_barang b ON l.id_barang = b.id_barang 
                                                  WHERE l.id_lelang = $id_lelang"));

// Get history
$history = mysqli_query($conn, "SELECT h.*, u.nama_lengkap 
                                FROM history_lelang h 
                                JOIN tb_user u ON h.id_user = u.id_user 
                                WHERE h.id_lelang = $id_lelang 
                                ORDER BY h.penawaran_harga DESC");

// Cek apakah user sudah pernah menawar
$user_bid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM history_lelang 
                                                     WHERE id_lelang = $id_lelang AND id_user = $id_user 
                                                     ORDER BY penawaran_harga DESC LIMIT 1"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Barang - Sistem Lelang Online</title>
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
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
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
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="dashboard.php" class="text-biscuit hover:text-biscuit-dark transition">Beranda</a>
                    <a href="barang_lelang.php" class="text-biscuit hover:text-biscuit-dark transition">Barang Lelang</a>
                    <a href="penawaran_saya.php" class="text-biscuit hover:text-biscuit-dark transition">Penawaran Saya</a>
                    <a href="pembayaran.php" class="text-biscuit hover:text-biscuit-dark transition">Pembayaran</a>
                    
                    <div class="flex items-center space-x-3 ml-4">
                        <div class="text-right hidden lg:block">
                            <p class="text-biscuit text-sm font-semibold"><?php echo $_SESSION['nama_lengkap']; ?></p>
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

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <div class="mb-6 flex items-center text-sm">
            <a href="barang_lelang.php" class="text-coffee hover:text-coffee-light transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Barang Lelang
            </a>
        </div>

        <!-- Alert Messages -->
        <?php if(isset($success)): ?>
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-green-700 font-semibold"><?php echo $success; ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500 text-2xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-red-700 font-semibold"><?php echo $error; ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Barang Detail -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-warm overflow-hidden border border-biscuit">
                    <!-- Image Gallery -->
                    <div class="h-96 bg-biscuit flex items-center justify-center relative overflow-hidden">
                        <?php $gambar_info = resolveUploadFile($lelang['gambar'], 'barang'); ?>
                        <?php if($gambar_info): ?>
                            <img src="<?php echo htmlspecialchars($gambar_info['url']); ?>" 
                                 alt="<?php echo htmlspecialchars($lelang['nama_barang']); ?>" 
                                 class="barang-image">
                        <?php else: ?>
                            <div class="text-center">
                                <i class="fas fa-image text-coffee-light text-7xl mb-2"></i>
                                <p class="text-coffee">Tidak ada gambar</p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Status Badge -->
                        <div class="absolute top-4 right-4">
                            <?php if($lelang['status'] == 'dibuka'): ?>
                                <span class="px-4 py-2 bg-coffee text-biscuit rounded-full font-semibold flex items-center shadow-lg">
                                    <span class="w-2 h-2 bg-biscuit rounded-full mr-2 animate-pulse"></span>
                                    Sedang Berlangsung
                                </span>
                            <?php else: ?>
                                <span class="px-4 py-2 bg-gray-200 text-gray-700 rounded-full font-semibold flex items-center shadow-lg">
                                    <i class="fas fa-times-circle mr-2"></i>
                                    Ditutup
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- User Bid Status -->
                        <?php if($user_bid): ?>
                        <div class="absolute top-4 left-4">
                            <span class="px-4 py-2 bg-biscuit text-coffee rounded-full font-semibold shadow-lg border border-coffee">
                                <i class="fas fa-check-circle mr-2"></i>
                                Anda menawar Rp <?php echo number_format($user_bid['penawaran_harga'], 0, ',', '.'); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-8">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h1 class="text-3xl font-bold text-coffee font-serif mb-2"><?php echo htmlspecialchars($lelang['nama_barang']); ?></h1>
                                <p class="text-gray-600 flex items-center">
                                    <i class="fas fa-calendar mr-2 text-coffee"></i>
                                    Tanggal Lelang: <?php echo formatTanggal($lelang['tgl_lelang']); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Price Info Cards -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-biscuit-light rounded-lg p-4 border border-biscuit">
                                <p class="text-sm text-coffee mb-1">Harga Awal</p>
                                <p class="text-2xl font-bold text-coffee"><?php echo formatRupiah($lelang['harga_awal']); ?></p>
                            </div>
                            <div class="bg-coffee-light bg-opacity-10 rounded-lg p-4 border border-coffee-light">
                                <p class="text-sm text-coffee-light mb-1">Harga Saat Ini</p>
                                <p class="text-2xl font-bold text-coffee-light"><?php echo formatRupiah($lelang['harga_akhir']); ?></p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <h3 class="text-xl font-bold text-coffee font-serif mb-4 flex items-center">
                                <i class="fas fa-align-left text-coffee mr-2"></i>
                                Deskripsi Barang
                            </h3>
                            <div class="prose max-w-none">
                                <p class="text-gray-600 leading-relaxed whitespace-pre-line">
                                    <?php echo nl2br(htmlspecialchars($lelang['deskripsi_barang'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leaderboard Section -->
                <div class="bg-white rounded-xl shadow-warm p-6 mt-6 border border-biscuit">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-coffee font-serif flex items-center">
                            <i class="fas fa-list-ol mr-2"></i>
                            Leaderboard Penawaran
                        </h2>
                        <span class="text-sm text-gray-500">
                            Total <?php echo mysqli_num_rows($history); ?> penawaran
                        </span>
                    </div>

                    <?php if(mysqli_num_rows($history) > 0): ?>
                    <div class="space-y-3">
                        <?php 
                        $rank = 1;
                        while($h = mysqli_fetch_assoc($history)): 
                            $is_current_user = ($h['id_user'] == $id_user);
                        ?>
                        <div class="flex items-center justify-between p-4 <?php echo $is_current_user ? 'bg-biscuit border-2 border-coffee' : 'bg-biscuit-light hover:bg-biscuit'; ?> rounded-lg transition duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold
                                    <?php echo $rank == 1 ? 'bg-coffee text-biscuit' : 
                                             ($rank == 2 ? 'bg-coffee-light text-biscuit' : 
                                             ($rank == 3 ? 'bg-coffee-dark text-biscuit' : 'bg-biscuit-dark text-coffee')); ?>">
                                    <?php echo $rank; ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-coffee">
                                        <?php echo htmlspecialchars($h['nama_lengkap']); ?>
                                        <?php if($is_current_user): ?>
                                            <span class="ml-2 text-xs bg-coffee text-biscuit px-2 py-0.5 rounded-full">Anda</span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <i class="fas fa-clock mr-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold <?php echo $is_current_user ? 'text-coffee' : 'text-coffee-light'; ?>">
                                    <?php echo formatRupiah($h['penawaran_harga']); ?>
                                </p>
                                <?php if($rank == 1 && $lelang['status'] == 'dibuka'): ?>
                                    <p class="text-xs text-coffee font-semibold">Memimpin</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php 
                        $rank++;
                        endwhile; 
                        ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-inbox text-biscuit text-5xl mb-3"></i>
                        <p class="text-gray-500">Belum ada penawaran untuk barang ini</p>
                        <p class="text-sm text-gray-400 mt-1">Jadilah yang pertama menawar!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column - Penawaran Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-warm p-6 sticky top-8 border border-biscuit">
                    <h2 class="text-2xl font-bold text-coffee font-serif mb-6 flex items-center">
                        <i class="fas fa-gavel mr-2"></i>
                        Buat Penawaran
                    </h2>

                    <?php if($lelang['status'] == 'dibuka'): ?>
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="id_barang" value="<?php echo $lelang['id_barang']; ?>">
                        
                        <!-- Minimum Price Info -->
                        <div class="bg-coffee rounded-lg p-5">
                            <p class="text-sm text-biscuit mb-2">Harga Minimum Penawaran</p>
                            <p class="text-3xl font-bold text-biscuit">
                                <?php echo formatRupiah($lelang['harga_akhir'] + 50000); ?>
                            </p>
                            <p class="text-xs text-biscuit opacity-80 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Kelipatan Rp 50.000
                            </p>
                        </div>

                        <!-- Input Field -->
                        <div>
                            <label class="block text-coffee font-semibold mb-3">
                                <i class="fas fa-money-bill-wave mr-2 text-coffee"></i>
                                Masukkan Nominal
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-3 text-coffee">Rp</span>
                                <input 
                                    type="text" 
                                    id="rupiah" 
                                    class="w-full pl-12 pr-4 py-3 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee text-lg font-semibold"
                                    placeholder="0"
                                    onkeyup="formatRupiah(this)"
                                >
                                <input type="hidden" name="penawaran_harga" id="penawaran_harga">
                            </div>
                        </div>

                        <!-- Quick Bid Options -->
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" onclick="setBid(50000)" class="px-3 py-2 bg-biscuit hover:bg-biscuit-dark rounded-lg text-sm font-semibold text-coffee transition">
                                + Rp 50rb
                            </button>
                            <button type="button" onclick="setBid(100000)" class="px-3 py-2 bg-biscuit hover:bg-biscuit-dark rounded-lg text-sm font-semibold text-coffee transition">
                                + Rp 100rb
                            </button>
                            <button type="button" onclick="setBid(250000)" class="px-3 py-2 bg-biscuit hover:bg-biscuit-dark rounded-lg text-sm font-semibold text-coffee transition">
                                + Rp 250rb
                            </button>
                            <button type="button" onclick="setBid(500000)" class="px-3 py-2 bg-biscuit hover:bg-biscuit-dark rounded-lg text-sm font-semibold text-coffee transition">
                                + Rp 500rb
                            </button>
                        </div>

                        <button 
                            type="submit" 
                            name="submit_penawaran"
                            class="w-full bg-coffee hover:bg-coffee-dark text-biscuit font-bold py-4 rounded-lg shadow-warm transform transition duration-200 hover:scale-105">
                            <i class="fas fa-hand-holding-usd mr-2"></i>
                            Ajukan Penawaran
                        </button>
                    </form>

                    <script>
                        function formatRupiah(input) {
                            let value = input.value.replace(/[^,\d]/g, '').toString();
                            let split = value.split(',');
                            let sisa = split[0].length % 3;
                            let rupiah = split[0].substr(0, sisa);
                            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
                            
                            if (ribuan) {
                                let separator = sisa ? '.' : '';
                                rupiah += separator + ribuan.join('.');
                            }
                            
                            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                            input.value = rupiah;
                            
                            // Set hidden input
                            let numericValue = value.replace(/\./g, '');
                            document.getElementById('penawaran_harga').value = numericValue;
                        }

                        function setBid(increment) {
                            let currentBid = <?php echo $lelang['harga_akhir'] + 50000; ?>;
                            let input = document.getElementById('rupiah');
                            let newBid = currentBid + increment;
                            
                            // Format and set
                            input.value = newBid.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            document.getElementById('penawaran_harga').value = newBid;
                        }

                        // Set initial value
                        window.onload = function() {
                            let initialValue = <?php echo $lelang['harga_akhir'] + 50000; ?>;
                            document.getElementById('rupiah').value = initialValue.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            document.getElementById('penawaran_harga').value = initialValue;
                        }
                    </script>
                    <?php else: ?>
                    <div class="text-center py-8">
                        <div class="mb-4">
                            <i class="fas fa-lock text-biscuit text-6xl"></i>
                        </div>
                        <p class="text-gray-500 font-semibold text-lg">Lelang Telah Ditutup</p>
                        <p class="text-sm text-gray-400 mt-2">Tidak dapat mengajukan penawaran</p>
                        
                        <?php 
                        // Cek apakah user adalah pemenang
                        $winner_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tb_lelang 
                                                                                WHERE id_lelang = $id_lelang 
                                                                                AND id_user = $id_user"));
                        if($winner_check): 
                        ?>
                        <div class="mt-6 p-4 bg-biscuit rounded-lg border border-coffee">
                            <i class="fas fa-trophy text-coffee text-2xl mb-2"></i>
                            <p class="text-coffee font-semibold">Selamat! Anda pemenangnya</p>
                            <a href="pembayaran.php" class="mt-3 inline-block bg-coffee hover:bg-coffee-dark text-biscuit px-4 py-2 rounded-lg text-sm font-semibold transition">
                                <i class="fas fa-credit-card mr-2"></i>Lanjut ke Pembayaran
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Important Info -->
                    <div class="mt-6 pt-6 border-t border-biscuit">
                        <h3 class="font-bold text-coffee mb-4 flex items-center">
                            <i class="fas fa-info-circle text-coffee mr-2"></i>
                            Informasi Penting
                        </h3>
                        <ul class="space-y-3 text-sm">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-coffee-light mr-2 mt-0.5"></i>
                                <span class="text-gray-600">Penawaran bersifat mengikat dan tidak dapat dibatalkan</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-coffee-light mr-2 mt-0.5"></i>
                                <span class="text-gray-600">Pemenang wajib melakukan pembayaran dalam 3x24 jam</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-coffee-light mr-2 mt-0.5"></i>
                                <span class="text-gray-600">Pastikan dana tersedia sebelum melakukan penawaran</span>
                            </li>
                        </ul>
                    </div>
                </div>
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
