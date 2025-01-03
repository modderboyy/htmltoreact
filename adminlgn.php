<?php
session_start();
include('db_connection.php');

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // admin.txt faylidan email va parolni o'qish
    $admin_file = 'admin.txt';
    if (file_exists($admin_file)) {
        $file_content = file($admin_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $admin_email = trim($file_content[0]);
        $admin_password = trim($file_content[1]);

        // Email va parolni tekshirish
        if ($email === $admin_email && $password === $admin_password) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['email'] = $email;
            header("Location: admin.php");
            exit();
        } else {
            $error_message = "Email yoki parol noto'g'ri!";
        }
    } else {
        $error_message = "admin.txt fayli topilmadi!";
    }
}
?>

<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Panelga Kirish</h1>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Parol</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Kirish</button>
        </form>
    </div>
</body>
</html>