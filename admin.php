<?php
session_start();

// Agar admin tizimga kirgan bo'lmasa, login sahifasiga yo'naltirish
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: adminlgn.php");
    exit();
}

include('db_connection.php');
require 'vendor/autoload.php'; // PhpSpreadsheet kutubxonasini chaqirish

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Toshkent vaqtini sozlash
date_default_timezone_set('Asia/Tashkent');

// Tanlangan sana
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Tanlangan sanaga ko'ra davomatni olish
$query = "SELECT xodimlar.nomi, davomat.kelish_vaqti, davomat.ketish_vaqti, davomat_sababi.sabab 
          FROM xodimlar 
          LEFT JOIN davomat ON xodimlar.id = davomat.xodim_id AND DATE(davomat.kelish_vaqti) = ?
          LEFT JOIN davomat_sababi ON xodimlar.id = davomat_sababi.xodim_id AND DATE(davomat_sababi.sana) = ?
          ORDER BY xodimlar.nomi ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $date, $date);
$stmt->execute();
$result = $stmt->get_result();

$attendance_data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $attendance_data[] = $row;
    }
}
$stmt->close();
$conn->close();

// Excel faylini yuklab olish uchun funksiya
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Sarlavhalar
    $sheet->setCellValue('A1', 'Xodim');
    $sheet->setCellValue('B1', 'Kelish Vaqti');
    $sheet->setCellValue('C1', 'Ketish Vaqti');
    $sheet->setCellValue('D1', 'QatnashmaslikSabab');

    // Ma'lumotlarni jadvalga qo'shish
    $row_num = 2; // 1-qator sarlavhalar uchun
    foreach ($attendance_data as $attendance) {
        $sheet->setCellValue('A' . $row_num, $attendance['nomi']);
        $sheet->setCellValue('B' . $row_num, $attendance['kelish_vaqti'] ? date('H:i', strtotime($attendance['kelish_vaqti'])) : '-');
        $sheet->setCellValue('C' . $row_num, $attendance['ketish_vaqti'] ? date('H:i', strtotime($attendance['ketish_vaqti'])) : '-');
        $sheet->setCellValue('D' . $row_num, $attendance['sabab'] ?: 'Noma\'lum sabab');
        $row_num++;
    }

    // Faylni Excel formatida eksport qilish
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="davomat.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}
?>

<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Davomat Jadvali</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .calendar {
            margin: 20px 0;
            text-align: center;
        }
        .calendar input[type="date"] {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #fdfdfd;
            transition: all 0.3s ease;
        }
        .calendar input[type="date"]:hover {
            border-color: #007bff;
            background-color: #f1f9ff;
        }
        .calendar input[type="date"]:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border: 1px solid #000000;
color: black;
font-family: 'Arial Black';
        }
        th {
            background-color: #007bff;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .text-center {
            text-align: center;
        }
        @media (max-width: 600px) {
            th, td {
                font-size: 14px;
color: black;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="font-family: 'Arial Black', sans-serif; text-align: center; color: #333;">"YETTILIK" Davomati</h1>

        <!-- Kalendar -->
        <div class="calendar">
            <form method="GET" action="admin.php">
                <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" onchange="this.form.submit()">
            </form>
        </div>

        <!-- Excel tugmasi -->
        <div class="calendar">
            <a href="admin.php?date=<?= htmlspecialchars($date) ?>&export=excel">
                <button type="button" style="padding: 10px 20px; font-size: 16px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Excel faylini yuklab olish
                </button>
            </a>
        </div>

        <!-- Jadval -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Xodim</th>
                        <th>Kelish Vaqti</th>
                        <th>Ketish Vaqti</th>
                        <th>Davomat Sababi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($attendance_data) > 0): ?>
                        <?php foreach ($attendance_data as $attendance): ?>
                            <?php
                                // Kelish vaqti rangini aniqlash
                                $kelish_vaqti = strtotime($attendance['kelish_vaqti']);
                                $kelish_rang = '';
                                if ($kelish_vaqti) {
                                    if ($kelish_vaqti >= strtotime('06:00') && $kelish_vaqti <= strtotime('09:00')) {
                                        $kelish_rang = 'color: green;';
                                    } else {
                                        $kelish_rang = 'color: red;';
                                    }
                                }

                                // Ketish vaqti rangini aniqlash
                                $ketish_vaqti = strtotime($attendance['ketish_vaqti']);
                                $ketish_rang = '';
                                if ($ketish_vaqti) {
                                    if ($ketish_vaqti >= strtotime('18:00') && $ketish_vaqti <= strtotime('23:59')) {
                                        $ketish_rang = 'color: green;';
                                    } else {
                                        $ketish_rang = 'color: red;';
                                    }
                                }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($attendance['nomi']); ?></td>
                                <td style="<?= $kelish_rang; ?>">
                                    <?php echo $attendance['kelish_vaqti'] ? date('H:i', $kelish_vaqti) : '-'; ?>
                                </td>
                                <td style="<?= $ketish_rang; ?>">
                                    <?php echo $attendance['ketish_vaqti'] ? date('H:i', $ketish_vaqti) : '-'; ?>
                                </td>
                                <td>
                                    <?php echo $attendance['sabab'] ?: "-"; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Hozircha ma'lumot mavjud emas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>