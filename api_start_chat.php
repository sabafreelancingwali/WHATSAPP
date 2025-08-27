<?php
require_once 'db.php';
require_login_json();
$uid = current_user_id();
$other_id = (int)($_POST['user_id'] ?? 0);
if(!$other_id || $other_id==$uid){ json_response(['ok'=>false,'error'=>'Invalid user']); }
 
// find existing 1:1
$sql = "
  SELECT c.id FROM chats c
  JOIN chat_members a ON a.chat_id=c.id AND a.user_id=?
  JOIN chat_members b ON b.chat_id=c.id AND b.user_id=?
  WHERE c.is_group=0 LIMIT 1";
$st = $conn->prepare($sql);
$st->bind_param('ii',$uid,$other_id); $st->execute();
$row = $st->get_result()->fetch_assoc();
if($row){ json_response(['ok'=>true,'chat_id'=>$row['id']]); }
 
// create
$conn->begin_transaction();
$conn->query("INSERT INTO chats (title,is_group) VALUES (NULL,0)");
$chat_id = $conn->insert_id;
$stm = $conn->prepare("INSERT INTO chat_members (chat_id,user_id) VALUES (?,?),(?,?)");
$stm->bind_param('iiii',$chat_id,$uid,$chat_id,$other_id);
$stm->execute();
$conn->commit();
 
json_response(['ok'=>true,'chat_id'=>$chat_id]);
