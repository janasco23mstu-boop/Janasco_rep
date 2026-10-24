<?php
require_once "auth.php";
require_once "db.php";

$pageTitle = "Dashboard";
include "header.php";
?>

<section class="card">
    <h1>Dashboard</h1>
    <p>Welcome, <strong><?= htmlspecialchars($_SESSION["username"]) ?></strong>!</p>

    <div class="dashboard-grid">
        <?php
        $items = [
            ["course.php", "Course", "tbl_course"],
            ["yearlevel.php", "Year Level", "tbl_yearlevel"],
            ["term.php", "Term", "tbl_term"],
            ["section.php", "Section", "tbl_section"],
            ["schoolyear.php", "School Year", "tbl_schoolyear"],
            ["subjects.php", "Subjects", "tbl_subject"],
            ["Instructor.PHP", "Instructor", "tbl_instructors"],
            ["users.php", "Users", "tbl_users"],
            ["usertype.php", "Usertype", "tbl_usertype"]
        ];

        foreach ($items as [$url, $label, $table]) {
            $r = $conn->query("SELECT COUNT(*) AS c FROM `$table`")->fetch_assoc();
            echo '<a class="dash-card" href="' . htmlspecialchars($url) . '"><strong>' . htmlspecialchars($label) . '</strong><span>' . (int) $r["c"] . ' records</span></a>';
        }
        ?>
    </div>

    <section class="info">
        <h2>User Access</h2>
        <p>Admin: Can access all pages.</p>
        <p>Registrar: Can access only the Subjects, Term, Instructor and School Year page.</p>
        <p>Dean: Can access only the Subjects, Section, Instructor and Year Level page.</p>
        <p>Instructor: Can access only the Subjects and Section page.</p>
        <p>Student: Can access the Subjects page only.</p>
    </section>
</section>

<?php include "footer.php"; ?>