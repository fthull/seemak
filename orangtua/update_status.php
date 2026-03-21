<?php
include '../conn.php';

$id = $_GET['id'];

mysqli_query($conn,"
UPDATE surat_resmi
SET status='terbaca'
WHERE id_surat='$id'
");
?>