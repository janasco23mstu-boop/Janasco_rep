<?php
require_once "auth.php";
require_once "db.php";

$pageTitle = "Instructor";
$message = "";
$record = null;
$uploadDir = __DIR__ . "/images/instructors";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

function saveInstructorPhoto(string $field, string $uploadDir): string {
    if (!isset($_FILES[$field]) || $_FILES[$field]["error"] === UPLOAD_ERR_NO_FILE) {
        return "";
    }

    if ($_FILES[$field]["error"] !== UPLOAD_ERR_OK || $_FILES[$field]["size"] > 2 * 1024 * 1024) {
        return "";
    }

    $imageInfo = @getimagesize($_FILES[$field]["tmp_name"]);
    if (!$imageInfo) {
        return "";
    }

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    $mime = $imageInfo["mime"];
    if (!isset($allowed[$mime])) {
        return "";
    }

    $filename = bin2hex(random_bytes(12)) . "." . $allowed[$mime];
    if (move_uploaded_file($_FILES[$field]["tmp_name"], $uploadDir . "/" . $filename)) {
        return "images/instructors/" . $filename;
    }

    return "";
}

function removeInstructorPhoto(string $photo): void {
    if ($photo !== "") {
        $path = __DIR__ . "/" . ltrim($photo, "/");
        if (is_file($path)) {
            unlink($path);
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["add"])) {
        $salutation = trim($_POST["salutation"] ?? "");
        $firstname = trim($_POST["firstname"] ?? "");
        $middlename = trim($_POST["middlename"] ?? "");
        $lastname = trim($_POST["lastname"] ?? "");
        $photo = saveInstructorPhoto("photo", $uploadDir);

        $stmt = $conn->prepare("INSERT INTO tbl_instructors (SALUTATION,FIRSTNAME,MIDDLENAME,LASTNAME,PHOTO) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sssss", $salutation, $firstname, $middlename, $lastname, $photo);
        $stmt->execute();
        $message = $stmt->affected_rows ? "Instructor added successfully." : "Unable to add instructor.";
    } elseif (isset($_POST["search"])) {
        $value = trim($_POST["search_value"] ?? "");
        $stmt = $conn->prepare("SELECT * FROM tbl_instructors WHERE FIRSTNAME LIKE CONCAT('%', ?, '%') OR MIDDLENAME LIKE CONCAT('%', ?, '%') OR LASTNAME LIKE CONCAT('%', ?, '%') OR SALUTATION LIKE CONCAT('%', ?, '%') ORDER BY ID LIMIT 1");
        $stmt->bind_param("ssss", $value, $value, $value, $value);
        $stmt->execute();
        $record = $stmt->get_result()->fetch_assoc();
        $message = $record ? "Record found. You can update or delete it below." : "No instructor found.";
    } elseif (isset($_POST["update"])) {
        $id = (int) ($_POST["id"] ?? 0);
        $salutation = trim($_POST["salutation"] ?? "");
        $firstname = trim($_POST["firstname"] ?? "");
        $middlename = trim($_POST["middlename"] ?? "");
        $lastname = trim($_POST["lastname"] ?? "");

        $stmt = $conn->prepare("SELECT PHOTO FROM tbl_instructors WHERE ID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();
        $photo = $old["PHOTO"] ?? "";

        $newPhoto = saveInstructorPhoto("photo", $uploadDir);
        if ($newPhoto !== "") {
            removeInstructorPhoto($photo);
            $photo = $newPhoto;
        }

        $stmt = $conn->prepare("UPDATE tbl_instructors SET SALUTATION=?,FIRSTNAME=?,MIDDLENAME=?,LASTNAME=?,PHOTO=? WHERE ID=?");
        $stmt->bind_param("sssssi", $salutation, $firstname, $middlename, $lastname, $photo, $id);
        $stmt->execute();
        $message = "Instructor updated successfully.";

        $stmt = $conn->prepare("SELECT * FROM tbl_instructors WHERE ID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $record = $stmt->get_result()->fetch_assoc();
    } elseif (isset($_POST["delete"])) {
        $id = (int) ($_POST["id"] ?? 0);
        $stmt = $conn->prepare("SELECT PHOTO FROM tbl_instructors WHERE ID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();

        $stmt = $conn->prepare("DELETE FROM tbl_instructors WHERE ID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows) {
            removeInstructorPhoto($old["PHOTO"] ?? "");
            $message = "Instructor deleted successfully.";
        } else {
            $message = "Instructor was not deleted.";
        }
        $record = null;
    }
}

$allRecords = $conn->query("SELECT ID, SALUTATION, FIRSTNAME, MIDDLENAME, LASTNAME, PHOTO FROM tbl_instructors ORDER BY ID");
include "header.php";
?>
<section class="card">
    <h1>Instructor</h1>
    <p>You can Add, Search, Update and Delete instructor records.</p>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="two-panels">
        <section>
            <h2>Add Instructor</h2>
            <form method="post" enctype="multipart/form-data" class="grid">
                <div><label>Salutation</label><input name="salutation" placeholder="Mr., Ms., Dr." required></div>
                <div><label>First Name</label><input name="firstname" required></div>
                <div><label>Middle Name</label><input name="middlename"></div>
                <div><label>Last Name</label><input name="lastname" required></div>
                <div class="full"><label>Photo</label><input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></div>
                <button class="full" name="add">Add Instructor</button>
            </form>
        </section>

        <section>
            <h2>Search Instructor</h2>
            <form method="post" class="search">
                <input name="search_value" placeholder="Search by name" required>
                <button name="search">Search</button>
            </form>
            <div class="record-list"><h3>All Instructor Records</h3><table><tr><th>ID</th><th>Salutation</th><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Action</th></tr><?php while ($item = $allRecords->fetch_assoc()): ?><tr><td><?= (int) $item["ID"] ?></td><td><?= htmlspecialchars($item["SALUTATION"]) ?></td><td><?= htmlspecialchars($item["FIRSTNAME"]) ?></td><td><?= htmlspecialchars($item["MIDDLENAME"]) ?></td><td><?= htmlspecialchars($item["LASTNAME"]) ?></td><td class="record-actions"><form method="post"><input type="hidden" name="search_value" value="<?= htmlspecialchars($item["LASTNAME"]) ?>"><button name="search">Edit</button></form><form method="post" onsubmit="return confirm('Delete this instructor?')"><input type="hidden" name="id" value="<?= (int) $item["ID"] ?>"><button name="delete" class="delete">Delete</button></form></td></tr><?php endwhile; ?></table></div>
        </section>
    </div>

    <?php if ($record): ?>
        <hr>
        <h2>Record #<?= (int) $record["ID"] ?></h2>

        <?php if (!empty($record["PHOTO"])): ?>
            <img class="photo-preview" src="<?= htmlspecialchars($record["PHOTO"]) ?>" alt="Instructor Photo">
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="grid">
            <input type="hidden" name="id" value="<?= (int) $record["ID"] ?>">
            <div><label>Salutation</label><input name="salutation" value="<?= htmlspecialchars($record["SALUTATION"]) ?>" required></div>
            <div><label>First Name</label><input name="firstname" value="<?= htmlspecialchars($record["FIRSTNAME"]) ?>" required></div>
            <div><label>Middle Name</label><input name="middlename" value="<?= htmlspecialchars($record["MIDDLENAME"]) ?>"></div>
            <div><label>Last Name</label><input name="lastname" value="<?= htmlspecialchars($record["LASTNAME"]) ?>" required></div>
            <div class="full"><label>Replace Photo</label><input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></div>
            <button name="update">Update Instructor</button>
            <button name="delete" class="delete" onclick="return confirm('Delete this instructor?')">Delete Instructor</button>
        </form>
    <?php endif; ?>
</section>
<?php include "footer.php"; ?>
