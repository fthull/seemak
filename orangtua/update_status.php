<?php
include '../conn.php';

$id = $_GET['id'];

mysqli_query($conn,"
UPDATE surat_pribadi 
SET status='dibaca' 
WHERE id='$id'
");
?>