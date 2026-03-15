<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Sistem Lelang Online</title>
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
        .shadow-warm {
            box-shadow: 0 10px 25px -5px rgba(111, 78, 55, 0.1), 0 8px 10px -6px rgba(111, 78, 55, 0.1);
        }
    </style>
    <meta http-equiv="refresh" content="3;url=login.php">
</head>
<body class="bg-coffee-dark min-h-screen flex items-center justify-center p-4">
    <!-- Background Pattern -->
    <div class="fixed inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23F7E1C0' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logout Card -->
        <div class="bg-white rounded-2xl shadow-warm overflow-hidden border border-biscuit">
            <!-- Header -->
            <div class="bg-coffee p-8 text-center border-b border-biscuit">
                <div class="w-24 h-24 bg-biscuit rounded-full mx-auto mb-4 flex items-center justify-center shadow-warm">
                    <i class="fas fa-check-circle text-coffee text-4xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-biscuit font-serif mb-2">Terima Kasih</h1>
                <p class="text-biscuit opacity-90">Anda telah berhasil logout</p>
            </div>

            <!-- Content -->
            <div class="p-8 bg-biscuit-light text-center">
                <div class="mb-6">
                    <i class="fas fa-coffee text-coffee text-5xl mb-4"></i>
                    <p class="text-coffee mb-2">Sampai jumpa kembali!</p>
                    <p class="text-coffee-light text-sm">Anda akan dialihkan ke halaman login dalam 3 detik...</p>
                </div>

                <div class="flex justify-center space-x-2 mb-6">
                    <div class="w-2 h-2 bg-coffee rounded-full animate-bounce" style="animation-delay: 0s"></div>
                    <div class="w-2 h-2 bg-coffee-light rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-2 h-2 bg-coffee rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>

                <a href="login.php" class="inline-block bg-coffee hover:bg-coffee-dark text-biscuit px-6 py-3 rounded-lg font-semibold transition duration-200 shadow-warm transform hover:scale-105">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login Kembali
                </a>

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