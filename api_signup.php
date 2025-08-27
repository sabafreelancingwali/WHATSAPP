<?php
require_once 'db.php';
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass = $_POST['password'] ?? '';
 
if(!$name || !$email || strlen($pass)<6){ json_response(['ok'=>false,'error'=>'Invalid input']); }
 
$stmt = $conn->prepare("INSERT INTO users (name,email,password_hash) VALUES (?,?,?)");
$hash = password_hash($pass, PASSWORD_DEFAULT);
try{
  $stmt->bind_param('sss',$name,$email,$hash);
  $stmt->execute();
  $_SESSION['user_id'] = $stmt->insert_id;
  json_response(['ok'=>true]);
}catch(Exception $e){
  json_response(['ok'=>false,'error'=>'Email already used']);
}
