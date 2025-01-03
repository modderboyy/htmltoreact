<?php
// DB connectionni sozlash
include 'db_connection.php';

// POSTdan ma'lumotlarni olish
$data = json_decode(file_get_contents("php://input"), true);
$reasons = $data['reasons'];

// Davomat sababini saqlash yoki yangilash
foreach ($reasons as $reason) {
    $xodim_id = $reason['employeeId'];
    $sabab = $reason['reason'];
    $sana = date('Y-m-d'); // Hozirgi sana (YYYY-MM-DD formatida)

    // Mavjud yozuvni tekshirish va yangilash
    $sql = "INSERT INTO davomat_sababi (xodim_id, sabab, sana) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE sabab = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $xodim_id, $sabab, $sana, $sabab);

    if (!$stmt->execute()) {
        // Xatolik yuz bersa
        echo json_encode(['status' => 'error', 'message' => 'Xatolik yuz berdi']);
        exit;
    }
}

echo json_encode(['status' => 'success', 'message' => 'Davomat sabablari saqlandi yoki yangilandi']);
?>
