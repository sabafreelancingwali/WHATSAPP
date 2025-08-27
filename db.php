<?php
// db.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$DB_HOST = 'localhost';
$DB_NAME = 'dbpsrrjhmvsjmz';
$DB_USER = 'upknjbhg8vsv8';
$DB_PASS = 'yz88ljtio3sf';
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$conn->set_charset('utf8mb4');
 
session_start();
 
// helper: require login for APIs/pages that need auth
function require_login_json() {
  if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['ok'=>false,'error'=>'UNAUTHENTICATED']);
    exit;
  }
}
 
function current_user_id() {
  return $_SESSION['user_id'] ?? null;
}
 
function json_response($data) {
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}
?>
