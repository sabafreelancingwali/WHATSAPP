<?php
require_once 'db.php';
require_login_json();
$uid = current_user_id();
$chat_id = (int)($_POST['chat_id'] ?? 0);
$body = trim($_POST['body'] ?? '');
 
if(!$chat_id || $body===''){ json_response(['ok'=>false,'error'=>'Empty']); }
 
// ensure member
$st = $conn->prepare("SELECT 1 FROM chat_members WHERE chat_id=? AND user_id=?");
$st->bind_param('ii',$chat_id,$uid); $st->execute();
if(!$st->get_result()->fetch_row()){ json_response(['ok'=>false,'error'=>'Not in chat']); }
 
$st = $conn->prepare("INSERT INTO messages (chat_id,sender_id,body) VALUES (?,?,?)");
$st->bind_param('iis',$chat_id,$uid,$body);
$st->execute();
$msg_id = $conn->insert_id;
 
// sender auto-reads own message
$st = $conn->prepare("INSERT IGNORE INTO read_receipts (message_id,user_id) VALUES (?,?)");
$st->bind_param('ii',$msg_id,$uid); $st->execute();
 
json_response(['ok'=>true,'id'=>$msg_id]);
