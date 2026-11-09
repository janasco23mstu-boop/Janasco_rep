<?php
require_once "auth.php";
require_once "db.php";
require_once "user_image.php";

$pageTitle = "Users";
$message = "";
$record = null;

$roleResult = $conn->query("SELECT ID, Usertype FROM tbl_usertype ORDER BY ID");
$roles = $roleResult ? $roleResult->fetch_all(MYSQLI_ASSOC) : [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["add"])) {
        $fullname = trim($_POST["fullname"] ?? "");
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        $usertype = trim($_POST["usertype"] ?? "");

        if ($fullname === "" || $username === "" || $password === "" || $usertype === "") {
            $message = "Please complete all user fields.";
        } elseif (!isset($_FILES["imageUpload"]) || $_FILES["imageUpload"]["error"] === UPLOAD_ERR_NO_FILE) {
            $message = "Please choose a profile image.";
        } else {
            $check = $conn->prepare("SELECT ID FROM tbl_users WHERE USERNAME = ? LIMIT 1");
            $check->bind_param("s", $username);
            $check->execute();

            if ($check->get_result()->num_rows > 0) {
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
                    $stmt->execute();
                    $message = $stmt->affected_rows ? "User added successfully." : "Unable to add user.";
                    if (!$stmt->affected_rows) {
                        @unlink(__DIR__ . "/" . $imagePath);
                    }
                }
            }
        }
    } elseif (isset($_POST["search"])) {
        $value = trim($_POST["search_value"] ?? "");
        $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE FULLNAME LIKE CONCAT('%', ?, '%') OR USERNAME LIKE CONCAT('%', ?, '%') ORDER BY ID LIMIT 1");
        $stmt->bind_param("ss", $value, $value);
        $stmt->execute();
        $record = $stmt->get_result()->fetch_assoc();
        $message = $record ? "Record found." : "No user found.";
    } elseif (isset($_POST["update"])) {
        $id = (int) ($_POST["id"] ?? 0);
        $fullname = trim($_POST["fullname"] ?? "");
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        $usertype = trim($_POST["usertype"] ?? "");

        if ($password !== "") {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE tbl_users SET FULLNAME=?, USERNAME=?, PASSWORD=?, USERTYPE=? WHERE ID=?");
            $stmt->bind_param("ssssi", $fullname, $username, $hash, $usertype, $id);
        } else {
            $stmt = $conn->prepare("UPDATE tbl_users SET FULLNAME=?, USERNAME=?, USERTYPE=? WHERE ID=?");
            $stmt->bind_param("sssi", $fullname, $username, $usertype, $id);
        }
        $stmt->execute();
        $message = "User updated successfully.";

        if ($id === (int) ($_SESSION["user_id"] ?? 0)) {
            $_SESSION["fullname"] = $fullname;
            $_SESSION["username"] = $username;
            $_SESSION["usertype"] = $usertype;
        }

        $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE ID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $record = $stmt->get_result()->fetch_assoc();
    } elseif (isset($_POST["delete"])) {
        $id = (int) ($_POST["id"] ?? 0);
        if ($id === (int) ($_SESSION["user_id"] ?? 0)) {
            $message = "You cannot delete the account currently in use.";
        } else {
            $stmt = $conn->prepare("DELETE FROM tbl_users WHERE ID=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $message = $stmt->affected_rows ? "User deleted successfully." : "User was not deleted.";
        }
        $record = null;
    }
}

$allRecords = $conn->query("SELECT ID, FULLNAME, USERNAME, USERTYPE, IMAGE FROM tbl_users ORDER BY ID");
include "header.php";
?>
<section class="card">
    <h1>Users</h1>
    <p>You can Add, Search, Update and Delete users and assign a Usertype.</p>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="two-panels">
        <section>
            <h2>Add User</h2>
            <form method="post" class="grid" enctype="multipart/form-data">
                <div><label>Full Name</label><input name="fullname" required></div>
                <div><label>Username</label><input name="username" required></div>
                <div><label>Password</label><input type="password" name="password" required></div>
                <div class="full"><label>Add Image</label><input type="file" name="imageUpload"
                        accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif" required></div>
                <div class="full">
                    <label>Usertype</label>
                    <select name="usertype" required>
                        <option value="">Select Usertype</option>
                        <?php foreach ($roles as $item): ?>
                            <option value="<?= htmlspecialchars($item["Usertype"]) ?>">
                                <?= htmlspecialchars($item["Usertype"]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="full" name="add">Add User</button>
            </form>
        </section>

        <section>
            <h2>Search User</h2>
            <form method="post" class="search">
                <input name="search_value" placeholder="Search by full name or username" required>
                <button name="search">Search</button>
            </form>
            <div class="record-list">
                <h3>All User Records</h3>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Image</th>
                        <th>Usertype</th>
                        <th>Action</th>
                    </tr><?php while ($item = $allRecords->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int) $item["ID"] ?></td>
                            <td><?= htmlspecialchars($item["FULLNAME"]) ?></td>
                            <td><?= htmlspecialchars($item["USERNAME"]) ?></td>
                            <td><?php if (!empty($item["IMAGE"]) && is_file(__DIR__ . "/" . $item["IMAGE"])): ?><img
                                        class="user-avatar-small" src="<?= htmlspecialchars($item["IMAGE"]) ?>"
                                        alt="User image"><?php else: ?>—<?php endif; ?></td>
                            <td><?= htmlspecialchars($item["USERTYPE"]) ?></td>
                            <td class="record-actions">
                                <form method="post"><input type="hidden" name="search_value"
                                        value="<?= htmlspecialchars($item["USERNAME"]) ?>"><button
                                        name="search">Edit</button></form>
                                <form method="post" onsubmit="return confirm('Delete this user?')"><input type="hidden"
                                        name="id" value="<?= (int) $item["ID"] ?>"><button name="delete"
                                        class="delete">Delete</button></form>
                            </td>
                        </tr><?php endwhile; ?>
                </table>
            </div>
        </section>
    </div>

    <?php if (!empty($record)): ?>
        <hr>
        <h2>Record #<?= (int) $record["ID"] ?></h2>
        <form method="post" class="grid">
            <input type="hidden" name="id" value="<?= (int) $record["ID"] ?>">
            <div><label>Full Name</label><input name="fullname" value="<?= htmlspecialchars($record["FULLNAME"]) ?>"
                    required></div>
            <div><label>Username</label><input name="username" value="<?= htmlspecialchars($record["USERNAME"]) ?>"
                    required></div>
            <div><label>New Password</label><input type="password" name="password"></div>
            <div class="full">
                <label>Usertype</label>
                <select name="usertype" required>
                    <?php foreach ($roles as $item): ?>
                        <option value="<?= htmlspecialchars($item["Usertype"]) ?>" <?= $record["USERTYPE"] === $item["Usertype"] ? "selected" : "" ?>><?= htmlspecialchars($item["Usertype"]) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button name="update">Update User</button>
            <button name="delete" class="delete" onclick="return confirm('Delete this user?')">Delete User</button>
        </form>
    <?php endif; ?>
</section>
<?php include "footer.php"; ?>