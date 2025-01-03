<?php
include('db_connection.php');
session_start();
// Set the default time zone to Asia/Tashkent
date_default_timezone_set('Asia/Tashkent');

// Get the current date and time
$current_date = date('Y-m-d');
$current_time = date('Y-m-d H:i:s');
// Foydalanuvchini tekshirish
if (!isset($_SESSION['foydalanuvchi_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Iltimos, tizimga kiring.']);
    exit;
}

// Sessiyadan foydalanuvchi ID va `son` qiymatini olish
$foydalanuvchi_id = $_SESSION['foydalanuvchi_id'];
$query = "SELECT son FROM foydalanuvchilar WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $foydalanuvchi_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Foydalanuvchi topilmadi.']);
    exit;
}
$row = $result->fetch_assoc();
$user_son = $row['son'];

// QR kodni olish
$data = json_decode(file_get_contents('php://input'), true);
$qr_code = $data['qr_code'];

// Xodimlar jadvalidan QR kodni tekshirish
$query = "SELECT id, qr_kod, qr_kod_chiqish FROM xodimlar WHERE son = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_son);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Xodim topilmadi.']);
    exit;
}
$row = $result->fetch_assoc();
$xodim_id = $row['id'];
$qr_kod = $row['qr_kod'];
$qr_kod_chiqish = $row['qr_kod_chiqish'];

// Bugungi sana va vaqt
$current_date = date('Y-m-d');
$current_time = date('Y-m-d H:i:s');

// QR kodni tekshirish
if ($qr_code === $qr_kod) {
    // Kelish QR kodini skanerlagan bo'lsa
    // Bugungi davomatni tekshirish
    $query = "SELECT * FROM davomat WHERE xodim_id = ? AND DATE(kelish_vaqti) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $xodim_id, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();

    // Agar kelish vaqtini saqlagan bo'lsa
    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Siz bugun ish vaqtini boshladingiz.']);
        exit;
    }

    // Kelish vaqtini saqlash
    $query = "INSERT INTO davomat (xodim_id, kelish_vaqti) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $xodim_id, $current_time);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Kelish vaqti muvaffaqiyatli saqlandi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Kelish vaqtini saqlashda xatolik yuz berdi.']);
    }
} elseif ($qr_code === $qr_kod_chiqish) {
    // Ketish QR kodini skanerlagan bo'lsa
    // Bugungi davomatni tekshirish
    $query = "SELECT * FROM davomat WHERE xodim_id = ? AND DATE(kelish_vaqti) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $xodim_id, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();

    // Agar kelish vaqti saqlangan bo'lsa
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Siz bugun kelish vaqtini saqlamadingiz.']);
        exit;
    }

    // Ketish vaqtini yangilash
    $davomat = $result->fetch_assoc();
    if (!empty($davomat['ketish_vaqti'])) {
        echo json_encode(['status' => 'error', 'message' => 'Siz bugun ketish vaqtini saqladingiz.']);
        exit;
    }

    // Ketish vaqtini saqlash
    $query = "UPDATE davomat SET ketish_vaqti = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $current_time, $davomat['id']);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Ketish vaqti muvaffaqiyatli saqlandi.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ketish vaqtini saqlashda xatolik yuz berdi.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'QR kod noto‘g‘ri.']);
}

// Ulashni yopish
$stmt->close();
$conn->close();
?>