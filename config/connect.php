<?php 
session_start();
$conn = mysqli_connect("localhost", "root", "", "db_ukm");

if(!$conn || $conn->connect_error) {
    die("Koneksi Gagal : " . $conn->connect_error);
}

?>