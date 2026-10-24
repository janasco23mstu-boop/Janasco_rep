<?php
session_start();
require_once "db.php";
require_once "user_image.php";

if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$message = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm"] ?? "";
    $usertype = $_POST["usertype"] ?? "";

    $allowed_types = ["Admin", "Registrar", "Dean", "Instructor", "Student"];

    if ($fullname === "" || $username === "" || $password === "" || $confirm === "" || $usertype === "") {
        $message = "Please complete all fields.";
    } elseif (!in_array($usertype, $allowed_types, true)) {
        $message = "Please select a valid usertype.";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } elseif (!isset($_FILES["imageUpload"]) || $_FILES["imageUpload"]["error"] === UPLOAD_ERR_NO_FILE) {
        $message = "Please choose a profile image.";
    } else {
        $stmt = $conn->prepare("SELECT ID FROM tbl_users WHERE USERNAME = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            $message = "Username already exists.";
        } else {
            $upload = save_user_image($username);
            if (!$upload["ok"]) {
                $message = $upload["message"];
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $imagePath = $upload["path"];
                $stmt = $conn->prepare("INSERT INTO tbl_users (FULLNAME, USERNAME, PASSWORD, USERTYPE, IMAGE) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $fullname, $username, $hash, $usertype, $imagePath);

                if ($stmt->execute()) {
                    $success = "Account created successfully as " . htmlspecialchars($usertype) . ". You can now log in.";
                } else {
                    @unlink(__DIR__ . "/" . $imagePath);
                    $message = "Unable to create account.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-body">
    <div class="auth-container">
        <section class="auth-card">
            <h1>Sign Up</h1>
            <?php if ($message): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-message"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <label>Full Name</label>
                <input name="fullname" required>
                <label>Username</label>
                <input name="username" required>
                <label>Your Image (Add 1x1 Profile Picture)</label>
                <input type="file" name="imageUpload" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif"
                    required>
                <label>Usertype</label>
                <select name="usertype" required>
                    <option value="">Select Usertype</option>
                    <option value="Admin">Admin</option>
                    <option value="Registrar">Registrar</option>
                    <option value="Dean">Dean</option>
                    <option value="Instructor">Instructor</option>
                    <option value="Student">Student</option>
                </select>
                <label>Password</label>
                <input type="password" name="password" required>
                <label>Confirm Password</label>
                <input type="password" name="confirm" required>
                <button class="auth-button" type="submit">Create Account</button>
            </form>
            <p class="auth-switch"><a href="login.php">Back to Login</a></p>
        </section>
    </div>
</body>

</html>