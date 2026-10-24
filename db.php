<?php
mysqli_report(MYSQLI_REPORT_OFF);

$host = getenv("DB_HOST") ?: getenv("MYSQL_HOST") ?: "localhost";
$user = getenv("DB_USER") ?: getenv("MYSQL_USER") ?: "root";
$password = getenv("DB_PASSWORD");
if ($password === false) {
    $password = getenv("MYSQL_PASSWORD") ?: "";
}
$database = getenv("DB_NAME") ?: getenv("MYSQL_DATABASE") ?: "4aanasco";
$portValue = getenv("DB_PORT") ?: getenv("MYSQL_PORT") ?: "3306";
$port = (int) $portValue;
if ($port < 1 || $port > 65535) {
    error_log("Database port is invalid.");
    http_response_code(500);
    exit("Database configuration error. Please contact the administrator.");
}

$isLocal = in_array(strtolower($host), ["localhost", "127.0.0.1", "::1"], true);
$caPath = getenv("DB_SSL_CA") ?: getenv("MYSQL_SSL_CA") ?: "/etc/secrets/ca.pem";

$conn = mysqli_init();
if (!$conn) {
    error_log("Unable to initialize MySQL connection.");
    http_response_code(500);
    exit("Database connection failed. Please contact the administrator.");
}

$clientFlags = 0;
if (!$isLocal) {
    if (!is_readable($caPath) || !is_file($caPath)) {
        error_log("Remote database SSL CA certificate is missing or unreadable.");
        http_response_code(500);
        exit("Database SSL configuration is missing. Please contact the administrator.");
    }
    if (!$conn->ssl_set(null, null, $caPath, null, null)) {
        error_log("Unable to configure database SSL.");
        http_response_code(500);
        exit("Database SSL configuration failed. Please contact the administrator.");
    }
    $clientFlags = MYSQLI_CLIENT_SSL;
}

if (!$conn->real_connect($host, $user, $password, $database, $port, null, $clientFlags)) {
    error_log("Database connection failed (" . $conn->connect_errno . "): " . $conn->connect_error);
    http_response_code(500);
    exit("Database connection failed. Check the hosting environment settings.");
}

$conn->set_charset("utf8mb4");
?>
