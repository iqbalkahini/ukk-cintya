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

checkLevel([2]); // Hanya petugas

// HANDLE TAMBAH BARANG
if(isset($_POST['tambah'])) {
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi_barang']);
    $harga_awal = str_replace('.', '', $_POST['harga_awal']); // Hapus titik dari format rupiah
    $tgl = mysqli_real_escape_string($conn, $_POST['tgl']);
    
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

// Get all barang
$barang = mysqli_query($conn, "SELECT * FROM tb_barang ORDER BY id_barang DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang - Petugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... (style sama seperti sebelumnya, biarkan sesuai) ... */
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap');
        * {
            font-family: 'Poppins', sans-serif;
        }
        h1, h2, h3, h4, h5, h6, .font-serif {
            font-family: 'Playfair Display', serif;
        }
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
        .barang-image { width: 100%; height: 100%; object-fit: cover; }
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
                    <a href="data_barang.php" class="flex items-center px-4 py-3 bg-biscuit text-coffee rounded-lg shadow-sm border border-biscuit-dark">
                        <i class="fas fa-box w-5"></i><span class="ml-3">Data Barang</span>
                    </a>
                    <a href="kelola_lelang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
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
            <!-- Alert Messages -->
            <?php if(isset($_SESSION['success'])): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-700 mr-2"></i>
                    <span><?php echo $_SESSION['success']; ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-700"><i class="fas fa-times"></i></button>
            </div>
            <?php unset($_SESSION['success']); endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-700 mr-2"></i>
                    <span><?php echo $_SESSION['error']; ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-700"><i class="fas fa-times"></i></button>
            </div>
            <?php unset($_SESSION['error']); endif; ?>

            <div class="mb-8">
                <h1 class="text-3xl font-bold text-coffee font-serif">Data Barang</h1>
                <p class="text-gray-600 mt-2">Kelola data barang (tambah, lihat)</p>
            </div>

            <!-- Form Tambah Barang -->
            <div class="bg-white rounded-xl shadow-warm p-6 mb-8 border border-biscuit">
                <h2 class="text-xl font-bold text-coffee font-serif mb-4">
                    <i class="fas fa-plus-circle mr-2"></i>Tambah Barang Baru
                </h2>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nama Barang -->
                        <div>
                            <label class="block text-coffee font-semibold mb-2"><i class="fas fa-tag mr-1"></i>Nama Barang <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_barang" required
                                   class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white"
                                   placeholder="Masukkan nama barang">
                        </div>

                        <!-- Tanggal -->
                        <div>
                            <label class="block text-coffee font-semibold mb-2"><i class="fas fa-calendar mr-1"></i>Tanggal Input <span class="text-red-500">*</span></label>
                            <input type="date" name="tgl" required
                                   value="<?php echo date('Y-m-d'); ?>"
                                   class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white">
                        </div>

                        <!-- Harga Awal -->
                        <div>
                            <label class="block text-coffee font-semibold mb-2"><i class="fas fa-money-bill-wave mr-1"></i>Harga Awal <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-coffee">Rp</span>
                                <input type="text" id="harga_awal" name="harga_awal_display" required
                                       onkeyup="formatRupiah(this)"
                                       class="w-full pl-12 pr-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white"
                                       placeholder="0">
                                <input type="hidden" name="harga_awal" id="harga_awal_hidden">
                            </div>
                        </div>

                        <!-- Gambar -->
                        <div>
                            <label class="block text-coffee font-semibold mb-2"><i class="fas fa-image mr-1"></i>Gambar Barang</label>
                            <div class="border-2 border-dashed border-biscuit rounded-lg p-4 text-center hover:border-coffee transition bg-white">
                                <input type="file" name="gambar" id="gambar" accept="image/*" class="hidden" onchange="previewImage(this)">
                                <label for="gambar" class="cursor-pointer block">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-coffee-light mb-2"></i>
                                    <p class="text-coffee">Klik untuk upload gambar</p>
                                    <p class="text-xs text-gray-500 mt-1">Format: JPG, JPEG, PNG, GIF (Max 2MB)</p>
                                </label>
                            </div>
                            <div id="preview-container" class="mt-4 hidden">
                                <p class="text-sm text-coffee mb-2">Preview:</p>
                                <img id="preview-image" class="max-h-40 rounded-lg border border-biscuit mx-auto object-contain" alt="Preview">
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="md:col-span-2">
                            <label class="block text-coffee font-semibold mb-2"><i class="fas fa-align-left mr-1"></i>Deskripsi Barang <span class="text-red-500">*</span></label>
                            <textarea name="deskripsi_barang" rows="4" required
                                      class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white"
                                      placeholder="Masukkan deskripsi barang..."></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="submit" name="tambah"
                                class="bg-coffee hover:bg-coffee-dark text-biscuit font-semibold px-8 py-3 rounded-lg transition duration-200 shadow-warm flex items-center">
                            <i class="fas fa-plus-circle mr-2"></i>Tambah Barang
                        </button>
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

            <!-- Tabel Barang -->
            <div class="bg-white rounded-xl shadow-warm overflow-hidden border border-biscuit">
                <div class="p-6 border-b border-biscuit bg-biscuit">
                    <h2 class="text-xl font-bold text-coffee font-serif">Daftar Barang</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-biscuit-light">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Gambar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Nama Barang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Harga Awal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Deskripsi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Status</th>
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
                                        if(!empty($row['gambar']) && file_exists('../uploads/barang/' . $row['gambar'])): 
                                        ?>
                                            <img src="../uploads/barang/<?php echo $row['gambar']; ?>" 
                                                 alt="<?php echo htmlspecialchars($row['nama_barang']); ?>" 
                                                 class="w-full h-full object-cover">
                                        <?php 
                                        elseif(!empty($row['gambar']) && file_exists('../uploads/' . $row['gambar'])): 
                                        ?>
                                            <img src="../uploads/<?php echo $row['gambar']; ?>" 
                                                 alt="<?php echo htmlspecialchars($row['nama_barang']); ?>" 
                                                 class="w-full h-full object-cover">
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
                                <td class="px-6 py-4 text-gray-600 max-w-xs truncate"><?php echo htmlspecialchars(substr($row['deskripsi_barang'], 0, 50) . '...'); ?></td>
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
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if(mysqli_num_rows($barang) == 0): ?>
                <div class="p-12 text-center">
                    <i class="fas fa-inbox text-biscuit text-6xl mb-4"></i>
                    <h3 class="text-xl font-bold text-coffee font-serif mb-2">Belum Ada Barang</h3>
                    <p class="text-gray-500">Tidak ada data barang.</p>
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
    </script>
</body>
</html>