<?php
session_start();
require_once('../config/config.php');
checkLevel([3]);

$id_user = $_SESSION['id_user'];

// Get penawaran user
$penawaran = mysqli_query($conn, "SELECT h.*, b.nama_barang, l.status as status_lelang, l.harga_akhir,
                                  (SELECT MAX(penawaran_harga) FROM history_lelang WHERE id_lelang = h.id_lelang) as harga_tertinggi
                                  FROM history_lelang h
                                  JOIN tb_barang b ON h.id_barang = b.id_barang
                                  JOIN tb_lelang l ON h.id_lelang = l.id_lelang
                                  WHERE h.id_user = $id_user
                                  ORDER BY h.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penawaran Saya - Sistem Lelang Online</title>
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
                    <a href="penawaran_saya.php" class="text-biscuit hover:text-biscuit-dark font-semibold">Penawaran Saya</a>
                    <a href="pembayaran.php" class="text-biscuit hover:text-biscuit-dark">Pembayaran</a>
                    <a href="../auth/logout.php" class="bg-biscuit hover:bg-biscuit-dark text-coffee px-4 py-2 rounded-lg transition duration-200 shadow-md">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-coffee font-serif mb-2">
                <i class="fas fa-history mr-3"></i>Riwayat Penawaran Saya
            </h1>
            <p class="text-gray-600">Pantau semua penawaran yang telah Anda buat</p>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-t-xl shadow-warm border border-biscuit border-b-0">
            <div class="flex border-b border-biscuit">
                <button onclick="filterPenawaran('semua')" class="tab-btn active px-6 py-4 font-semibold text-coffee border-b-2 border-coffee">
                    Semua Penawaran
                </button>
                <button onclick="filterPenawaran('menang')" class="tab-btn px-6 py-4 font-semibold text-coffee hover:text-coffee-light">
                    Penawaran Menang
                </button>
                <button onclick="filterPenawaran('kalah')" class="tab-btn px-6 py-4 font-semibold text-coffee hover:text-coffee-light">
                    Penawaran Kalah
                </button>
                <button onclick="filterPenawaran('aktif')" class="tab-btn px-6 py-4 font-semibold text-coffee hover:text-coffee-light">
                    Lelang Aktif
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-b-xl shadow-warm overflow-hidden border border-biscuit border-t-0">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-biscuit-light">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Penawaran Saya</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Harga Tertinggi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-biscuit">
                        <?php 
                        $no = 1;
                        while($row = mysqli_fetch_assoc($penawaran)): 
                            $is_winning = $row['penawaran_harga'] == $row['harga_tertinggi'];
                            $is_active = $row['status_lelang'] == 'dibuka';
                        ?>
                        <tr class="hover:bg-biscuit-light penawaran-row transition duration-200" 
                            data-status="<?php echo $is_active ? 'aktif' : ($is_winning ? 'menang' : 'kalah'); ?>">
                            <td class="px-6 py-4 text-coffee"><?php echo $no++; ?></td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-coffee"><?php echo $row['nama_barang']; ?></div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-coffee">
                                <?php echo formatRupiah($row['penawaran_harga']); ?>
                            </td>
                            <td class="px-6 py-4 font-semibold text-coffee-light">
                                <?php echo formatRupiah($row['harga_tertinggi']); ?>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if($is_active): ?>
                                    <?php if($is_winning): ?>
                                        <span class="px-3 py-1 bg-biscuit text-coffee rounded-full text-sm font-semibold border border-biscuit-dark">
                                            <i class="fas fa-trophy mr-1"></i>Menang Sementara
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-yellow-50 text-yellow-700 rounded-full text-sm font-semibold border border-yellow-200">
                                            <i class="fas fa-clock mr-1"></i>Kalah Sementara
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if($is_winning): ?>
                                        <span class="px-3 py-1 bg-coffee text-biscuit rounded-full text-sm font-semibold">
                                            <i class="fas fa-check-circle mr-1"></i>Pemenang
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-semibold">
                                            <i class="fas fa-times-circle mr-1"></i>Tidak Menang
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <a href="detail_lelang.php?id=<?php echo $row['id_lelang']; ?>" 
                                   class="text-coffee hover:text-coffee-light font-semibold transition duration-200">
                                    <i class="fas fa-eye mr-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php 
            mysqli_data_seek($penawaran, 0);
            if(mysqli_num_rows($penawaran) == 0): 
            ?>
            <div class="p-12 text-center">
                <i class="fas fa-inbox text-biscuit text-6xl mb-4"></i>
                <h3 class="text-xl font-bold text-coffee font-serif mb-2">Belum Ada Penawaran</h3>
                <p class="text-gray-500 mb-6">Anda belum membuat penawaran apapun</p>
                <a href="barang_lelang.php" class="inline-block bg-coffee hover:bg-coffee-dark text-biscuit px-6 py-3 rounded-lg font-semibold transition duration-200 shadow-warm">
                    <i class="fas fa-gavel mr-2"></i>Mulai Menawar
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Statistics Cards -->
        <?php 
        mysqli_data_seek($penawaran, 0);
        $total = 0;
        $menang = 0;
        $aktif = 0;
        while($row = mysqli_fetch_assoc($penawaran)) {
            $total++;
            if($row['status_lelang'] == 'dibuka') {
                $aktif++;
                if($row['penawaran_harga'] == $row['harga_tertinggi']) $menang++;
            } elseif($row['penawaran_harga'] == $row['harga_tertinggi']) {
                $menang++;
            }
        }
        ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-coffee">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Penawaran</p>
                        <p class="text-3xl font-bold text-coffee mt-2"><?php echo $total; ?></p>
                    </div>
                    <div class="bg-biscuit p-4 rounded-full">
                        <i class="fas fa-hand-holding-usd text-coffee text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-coffee-light">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Penawaran Menang</p>
                        <p class="text-3xl font-bold text-coffee mt-2"><?php echo $menang; ?></p>
                    </div>
                    <div class="bg-biscuit p-4 rounded-full">
                        <i class="fas fa-trophy text-coffee-light text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-warm p-6 border-l-4 border-amber-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Lelang Aktif</p>
                        <p class="text-3xl font-bold text-coffee mt-2"><?php echo $aktif; ?></p>
                    </div>
                    <div class="bg-biscuit p-4 rounded-full">
                        <i class="fas fa-clock text-amber-700 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filterPenawaran(status) {
            const rows = document.querySelectorAll('.penawaran-row');
            const tabs = document.querySelectorAll('.tab-btn');
            
            // Update active tab
            tabs.forEach(tab => {
                tab.classList.remove('active', 'text-coffee', 'border-b-2', 'border-coffee');
                tab.classList.add('text-coffee');
            });
            event.target.classList.add('active', 'text-coffee', 'border-b-2', 'border-coffee');
            event.target.classList.remove('text-coffee');
            
            // Filter rows
            rows.forEach(row => {
                if(status === 'semua') {
                    row.style.display = '';
                } else {
                    const rowStatus = row.getAttribute('data-status');
                    row.style.display = rowStatus === status ? '' : 'none';
                }
            });
        }
    </script>
</body>
</html>