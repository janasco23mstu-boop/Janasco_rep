<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once "db.php";

if (!isset($_SESSION["user_id"]) || empty($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT ID, FULLNAME, USERNAME, USERTYPE, IMAGE FROM tbl_users WHERE ID = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$currentUser = $stmt->get_result()->fetch_assoc();

if (!$currentUser) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

$_SESSION["fullname"] = $currentUser["FULLNAME"];
$_SESSION["username"] = $currentUser["USERNAME"];
$_SESSION["usertype"] = $currentUser["USERTYPE"];
$_SESSION["user_image"] = $currentUser["IMAGE"] ?? "";

$role = $currentUser["USERTYPE"];
$currentPage = strtolower(basename($_SERVER["PHP_SELF"]));

$permissions = [
    "Admin" => ["index.php", "course.php", "yearlevel.php", "term.php", "section.php", "schoolyear.php", "subjects.php", "users.php", "usertype.php", "instructor.php", "instructors.php"],
    "Registrar" => ["subjects.php", "term.php", "instructor.php", "instructors.php", "schoolyear.php"],
    "Dean" => ["subjects.php", "section.php", "instructor.php", "instructors.php", "yearlevel.php"],
    "Instructor" => ["subjects.php", "section.php"],
    "Student" => ["subjects.php"]
];

if ($currentPage === "index.php" && $role !== "Admin") {
    header("Location: subjects.php");
    exit;
}

if (!isset($permissions[$role]) || !in_array($currentPage, $permissions[$role], true)) {
    http_response_code(403);
    $pageTitle = "Access Denied";
    $allowedPage = "subjects.php";
    if ($role === "Admin") {
        $allowedPage = "index.php";
    } elseif ($role === "Registrar") {
        $allowedPage = "subjects.php";
    } elseif ($role === "Dean") {
        $allowedPage = "subjects.php";
    } elseif ($role === "Instructor") {
        $allowedPage = "subjects.php";
    }
    include "header.php";
    ?>
    <section class="card">
        <h1>Access Denied</h1>
        <p>You do not have permission to access this page.</p>
        <a class="dash-card" href="<?= htmlspecialchars($allowedPage) ?>">Return to an allowed page</a>
    </section>
    <?php
    include "footer.php";
    exit;
}

function can_access(string $page): bool {
    global $permissions, $role;
    return isset($permissions[$role]) && in_array(strtolower($page), $permissions[$role], true);
}
?>
