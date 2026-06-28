<?php
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "kolekcja_gier";

$conn = new mysqli($host, $user, $pass, $db_name);

if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}
session_start();
?>