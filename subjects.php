<?php
require_once "auth.php";
require_once "db.php";

$pageTitle = "Subjects";
$message = "";
$record = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["add"]) || isset($_POST["update"])) {
        $course = trim($_POST["course"] ?? "");
        $yearlevel = trim($_POST["yearlevel"] ?? "");
        $term = trim($_POST["term"] ?? "");
        $code = trim($_POST["code"] ?? "");
        $subjecttitle = trim($_POST["subjecttitle"] ?? "");
        $lecture = (float) ($_POST["lecture"] ?? 0);
        $laboratory = (float) ($_POST["laboratory"] ?? 0);
        $credit = (float) ($_POST["credit"] ?? 0);
        $grade = (float) ($_POST["grade"] ?? 0);
        $pre = trim($_POST["pre_requisite"] ?? "");

        if (isset($_POST["add"])) {
            $stmt = $conn->prepare("INSERT INTO tbl_subject (COURSE,YEARLEVEL,TERM,CODE,SUBJECTTITLE,LECTURE,LABORATORY,CREDIT,GRADE,`PRE-REQUISITE`) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssddds", $course, $yearlevel, $term, $code, $subjecttitle, $lecture, $laboratory, $credit, $grade, $pre);
            $stmt->execute();
            $message = $stmt->affected_rows ? "Subject added successfully." : "Unable to add subject.";
        } else {
            $id = (int) ($_POST["id"] ?? 0);
            $stmt = $conn->prepare("UPDATE tbl_subject SET COURSE=?,YEARLEVEL=?,TERM=?,CODE=?,SUBJECTTITLE=?,LECTURE=?,LABORATORY=?,CREDIT=?,GRADE=?,`PRE-REQUISITE`=? WHERE ID=?");
            $stmt->bind_param("ssssssdddsi", $course, $yearlevel, $term, $code, $subjecttitle, $lecture, $laboratory, $credit, $grade, $pre, $id);
            $stmt->execute();
            $message = "Subject updated successfully.";
            $stmt = $conn->prepare("SELECT * FROM tbl_subject WHERE ID=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $record = $stmt->get_result()->fetch_assoc();
        }
    } elseif (isset($_POST["search"])) {
        $value = trim($_POST["search_value"] ?? "");
        $stmt = $conn->prepare("SELECT * FROM tbl_subject WHERE CODE LIKE CONCAT('%',?,'%') OR SUBJECTTITLE LIKE CONCAT('%',?,'%') ORDER BY ID LIMIT 1");
        $stmt->bind_param("ss", $value, $value);
        $stmt->execute();
        $record = $stmt->get_result()->fetch_assoc();
        $message = $record ? "Record found. You can update or delete it below." : "No subject found.";
    } elseif (isset($_POST["delete"])) {
        $id = (int) ($_POST["id"] ?? 0);
        $stmt = $conn->prepare("DELETE FROM tbl_subject WHERE ID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = $stmt->affected_rows ? "Subject deleted successfully." : "Subject was not deleted.";
        $record = null;
    }
}
$allRecords = $conn->query("SELECT ID, CODE, SUBJECTTITLE, COURSE, YEARLEVEL, TERM FROM tbl_subject ORDER BY ID");
include "header.php";
?>
<section class="card">
    <h1>Subjects</h1>
    <p>You can Add, Search, Update and Delete here the Subjects!</p>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <div class="two-panels">
        <section>
            <h2>Add Subject</h2>
            <form method="post" class="grid">
                <div><label>Course</label><input name="course" required></div>
                <div><label>Year Level</label><input name="yearlevel" required></div>
                <div><label>Term</label><input name="term" required></div>
                <div><label>Code</label><input name="code" required></div>
                <div class="full"><label>Subject Title</label><input name="subjecttitle" required></div>
                <div><label>Lecture</label><input type="number" step="0.01" name="lecture" value="0" required></div>
                <div><label>Laboratory</label><input type="number" step="0.01" name="laboratory" value="0" required>
                </div>
                <div><label>Credit</label><input type="number" step="0.01" name="credit" value="0" required></div>
                <div><label>Grade</label><input type="number" step="0.01" name="grade" value="0" required></div>
                <div class="full"><label>Pre-Requisite</label><input name="pre_requisite" value="NONE" required></div>
                <button class="full" name="add">Add Subject</button>
            </form>
        </section>
        <section>
            <h2>Search Subject</h2>
            <form method="post" class="search">
                <input name="search_value" placeholder="Search code or subject title" required>
                <button name="search">Search</button>
            </form>
            <div class="record-list"><h3>All Subject Records</h3><table><tr><th>ID</th><th>Code</th><th>Subject Title</th><th>Course</th><th>Year Level</th><th>Term</th><th>Action</th></tr><?php while ($item = $allRecords->fetch_assoc()): ?><tr><td><?= (int) $item["ID"] ?></td><td><?= htmlspecialchars($item["CODE"]) ?></td><td><?= htmlspecialchars($item["SUBJECTTITLE"]) ?></td><td><?= htmlspecialchars($item["COURSE"]) ?></td><td><?= htmlspecialchars($item["YEARLEVEL"]) ?></td><td><?= htmlspecialchars($item["TERM"]) ?></td><td class="record-actions"><form method="post"><input type="hidden" name="search_value" value="<?= htmlspecialchars($item["CODE"]) ?>"><button name="search">Edit</button></form><form method="post" onsubmit="return confirm('Delete this record?')"><input type="hidden" name="id" value="<?= (int) $item["ID"] ?>"><button name="delete" class="delete">Delete</button></form></td></tr><?php endwhile; ?></table></div>
        </section>
    </div>

    <?php if ($record): ?>
        <hr>
        <h2>Record #<?= (int) $record["ID"] ?></h2>
        <form method="post" class="grid">
            <input type="hidden" name="id" value="<?= (int) $record["ID"] ?>">
            <div><label>Course</label><input name="course" value="<?= htmlspecialchars($record["COURSE"]) ?>" required>
            </div>
            <div><label>Year Level</label><input name="yearlevel" value="<?= htmlspecialchars($record["YEARLEVEL"]) ?>"
                    required></div>
            <div><label>Term</label><input name="term" value="<?= htmlspecialchars($record["TERM"]) ?>" required></div>
            <div><label>Code</label><input name="code" value="<?= htmlspecialchars($record["CODE"]) ?>" required></div>
            <div class="full"><label>Subject Title</label><input name="subjecttitle"
                    value="<?= htmlspecialchars($record["SUBJECTTITLE"]) ?>" required></div>
            <div><label>Lecture</label><input type="number" step="0.01" name="lecture"
                    value="<?= htmlspecialchars($record["LECTURE"]) ?>" required></div>
            <div><label>Laboratory</label><input type="number" step="0.01" name="laboratory"
                    value="<?= htmlspecialchars($record["LABORATORY"]) ?>" required></div>
            <div><label>Credit</label><input type="number" step="0.01" name="credit"
                    value="<?= htmlspecialchars($record["CREDIT"]) ?>" required></div>
            <div><label>Grade</label><input type="number" step="0.01" name="grade"
                    value="<?= htmlspecialchars($record["GRADE"]) ?>" required></div>
            <div class="full"><label>Pre-Requisite</label><input name="pre_requisite"
                    value="<?= htmlspecialchars($record["PRE-REQUISITE"]) ?>" required></div>
            <button name="update">Update Subject</button>
            <button name="delete" class="delete" onclick="return confirm('Delete this subject?')">Delete Subject</button>
        </form>
    <?php endif; ?>
</section>
<?php include "footer.php"; ?>