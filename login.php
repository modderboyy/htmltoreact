<?php
include('db_connection.php');
session_start();

// JSON formatda kiruvchi ma'lumotlarni olish
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'];
$password = $data['password'];

// Foydalanuvchi ma'lumotlarini bazadan olish
$query = "SELECT * FROM foydalanuvchilar WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email yoki parol noto‘g‘ri!']);
    exit;
}

$row = $result->fetch_assoc();
$stored_password = $row['parol'];  // Baza'dagi parol (hashlanmış)

if (password_verify($password, $stored_password)) {
    // Parol to'g'ri bo'lsa, foydalanuvchini tizimga kirgizish
    $_SESSION['foydalanuvchi_id'] = $row['id'];
    $role = $row['role'];  // Foydalanuvchining roli
    echo json_encode(['status' => 'success', 'message' => 'Tizimga muvaffaqiyatli kirildi', 'role' => $role]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Email yoki parol noto‘g‘ri!']);
}

$stmt->close();
$conn->close();
?>