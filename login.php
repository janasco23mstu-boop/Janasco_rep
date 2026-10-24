<?php
session_start();
require_once "db.php";

if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $message = "Please enter your username and password.";
    } else {
        $stmt = $conn->prepare("SELECT ID, FULLNAME, USERNAME, PASSWORD, USERTYPE, IMAGE FROM tbl_users WHERE USERNAME = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user["PASSWORD"])) {
            if (password_needs_rehash($user["PASSWORD"], PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE tbl_users SET PASSWORD = ? WHERE ID = ?");
                $update->bind_param("si", $newHash, $user["ID"]);
                $update->execute();
            }

            session_regenerate_id(true);
            $_SESSION["user_id"] = (int) $user["ID"];
            $_SESSION["fullname"] = $user["FULLNAME"];
            $_SESSION["username"] = $user["USERNAME"];
            $_SESSION["usertype"] = $user["USERTYPE"];
            $_SESSION["user_image"] = $user["IMAGE"] ?? "";

            header("Location: index.php");
            exit;
        }

        $message = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <section class="auth-card">
            <h1>Login</h1>
            <p class="auth-subtitle">DB Management System</p>
            <?php if ($message): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="post">
                <label>Username</label>
                <input name="username" required autofocus>
                <label>Password</label>
                <input type="password" name="password" required>
                <button class="auth-button" type="submit">Login</button>
                <p style="text-align:center; margin-top:20px;">
                    Don't have an account?
                    <a href="signup.php">Create Account</a>
                </p>
            </form>
        </section>
    </div>
</body>
</html>
