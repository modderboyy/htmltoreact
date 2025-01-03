<?php
$host = "localhost";
$username = "bestboy_musapp";
$password = "Diyor2010";
$database = "bestboy_musapp";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Маълумотлар базасига уланиш хато: " . mysqli_connect_error());
}
?>