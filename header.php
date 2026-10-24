<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$loggedInFullname = $_SESSION["fullname"] ?? $_SESSION["username"] ?? "User";
$loggedInUsername = $_SESSION["username"] ?? "User";
$loggedInUsertype = $_SESSION["usertype"] ?? "User";
$loggedInImage = $_SESSION["user_image"] ?? "";
$loggedInImageExists = $loggedInImage !== "" && is_file(__DIR__ . "/" . $loggedInImage);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? "Dashboard") ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header class="site-header">
        <div class="logo">
            <img src="images/mylogo.png" alt="Japs Creation Logo">
            <span class="logo-red">JAPS</span>
            <span class="logo-white">CREATION</span>
        </div>
        <div class="session-user">
            <?php if ($loggedInImageExists): ?>
                <img class="user-avatar" src="<?= htmlspecialchars($loggedInImage) ?>" alt="Profile image">
            <?php else: ?>
                <i class="fa-solid fa-circle-user"></i>
            <?php endif; ?>
            <span><strong><?= htmlspecialchars($loggedInFullname) ?></strong>
                (<?= htmlspecialchars($loggedInUsertype) ?>)</span>
        </div>
    </header>

    <div class="layout">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <?php if (function_exists("can_access") && can_access("index.php")): ?>
                    <a href="index.php"><i class="fa-solid fa-house"></i><span>Dashboard</span></a>
                <?php endif; ?>

                <?php if (function_exists("can_access") && can_access("course.php")): ?>
                    <a href="course.php"><i class="fa-solid fa-book"></i><span>Course</span></a>
                <?php endif; ?>

                <?php if (function_exists("can_access") && can_access("yearlevel.php")): ?>
                    <a href="yearlevel.php"><i class="fa-solid fa-layer-group"></i><span>Year Level</span></a>
                <?php endif; ?>

                <?php if (function_exists("can_access") && can_access("term.php")): ?>
                    <a href="term.php"><i class="fa-solid fa-calendar"></i><span>Term</span></a>
                <?php endif; ?>

                <?php if (function_exists("can_access") && can_access("section.php")): ?>
                    <a href="section.php"><i class="fa-solid fa-users"></i><span>Section</span></a>
                <?php endif; ?>

                <?php if (function_exists("can_access") && can_access("schoolyear.php")): ?>
                    <a href="schoolyear.php"><i class="fa-solid fa-school"></i><span>School Year</span></a>
                <?php endif; ?>

                <?php if (function_exists("can_access") && can_access("subjects.php")): ?>
                    <a href="subjects.php"><i class="fa-solid fa-book-open"></i><span>Subjects</span></a>
                <?php endif; ?>

                <?php if (function_exists("can_access") && can_access("instructor.php")): ?>
                    <a href="Instructor.PHP"><i class="fa-solid fa-chalkboard-user"></i><span>Instructor</span></a>
                <?php endif; ?>

                <?php if (function_exists("can_access") && can_access("users.php")): ?>
                    <a href="users.php"><i class="fa-solid fa-user"></i><span>Users</span></a>
                <?php endif; ?>

                <?php if (function_exists("can_access") && can_access("usertype.php")): ?>
                    <a href="usertype.php"><i class="fa-solid fa-user-tag"></i><span>Usertype</span></a>
                <?php endif; ?>

                <a href="logout.php" class="logout-link"><i
                        class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
            </nav>
        </aside>

        <main class="main-content">