<?php
session_start();
require_once('../config/config.php');
checkLevel([2]);

$id_barang = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$edit_data = null;

if ($id_barang > 0) {
    $result_edit = mysqli_query($conn, "SELECT * FROM tb_barang WHERE id_barang = $id_barang");
    $edit_data = $result_edit ? mysqli_fetch_assoc($result_edit) : null;

    if (!$edit_data) {
        $_SESSION['error'] = "Data barang tidak ditemukan.";
        header('Location: data_barang.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_post = isset($_POST['id_barang']) ? (int) $_POST['id_barang'] : 0;
    $is_edit = $id_post > 0;
    $nama_barang = mysqli_real_escape_string($conn, trim($_POST['nama_barang']));
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi_barang']));
    $harga_awal = (int) str_replace('.', '', $_POST['harga_awal']);
    $tgl = mysqli_real_escape_string($conn, $_POST['tgl']);
    $gambar_baru = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $target_dir = dirname(__DIR__) . '/uploads/barang/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extension, $allowed_types, true)) {
            $_SESSION['error'] = "Format gambar harus JPG, JPEG, PNG, atau GIF.";
            header('Location: ' . ($is_edit ? 'barang_form.php?id=' . $id_post : 'barang_form.php'));
            exit;
        }

        $gambar_baru = 'barang_' . time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['gambar']['name']));
        if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $target_dir . $gambar_baru)) {
            $_SESSION['error'] = "Gagal upload gambar barang.";
            header('Location: ' . ($is_edit ? 'barang_form.php?id=' . $id_post : 'barang_form.php'));
            exit;
        }
    }

    if ($is_edit) {
        $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT gambar FROM tb_barang WHERE id_barang = $id_post"));
        $gambar_query = '';
        if ($gambar_baru !== '') {
            $gambar_query = ", gambar = '" . mysqli_real_escape_string($conn, $gambar_baru) . "'";
        }

        $query = "UPDATE tb_barang SET
                    nama_barang = '$nama_barang',
                    deskripsi_barang = '$deskripsi',
                    harga_awal = '$harga_awal',
                    tgl = '$tgl'
                    $gambar_query
                  WHERE id_barang = $id_post";

        if (mysqli_query($conn, $query)) {
            if ($gambar_baru !== '' && !empty($old['gambar'])) {
                deleteUploadFile($old['gambar'], 'barang');
            }
            $_SESSION['success'] = "Barang berhasil diupdate.";
            header('Location: data_barang.php');
            exit;
        }

        if ($gambar_baru !== '') {
            deleteUploadFile($gambar_baru, 'barang');
        }
        $_SESSION['error'] = "Gagal mengupdate barang: " . mysqli_error($conn);
        header('Location: barang_form.php?id=' . $id_post);
        exit;
    }

    $gambar_db = mysqli_real_escape_string($conn, $gambar_baru);
    $query = "INSERT INTO tb_barang (nama_barang, deskripsi_barang, harga_awal, tgl, gambar, status_barang)
              VALUES ('$nama_barang', '$deskripsi', '$harga_awal', '$tgl', '$gambar_db', 'pending')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Barang berhasil ditambahkan.";
        header('Location: data_barang.php');
        exit;
    }

    if ($gambar_baru !== '') {
        deleteUploadFile($gambar_baru, 'barang');
    }
    $_SESSION['error'] = "Gagal menambahkan barang: " . mysqli_error($conn);
    header('Location: barang_form.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_data ? 'Edit Barang' : 'Tambah Barang'; ?> - Petugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap');
        * { font-family: 'Poppins', sans-serif; }
        h1, h2, h3, h4, h5, h6, .font-serif { font-family: 'Playfair Display', serif; }
        .bg-coffee { background-color: #6F4E37; }
        .bg-coffee-dark { background-color: #4A3729; }
        .bg-biscuit { background-color: #F7E1C0; }
        .bg-biscuit-light { background-color: #FEF3E2; }
        .bg-biscuit-dark { background-color: #E5C9A8; }
        .text-coffee { color: #6F4E37; }
        .text-biscuit { color: #F7E1C0; }
        .border-biscuit { border-color: #E5C9A8; }
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

        <main class="flex-1 p-8">
            <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-700 mr-2"></i>
                    <span><?php echo htmlspecialchars($_SESSION['error']); ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-700"><i class="fas fa-times"></i></button>
            </div>
            <?php unset($_SESSION['error']); endif; ?>

            <div class="mb-8">
                <a href="data_barang.php" class="inline-flex items-center text-coffee hover:underline mb-4">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Data Barang
                </a>
                <h1 class="text-3xl font-bold text-coffee font-serif"><?php echo $edit_data ? 'Edit Barang' : 'Tambah Barang'; ?></h1>
                <p class="text-gray-600 mt-2"><?php echo $edit_data ? 'Perbarui data barang yang sudah ada.' : 'Isi form berikut untuk menambahkan barang baru.'; ?></p>
            </div>

            <div class="bg-white rounded-xl shadow-warm p-6 border border-biscuit max-w-4xl">
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <?php if ($edit_data): ?>
                    <input type="hidden" name="id_barang" value="<?php echo $edit_data['id_barang']; ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-coffee font-semibold mb-2">Nama Barang <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_barang" required value="<?php echo htmlspecialchars($edit_data['nama_barang'] ?? ''); ?>" class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-[#6F4E37] focus:border-[#6F4E37] bg-white">
                        </div>
                        <div>
                            <label class="block text-coffee font-semibold mb-2">Tanggal Input <span class="text-red-500">*</span></label>
                            <input type="date" name="tgl" required value="<?php echo htmlspecialchars($edit_data['tgl'] ?? date('Y-m-d')); ?>" class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-[#6F4E37] focus:border-[#6F4E37] bg-white">
                        </div>
                        <div>
                            <label class="block text-coffee font-semibold mb-2">Harga Awal <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-coffee">Rp</span>
                                <input type="text" id="harga_awal" value="<?php echo isset($edit_data['harga_awal']) ? number_format($edit_data['harga_awal'], 0, ',', '.') : ''; ?>" onkeyup="formatRupiah(this)" class="w-full pl-12 pr-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-[#6F4E37] focus:border-[#6F4E37] bg-white" required>
                                <input type="hidden" name="harga_awal" id="harga_awal_hidden" value="<?php echo htmlspecialchars($edit_data['harga_awal'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-coffee font-semibold mb-2">Gambar Barang</label>
                            <div class="border-2 border-dashed border-biscuit rounded-lg p-4 text-center hover:border-[#6F4E37] transition bg-white">
                                <input type="file" name="gambar" id="gambar" accept="image/*" class="hidden" onchange="previewImage(this)">
                                <label for="gambar" class="cursor-pointer block">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-coffee mb-2"></i>
                                    <p class="text-coffee">Klik untuk upload gambar</p>
                                </label>
                            </div>
                            <?php $gambar_info = !empty($edit_data['gambar']) ? resolveUploadFile($edit_data['gambar'], 'barang') : null; ?>
                            <div id="preview-container" class="mt-4 <?php echo $gambar_info ? '' : 'hidden'; ?>">
                                <img id="preview-image" src="<?php echo $gambar_info ? htmlspecialchars($gambar_info['url']) : ''; ?>" class="max-h-40 rounded-lg border border-biscuit mx-auto object-contain" alt="Preview">
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-coffee font-semibold mb-2">Deskripsi Barang <span class="text-red-500">*</span></label>
                            <textarea name="deskripsi_barang" rows="5" required class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-[#6F4E37] focus:border-[#6F4E37] bg-white"><?php echo htmlspecialchars($edit_data['deskripsi_barang'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-coffee hover:bg-coffee-dark text-biscuit font-semibold px-8 py-3 rounded-lg transition duration-200 shadow-warm flex items-center">
                            <i class="fas <?php echo $edit_data ? 'fa-save' : 'fa-plus-circle'; ?> mr-2"></i><?php echo $edit_data ? 'Update Barang' : 'Simpan Barang'; ?>
                        </button>
                        <a href="data_barang.php" class="bg-gray-400 hover:bg-gray-500 text-white font-semibold px-8 py-3 rounded-lg transition duration-200 shadow-warm flex items-center">
                            <i class="fas fa-times mr-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

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
            input.value = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            document.getElementById('harga_awal_hidden').value = value.replace(/\./g, '');
        }

        function previewImage(input) {
            const file = input.files[0];
            const preview = document.getElementById('preview-image');
            const container = document.getElementById('preview-container');
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                container.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }

        window.onload = function () {
            const hargaDisplay = document.getElementById('harga_awal');
            if (hargaDisplay && hargaDisplay.value) {
                document.getElementById('harga_awal_hidden').value = hargaDisplay.value.replace(/\./g, '');
            }
        };
    </script>
</body>
</html>
