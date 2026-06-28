<?php
session_start();
include 'dbconnect.php';

$error = "";

/* DEBUG: remove later */
echo "<pre>";
print_r($_POST);
echo "</pre>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        $conn = new PDO(
            "mysql:host=$servername;dbname=$database",
            $username,
            $password
        );

        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get submitted values safely
        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';

        if (empty($user) || empty($pass)) {
            $error = "Username and password are required.";
        } else {

            // Change table name if needed
            $stmt = $conn->prepare("
                SELECT * FROM user
                WHERE username = :username
            ");

            $stmt->bindParam(':username', $user);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {

                // Password check
                if ($pass == $result['password']) {

                    $_SESSION['username'] = $result['username'];

                    header("Location: admin_menu.php");
                    exit();

                } else {
                    $error = "Incorrect password.";
                }

            } else {
                $error = "Username not found.";
            }
        }

    } catch (PDOException $e) {
        $error = "Connection failed: " . $e->getMessage();
    }

} else {
    $error = "Invalid request method.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Cit-E Cycling</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root{
            --forest:#1e7a46;
            --forest-dark:#145c34;
            --amber:#f2a93b;
            --ink:#101b2c;
        }

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'Inter',sans-serif;
            color:var(--ink);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            background:linear-gradient(
                150deg,
                var(--ink) 0%,
                var(--forest-dark) 65%,
                var(--forest) 100%
            );
        }

        h1,h2,h3{
            font-family:'Space Grotesk','Inter',sans-serif;
        }

        .simple-page{
            max-width:500px;
            width:90%;
            padding:40px;
            background:white;
            border-radius:20px;
            box-shadow:0 20px 40px rgba(0,0,0,.25);
            text-align:center;
        }

        .simple-page h2{
            color:#c0392b;
            margin-bottom:15px;
        }

        .simple-page a{
            text-decoration:none;
            color:var(--forest);
            font-weight:600;
        }

        .simple-page a:hover{
            color:var(--forest-dark);
        }
    </style>
</head>
<body>

<div class="simple-page">
    <h2><?php echo htmlspecialchars($error); ?></h2>
    <a href="admin_login.html">← Back to Login</a>
</div>

</body>
</html>