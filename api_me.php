<?php
require_once 'db.php';
require_login_json();
$uid = current_user_id();
$stmt = $conn->prepare("SELECT id,name,email,avatar FROM users WHERE id=?");
$stmt->bind_param('i',$uid); $stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
json_response(['ok'=>true,'user'=>$u]);
