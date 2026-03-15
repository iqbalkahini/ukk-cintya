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

// HANDLE TAMBAH BARANG
if(isset($_POST['tambah'])) {
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi_barang']);
    $harga_awal = str_replace('.', '', $_POST['harga_awal']); // Hapus titik dari format rupiah
    $tgl = $_POST['tgl'];
    
    // Upload gambar
    $gambar = '';
    if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../uploads/barang/";
        
        // Buat folder jika belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Validasi tipe file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if(in_array($imageFileType, $allowed_types)) {
            if(move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                $gambar = $file_name;
            }
        }
    }
    
    $query = "INSERT INTO tb_barang (nama_barang, deskripsi_barang, harga_awal, tgl, gambar, status_barang) 
              VALUES ('$nama_barang', '$deskripsi', '$harga_awal', '$tgl', '$gambar', 'pending')";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Barang berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan barang: " . mysqli_error($conn);
    }
    
    header('Location: data_barang.php');
    exit;
}

// HANDLE EDIT BARANG
if(isset($_POST['edit'])) {
    $id_barang = $_POST['id_barang'];
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi_barang']);
    $harga_awal = str_replace('.', '', $_POST['harga_awal']); // Hapus titik dari format rupiah
    $tgl = $_POST['tgl'];
    $status_barang = $_POST['status_barang'];
    
    // Cek apakah upload gambar baru
    $gambar_query = "";
    if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../uploads/barang/";
        
        // Buat folder jika belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Validasi tipe file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if(in_array($imageFileType, $allowed_types)) {
            if(move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                // Hapus gambar lama jika ada
                $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT gambar FROM tb_barang WHERE id_barang = $id_barang"));
                if($old['gambar'] && file_exists('../uploads/barang/' . $old['gambar'])) {
                    unlink('../uploads/barang/' . $old['gambar']);
                }
                $gambar_query = ", gambar = '$file_name'";
            }
        }
    }
    
    $query = "UPDATE tb_barang SET 
              nama_barang = '$nama_barang',
              deskripsi_barang = '$deskripsi',
              harga_awal = '$harga_awal',
              tgl = '$tgl',
              status_barang = '$status_barang'
              $gambar_query
              WHERE id_barang = $id_barang";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Barang berhasil diupdate!";
    } else {
        $_SESSION['error'] = "Gagal mengupdate barang: " . mysqli_error($conn);
    }
    
    header('Location: data_barang.php');
    exit;
}

// HANDLE HAPUS BARANG
if(isset($_GET['delete'])) {
    $id_barang = $_GET['delete'];
    
    // Cek apakah barang sedang digunakan di lelang
    $check = mysqli_query($conn, "SELECT * FROM tb_lelang WHERE id_barang = $id_barang");
    if(mysqli_num_rows($check) > 0) {
        $_SESSION['error'] = "Tidak dapat menghapus barang karena sudah digunakan di lelang!";
        header('Location: data_barang.php');
        exit;
    }
    
    // Hapus gambar jika ada
    $gambar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT gambar FROM tb_barang WHERE id_barang = $id_barang"));
    if($gambar['gambar'] && file_exists('../uploads/barang/' . $gambar['gambar'])) {
        unlink('../uploads/barang/' . $gambar['gambar']);
    }
    
    $query = "DELETE FROM tb_barang WHERE id_barang = $id_barang";
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Barang berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus barang: " . mysqli_error($conn);
    }
    
    header('Location: data_barang.php');
    exit;
}

// Get data untuk edit
$edit_data = null;
if(isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tb_barang WHERE id_barang = $id"));
}

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
    <title>Data Barang - Admin</title>
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
                    <a href="total_barang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-box w-5"></i>
                        <span class="ml-3">Total Barang</span>
                    </a>
                    <a href="data_barang.php" class="flex items-center px-4 py-3 bg-biscuit text-coffee rounded-lg shadow-sm border border-biscuit-dark">
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
            <!-- Alert Messages -->
            <?php if(isset($_SESSION['success'])): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-700 mr-2"></i>
                    <span><?php echo $_SESSION['success']; ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-700 mr-2"></i>
                    <span><?php echo $_SESSION['error']; ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="mb-8">
                <h1 class="text-3xl font-bold text-coffee font-serif">Data Barang</h1>
                <p class="text-gray-600 mt-2">Kelola semua data barang dalam sistem</p>
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

            <!-- Form Tambah/Edit Barang -->
            <div class="bg-white rounded-xl shadow-warm p-6 mb-8 border border-biscuit">
                <h2 class="text-xl font-bold text-coffee font-serif mb-4">
                    <i class="fas <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?> mr-2"></i>
                    <?php echo $edit_data ? 'Edit Barang' : 'Tambah Barang Baru'; ?>
                </h2>
                
                <form method="POST" enctype="multipart/form-data">
                    <?php if($edit_data): ?>
                    <input type="hidden" name="id_barang" value="<?php echo $edit_data['id_barang']; ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nama Barang -->
                        <div>
                            <label class="block text-coffee font-semibold mb-2">
                                <i class="fas fa-tag mr-1"></i>Nama Barang<span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_barang" required
                                   value="<?php echo $edit_data['nama_barang'] ?? ''; ?>"
                                   class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white"
                                   placeholder="Masukkan nama barang">
                        </div>

                        <!-- Tanggal -->
                        <div>
                            <label class="block text-coffee font-semibold mb-2">
                                <i class="fas fa-calendar mr-1"></i>Tanggal Input<span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tgl" required
                                   value="<?php echo $edit_data['tgl'] ?? date('Y-m-d'); ?>"
                                   class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white">
                        </div>

                        <!-- Harga Awal -->
                        <div>
                            <label class="block text-coffee font-semibold mb-2">
                                <i class="fas fa-money-bill-wave mr-1"></i>Harga Awal <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-coffee">Rp</span>
                                <input type="text" id="harga_awal" name="harga_awal_display" required
                                       value="<?php echo isset($edit_data['harga_awal']) ? number_format($edit_data['harga_awal'], 0, ',', '.') : ''; ?>"
                                       onkeyup="formatRupiah(this)"
                                       class="w-full pl-12 pr-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white"
                                       placeholder="0">
                                <input type="hidden" name="harga_awal" id="harga_awal_hidden" 
                                       value="<?php echo $edit_data['harga_awal'] ?? ''; ?>">
                            </div>
                        </div>

                        <!-- Status Barang (hanya untuk edit) -->
                        <?php if($edit_data): ?>
                        <div>
                            <label class="block text-coffee font-semibold mb-2">
                                <i class="fas fa-info-circle mr-1"></i>Status Barang
                            </label>
                            <select name="status_barang" class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white">
                                <option value="pending" <?php echo $edit_data['status_barang'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="dibuka" <?php echo $edit_data['status_barang'] == 'dibuka' ? 'selected' : ''; ?>>Dibuka</option>
                                <option value="ditutup" <?php echo $edit_data['status_barang'] == 'ditutup' ? 'selected' : ''; ?>>Ditutup</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <!-- Gambar -->
                        <div class="<?php echo $edit_data ? 'md:col-span-2' : 'md:col-span-1'; ?>">
                            <label class="block text-coffee font-semibold mb-2">
                                <i class="fas fa-image mr-1"></i>Gambar Barang
                            </label>
                            <div class="border-2 border-dashed border-biscuit rounded-lg p-4 text-center hover:border-coffee transition bg-white">
                                <input type="file" name="gambar" id="gambar" accept="image/*" class="hidden" onchange="previewImage(this)">
                                <label for="gambar" class="cursor-pointer block">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-coffee-light mb-2"></i>
                                    <p class="text-coffee">Klik untuk upload gambar</p>
                                    <p class="text-xs text-gray-500 mt-1">Format: JPG, JPEG, PNG, GIF (Max 2MB)</p>
                                </label>
                            </div>
                            <div id="preview-container" class="mt-4 <?php echo (!empty($edit_data['gambar'])) ? '' : 'hidden'; ?>">
                                <p class="text-sm text-coffee mb-2">Preview:</p>
                                <img id="preview-image" src="<?php echo (!empty($edit_data['gambar']) && file_exists('../uploads/barang/' . $edit_data['gambar'])) ? '../uploads/barang/' . $edit_data['gambar'] : ''; ?>" 
                                     class="max-h-40 rounded-lg border border-biscuit mx-auto object-contain" alt="Preview">
                            </div>
                            <?php if($edit_data && !empty($edit_data['gambar'])): ?>
                            <p class="text-sm text-coffee mt-2">
                                <i class="fas fa-info-circle mr-1"></i>Gambar saat ini: <?php echo $edit_data['gambar']; ?>
                            </p>
                            <?php endif; ?>
                        </div>

                        <!-- Deskripsi -->
                        <div class="md:col-span-2">
                            <label class="block text-coffee font-semibold mb-2">
                                <i class="fas fa-align-left mr-1"></i>Deskripsi Barang <span class="text-red-500">*</span>
                            </label>
                            <textarea name="deskripsi_barang" rows="4" required
                                      class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white"
                                      placeholder="Masukkan deskripsi barang..."><?php echo $edit_data['deskripsi_barang'] ?? ''; ?></textarea>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 mt-6">
                        <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>"
                                class="bg-coffee hover:bg-coffee-dark text-biscuit font-semibold px-8 py-3 rounded-lg transition duration-200 shadow-warm flex items-center">
                            <i class="fas <?php echo $edit_data ? 'fa-save' : 'fa-plus-circle'; ?> mr-2"></i>
                            <?php echo $edit_data ? 'Update Barang' : 'Tambah Barang'; ?>
                        </button>
                        <?php if($edit_data): ?>
                        <a href="data_barang.php" 
                           class="bg-gray-400 hover:bg-gray-500 text-white font-semibold px-8 py-3 rounded-lg transition duration-200 shadow-warm flex items-center">
                            <i class="fas fa-times mr-2"></i>Batal
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-xl shadow-warm p-6 mb-6 border border-biscuit">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1 relative">
                        <i class="fas fa-search absolute left-3 top-3 text-coffee-light"></i>
                        <input type="text" id="searchInput" placeholder="Cari nama barang..." 
                               class="w-full pl-10 pr-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white">
                    </div>
                    <select id="filterStatus" class="px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="dibuka">Dibuka</option>
                        <option value="ditutup">Ditutup</option>
                    </select>
                </div>
            </div>

            <!-- Table View -->
            <div class="bg-white rounded-xl shadow-warm overflow-hidden border border-biscuit">
                <div class="p-6 border-b border-biscuit bg-biscuit flex justify-between items-center">
                    <h2 class="text-xl font-bold text-coffee font-serif">Daftar Barang</h2>
                    <span class="bg-coffee text-biscuit px-4 py-2 rounded-lg text-sm font-semibold">
                        <i class="fas fa-box mr-2"></i>Total: <?php echo $total_barang; ?> Barang
                    </span>
                </div>
                
                <?php if($total_barang > 0): ?>
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
                            <?php 
                            $no = 1;
                            while($row = mysqli_fetch_assoc($barang)): 
                            ?>
                            <tr class="hover:bg-biscuit-light barang-row transition duration-200" data-status="<?php echo $row['status_barang']; ?>">
                                <td class="px-6 py-4 text-coffee"><?php echo $no++; ?></td>
                                <td class="px-6 py-4">
                                    <div class="w-16 h-16 bg-biscuit rounded-lg flex items-center justify-center overflow-hidden border border-biscuit-dark">
                                        <?php 
                                        // Cek gambar di folder uploads/barang/
                                        if(!empty($row['gambar']) && file_exists('../uploads/barang/' . $row['gambar'])): 
                                        ?>
                                            <img src="../uploads/barang/<?php echo $row['gambar']; ?>" 
                                                 alt="<?php echo $row['nama_barang']; ?>" 
                                                 class="w-full h-full object-cover">
                                        <?php 
                                        // Cek gambar di folder uploads/ (folder lama)
                                        elseif(!empty($row['gambar']) && file_exists('../uploads/' . $row['gambar'])): 
                                        ?>
                                            <img src="../uploads/<?php echo $row['gambar']; ?>" 
                                                 alt="<?php echo $row['nama_barang']; ?>" 
                                                 class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <i class="fas fa-image text-coffee text-2xl"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 barang-name">
                                    <div class="font-medium text-coffee"><?php echo $row['nama_barang']; ?></div>
                                    <div class="text-xs text-gray-500 mt-1">ID: #<?php echo $row['id_barang']; ?></div>
                                </td>
                                <td class="px-6 py-4 text-gray-600"><?php echo formatTanggal($row['tgl']); ?></td>
                                <td class="px-6 py-4 text-coffee-light font-semibold"><?php echo formatRupiah($row['harga_awal']); ?></td>
                                <td class="px-6 py-4">
                                    <?php if($row['status_barang'] == 'dibuka'): ?>
                                        <span class="px-3 py-1 bg-biscuit text-coffee rounded-full text-xs font-semibold border border-biscuit-dark">
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
                                </td>
                                <td class="px-6 py-4 text-center text-coffee"><?php echo $row['jumlah_lelang']; ?></td>
                                <td class="px-6 py-4 text-center text-coffee-light"><?php echo $row['lelang_aktif']; ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-3">
                                        <a href="?edit=<?php echo $row['id_barang']; ?>" 
                                           class="text-coffee hover:text-coffee-light transition duration-200" title="Edit">
                                            <i class="fas fa-edit text-lg"></i>
                                        </a>
                                        <a href="?delete=<?php echo $row['id_barang']; ?>" 
                                           onclick="return confirm('Yakin ingin menghapus barang <?php echo $row['nama_barang']; ?>?')"
                                           class="text-red-600 hover:text-red-800 transition duration-200" title="Hapus">
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
                    <i class="fas fa-inbox text-biscuit text-6xl mb-4"></i>
                    <h3 class="text-xl font-bold text-coffee font-serif mb-2">Belum Ada Barang</h3>
                    <p class="text-gray-500 mb-6">Mulai tambahkan barang untuk dilelang</p>
                    <button onclick="document.getElementById('tambah-barang').scrollIntoView({behavior: 'smooth'})" 
                            class="inline-block bg-coffee hover:bg-coffee-dark text-biscuit px-6 py-3 rounded-lg font-semibold shadow-warm">
                        <i class="fas fa-plus mr-2"></i>Tambah Barang
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Format Rupiah untuk input
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
            document.getElementById('harga_awal_hidden').value = numericValue;
        }

        // Preview Image
        function previewImage(input) {
            const file = input.files[0];
            const preview = document.getElementById('preview-image');
            const container = document.getElementById('preview-container');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    container.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            filterTable();
        });

        // Filter by status
        document.getElementById('filterStatus').addEventListener('change', function() {
            filterTable();
        });

        function filterTable() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const statusValue = document.getElementById('filterStatus').value;
            const rows = document.querySelectorAll('.barang-row');

            rows.forEach(row => {
                const barangName = row.querySelector('.barang-name').textContent.toLowerCase();
                const status = row.getAttribute('data-status');

                const matchSearch = barangName.includes(searchValue);
                const matchStatus = statusValue === '' || status === statusValue;

                if (matchSearch && matchStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Set hidden input value on page load
        window.onload = function() {
            const hargaDisplay = document.getElementById('harga_awal');
            if (hargaDisplay && hargaDisplay.value) {
                let value = hargaDisplay.value.replace(/\./g, '');
                document.getElementById('harga_awal_hidden').value = value;
            }
        }
    </script>
</body>
</html>