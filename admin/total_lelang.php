<?php
session_start();
require_once('../config/config.php');

// Fungsi helper
if(!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

if(!function_exists('formatTanggal')) {
    function formatTanggal($tanggal) {
        return date('d/m/Y', strtotime($tanggal));
    }
}

checkLevel([1]); // Hanya admin

// Handle Delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM tb_lelang WHERE id_lelang = $id");
    header('Location: total_lelang.php');
    exit;
}

// Get all lelang with gambar
$lelang = mysqli_query($conn, "SELECT l.*, b.nama_barang, b.harga_awal, b.gambar, u.nama_lengkap as pemenang, p.nama_lengkap as petugas_nama
                               FROM tb_lelang l 
                               JOIN tb_barang b ON l.id_barang = b.id_barang
                               LEFT JOIN tb_user u ON l.id_user = u.id_user
                               LEFT JOIN tb_user p ON l.id_petugas = p.id_user
                               ORDER BY l.id_lelang DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Total Lelang - Admin</title>
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
                    <a href="data_barang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-database w-5"></i>
                        <span class="ml-3">Data Barang</span>
                    </a>
                    <a href="total_lelang.php" class="flex items-center px-4 py-3 bg-biscuit text-coffee rounded-lg shadow-sm border border-biscuit-dark">
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
                <h1 class="text-3xl font-bold text-coffee font-serif">Total Lelang</h1>
                <p class="text-gray-600 mt-2">Kelola semua data lelang dalam sistem</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <?php
                $total_lelang = mysqli_num_rows($lelang);
                mysqli_data_seek($lelang, 0);
                $lelang_dibuka = 0;
                $lelang_ditutup = 0;
                $total_nilai = 0;
                
                while($temp = mysqli_fetch_assoc($lelang)) {
                    if($temp['status'] == 'dibuka') $lelang_dibuka++;
                    else $lelang_ditutup++;
                    if($temp['status'] == 'ditutup') $total_nilai += $temp['harga_akhir'];
                }
                mysqli_data_seek($lelang, 0);
                ?>
                
                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-coffee hover:shadow-lg transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Lelang</p>
                            <p class="text-3xl font-bold text-coffee mt-2"><?php echo $total_lelang; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Semua Lelang</p>
                        </div>
                        <div class="bg-biscuit p-4 rounded-full">
                            <i class="fas fa-gavel text-coffee text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-coffee-light hover:shadow-lg transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Lelang Dibuka</p>
                            <p class="text-3xl font-bold text-coffee mt-2"><?php echo $lelang_dibuka; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Sedang Berlangsung</p>
                        </div>
                        <div class="bg-biscuit p-4 rounded-full">
                            <i class="fas fa-play-circle text-coffee-light text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-amber-700 hover:shadow-lg transition duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Lelang Ditutup</p>
                            <p class="text-3xl font-bold text-coffee mt-2"><?php echo $lelang_ditutup; ?></p>
                            <p class="text-xs text-gray-500 mt-1">Telah Selesai</p>
                        </div>
                        <div class="bg-biscuit p-4 rounded-full">
                            <i class="fas fa-check-circle text-amber-700 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="bg-white rounded-xl shadow-warm p-6 mb-6 border border-biscuit">
                <div class="flex flex-col md:flex-row gap-4">
                    <input type="text" id="searchInput" placeholder="Cari nama barang..." 
                           class="flex-1 px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee">
                    <select id="filterStatus" class="px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee">
                        <option value="">Semua Status</option>
                        <option value="dibuka">Dibuka</option>
                        <option value="ditutup">Ditutup</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-warm overflow-hidden border border-biscuit">
                <div class="p-6 border-b border-biscuit bg-biscuit">
                    <h2 class="text-xl font-bold text-coffee font-serif">Daftar Semua Lelang</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-biscuit-light">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Gambar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Nama Barang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Tanggal Lelang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Harga Awal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Harga Akhir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Pemenang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Petugas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-biscuit" id="tableBody">
                            <?php while($row = mysqli_fetch_assoc($lelang)): ?>
                            <tr class="hover:bg-biscuit-light lelang-row transition duration-200" data-status="<?php echo $row['status']; ?>">
                                <td class="px-6 py-4 font-medium text-coffee"><?php echo $row['id_lelang']; ?></td>
                                <td class="px-6 py-4">
                                    <div class="w-12 h-12 bg-biscuit rounded flex items-center justify-center overflow-hidden">
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
                                </td>
                                <td class="px-6 py-4 barang-name">
                                    <div class="font-medium text-coffee"><?php echo $row['nama_barang']; ?></div>
                                </td>
                                <td class="px-6 py-4 text-gray-600"><?php echo formatTanggal($row['tgl_lelang']); ?></td>
                                <td class="px-6 py-4 text-gray-600"><?php echo formatRupiah($row['harga_awal']); ?></td>
                                <td class="px-6 py-4 text-coffee-light font-semibold"><?php echo formatRupiah($row['harga_akhir']); ?></td>
                                <td class="px-6 py-4">
                                    <?php if($row['pemenang']): ?>
                                        <span class="text-coffee font-medium"><?php echo $row['pemenang']; ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-gray-600"><?php echo $row['petugas_nama'] ?? '-'; ?></td>
                                <td class="px-6 py-4">
                                    <?php if($row['status'] == 'dibuka'): ?>
                                        <span class="px-3 py-1 bg-biscuit text-coffee rounded-full text-xs font-semibold border border-biscuit-dark">
                                            <i class="fas fa-circle text-xs mr-1"></i>Dibuka
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">
                                            <i class="fas fa-check-circle mr-1"></i>Ditutup
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <a href="detail_lelang.php?id=<?php echo $row['id_lelang']; ?>" 
                                           class="text-coffee hover:text-coffee-light" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?delete=<?php echo $row['id_lelang']; ?>" 
                                           onclick="return confirm('Yakin ingin menghapus lelang ini?')"
                                           class="text-coffee hover:text-coffee-light" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Summary Footer -->
                <div class="p-6 bg-biscuit-light border-t border-biscuit">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-sm text-coffee">Total Lelang</p>
                            <p class="text-2xl font-bold text-coffee"><?php echo $total_lelang; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-coffee">Lelang Aktif</p>
                            <p class="text-2xl font-bold text-coffee-light"><?php echo $lelang_dibuka; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-coffee">Total Nilai Transaksi</p>
                            <p class="text-2xl font-bold text-amber-700"><?php echo formatRupiah($total_nilai); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
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
            const rows = document.querySelectorAll('.lelang-row');

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