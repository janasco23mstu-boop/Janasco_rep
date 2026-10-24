<?php
require_once "auth.php";
require_once "db.php";

$pageTitle = "Usertype";
$message = "";
$record = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["add"])) {
        $f0 = trim($_POST["a0"] ?? "");
        $stmt = $conn->prepare("INSERT INTO tbl_usertype (`Usertype`) VALUES (?)");
        $stmt->bind_param("s", $f0);
        $stmt->execute();
        $message = $stmt->affected_rows ? "Usertype added successfully." : "Unable to add record.";
    } elseif (isset($_POST["search"])) {
        $value = trim($_POST["search_value"] ?? "");
        $stmt = $conn->prepare("SELECT * FROM tbl_usertype WHERE `Usertype` LIKE CONCAT('%', ?, '%') ORDER BY ID LIMIT 1");
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $record = $stmt->get_result()->fetch_assoc();
        $message = $record ? "Record found. You can update or delete it below." : "No record found.";
    } elseif (isset($_POST["update"])) {
        $id = (int) ($_POST["id"] ?? 0);
        $f0 = trim($_POST["f0"] ?? "");
        $stmt = $conn->prepare("UPDATE tbl_usertype SET `Usertype` = ? WHERE ID = ?");
        $stmt->bind_param("si", $f0, $id);
        $stmt->execute();
        $message = $stmt->affected_rows >= 0 ? "Usertype updated successfully." : "Unable to update record.";
        $stmt = $conn->prepare("SELECT * FROM tbl_usertype WHERE ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $record = $stmt->get_result()->fetch_assoc();
    } elseif (isset($_POST["delete"])) {
        $id = (int) ($_POST["id"] ?? 0);
        $stmt = $conn->prepare("DELETE FROM tbl_usertype WHERE ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = $stmt->affected_rows ? "Usertype deleted successfully." : "Record was not deleted.";
        $record = null;
    }
}
$allRecords = $conn->query("SELECT ID, Usertype FROM tbl_usertype ORDER BY ID");
include "header.php";
?>
<section class="card">
    <div class="page-heading">
        <div>
            <h1>Usertype</h1>
            <p>You can Add, Search, Update and Delete here the Usertype!</p>
        </div>
    </div>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <div class="two-panels">
        <section>
            <h2>Add Usertype</h2>
            <form method="post" class="grid">
                <div><label>Usertype</label><input type="text" name="a0" required></div>

                <button class="full" type="submit" name="add">Add Usertype</button>
            </form>
        </section>
        <section>
            <h2>Search Usertype</h2>
            <form method="post" class="search">
                <input type="text" name="search_value" placeholder="Search by Usertype" required>
                <button type="submit" name="search">Search</button>
            </form>
            <div class="record-list"><h3>All Usertype Records</h3><table><tr><th>ID</th><th>Usertype</th><th>Action</th></tr><?php while ($item = $allRecords->fetch_assoc()): ?><tr><td><?= (int) $item["ID"] ?></td><td><?= htmlspecialchars($item["Usertype"]) ?></td><td class="record-actions"><form method="post"><input type="hidden" name="search_value" value="<?= htmlspecialchars($item["Usertype"]) ?>"><button name="search">Edit</button></form><form method="post" onsubmit="return confirm('Delete this record?')"><input type="hidden" name="id" value="<?= (int) $item["ID"] ?>"><button name="delete" class="delete">Delete</button></form></td></tr><?php endwhile; ?></table></div>
        </section>
    </div>

    <?php if ($record): ?>
        <hr>
        <h2>Record #<?= (int) $record["ID"] ?></h2>
        <form method="post" class="grid">
            <input type="hidden" name="id" value="<?= (int) $record["ID"] ?>">
            <div class="full"><label>Usertype</label><input type="text" name="f0"
                    value="<?= htmlspecialchars($record['Usertype'] ?? '') ?>" required></div>
            <button type="submit" name="update">Update Usertype</button>
            <button type="submit" name="delete" class="delete" onclick="return confirm('Delete this record?')">Delete
                Usertype</button>
        </form>
    <?php endif; ?>
</section>
<?php include "footer.php"; ?>