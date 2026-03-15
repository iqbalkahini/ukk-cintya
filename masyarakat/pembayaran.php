<?php
session_start();
require_once('../config/config.php');
checkLevel([3]);

$id_user = $_SESSION['id_user'];

// Get lelang yang dimenangkan
$lelang_menang = mysqli_query($conn, "SELECT l.*, b.nama_barang, b.harga_awal,
                                      (SELECT status_pembayaran FROM tb_pembayaran WHERE id_lelang = l.id_lelang AND id_user = $id_user ORDER BY id_pembayaran DESC LIMIT 1) as status_bayar
                                      FROM tb_lelang l
                                      JOIN tb_barang b ON l.id_barang = b.id_barang
                                      WHERE l.id_user = $id_user AND l.status = 'ditutup'
                                      ORDER BY l.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Sistem Lelang Online</title>
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
                    <a href="penawaran_saya.php" class="text-biscuit hover:text-biscuit-dark">Penawaran Saya</a>
                    <a href="pembayaran.php" class="text-biscuit hover:text-biscuit-dark font-semibold">Pembayaran</a>
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
                <i class="fas fa-credit-card mr-3"></i>Pembayaran
            </h1>
            <p class="text-gray-600">Kelola pembayaran lelang yang Anda menangkan</p>
        </div>

        <!-- Informasi Pembayaran -->
        <div class="bg-coffee border-l-4 border-coffee-light p-6 mb-8 rounded-lg shadow-warm">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-biscuit text-2xl mr-4 mt-1"></i>
                <div>
                    <h3 class="font-bold text-biscuit mb-2">Informasi Penting</h3>
                    <ul class="text-biscuit space-y-1 text-sm opacity-90">
                        <li>• Selesaikan pembayaran maksimal 3x24 jam setelah lelang ditutup</li>
                        <li>• Upload bukti pembayaran yang jelas dan valid</li>
                        <li>• Hubungi admin jika ada kendala dalam pembayaran</li>
                        <li>• Barang akan dikirim setelah pembayaran dikonfirmasi</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Daftar Lelang yang Dimenangkan -->
        <div class="bg-white rounded-xl shadow-warm overflow-hidden border border-biscuit">
            <div class="p-6 border-b border-biscuit bg-coffee">
                <h2 class="text-2xl font-bold text-biscuit font-serif">
                    <i class="fas fa-trophy mr-2"></i>Lelang yang Anda Menangkan
                </h2>
            </div>

            <div class="divide-y divide-biscuit">
                <?php while($row = mysqli_fetch_assoc($lelang_menang)): ?>
                <div class="p-6 hover:bg-biscuit-light transition duration-200">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <!-- Info Barang -->
                        <div class="flex-1 mb-4 lg:mb-0">
                            <div class="flex items-start">
                                <div class="w-16 h-16 bg-biscuit rounded-lg flex items-center justify-center mr-4 flex-shrink-0 border border-biscuit-dark">
                                    <i class="fas fa-image text-coffee text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-xl text-coffee font-serif mb-2"><?php echo $row['nama_barang']; ?></h3>
                                    <div class="space-y-1 text-sm text-gray-600">
                                        <p><i class="fas fa-calendar mr-2 text-coffee"></i>Tanggal Lelang: <?php echo formatTanggal($row['tgl_lelang']); ?></p>
                                        <p><i class="fas fa-tag mr-2 text-coffee"></i>Harga Awal: <?php echo formatRupiah($row['harga_awal']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Pembayaran -->
                        <div class="lg:ml-8 lg:text-right">
                            <p class="text-sm text-coffee mb-1">Total Pembayaran</p>
                            <p class="text-3xl font-bold text-coffee-light mb-3">
                                <?php echo formatRupiah($row['harga_akhir']); ?>
                            </p>

                            <?php 
                            $status_bayar = $row['status_bayar'];
                            if($status_bayar == 'selesai'): 
                            ?>
                                <span class="inline-block px-4 py-2 bg-biscuit text-coffee rounded-full font-semibold border border-biscuit-dark">
                                    <i class="fas fa-check-circle mr-2"></i>Lunas
                                </span>
                            <?php elseif($status_bayar == 'dibayar'): ?>
                                <span class="inline-block px-4 py-2 bg-biscuit text-coffee rounded-full font-semibold border border-biscuit-dark">
                                    <i class="fas fa-clock mr-2"></i>Menunggu Konfirmasi
                                </span>
                            <?php else: ?>
                                <a href="proses_bayar.php?id=<?php echo $row['id_lelang']; ?>" 
                                   class="inline-block px-6 py-3 bg-coffee hover:bg-coffee-dark text-biscuit rounded-lg font-semibold transition duration-200 shadow-warm">
                                    <i class="fas fa-money-bill-wave mr-2"></i>Bayar Sekarang
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <?php 
            mysqli_data_seek($lelang_menang, 0);
            if(mysqli_num_rows($lelang_menang) == 0): 
            ?>
            <div class="p-12 text-center">
                <i class="fas fa-inbox text-biscuit text-6xl mb-4"></i>
                <h3 class="text-xl font-bold text-coffee font-serif mb-2">Belum Ada Lelang yang Dimenangkan</h3>
                <p class="text-gray-500 mb-6">Anda belum memenangkan lelang apapun</p>
                <a href="barang_lelang.php" class="inline-block bg-coffee hover:bg-coffee-dark text-biscuit px-6 py-3 rounded-lg font-semibold transition duration-200 shadow-warm">
                    <i class="fas fa-gavel mr-2"></i>Lihat Barang Lelang
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Metode Pembayaran -->
        <div class="bg-white rounded-xl shadow-warm p-6 mt-8 border border-biscuit">
            <h3 class="text-xl font-bold text-coffee font-serif mb-4">
                <i class="fas fa-university mr-2"></i>Metode Pembayaran
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border border-biscuit rounded-lg p-4 bg-biscuit-light">
                    <h4 class="font-bold text-coffee mb-2">Transfer Bank BCA</h4>
                    <p class="text-sm text-gray-600 mb-1">No. Rekening: 1234567890</p>
                    <p class="text-sm text-gray-600">a.n. Sistem Lelang Online</p>
                </div>
                <div class="border border-biscuit rounded-lg p-4 bg-biscuit-light">
                    <h4 class="font-bold text-coffee mb-2">Transfer Bank Mandiri</h4>
                    <p class="text-sm text-gray-600 mb-1">No. Rekening: 0987654321</p>
                    <p class="text-sm text-gray-600">a.n. Sistem Lelang Online</p>
                </div>
                <div class="border border-biscuit rounded-lg p-4 bg-biscuit-light">
                    <h4 class="font-bold text-coffee mb-2">Transfer Bank BNI</h4>
                    <p class="text-sm text-gray-600 mb-1">No. Rekening: 5678901234</p>
                    <p class="text-sm text-gray-600">a.n. Sistem Lelang Online</p>
                </div>
                <div class="border border-biscuit rounded-lg p-4 bg-biscuit-light">
                    <h4 class="font-bold text-coffee mb-2">E-Wallet (OVO/GoPay/Dana)</h4>
                    <p class="text-sm text-gray-600 mb-1">No. HP: 081234567890</p>
                    <p class="text-sm text-gray-600">a.n. Sistem Lelang Online</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>