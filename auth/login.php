<?php
session_start();
require_once('../config/config.php');

// Jika sudah login, redirect
if (isset($_SESSION['id_user'])) {
    if ($_SESSION['id_level'] == 1) {
        header('Location: ../admin/dashboard.php');
    } elseif ($_SESSION['id_level'] == 2) {
        header('Location: ../petugas/dashboard.php');
    } else {
        header('Location: ../masyarakat/dashboard.php');
    }
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);
    
    $query = "SELECT u.*, l.level FROM tb_user u 
              JOIN tb_level l ON u.id_level = l.id_level 
              WHERE u.username = '$username' AND u.password = '$password'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $data = mysqli_fetch_assoc($result);
        
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
        $_SESSION['id_level'] = $data['id_level'];
        $_SESSION['level'] = $data['level'];
        
        if ($data['id_level'] == 1) {
            header('Location: ../admin/dashboard.php');
        } elseif ($data['id_level'] == 2) {
            header('Location: ../petugas/dashboard.php');
        } else {
            header('Location: ../masyarakat/dashboard.php');
        }
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Lelang Online</title>
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
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-warm overflow-hidden border border-biscuit">
            <!-- Header -->
            <div class="bg-coffee p-8 text-center border-b border-biscuit">
                <div class="w-24 h-24 bg-biscuit rounded-full mx-auto mb-4 flex items-center justify-center shadow-warm">
                    <i class="fas fa-gavel text-coffee text-4xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-biscuit font-serif mb-2">Sistem Lelang Online</h1>
                <p class="text-biscuit opacity-90">Selamat Datang Kembali</p>
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

                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label class="block text-coffee text-sm font-semibold mb-2" for="username">
                            <i class="fas fa-user mr-2"></i>Nama Pengguna
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required
                            class="w-full px-4 py-3 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee transition duration-200 outline-none bg-white"
                            placeholder="Nama Pengguna"
                        >
                    </div>

                    <div>
                        <label class="block text-coffee text-sm font-semibold mb-2" for="password">
                            <i class="fas fa-lock mr-2"></i>Kata Sandi
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                class="w-full px-4 py-3 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee transition duration-200 outline-none bg-white"
                                placeholder="Kata Sandi"
                            >
                            <button 
                                type="button" 
                                onclick="togglePassword()"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-coffee-light hover:text-coffee transition duration-200"
                            >
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        name="login"
                        class="w-full bg-coffee hover:bg-coffee-dark text-biscuit font-semibold py-3 rounded-lg transition duration-200 shadow-warm transform hover:scale-105"
                    >
                        <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-coffee text-sm">
                        Belum punya akun? 
                        <a href="register.php" class="text-coffee-light hover:text-coffee font-semibold transition duration-200">
                            Daftar Sekarang
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

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>