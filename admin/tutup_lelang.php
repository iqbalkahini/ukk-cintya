<?php
session_start();
require_once('../config/config.php');
checkLevel([1]); // Hanya admin

if(isset($_GET['id'])) {
    $id_lelang = $_GET['id'];
    
    // Ambil data lelang
    $lelang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tb_lelang WHERE id_lelang = $id_lelang"));
    
    if($lelang) {
        // Update status lelang menjadi ditutup
        $query = "UPDATE tb_lelang SET status = 'ditutup' WHERE id_lelang = $id_lelang";
        
        if(mysqli_query($conn, $query)) {
            // Update status barang menjadi ditutup
            mysqli_query($conn, "UPDATE tb_barang SET status_barang = 'ditutup' WHERE id_barang = " . $lelang['id_barang']);
            
            $_SESSION['success'] = "Lelang berhasil ditutup";
        } else {
            $_SESSION['error'] = "Gagal menutup lelang: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Data lelang tidak ditemukan";
    }
}

header('Location: total_lelang.php');
exit;
?>