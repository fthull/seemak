<?php
session_start();
include '../conn.php';

$id = $_GET['id'];

$q = mysqli_query($conn,"SELECT * FROM surat WHERE id='$id'");
$d = mysqli_fetch_assoc($q);

$data = [
    "nomor"   => $d['nomor'],
    "perihal" => $d['judul'],
    "tujuan"  => $_SESSION['nama'], // Menggunakan nama orang tua yang login
    "isi"     => $d['isi'],
    "tanggal" => date('d F Y', strtotime($d['tanggal'])),
    "ttd"     => $d['ttd']
];

echo json_encode($data);