<?php
$conn= mysqli_connect("localhost" , "root", "", "seemak");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>