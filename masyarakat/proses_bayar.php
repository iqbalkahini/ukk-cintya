<?php
session_start();

// Error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek apakah file config ada
if (!file_exists('../config/config.php')) {
    die("Error: File config.php tidak ditemukan. Pastikan path benar.");
}

require_once('../config/config.php');

// Fungsi helper untuk format rupiah
if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

// Fungsi helper untuk format tanggal
if (!function_exists('formatTanggal')) {
    function formatTanggal($tanggal) {
        $bulan = array(
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        );
        $pecah = explode('-', date('Y-m-d', strtotime($tanggal)));
        return $pecah[2] . ' ' . $bulan[(int)$pecah[1]] . ' ' . $pecah[0];
    }
}

// Fungsi checkLevel
if (!function_exists('checkLevel')) {
    function checkLevel($allowed_levels = []) {
        if (!isset($_SESSION['level'])) {
            header('Location: ../auth/login.php');
            exit();
        }
        
        if (!in_array($_SESSION['level'], $allowed_levels)) {
            header('Location: ../auth/login.php');
            exit();
        }
    }
}

// Cek level user (3 = masyarakat)
checkLevel([3]);

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    header('Location: ../auth/login.php');
    exit();
}

$id_user = $_SESSION['id_user'];

// Cek apakah ada ID lelang
if (!isset($_GET['id'])) {
    header('Location: pembayaran.php');
    exit();
}

$id_lelang = mysqli_real_escape_string($conn, $_GET['id']);

// Get data lelang
$query = "SELECT l.*, b.nama_barang, b.deskripsi_barang, b.harga_awal
          FROM tb_lelang l
          JOIN tb_barang b ON l.id_barang = b.id_barang
          WHERE l.id_lelang = '$id_lelang' AND l.id_user = '$id_user' AND l.status = 'ditutup'";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>alert('Lelang tidak ditemukan!'); window.location='pembayaran.php';</script>";
    exit();
}

$lelang = mysqli_fetch_assoc($result);

// Cek apakah sudah ada pembayaran
$cek_bayar = mysqli_query($conn, "SELECT * FROM tb_pembayaran WHERE id_lelang = '$id_lelang' AND id_user = '$id_user'");
$pembayaran_ada = mysqli_fetch_assoc($cek_bayar);

// Variable untuk menyimpan status upload
$upload_success = false;
$upload_message = '';

// Proses submit pembayaran
if (isset($_POST['submit_bayar'])) {
    $metode_bayar = mysqli_real_escape_string($conn, $_POST['metode_bayar']);
    
    // Upload bukti pembayaran
    $target_dir = "../uploads/bukti_bayar/";
    
    // Buat folder jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = time() . '_' . basename($_FILES["bukti_bayar"]["name"]);
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Cek apakah file adalah gambar
    if(isset($_POST["submit_bayar"])) {
        $check = getimagesize($_FILES["bukti_bayar"]["tmp_name"]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            $upload_message = 'File bukan gambar!';
            $uploadOk = 0;
        }
    }
    
    // Cek ukuran file (max 5MB)
    if ($_FILES["bukti_bayar"]["size"] > 5000000) {
        $upload_message = 'File terlalu besar! Maksimal 5MB';
        $uploadOk = 0;
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        $upload_message = 'Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan!';
        $uploadOk = 0;
    }
    
    // Cek jika uploadOk = 0
    if ($uploadOk == 0) {
        // Error sudah diset di $upload_message
    } else {
        if (move_uploaded_file($_FILES["bukti_bayar"]["tmp_name"], $target_file)) {
            // Insert ke database
            if ($pembayaran_ada) {
                // Update pembayaran yang sudah ada - sesuai struktur tabel
                $update = mysqli_query($conn, "UPDATE tb_pembayaran SET 
                                              jumlah = '{$lelang['harga_akhir']}',
                                              metode_pembayaran = '$metode_bayar',
                                              bukti_pembayaran = '$file_name',
                                              status_pembayaran = 'dibayar'
                                              WHERE id_pembayaran = '{$pembayaran_ada['id_pembayaran']}'");
                
                if ($update) {
                    $upload_success = true;
                    $upload_message = 'Pembayaran berhasil diupload! Menunggu konfirmasi admin.';
                } else {
                    $upload_message = 'Gagal update data pembayaran: ' . mysqli_error($conn);
                }
            } else {
                // Insert pembayaran baru - sesuai struktur tabel
                $insert = mysqli_query($conn, "INSERT INTO tb_pembayaran 
                                              (id_lelang, id_user, jumlah, metode_pembayaran, bukti_pembayaran, status_pembayaran) 
                                              VALUES 
                                              ('$id_lelang', '$id_user', '{$lelang['harga_akhir']}', '$metode_bayar', '$file_name', 'dibayar')");
                
                if ($insert) {
                    $upload_success = true;
                    $upload_message = 'Pembayaran berhasil diupload! Menunggu konfirmasi admin.';
                } else {
                    $upload_message = 'Gagal menyimpan data pembayaran: ' . mysqli_error($conn);
                }
            }
        } else {
            $upload_message = 'Terjadi kesalahan saat upload file!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Pembayaran - Sistem Lelang Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css-all.min.css">
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
                    <a href="barang_lelang.php" class="text-biscuit hover:text-biscuit-dark">Barang Lelang</a>
                    <a href="penawaran_saya.php" class="text-biscuit hover:text-biscuit-dark">Penawaran Saya</a>
                    <a href="pembayaran.php" class="text-biscuit hover:text-biscuit-dark font-semibold">Pembayaran</a>
                    <a href="../auth/logout.php" class="bg-biscuit hover:bg-biscuit-dark text-coffee px-4 py-2 rounded-lg transition duration-200 shadow-md">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-8">

        <?php if ($upload_success): ?>
        <!-- HALAMAN SUKSES PEMBAYARAN -->
        <div class="text-center">
            <!-- Icon Success -->
            <div class="mb-8">
                <div class="mx-auto w-24 h-24 bg-biscuit rounded-full flex items-center justify-center animate-bounce shadow-warm">
                    <i class="fas fa-check-circle text-6xl text-coffee"></i>
                </div>
            </div>

            <!-- Judul -->
            <h1 class="text-4xl font-bold text-coffee font-serif mb-4">
                Pembayaran Berhasil Dikirim!
            </h1>
            <p class="text-xl text-gray-600 mb-8">
                Bukti pembayaran Anda telah berhasil diupload dan sedang menunggu konfirmasi dari admin.
            </p>

            <!-- Info Detail -->
            <div class="bg-white rounded-xl shadow-warm p-8 mb-8 border border-biscuit">
                <div class="border-b border-biscuit pb-6 mb-6">
                    <h2 class="text-2xl font-bold text-coffee font-serif mb-2">Detail Pembayaran</h2>
                    <p class="text-gray-600">Berikut adalah ringkasan pembayaran Anda</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
                    <div class="bg-biscuit-light p-4 rounded-lg border border-biscuit">
                        <p class="text-sm text-coffee mb-1">Nama Barang</p>
                        <p class="font-bold text-coffee text-lg"><?php echo htmlspecialchars($lelang['nama_barang']); ?></p>
                    </div>
                    <div class="bg-biscuit-light p-4 rounded-lg border border-biscuit">
                        <p class="text-sm text-coffee mb-1">Tanggal Lelang</p>
                        <p class="font-bold text-coffee text-lg"><?php echo formatTanggal($lelang['tgl_lelang']); ?></p>
                    </div>
                    <div class="bg-biscuit-light p-4 rounded-lg border border-biscuit">
                        <p class="text-sm text-coffee mb-1">Total Pembayaran</p>
                        <p class="font-bold text-coffee-light text-2xl"><?php echo formatRupiah($lelang['harga_akhir']); ?></p>
                    </div>
                    <div class="bg-biscuit-light p-4 rounded-lg border border-biscuit">
                        <p class="text-sm text-coffee mb-1">Status</p>
                        <span class="inline-block px-4 py-2 bg-biscuit text-coffee rounded-full font-semibold border border-biscuit-dark">
                            <i class="fas fa-clock mr-2"></i>Menunggu Konfirmasi
                        </span>
                    </div>
                </div>
            </div>

            <!-- Timeline Proses -->
            <div class="bg-coffee border-l-4 border-coffee-light p-6 rounded-lg mb-8 text-left shadow-warm">
                <h3 class="font-bold text-biscuit mb-4 flex items-center">
                    <i class="fas fa-tasks mr-2"></i>
                    Proses Selanjutnya
                </h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-biscuit rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-check text-coffee text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-biscuit">Bukti Pembayaran Terkirim</p>
                            <p class="text-sm text-biscuit opacity-80">Bukti pembayaran Anda berhasil diupload ke sistem</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-biscuit rounded-full flex items-center justify-center mr-4 animate-pulse">
                            <i class="fas fa-clock text-coffee text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-biscuit">Menunggu Verifikasi Admin</p>
                            <p class="text-sm text-biscuit opacity-80">Admin akan memverifikasi pembayaran Anda (maksimal 1x24 jam)</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-biscuit opacity-50 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-box text-coffee text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-biscuit">Proses Pengiriman Barang</p>
                            <p class="text-sm text-biscuit opacity-80">Barang akan diproses dan dikirim setelah pembayaran dikonfirmasi</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="pembayaran.php" 
                   class="inline-flex items-center justify-center px-8 py-4 bg-coffee hover:bg-coffee-dark text-biscuit font-bold rounded-lg transition duration-200 shadow-warm">
                    <i class="fas fa-list mr-2"></i>Lihat Semua Pembayaran
                </a>
                <a href="dashboard.php" 
                   class="inline-flex items-center justify-center px-8 py-4 bg-coffee-light hover:bg-coffee-dark text-biscuit font-bold rounded-lg transition duration-200 shadow-warm">
                    <i class="fas fa-home mr-2"></i>Kembali ke Dashboard
                </a>
            </div>

            <!-- Contact Info -->
            <div class="mt-8 p-6 bg-biscuit rounded-lg border border-biscuit-dark">
                <p class="text-coffee mb-2">
                    <i class="fas fa-question-circle mr-2"></i>
                    <strong>Ada pertanyaan?</strong>
                </p>
                <p class="text-gray-600 text-sm">
                    Hubungi admin kami jika ada kendala atau pertanyaan terkait pembayaran Anda
                </p>
                <div class="mt-4 flex flex-col sm:flex-row gap-2 justify-center text-sm">
                    <span class="inline-flex items-center text-coffee">
                        <i class="fas fa-envelope mr-2"></i>admin@lelang.com
                    </span>
                    <span class="hidden sm:inline text-coffee">|</span>
                    <span class="inline-flex items-center text-coffee">
                        <i class="fas fa-phone mr-2"></i>(021) 123-4567
                    </span>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- HALAMAN FORM UPLOAD -->

        <!-- Alert Error jika ada -->
        <?php if (!empty($upload_message)): ?>
        <div class="bg-biscuit border-l-4 border-coffee p-4 mb-6 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-coffee text-2xl mr-3"></i>
                <p class="text-coffee font-semibold"><?php echo $upload_message; ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Breadcrumb -->
        <div class="mb-6">
            <a href="pembayaran.php" class="text-coffee hover:text-coffee-light">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Pembayaran
            </a>
        </div>

        <div class="mb-8">
            <h1 class="text-4xl font-bold text-coffee font-serif mb-2">
                <i class="fas fa-money-bill-wave mr-3"></i>Proses Pembayaran
            </h1>
            <p class="text-gray-600">Upload bukti pembayaran Anda</p>
        </div>

        <!-- Detail Lelang -->
        <div class="bg-white rounded-xl shadow-warm p-6 mb-6 border border-biscuit">
            <h2 class="text-xl font-bold text-coffee font-serif mb-4">
                <i class="fas fa-info-circle mr-2"></i>Detail Lelang
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-coffee">Nama Barang</p>
                    <p class="font-bold text-gray-800"><?php echo htmlspecialchars($lelang['nama_barang']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-coffee">Tanggal Lelang</p>
                    <p class="font-bold text-gray-800"><?php echo formatTanggal($lelang['tgl_lelang']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-coffee">Harga Awal</p>
                    <p class="font-bold text-gray-800"><?php echo formatRupiah($lelang['harga_awal']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-coffee">Total Pembayaran</p>
                    <p class="font-bold text-coffee-light text-2xl"><?php echo formatRupiah($lelang['harga_akhir']); ?></p>
                </div>
            </div>
        </div>

        <!-- Informasi Rekening -->
        <div class="bg-coffee border-l-4 border-coffee-light p-6 mb-6 rounded-lg shadow-warm">
            <h3 class="font-bold text-biscuit mb-3">
                <i class="fas fa-university mr-2"></i>Informasi Rekening Pembayaran
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white p-4 rounded-lg border border-biscuit">
                    <p class="font-bold text-coffee">Bank BCA</p>
                    <p class="text-sm text-gray-600">No. Rek: 1234567890</p>
                    <p class="text-sm text-gray-600">a.n. Sistem Lelang Online</p>
                </div>
                <div class="bg-white p-4 rounded-lg border border-biscuit">
                    <p class="font-bold text-coffee">Bank Mandiri</p>
                    <p class="text-sm text-gray-600">No. Rek: 0987654321</p>
                    <p class="text-sm text-gray-600">a.n. Sistem Lelang Online</p>
                </div>
                <div class="bg-white p-4 rounded-lg border border-biscuit">
                    <p class="font-bold text-coffee">Bank BNI</p>
                    <p class="text-sm text-gray-600">No. Rek: 5678901234</p>
                    <p class="text-sm text-gray-600">a.n. Sistem Lelang Online</p>
                </div>
                <div class="bg-white p-4 rounded-lg border border-biscuit">
                    <p class="font-bold text-coffee">E-Wallet</p>
                    <p class="text-sm text-gray-600">No. HP: 081234567890</p>
                    <p class="text-sm text-gray-600">a.n. Sistem Lelang Online</p>
                </div>
            </div>
        </div>

        <!-- Form Pembayaran -->
        <div class="bg-white rounded-xl shadow-warm p-6 border border-biscuit">
            <h2 class="text-xl font-bold text-coffee font-serif mb-6">
                <i class="fas fa-upload mr-2"></i>Upload Bukti Pembayaran
            </h2>

            <form method="POST" enctype="multipart/form-data">
                <div class="space-y-6">
                    <!-- Metode Pembayaran -->
                    <div>
                        <label class="block text-coffee font-semibold mb-2">
                            Metode Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <select name="metode_bayar" required 
                                class="w-full px-4 py-3 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee bg-white">
                            <option value="">-- Pilih Metode Pembayaran --</option>
                            <option value="Transfer Bank BCA">Transfer Bank BCA</option>
                            <option value="Transfer Bank Mandiri">Transfer Bank Mandiri</option>
                            <option value="Transfer Bank BNI">Transfer Bank BNI</option>
                            <option value="E-Wallet (OVO)">E-Wallet (OVO)</option>
                            <option value="E-Wallet (GoPay)">E-Wallet (GoPay)</option>
                            <option value="E-Wallet (Dana)">E-Wallet (Dana)</option>
                        </select>
                    </div>

                    <!-- Upload Bukti -->
                    <div>
                        <label class="block text-coffee font-semibold mb-2">
                            Bukti Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <div class="border-2 border-dashed border-biscuit rounded-lg p-6 text-center hover:border-coffee transition bg-white">
                            <input type="file" name="bukti_bayar" id="bukti_bayar" required accept="image/*"
                                   class="hidden" onchange="previewImage(event)">
                            <label for="bukti_bayar" class="cursor-pointer">
                                <i class="fas fa-cloud-upload-alt text-4xl text-coffee-light mb-3"></i>
                                <p class="text-coffee">Klik untuk upload gambar</p>
                                <p class="text-sm text-gray-500 mt-2">Format: JPG, PNG, GIF (Max 5MB)</p>
                            </label>
                        </div>
                        <div id="preview" class="mt-4 hidden">
                            <img id="preview-image" class="max-w-full h-64 rounded-lg mx-auto shadow-md border border-biscuit" alt="Preview">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex space-x-4">
                        <button type="submit" name="submit_bayar"
                                class="flex-1 bg-coffee hover:bg-coffee-dark text-biscuit font-bold py-3 px-6 rounded-lg transition duration-200 shadow-warm">
                            <i class="fas fa-paper-plane mr-2"></i>Kirim Bukti Pembayaran
                        </button>
                        <a href="pembayaran.php" 
                           class="flex-1 bg-coffee-light hover:bg-coffee-dark text-biscuit font-bold py-3 px-6 rounded-lg transition duration-200 shadow-warm text-center">
                            <i class="fas fa-times mr-2"></i>Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Panduan Pembayaran -->
        <div class="bg-biscuit border-l-4 border-coffee p-6 mt-6 rounded-lg">
            <h3 class="font-bold text-coffee mb-3">
                <i class="fas fa-exclamation-triangle mr-2"></i>Panduan Pembayaran
            </h3>
            <ul class="text-coffee space-y-2 text-sm">
                <li>1. Transfer sesuai dengan total pembayaran yang tertera</li>
                <li>2. Screenshot/foto bukti transfer yang jelas</li>
                <li>3. Upload bukti pembayaran melalui form di atas</li>
                <li>4. Admin akan mengkonfirmasi pembayaran Anda maksimal 1x24 jam</li>
                <li>5. Barang akan diproses pengiriman setelah pembayaran dikonfirmasi</li>
            </ul>
        </div>

        <?php endif; ?>
    </div>
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').classList.remove('hidden');
                    document.getElementById('preview-image').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>