<?php
require_once "auth.php";
require_once "db.php";

$pageTitle = "Course";
$message = "";
$record = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["add"])) {
        $f0 = trim($_POST["a0"] ?? "");
        $f1 = trim($_POST["a1"] ?? "");
        $stmt = $conn->prepare("INSERT INTO tbl_course (`COURSE`, `DESCRIPTION`) VALUES (?, ?)");
        $stmt->bind_param("ss", $f0, $f1);
        $stmt->execute();
        $message = $stmt->affected_rows ? "Course added successfully." : "Unable to add record.";
    } elseif (isset($_POST["search"])) {
        $value = trim($_POST["search_value"] ?? "");
        $stmt = $conn->prepare("SELECT * FROM tbl_course WHERE `COURSE` LIKE CONCAT('%', ?, '%') ORDER BY ID LIMIT 1");
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $record = $stmt->get_result()->fetch_assoc();
        $message = $record ? "Record found. You can update or delete it below." : "No record found.";
    } elseif (isset($_POST["update"])) {
        $id = (int) ($_POST["id"] ?? 0);
        $f0 = trim($_POST["f0"] ?? "");
        $f1 = trim($_POST["f1"] ?? "");
        $stmt = $conn->prepare("UPDATE tbl_course SET `COURSE` = ?, `DESCRIPTION` = ? WHERE ID = ?");
        $stmt->bind_param("ssi", $f0, $f1, $id);
        $stmt->execute();
        $message = $stmt->affected_rows >= 0 ? "Course updated successfully." : "Unable to update record.";
        $stmt = $conn->prepare("SELECT * FROM tbl_course WHERE ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $record = $stmt->get_result()->fetch_assoc();
    } elseif (isset($_POST["delete"])) {
        $id = (int) ($_POST["id"] ?? 0);
        $stmt = $conn->prepare("DELETE FROM tbl_course WHERE ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = $stmt->affected_rows ? "Course deleted successfully." : "Record was not deleted.";
        $record = null;
    }
}
$allRecords = $conn->query("SELECT ID, `COURSE`, `DESCRIPTION` FROM tbl_course ORDER BY ID");
include "header.php";
?>
<section class="card">
    <div class="page-heading">
        <div>
            <h1>Course</h1>
            <p>You can Add, Search, Update and Delete here the Course!</p>
        </div>
    </div>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <div class="two-panels">
        <section>
            <h2>Add Course</h2>
            <form method="post" class="grid">
                <div><label>Course</label><input type="text" name="a0" required></div>
                <div><label>Description</label><input type="text" name="a1" required></div>

                <button class="full" type="submit" name="add">Add Course</button>
            </form>
        </section>
        <section>
            <h2>Search Course</h2>
            <form method="post" class="search">
                <input type="text" name="search_value" placeholder="Search by Course" required>
                <button type="submit" name="search">Search</button>
            </form>
            <div class="record-list"><h3>All Course Records</h3><table><tr><th>ID</th><th>Course</th><th>Description</th><th>Action</th></tr><?php while ($item = $allRecords->fetch_assoc()): ?><tr><td><?= (int) $item["ID"] ?></td><td><?= htmlspecialchars($item["COURSE"]) ?></td><td><?= htmlspecialchars($item["DESCRIPTION"]) ?></td><td class="record-actions"><form method="post"><input type="hidden" name="search_value" value="<?= htmlspecialchars($item["COURSE"]) ?>"><button name="search">Edit</button></form><form method="post" onsubmit="return confirm('Delete this record?')"><input type="hidden" name="id" value="<?= (int) $item["ID"] ?>"><button name="delete" class="delete">Delete</button></form></td></tr><?php endwhile; ?></table></div>
        </section>
    </div>

    <?php if ($record): ?>
        <hr>
        <h2>Edit Course <?= (int) $record["ID"] ?></h2>
        <form method="post" class="grid">
            <input type="hidden" name="id" value="<?= (int) $record["ID"] ?>">
            <div class=""><label>Course</label><input type="text" name="f0"
                    value="<?= htmlspecialchars($record['COURSE'] ?? '') ?>" required></div>
            <div class=""><label>Description</label><input type="text" name="f1"
                    value="<?= htmlspecialchars($record['DESCRIPTION'] ?? '') ?>" required></div>
            <button type="submit" name="update">Update Course</button>
            <button type="submit" name="delete" class="delete" onclick="return confirm('Delete this record?')">Delete
                Course</button>
        </form>
    <?php endif; ?>
</section>
<?php include "footer.php"; ?>