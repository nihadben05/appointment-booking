<?php
require_once __DIR__ . "/functions.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

set_exception_handler(function ($e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Something went wrong. Please try again later.");
});

$host = "localhost";
$user = "root";
$pass = "";
$db   = "appointment_db";

try {
    $conn = mysqli_connect($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Database connection failed");
}
