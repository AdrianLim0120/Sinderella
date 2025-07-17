<?php
$conn = new mysqli("localhost", "root", "", "sinderella_dbs");
// $conn = new mysqli("sql200.infinityfree.com", "if0_39231483", "Sinderella666", "if0_39231483_sinderella_dbs");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>