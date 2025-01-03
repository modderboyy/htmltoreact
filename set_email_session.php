<?php
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'];

$_SESSION['email'] = $email;

echo json_encode(['status' => 'success']);
?>