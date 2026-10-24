<?php
require_once "includes/functions.php";

if (!empty($_SESSION['user_id'])) {
    redirect(home_for_role($_SESSION['role'] ?? ''));
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appointment Booking System</title>
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Tahoma', Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,.08);
            margin-bottom: 40px;
        }

        .header .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header .logo span {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }

        .header .actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            background: #3498db;
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            transition: 0.3s;
        }

        .btn:hover {
            background: #2980b9;
        }

        .actions-box {
            background: #fff;
            padding: 60px 40px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,.08);
            text-align: center;
            max-width: 1000px;
            min-height: 300px;
            margin: 0 auto 50px;
        }

        .actions-box h1 {
            font-size: 50px;
            color: #2c3e50;
            margin: 0;
            line-height: 1.4;
        }

        .actions-box p {
            font-size: 20px;
            color: #555;
        }

        .actions-box .actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 50px;
            margin-top: 30px;
        }

        @media (max-width: 600px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .header .actions {
                justify-content: center;
            }

            .actions-box {
                padding: 40px 20px;
            }

            .actions-box h1 {
                font-size: 32px;
            }

            .actions-box p {
                font-size: 16px;
            }

            .actions-box .actions {
                gap: 15px;
            }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="logo">
        <span>Appointment Booking System</span>
    </div>

    <div class="actions">
        <a href="login.php" class="btn">Log In</a>
        <a href="register.php" class="btn">Sign Up</a>
    </div>
</header>

<section class="actions-box">
    <h1>Welcome<br> to the Appointment Booking System</h1>
    <p>Log in or create a new account to start managing your appointments quickly and easily.</p>
    <div class="actions">
        <a href="login.php" class="btn">Log In</a>
        <a href="register.php" class="btn">Sign Up</a>
    </div>
</section>

</body>
</html>
