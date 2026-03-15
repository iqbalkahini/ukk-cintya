<?php
session_start();
require_once('../config/config.php');
checkLevel([1, 2]);

$is_admin = $_SESSION['id_level'] == 1;

// Get filter
$tanggal_dari = $_GET['dari'] ?? date('Y-m-01');
$tanggal_sampai = $_GET['sampai'] ?? date('Y-m-d');

// Get data laporan
$laporan = mysqli_query($conn, "SELECT l.*, b.nama_barang, b.harga_awal, u.nama_lengkap as pemenang
                                FROM tb_lelang l 
                                JOIN tb_barang b ON l.id_barang = b.id_barang
                                LEFT JOIN tb_user u ON l.id_user = u.id_user
                                WHERE l.tgl_lelang BETWEEN '$tanggal_dari' AND '$tanggal_sampai'
                                ORDER BY l.tgl_lelang DESC");

// Calculate statistics
$total_lelang = 0;
$total_nilai = 0;
$lelang_selesai = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Lelang - Sistem Lelang Online</title>
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
                    <p class="text-lg font-bold text-coffee font-serif"><?php echo $is_admin ? 'Administrator' : 'Petugas'; ?></p>
                </div>
                
                <nav class="space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-home w-5"></i>
                        <span class="ml-3">Beranda</span>
                    </a>
                    <?php if($is_admin): ?>
                    <a href="total_barang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-box w-5"></i>
                        <span class="ml-3">Total Barang</span>
                    </a>
                    <?php endif; ?>
                    <a href="data_barang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-database w-5"></i>
                        <span class="ml-3">Data Barang</span>
                    </a>
                    <a href="total_lelang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-gavel w-5"></i>
                        <span class="ml-3">Total Lelang</span>
                    </a>
                    <a href="laporan.php" class="flex items-center px-4 py-3 bg-biscuit text-coffee rounded-lg shadow-sm border border-biscuit-dark">
                        <i class="fas fa-file-alt w-5"></i>
                        <span class="ml-3">Laporan</span>
                    </a>
                    <?php if($is_admin): ?>
                    <a href="data_user.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-users w-5"></i>
                        <span class="ml-3">Data Pengguna</span>
                    </a>
                    <?php endif; ?>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-coffee font-serif">
                    <i class="fas fa-chart-bar mr-2"></i>Laporan Lelang
                </h1>
                <p class="text-gray-600 mt-2">Laporan data lelang periode tertentu</p>
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-xl shadow-warm p-6 mb-8 border border-biscuit">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-coffee font-semibold mb-2">Tanggal Dari</label>
                        <input type="date" name="dari" value="<?php echo $tanggal_dari; ?>"
                               class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee">
                    </div>
                    <div>
                        <label class="block text-coffee font-semibold mb-2">Tanggal Sampai</label>
                        <input type="date" name="sampai" value="<?php echo $tanggal_sampai; ?>"
                               class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-coffee hover:bg-coffee-dark text-biscuit px-6 py-2 rounded-lg transition duration-200 shadow-warm">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="window.print()" 
                                class="w-full bg-coffee-light hover:bg-coffee-dark text-biscuit px-6 py-2 rounded-lg transition duration-200 shadow-warm">
                            <i class="fas fa-print mr-2"></i>Cetak
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabel Laporan -->
            <div class="bg-white rounded-xl shadow-warm overflow-hidden border border-biscuit">
                <div class="p-6 border-b border-biscuit bg-biscuit">
                    <h2 class="text-xl font-bold text-coffee font-serif">
                        Data Lelang Periode <?php echo formatTanggal($tanggal_dari); ?> s/d <?php echo formatTanggal($tanggal_sampai); ?>
                    </h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full" id="laporan-table">
                        <thead class="bg-biscuit-light">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Nama Barang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Harga Awal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Harga Akhir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Pemenang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-biscuit">
                            <?php 
                            $no = 1;
                            while($row = mysqli_fetch_assoc($laporan)): 
                                $total_lelang++;
                                if($row['status'] == 'ditutup') {
                                    $lelang_selesai++;
                                    $total_nilai += $row['harga_akhir'];
                                }
                            ?>
                            <tr class="hover:bg-biscuit-light transition duration-200">
                                <td class="px-6 py-4 text-coffee"><?php echo $no++; ?></td>
                                <td class="px-6 py-4 text-gray-600"><?php echo formatTanggal($row['tgl_lelang']); ?></td>
                                <td class="px-6 py-4 font-medium text-coffee"><?php echo $row['nama_barang']; ?></td>
                                <td class="px-6 py-4 text-gray-600"><?php echo formatRupiah($row['harga_awal']); ?></td>
                                <td class="px-6 py-4 text-coffee-light font-semibold"><?php echo formatRupiah($row['harga_akhir']); ?></td>
                                <td class="px-6 py-4 text-coffee"><?php echo $row['pemenang'] ?? '-'; ?></td>
                                <td class="px-6 py-4">
                                    <?php if($row['status'] == 'dibuka'): ?>
                                        <span class="px-3 py-1 bg-biscuit text-coffee rounded-full text-xs font-semibold border border-biscuit-dark">Dibuka</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-coffee text-biscuit rounded-full text-xs font-semibold">Selesai</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="bg-biscuit-light">
                            <tr class="font-bold">
                                <td colspan="4" class="px-6 py-4 text-right text-coffee">Total Nilai Lelang Selesai:</td>
                                <td class="px-6 py-4 text-coffee-light"><?php echo formatRupiah($total_nilai); ?></td>
                                <td colspan="2" class="px-6 py-4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Summary -->
                <div class="p-6 border-t border-biscuit bg-biscuit">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-white rounded-lg shadow border border-biscuit">
                            <p class="text-coffee text-sm">Total Lelang</p>
                            <p class="text-3xl font-bold text-coffee"><?php echo $total_lelang; ?></p>
                        </div>
                        <div class="text-center p-4 bg-white rounded-lg shadow border border-biscuit">
                            <p class="text-coffee text-sm">Lelang Selesai</p>
                            <p class="text-3xl font-bold text-coffee-light"><?php echo $lelang_selesai; ?></p>
                        </div>
                        <div class="text-center p-4 bg-white rounded-lg shadow border border-biscuit">
                            <p class="text-coffee text-sm">Total Nilai Transaksi</p>
                            <p class="text-3xl font-bold text-amber-700"><?php echo formatRupiah($total_nilai); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        @media print {
            aside, nav, button, .no-print { display: none !important; }
            main { padding: 20px !important; width: 100% !important; }
        }
    </style>
</body>
</html>