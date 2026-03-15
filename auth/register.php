<?php
session_start();
require_once('../config/config.php');

// Jika sudah login, redirect
if (isset($_SESSION['id_user'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $telp = mysqli_real_escape_string($conn, $_POST['telp']);
    
    // Validasi
    if (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } else {
        // Cek username sudah ada atau belum
        $check = mysqli_query($conn, "SELECT * FROM tb_user WHERE username = '$username'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            $password_hash = md5($password);
            $query = "INSERT INTO tb_user (nama_lengkap, username, password, telp, id_level) 
                      VALUES ('$nama_lengkap', '$username', '$password_hash', '$telp', 3)";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Registrasi gagal: ' . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Lelang Online</title>
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
<body class="bg-coffee-dark min-h-screen flex items-center justify-center p-4">
    <!-- Background Pattern -->
    <div class="fixed inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23F7E1C0' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <!-- Register Card -->
        <div class="bg-white rounded-2xl shadow-warm overflow-hidden border border-biscuit">
            <!-- Header -->
            <div class="bg-coffee p-8 text-center border-b border-biscuit">
                <div class="w-24 h-24 bg-biscuit rounded-full mx-auto mb-4 flex items-center justify-center shadow-warm">
                    <i class="fas fa-user-plus text-coffee text-4xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-biscuit font-serif mb-2">Daftar Akun Baru</h1>
                <p class="text-biscuit opacity-90">Bergabunglah dengan Sistem Lelang Online</p>
            </div>

            <!-- Form -->
            <div class="p-8 bg-biscuit-light">
                <?php if ($error): ?>
                <div class="mb-6 bg-white border-l-4 border-coffee p-4 rounded shadow-warm">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-coffee mr-3"></i>
                        <p class="text-coffee"><?php echo $error; ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="mb-6 bg-white border-l-4 border-coffee-light p-4 rounded shadow-warm">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-coffee-light mr-3"></i>
                        <p class="text-coffee-light"><?php echo $success; ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-5">
                    <div>
                        <label class="block text-coffee text-sm font-semibold mb-2">
                            <i class="fas fa-user mr-2"></i>Nama Lengkap
                        </label>
                        <input 
                            type="text" 
                            name="nama_lengkap" 
                            required
                            class="w-full px-4 py-3 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee transition duration-200 outline-none bg-white"
                            placeholder="Masukkan nama lengkap"
                        >
                    </div>

                    <div>
                        <label class="block text-coffee text-sm font-semibold mb-2">
                            <i class="fas fa-at mr-2"></i>Username
                        </label>
                        <input 
                            type="text" 
                            name="username" 
                            required
                            class="w-full px-4 py-3 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee transition duration-200 outline-none bg-white"
                            placeholder="Pilih username"
                        >
                    </div>

                    <div>
                        <label class="block text-coffee text-sm font-semibold mb-2">
                            <i class="fas fa-phone mr-2"></i>No. Telepon
                        </label>
                        <input 
                            type="tel" 
                            name="telp" 
                            required
                            class="w-full px-4 py-3 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee transition duration-200 outline-none bg-white"
                            placeholder="08xxxxxxxxxx"
                        >
                    </div>

                    <div>
                        <label class="block text-coffee text-sm font-semibold mb-2">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee transition duration-200 outline-none bg-white"
                            placeholder="Minimal 6 karakter"
                        >
                    </div>

                    <div>
                        <label class="block text-coffee text-sm font-semibold mb-2">
                            <i class="fas fa-lock mr-2"></i>Konfirmasi Password
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                name="confirm_password" 
                                required
                                class="w-full px-4 py-3 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee transition duration-200 outline-none bg-white"
                                placeholder="Ulangi password"
                            >
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        name="register"
                        class="w-full bg-coffee hover:bg-coffee-dark text-biscuit font-semibold py-3 rounded-lg transition duration-200 shadow-warm transform hover:scale-105"
                    >
                        <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-coffee text-sm">
                        Sudah punya akun? 
                        <a href="login.php" class="text-coffee-light hover:text-coffee font-semibold transition duration-200">
                            Login di sini
                        </a>
                    </p>
                </div>

                <!-- Coffee Bean Decoration -->
                <div class="mt-8 flex justify-center space-x-2">
                    <div class="w-2 h-2 bg-coffee rounded-full"></div>
                    <div class="w-2 h-2 bg-coffee-light rounded-full"></div>
                    <div class="w-2 h-2 bg-coffee rounded-full"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>