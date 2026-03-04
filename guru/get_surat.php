<?php
session_start();
include '../conn.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit;
}

$id = $_GET['id'];

// Ambil data surat
$q = mysqli_query($conn, "SELECT * FROM surat WHERE id='$id'");
if(mysqli_num_rows($q) == 0) {
    http_response_code(404);
    exit;
}

$data = mysqli_fetch_assoc($q);

// Return JSON
header('Content-Type: application/json');
echo json_encode([
    'nomor' => htmlspecialchars($data['nomor']),
    'perihal' => htmlspecialchars($data['perihal']),
    'tujuan' => htmlspecialchars($data['tujuan']),
    'isi' => htmlspecialchars($data['isi']),
    'tanggal' => htmlspecialchars($data['tanggal']),
    'ttd' => htmlspecialchars($data['ttd'])
]);
?>
