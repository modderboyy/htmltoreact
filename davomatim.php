<?php
header('Content-Type: application/json');
require 'db.php'; // Ma'lumotlar bazasiga ulanish

$data = json_decode(file_get_contents('php://input'), true);
$xodim_id = $data['xodim_id'];

$query = $db->prepare("SELECT f.ism, d.kelish_vaqti, d.ketish_vaqti FROM davomat d 
                        JOIN foydalanuvchilar f ON d.xodim_id = f.id
                        WHERE d.xodim_id = ?");
$query->execute([$xodim_id]);
$results = $query->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);