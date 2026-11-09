<?php
function save_user_image(string $username, string $field = "imageUpload"): array
{
    if (!isset($_FILES[$field]) || $_FILES[$field]["error"] === UPLOAD_ERR_NO_FILE) {
        return ["ok" => false, "path" => "", "message" => "Please choose a user image."];
    }
    $file = $_FILES[$field];
    if ($file["error"] !== UPLOAD_ERR_OK || !is_uploaded_file($file["tmp_name"])) {
        return ["ok" => false, "path" => "", "message" => "Sorry, there was an error uploading the image."];
    }
    if ($file["size"] > 5 * 1024 * 1024) {
        return ["ok" => false, "path" => "", "message" => "Image must be 5 MB or smaller."];
    }
    $imageInfo = @getimagesize($file["tmp_name"]);
    $mime = $imageInfo["mime"] ?? "";
    $allowed = ["image/jpeg", "image/png", "image/gif"];
    if (!in_array($mime, $allowed, true)) {
        return ["ok" => false, "path" => "", "message" => "Invalid image type. Upload JPG, PNG, or GIF."];
    }
    $targetDir = __DIR__ . "/images/users/";
    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
        return ["ok" => false, "path" => "", "message" => "Unable to create the user image folder."];
    }
    $safeUsername = preg_replace('/[^A-Za-z0-9._-]/', '_', $username);
    if ($safeUsername === "" || $safeUsername === "." || $safeUsername === "..") {
        return ["ok" => false, "path" => "", "message" => "Username cannot be used as an image filename."];
    }
    $targetFile = $targetDir . $safeUsername . ".JPG";
    if (!move_uploaded_file($file["tmp_name"], $targetFile)) {
        return ["ok" => false, "path" => "", "message" => "Sorry, there was an error saving the image."];
    }
    return ["ok" => true, "path" => "images/users/" . $safeUsername . ".JPG", "message" => "The image was uploaded successfully."];
}
?>