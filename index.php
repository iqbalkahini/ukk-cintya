<?php
session_start();
require_once('config/config.php');

// Get active lelang count for hero section
$active_lelang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_lelang WHERE status = 'dibuka'"))['total'];

// Get some featured items
$featured_items = mysqli_query($conn, "SELECT l.*, b.nama_barang, b.harga_awal, b.gambar, b.deskripsi_barang,
                                       (SELECT MAX(penawaran_harga) FROM history_lelang WHERE id_lelang = l.id_lelang) as harga_tertinggi
                                       FROM tb_lelang l 
                                       JOIN tb_barang b ON l.id_barang = b.id_barang 
                                       WHERE l.status = 'dibuka'
                                       ORDER BY l.created_at DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Lelang Online - Coffee & Biskuit</title>
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
        .hero-pattern {
            background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23F7E1C0" fill-opacity="0.2"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');
        }
        
        /* Animasi untuk navbar */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .navbar-animate {
            animation: fadeInDown 0.8s ease-out;
        }
        
        /* Efek glassmorphism untuk navbar */
        .navbar-glass {
            background: rgba(111, 78, 55, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(247, 225, 192, 0.1);
        }
        
        /* Efek glow untuk tombol */
        .btn-glow {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .btn-glow:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-glow:hover:before {
            left: 100%;
        }
        
        /* Pattern untuk hero section */
        .hero-pattern-custom {
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(247, 225, 192, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(247, 225, 192, 0.1) 0%, transparent 50%),
                linear-gradient(45deg, rgba(247, 225, 192, 0.05) 25%, transparent 25%),
                linear-gradient(-45deg, rgba(247, 225, 192, 0.05) 25%, transparent 25%);
            background-size: 100% 100%, 100% 100%, 40px 40px, 40px 40px;
            background-position: 0 0, 0 0, 0 0, 20px 20px;
        }
        
        /* Efek shine untuk judul */
        .text-shine {
            background: linear-gradient(120deg, #F7E1C0 0%, #F7E1C0 40%, #ffffff 50%, #F7E1C0 60%, #F7E1C0 100%);
            background-size: 200% auto;
            color: transparent;
            -webkit-background-clip: text;
            background-clip: text;
            animation: shine 8s linear infinite;
        }
        
        @keyframes shine {
            to {
                background-position: 200% center;
            }
        }
        
        /* Efek floating untuk statistik */
        .floating-card {
            animation: float 3s ease-in-out infinite;
        }
        
        .floating-card:nth-child(2) {
            animation-delay: 0.5s;
        }
        
        .floating-card:nth-child(3) {
            animation-delay: 1s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
    </style>
</head>
<body class="bg-biscuit-light">
    <!-- Navbar dengan efek glass -->
    <nav class="navbar-glass shadow-lg sticky top-0 z-50 navbar-animate">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center group">
                    <div class="relative">
                        <i class="fas fa-gavel text-biscuit text-2xl mr-3 transform group-hover:rotate-12 transition-transform duration-300"></i>
                        <div class="absolute -inset-1 bg-biscuit/20 rounded-full blur-sm group-hover:bg-biscuit/30 transition-all duration-300"></div>
                    </div>
                    <span class="text-biscuit text-xl font-bold font-serif relative">
                        Lelang<span class="text-biscuit">Online</span>
                        <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-biscuit group-hover:w-full transition-all duration-300"></span>
                    </span>
                </div>
                <div class="flex items-center space-x-6">
                    <?php if(isset($_SESSION['id_user'])): ?>
                        <?php if($_SESSION['id_level'] == 1): ?>
                            <a href="admin/dashboard.php" class="bg-biscuit hover:bg-biscuit-dark text-coffee px-4 py-2 rounded-lg transition duration-200 shadow-warm btn-glow relative overflow-hidden">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard Admin
                            </a>
                        <?php elseif($_SESSION['id_level'] == 2): ?>
                            <a href="petugas/dashboard.php" class="bg-biscuit hover:bg-biscuit-dark text-coffee px-4 py-2 rounded-lg transition duration-200 shadow-warm btn-glow relative overflow-hidden">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard Petugas
                            </a>
                        <?php else: ?>
                            <a href="masyarakat/dashboard.php" class="bg-biscuit hover:bg-biscuit-dark text-coffee px-4 py-2 rounded-lg transition duration-200 shadow-warm btn-glow relative overflow-hidden">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard Saya
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="auth/login.php" class="bg-biscuit hover:bg-biscuit-dark text-coffee px-4 py-2 rounded-lg transition duration-200 shadow-warm btn-glow relative overflow-hidden">
                            <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                        </a>
                        <a href="auth/register.php" class="bg-coffee-light hover:bg-coffee-dark text-biscuit px-4 py-2 rounded-lg transition duration-200 shadow-warm btn-glow relative overflow-hidden border border-biscuit/30">
                            <i class="fas fa-user-plus mr-2"></i>Daftar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section dengan desain lebih estetik -->
    <section id="home" class="relative min-h-screen flex items-center overflow-hidden">
        <!-- Background gradient yang lebih dinamis -->
        <div class="absolute inset-0 bg-gradient-to-br from-coffee-dark via-coffee to-coffee-light hero-pattern-custom">
            <!-- Efek partikel -->
            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-biscuit/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-biscuit/10 rounded-full blur-3xl"></div>
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-biscuit/5 rounded-full blur-3xl"></div>
            </div>
            
            <!-- Pola geometris -->
            <div class="absolute inset-0" style="background-image: 
                linear-gradient(45deg, rgba(247, 225, 192, 0.03) 25%, transparent 25%),
                linear-gradient(-45deg, rgba(247, 225, 192, 0.03) 25%, transparent 25%);
                background-size: 60px 60px;
                background-position: 0 0, 0 30px;">
            </div>
        </div>
        
        <!-- Konten Hero -->
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 z-10">
            <div class="text-center max-w-4xl mx-auto">
                <!-- Badge dengan animasi -->
                <div class="flex justify-center mb-6 animate__animated animate__fadeInDown">
                    <span class="inline-flex items-center bg-white/10 text-biscuit px-4 py-2 rounded-full text-sm font-medium backdrop-blur-sm border border-white/20 hover:bg-white/20 transition-all duration-300">
                        <i class="fas fa-gavel mr-2 text-sm animate-pulse"></i>
                        #LelangOnlineTerpercaya
                    </span>
                </div>
                
                <!-- Judul Utama dengan efek shine -->
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold text-biscuit font-serif mb-6 leading-tight">
                    Selamat Datang di 
                    <span class="block sm:inline text-shine">LelangOnline</span>
                </h1>
                
                <!-- Deskripsi dengan efek fade -->
                <p class="text-base sm:text-lg md:text-xl text-biscuit/90 mb-10 max-w-2xl mx-auto leading-relaxed animate-pulse-slow">
                    Temukan barang impian Anda dengan harga terbaik melalui sistem lelang 
                    yang aman, transparan, dan terpercaya
                </p>
                
                <!-- Tombol CTA dengan efek hover lebih menarik -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-16">
                    <a href="#auctions" 
                       class="inline-flex items-center justify-center bg-biscuit hover:bg-biscuit-dark text-coffee px-6 py-3 md:px-8 md:py-4 rounded-lg font-semibold text-sm md:text-base transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 hover:scale-105 btn-glow relative overflow-hidden group">
                        <i class="fas fa-gavel mr-2 group-hover:rotate-12 transition-transform duration-300"></i>
                        <span class="relative z-10">Lihat Lelang Aktif</span>
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                    </a>
                    
                    <?php if(!isset($_SESSION['id_user'])): ?>
                    <a href="auth/register.php" 
                       class="inline-flex items-center justify-center bg-transparent border-2 border-biscuit hover:bg-biscuit hover:text-coffee text-biscuit px-6 py-3 md:px-8 md:py-4 rounded-lg font-semibold text-sm md:text-base transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 group">
                        <i class="fas fa-user-plus mr-2 group-hover:rotate-12 transition-transform duration-300"></i>
                        <span class="relative z-10">Daftar Sekarang</span>
                        <div class="absolute inset-0 bg-biscuit/0 hover:bg-biscuit/20 transition-all duration-300 rounded-lg"></div>
                    </a>
                    <?php endif; ?>
                </div>
                
                <!-- Statistik dengan efek floating -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 max-w-3xl mx-auto">
                    <!-- Lelang Aktif -->
                    <div class="text-center floating-card transform hover:scale-105 transition-all duration-300">
                        <div class="relative inline-block">
                            <div class="text-3xl md:text-4xl font-bold text-biscuit mb-1 relative z-10"><?php echo $active_lelang; ?></div>
                            <div class="absolute -inset-2 bg-biscuit/10 rounded-full blur-xl"></div>
                        </div>
                        <div class="text-sm md:text-base text-biscuit/80 flex items-center justify-center">
                            <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                            Lelang Aktif
                        </div>
                    </div>
                    
                    <!-- Barang Terjual -->
                    <div class="text-center floating-card transform hover:scale-105 transition-all duration-300" style="animation-delay: 0.5s;">
                        <div class="relative inline-block">
                            <div class="text-3xl md:text-4xl font-bold text-biscuit mb-1 relative z-10">100+</div>
                            <div class="absolute -inset-2 bg-biscuit/10 rounded-full blur-xl"></div>
                        </div>
                        <div class="text-sm md:text-base text-biscuit/80 flex items-center justify-center">
                            <span class="w-2 h-2 bg-blue-400 rounded-full mr-2 animate-pulse"></span>
                            Barang Terjual
                        </div>
                    </div>
                    
                    <!-- Pengguna -->
                    <div class="text-center floating-card transform hover:scale-105 transition-all duration-300" style="animation-delay: 1s;">
                        <div class="relative inline-block">
                            <div class="text-3xl md:text-4xl font-bold text-biscuit mb-1 relative z-10">500+</div>
                            <div class="absolute -inset-2 bg-biscuit/10 rounded-full blur-xl"></div>
                        </div>
                        <div class="text-sm md:text-base text-biscuit/80 flex items-center justify-center">
                            <span class="w-2 h-2 bg-yellow-400 rounded-full mr-2 animate-pulse"></span>
                            Pengguna Aktif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Decorative Wave dengan efek gradien -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" class="w-full h-auto">
                <defs>
                    <linearGradient id="waveGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" style="stop-color:#F7E1C0;stop-opacity:1" />
                        <stop offset="50%" style="stop-color:#E5C9A8;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#F7E1C0;stop-opacity:1" />
                    </linearGradient>
                </defs>
                <path fill="url(#waveGradient)" fill-opacity="0.8" d="M0,64L48,74.7C96,85,192,107,288,106.7C384,107,480,85,576,74.7C672,64,768,64,864,74.7C960,85,1056,107,1152,112C1248,117,1344,107,1392,101.3L1440,96L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z">
                </path>
            </svg>
        </div>
    </section>
    
    <!-- Active Auctions Section (tetap sama seperti sebelumnya) -->
    <section id="auctions" class="py-20 bg-white">
        <!-- ... konten section auctions tetap sama ... -->
    </section>