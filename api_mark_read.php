<?php
require_once 'db.php';
require_login_json();
$uid = current_user_id();
$chat_id = (int)($_POST['chat_id'] ?? 0);
$max_id = (int)($_POST['max_id'] ?? 0);
 
if(!$chat_id || !$max_id){ json_response(['ok'=>true]); }
 
// ensure member
$st = $conn->prepare("SELECT 1 FROM chat_members WHERE chat_id=? AND user_id=?");
$st->bind_param('ii',$chat_id,$uid); $st->execute();
if(!$st->get_result()->fetch_row()){ json_response(['ok'=>true]); }
 
// mark all <= max in this chat
$sql = "INSERT IGNORE INTO read_receipts (message_id,user_id)
        SELECT m.id, ? FROM messages m WHERE m.chat_id=? AND m.id<=?";
$st = $conn->prepare($sql);
$st->bind_param('iii',$uid,$chat_id,$max_id);
$st->execute();
 
json_response(['ok'=>true]);
 
