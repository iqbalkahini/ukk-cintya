<?php
session_start();
require_once('../config/config.php');

// Fungsi helper
if(!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        if($angka === null || $angka === '') return 'Rp 0';
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

if(!function_exists('formatTanggal')) {
    function formatTanggal($tanggal) {
        if($tanggal === null || $tanggal === '') return '-';
        return date('d/m/Y', strtotime($tanggal));
    }
}

// Debug: Check if user is logged in
if(!isset($_SESSION['id_user'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Check level access
checkLevel([1, 2]); // Admin dan petugas

$is_admin = $_SESSION['id_level'] == 1;

// Get lelang ID from URL
$id_lelang = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Debug: Check if ID is valid
if($id_lelang == 0) {
    $_SESSION['error'] = "ID lelang tidak valid";
    header('Location: dashboard.php');
    exit;
}

// Get lelang details 
$query_lelang = "SELECT l.*, b.nama_barang, b.deskripsi_barang, b.harga_awal, b.gambar, 
                        b.tgl as tanggal_input_barang, b.status_barang, 
                        u.nama_lengkap as pemenang, 
                        p.nama_lengkap as petugas_nama,
                        p.username as petugas_username
                 FROM tb_lelang l 
                 JOIN tb_barang b ON l.id_barang = b.id_barang
                 LEFT JOIN tb_user u ON l.id_user = u.id_user
                 LEFT JOIN tb_user p ON l.id_petugas = p.id_user
                 WHERE l.id_lelang = $id_lelang";

$result_lelang = mysqli_query($conn, $query_lelang);

if(!$result_lelang) {
    die("Query Error: " . mysqli_error($conn));
}

$lelang = mysqli_fetch_assoc($result_lelang);

if(!$lelang) {
    $_SESSION['error'] = "Data lelang dengan ID $id_lelang tidak ditemukan";
    header('Location: dashboard.php');
    exit;
}

// Cek apakah tabel history ada
$history_available = false;
$history = null;
$history_error = "";

// Coba cek tabel history_lelang
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'tb_history_lelang'");
if(mysqli_num_rows($check_table) > 0) {
    // Tabel tb_history_lelang ada
    $history = mysqli_query($conn, "SELECT h.*, u.nama_lengkap
                                    FROM tb_history_lelang h
                                    JOIN tb_user u ON h.id_user = u.id_user
                                    WHERE h.id_lelang = $id_lelang
                                    ORDER BY h.penawaran_harga DESC, h.created_at DESC");
    
    if($history) {
        $history_available = true;
    } else {
        $history_error = mysqli_error($conn);
    }
} else {
    // Coba cek tabel history
    $check_table2 = mysqli_query($conn, "SHOW TABLES LIKE 'history_lelang'");
    if(mysqli_num_rows($check_table2) > 0) {
        $history = mysqli_query($conn, "SELECT h.*, u.nama_lengkap
                                        FROM history_lelang h
                                        JOIN tb_user u ON h.id_user = u.id_user
                                        WHERE h.id_lelang = $id_lelang
                                        ORDER BY h.penawaran_harga DESC, h.created_at DESC");
        if($history) {
            $history_available = true;
        } else {
            $history_error = mysqli_error($conn);
        }
    } else {
        // Coba cek di tb_lelang apakah ada kolom history
        $history_error = "Tabel history tidak ditemukan. Sistem mungkin menyimpan penawaran di tempat lain.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Lelang - <?php echo htmlspecialchars($lelang['nama_barang']); ?></title>
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
        .animate-pulse-slow {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
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
                        <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User'); ?>
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
                        <i class="fas fa-home w-5"></i>
                        <span class="ml-3">Beranda</span>
                    </a>
                    <a href="data_barang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
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

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <!-- Breadcrumb -->
            <div class="mb-6">
                <div class="flex items-center text-sm text-coffee-light">
                    <a href="dashboard.php" class="hover:text-coffee transition">
                        <i class="fas fa-home mr-2"></i>Beranda
                    </a>
                    <i class="fas fa-chevron-right mx-3 text-xs"></i>
                    <span class="text-coffee">Detail Lelang</span>
                </div>
                <h1 class="text-3xl font-bold text-coffee font-serif mt-2">Detail Lelang</h1>
            </div>

            <!-- Back Button -->
            <div class="mb-6">
                <a href="dashboard.php" class="inline-flex items-center bg-biscuit hover:bg-biscuit-dark text-coffee px-4 py-2 rounded-lg transition duration-200 shadow-sm border border-biscuit-dark">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Beranda
                </a>
            </div>

            <!-- Status Banner -->
            <div class="bg-white rounded-xl shadow-warm p-4 mb-6 border-l-4 <?php echo $lelang['status'] == 'dibuka' ? 'border-coffee-light' : 'border-amber-700'; ?>">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <?php if($lelang['status'] == 'dibuka'): ?>
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse-slow mr-3"></div>
                            <span class="font-semibold text-coffee">Status Lelang: <span class="text-coffee-light">DIBUKA - Sedang Berlangsung</span></span>
                        <?php else: ?>
                            <div class="w-3 h-3 bg-amber-700 rounded-full mr-3"></div>
                            <span class="font-semibold text-coffee">Status Lelang: <span class="text-amber-700">DITUTUP - Lelang Selesai</span></span>
                        <?php endif; ?>
                    </div>
                    <?php if($lelang['status'] == 'dibuka' && $is_admin): ?>
                        <a href="tutup_lelang.php?id=<?php echo $id_lelang; ?>" 
                           onclick="return confirm('Yakin ingin menutup lelang ini?')"
                           class="bg-coffee hover:bg-coffee-dark text-biscuit px-4 py-2 rounded-lg transition duration-200 text-sm shadow-sm">
                            <i class="fas fa-lock mr-2"></i>Tutup Lelang
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Error/Success Messages -->
            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                    <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if(!empty($history_error)): ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Info: <?php echo $history_error; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Barang Info -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-warm overflow-hidden border border-biscuit sticky top-8">
                        <!-- Image -->
                        <div class="h-64 bg-biscuit flex items-center justify-center relative overflow-hidden">
                            <?php 
                            // Cek gambar di folder uploads/barang/
                            if(!empty($lelang['gambar']) && file_exists('../uploads/barang/' . $lelang['gambar'])): 
                            ?>
                                <img src="../uploads/barang/<?php echo htmlspecialchars($lelang['gambar']); ?>" 
                                     alt="<?php echo htmlspecialchars($lelang['nama_barang']); ?>" 
                                     class="barang-image">
                            <?php 
                            // Cek gambar di folder uploads/ (folder lama)
                            elseif(!empty($lelang['gambar']) && file_exists('../uploads/' . $lelang['gambar'])): 
                            ?>
                                <img src="../uploads/<?php echo htmlspecialchars($lelang['gambar']); ?>" 
                                     alt="<?php echo htmlspecialchars($lelang['nama_barang']); ?>" 
                                     class="barang-image">
                            <?php else: ?>
                                <div class="text-center">
                                    <i class="fas fa-image text-coffee-light text-6xl mb-2"></i>
                                    <p class="text-coffee text-sm">Tidak ada gambar</p>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Status Badge -->
                            <div class="absolute top-4 right-4">
                                <?php if($lelang['status_barang'] == 'dibuka'): ?>
                                    <span class="px-3 py-1 bg-coffee text-biscuit rounded-full text-sm font-semibold shadow-lg">
                                        <i class="fas fa-circle text-xs mr-1 animate-pulse-slow"></i>Dibuka
                                    </span>
                                <?php elseif($lelang['status_barang'] == 'ditutup'): ?>
                                    <span class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-semibold shadow-lg">
                                        <i class="fas fa-times-circle mr-1"></i>Ditutup
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-semibold shadow-lg">
                                        <i class="fas fa-clock mr-1"></i>Pending
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-coffee font-serif mb-2"><?php echo htmlspecialchars($lelang['nama_barang']); ?></h2>
                            
                            <div class="space-y-4">
                                <!-- ID Barang -->
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-tag text-coffee-light w-6"></i>
                                    <span class="text-gray-600">ID Barang: <span class="font-semibold text-coffee">#<?php echo $lelang['id_barang']; ?></span></span>
                                </div>

                                <!-- Harga Awal -->
                                <div class="flex items-center">
                                    <i class="fas fa-money-bill-wave text-coffee-light w-6"></i>
                                    <div>
                                        <p class="text-sm text-gray-500">Harga Awal</p>
                                        <p class="text-xl font-bold text-coffee"><?php echo formatRupiah($lelang['harga_awal']); ?></p>
                                    </div>
                                </div>

                                <!-- Harga Akhir -->
                                <div class="flex items-center">
                                    <i class="fas fa-trophy text-coffee-light w-6"></i>
                                    <div>
                                        <p class="text-sm text-gray-500">Harga Akhir</p>
                                        <p class="text-2xl font-bold text-amber-700"><?php echo formatRupiah($lelang['harga_akhir']); ?></p>
                                    </div>
                                </div>

                                <!-- Tanggal Input Barang -->
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-calendar-plus text-coffee-light w-6"></i>
                                    <span class="text-gray-600">Tanggal Input: <?php echo formatTanggal($lelang['tanggal_input_barang']); ?></span>
                                </div>

                                <!-- Tanggal Lelang -->
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-calendar-alt text-coffee-light w-6"></i>
                                    <span class="text-gray-600">Tanggal Lelang: <?php echo formatTanggal($lelang['tgl_lelang']); ?></span>
                                </div>

                                <!-- Petugas -->
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-user-tie text-coffee-light w-6"></i>
                                    <span class="text-gray-600">Petugas: 
                                        <span class="font-semibold text-coffee">
                                            <?php 
                                            if(!empty($lelang['petugas_nama'])) {
                                                echo htmlspecialchars($lelang['petugas_nama']);
                                            } elseif(!empty($lelang['petugas_username'])) {
                                                echo htmlspecialchars($lelang['petugas_username']);
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </span>
                                    </span>
                                </div>

                                <!-- Pemenang -->
                                <?php if(!empty($lelang['pemenang'])): ?>
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-crown text-coffee-light w-6"></i>
                                    <span class="text-gray-600">Pemenang: 
                                        <span class="font-semibold text-coffee"><?php echo htmlspecialchars($lelang['pemenang']); ?></span>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Deskripsi -->
                            <div class="mt-6 pt-6 border-t border-biscuit">
                                <h3 class="font-bold text-coffee mb-3">Deskripsi Barang</h3>
                                <p class="text-gray-600 text-sm leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars($lelang['deskripsi_barang'] ?? 'Tidak ada deskripsi')); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Bidding History -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-warm overflow-hidden border border-biscuit">
                        <div class="p-6 border-b border-biscuit bg-biscuit">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-bold text-coffee font-serif">
                                    <i class="fas fa-history mr-2"></i>Riwayat Penawaran
                                </h2>
                                <span class="bg-coffee text-biscuit px-4 py-2 rounded-lg text-sm font-semibold">
                                    <i class="fas fa-users mr-2"></i><?php echo $history_available && $history ? mysqli_num_rows($history) : 0; ?> Penawaran
                                </span>
                            </div>
                        </div>

                        <?php if($history_available && $history && mysqli_num_rows($history) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-biscuit-light">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">No</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Nama Penawar</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Penawaran Harga</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-biscuit">
                                    <?php 
                                    $no = 1;
                                    $max_bid = 0;
                                    mysqli_data_seek($history, 0);
                                    while($row = mysqli_fetch_assoc($history)): 
                                        if($no == 1) $max_bid = $row['penawaran_harga'];
                                    ?>
                                    <tr class="hover:bg-biscuit-light transition duration-200 <?php echo $no == 1 && $lelang['status'] == 'ditutup' ? 'bg-biscuit bg-opacity-30' : ''; ?>">
                                        <td class="px-6 py-4 font-medium text-coffee"><?php echo $no++; ?></td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <?php if($no == 2 && $lelang['status'] == 'ditutup' && $row['penawaran_harga'] == $max_bid): ?>
                                                    <i class="fas fa-crown text-yellow-500 mr-2" title="Pemenang"></i>
                                                <?php endif; ?>
                                                <span class="font-medium text-coffee"><?php echo htmlspecialchars($row['nama_lengkap']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="<?php echo ($row['penawaran_harga'] == $max_bid && $lelang['status'] == 'ditutup') ? 'text-amber-700 font-bold text-lg' : 'text-coffee-light font-semibold'; ?>">
                                                <?php echo formatRupiah($row['penawaran_harga']); ?>
                                                <?php if($row['penawaran_harga'] == $max_bid && $lelang['status'] == 'ditutup'): ?>
                                                    <span class="ml-2 text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">Tertinggi</span>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 text-sm">
                                            <i class="far fa-clock mr-1"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Winner Summary if closed -->
                        <?php if($lelang['status'] == 'ditutup' && !empty($lelang['id_user'])): ?>
                        <div class="p-6 bg-biscuit-light border-t border-biscuit">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-coffee text-sm mb-1">Pemenang Lelang</p>
                                    <p class="text-2xl font-bold text-coffee font-serif"><?php echo htmlspecialchars($lelang['pemenang']); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-coffee text-sm mb-1">Harga Akhir</p>
                                    <p class="text-2xl font-bold text-amber-700"><?php echo formatRupiah($lelang['harga_akhir']); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php else: ?>
                        <div class="p-12 text-center">
                            <i class="fas fa-gavel text-biscuit text-6xl mb-4"></i>
                            <h3 class="text-xl font-bold text-coffee mb-2">Belum Ada Penawaran</h3>
                            <p class="text-gray-500">Belum ada penawar untuk lelang ini</p>
                            
                            <?php if(!$history_available): ?>
                            <p class="text-xs text-coffee-light mt-4">
                                <i class="fas fa-info-circle mr-1"></i>
                                Sistem sedang dalam mode terbatas (tabel history tidak tersedia)
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Additional Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <!-- Info Lelang -->
                        <div class="bg-white rounded-xl shadow-warm p-6 border border-biscuit">
                            <h3 class="font-bold text-coffee mb-4 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-coffee-light"></i>
                                Informasi Lelang
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">ID Lelang</span>
                                    <span class="font-semibold text-coffee">#<?php echo $lelang['id_lelang']; ?></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Dibuat Pada</span>
                                    <span class="font-semibold text-coffee"><?php echo date('d/m/Y H:i', strtotime($lelang['created_at'] ?? $lelang['tgl_lelang'])); ?></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Total Penawaran</span>
                                    <span class="font-semibold text-coffee"><?php echo $history_available && $history ? mysqli_num_rows($history) : 0; ?>x penawaran</span>
                                </div>
                                <?php if(!empty($lelang['id_user'])): ?>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">ID Pemenang</span>
                                    <span class="font-semibold text-coffee">#<?php echo $lelang['id_user']; ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white rounded-xl shadow-warm p-6 border border-biscuit">
                            <h3 class="font-bold text-coffee mb-4 flex items-center">
                                <i class="fas fa-bolt mr-2 text-coffee-light"></i>
                                Aksi Cepat
                            </h3>
                            <div class="space-y-3">
                                <a href="data_barang.php?edit=<?php echo $lelang['id_barang']; ?>" 
                                   class="block w-full bg-biscuit hover:bg-biscuit-dark text-coffee text-center py-2 rounded-lg transition duration-200 shadow-sm border border-biscuit-dark">
                                    <i class="fas fa-edit mr-2"></i>Edit Barang
                                </a>
                                <?php if($lelang['status'] == 'dibuka' && $is_admin): ?>
                                <a href="tutup_lelang.php?id=<?php echo $id_lelang; ?>" 
                                   onclick="return confirm('Yakin ingin menutup lelang ini?')"
                                   class="block w-full bg-coffee hover:bg-coffee-dark text-biscuit text-center py-2 rounded-lg transition duration-200 shadow-sm">
                                    <i class="fas fa-lock mr-2"></i>Tutup Lelang
                                </a>
                                <?php endif; ?>
                                <a href="laporan.php?dari=<?php echo date('Y-m-d', strtotime('-30 days')); ?>&sampai=<?php echo date('Y-m-d'); ?>" 
                                   class="block w-full bg-biscuit hover:bg-biscuit-dark text-coffee text-center py-2 rounded-lg transition duration-200 shadow-sm border border-biscuit-dark">
                                    <i class="fas fa-chart-line mr-2"></i>Lihat Laporan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
