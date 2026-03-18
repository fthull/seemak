<?php
include '../conn.php';

$id = $_GET['id'];

$q = mysqli_query($conn,"SELECT * FROM surat WHERE id='$id'");
$d = mysqli_fetch_assoc($q);

$data = [
    "nomor"   => $d['nomor'],
    "perihal" => $d['judul'],
    "tujuan"  => $d['tujuan'],
    "isi"     => $d['isi'],
    "tanggal" => date('d F Y', strtotime($d['tanggal'])),
    "ttd"     => "Kepala MAK Negeri Ende"
];

echo json_encode($data);