<?php
session_start();
include '../conn.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID tidak ditemukan']);
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$user_id = $_SESSION['id'];

// Ambil ID orang tua terlebih dahulu
$qOrtu = mysqli_query($conn, "SELECT id FROM orang_tua WHERE user_id='$user_id'");
$dOrtu = mysqli_fetch_assoc($qOrtu);
$id_orangtua = $dOrtu['id'];

// Ambil data surat berdasarkan ID dan pastikan milik orang tua tersebut
$query = mysqli_query($conn, "
    SELECT * FROM surat_wali 
    WHERE id = '$id' AND id_orangtua = '$id_orangtua'
");

if (mysqli_num_rows($query) > 0) {
    $data = mysqli_fetch_assoc($query);
    
    // Konversi baris baru (\n) menjadi <br> agar rapi di HTML
    $data['isi'] = nl2br($data['isi']);
    
    echo json_encode($data);
} else {
    echo json_encode(['error' => 'Surat tidak ditemukan atau akses dilarang']);
}
?>