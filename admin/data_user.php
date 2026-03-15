<?php
session_start();
require_once('../config/config.php');
checkLevel([1]); // Hanya admin

// Handle Delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if($id != $_SESSION['id_user']) { // Tidak boleh hapus diri sendiri
        mysqli_query($conn, "DELETE FROM tb_user WHERE id_user = $id");
    }
    header('Location: data_user.php');
    exit;
}

// Handle Add/Edit
if(isset($_POST['submit'])) {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $telp = mysqli_real_escape_string($conn, $_POST['telp']);
    $id_level = intval($_POST['id_level']);

    if(isset($_POST['id_user']) && $_POST['id_user'] != '') {
        // Update
        $id = intval($_POST['id_user']);
        if (!empty($_POST['password'])) {
            $password = md5($_POST['password']);
            $query = "UPDATE tb_user SET 
                      nama_lengkap = '$nama_lengkap',
                      username = '$username',
                      password = '$password',
                      telp = '$telp',
                      id_level = $id_level
                      WHERE id_user = $id";
        } else {
            $query = "UPDATE tb_user SET 
                      nama_lengkap = '$nama_lengkap',
                      username = '$username',
                      telp = '$telp',
                      id_level = $id_level
                      WHERE id_user = $id";
        }
    } else {
        // Insert
        $password = md5($_POST['password']);
        $query = "INSERT INTO tb_user (nama_lengkap, username, password, telp, id_level) 
                  VALUES ('$nama_lengkap', '$username', '$password', '$telp', $id_level)";
    }
    mysqli_query($conn, $query);
    header('Location: data_user.php');
    exit;
}

// Get data for edit
$edit_data = null;
if(isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tb_user WHERE id_user = $id"));
}

// Get all user
$user = mysqli_query($conn, "SELECT u.*, l.level FROM tb_user u JOIN tb_level l ON u.id_level = l.id_level ORDER BY u.id_user DESC");

// Level options
$level_options = mysqli_query($conn, "SELECT * FROM tb_level");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User - Admin</title>
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
                    <a href="total_lelang.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-gavel w-5"></i>
                        <span class="ml-3">Total Lelang</span>
                    </a>
                    <a href="laporan.php" class="flex items-center px-4 py-3 text-coffee hover:bg-biscuit rounded-lg transition duration-200">
                        <i class="fas fa-file-alt w-5"></i>
                        <span class="ml-3">Laporan</span>
                    </a>
                    <a href="data_user.php" class="flex items-center px-4 py-3 bg-biscuit text-coffee rounded-lg shadow-sm border border-biscuit-dark">
                        <i class="fas fa-users w-5"></i>
                        <span class="ml-3">Data Pengguna</span>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-coffee font-serif">Kelola Pengguna</h1>
                <p class="text-gray-600 mt-2">Tambahkan, ubah, dan hapus pengguna sistem</p>
            </div>

            <!-- Form Add/Edit -->
            <div class="bg-white rounded-xl shadow-warm p-6 mb-6 border border-biscuit">
                <h2 class="text-xl font-bold text-coffee font-serif mb-4">
                    <?php echo $edit_data ? 'Edit User' : 'Tambah Pengguna Baru'; ?>
                </h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="id_user" value="<?php echo $edit_data['id_user'] ?? ''; ?>">

                    <div>
                        <label class="block text-coffee font-semibold mb-2">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" required
                            value="<?php echo $edit_data['nama_lengkap'] ?? ''; ?>"
                            class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee">
                    </div>

                    <div>
                        <label class="block text-coffee font-semibold mb-2">Nama Pengguna</label>
                        <input type="text" name="username" required autocomplete="off"
                            value="<?php echo $edit_data['username'] ?? ''; ?>"
                            class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee">
                    </div>

                    <div>
                        <label class="block text-coffee font-semibold mb-2">No. Telp</label>
                        <input type="text" name="telp"
                            value="<?php echo $edit_data['telp'] ?? ''; ?>"
                            class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee">
                    </div>

                    <div>
                        <label class="block text-coffee font-semibold mb-2">Tingkat Pengguna</label>
                        <select name="id_level" required class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee">
                            <option value="">-- Pilih Tingkat --</option>
                            <?php while($level = mysqli_fetch_assoc($level_options)): ?>
                                <option value="<?php echo $level['id_level']; ?>"
                                    <?php echo ($edit_data['id_level'] ?? '') == $level['id_level'] ? 'selected' : ''; ?>>
                                    <?php echo $level['level']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-coffee font-semibold mb-2">
                            Kata Sandi <?php echo $edit_data ? '<span class="text-xs text-gray-500">(Kosongkan jika tidak ingin ubah)</span>' : ''; ?>
                        </label>
                        <input type="password" name="password" <?php echo $edit_data ? '' : "required"; ?>
                            class="w-full px-4 py-2 border border-biscuit rounded-lg focus:ring-2 focus:ring-coffee focus:border-coffee">
                    </div>

                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit" name="submit" 
                                class="bg-coffee hover:bg-coffee-dark text-biscuit px-6 py-2 rounded-lg transition duration-200 shadow-warm">
                            <i class="fas fa-save mr-2"></i><?php echo $edit_data ? 'Update' : 'Tambah'; ?>
                        </button>
                        <?php if($edit_data): ?>
                        <a href="data_user.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg transition duration-200 shadow-warm">
                            <i class="fas fa-times mr-2"></i>Batal
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-warm overflow-hidden border border-biscuit">
                <div class="p-6 border-b border-biscuit bg-biscuit">
                    <h2 class="text-xl font-bold text-coffee font-serif">Daftar Pengguna</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-biscuit-light">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Nama Lengkap</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Nama Pengguna</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Telp</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Tingkat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-coffee uppercase tracking-wider border-b border-biscuit">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-biscuit">
                            <?php $no = 1; while($row = mysqli_fetch_assoc($user)): ?>
                            <tr class="hover:bg-biscuit-light transition duration-200">
                                <td class="px-6 py-4 text-coffee"><?php echo $no++; ?></td>
                                <td class="px-6 py-4 font-medium text-coffee"><?php echo $row['nama_lengkap']; ?></td>
                                <td class="px-6 py-4 text-coffee"><?php echo $row['username']; ?></td>
                                <td class="px-6 py-4 text-coffee"><?php echo $row['telp']; ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-biscuit text-coffee rounded-full text-xs border border-biscuit-dark">
                                        <?php echo $row['level']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="?edit=<?php echo $row['id_user']; ?>" 
                                       class="text-coffee hover:text-coffee-light mr-3 transition duration-200">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if($row['id_user'] != $_SESSION['id_user']): ?>
                                    <a href="?delete=<?php echo $row['id_user']; ?>" 
                                       onclick="return confirm('Yakin ingin menghapus user ini?')"
                                       class="text-coffee hover:text-coffee-light transition duration-200">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>