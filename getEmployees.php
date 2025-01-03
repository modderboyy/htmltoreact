<?php
include('db_connection.php');

// Xodimlar ro'yxatini olish
$query = "SELECT id, nomi FROM xodimlar";
$result = $conn->query($query);

$employees = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}

$conn->close();

// JSON shaklida javob qaytarish
echo json_encode($employees);
?>