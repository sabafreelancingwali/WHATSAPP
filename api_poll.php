<?php
require_once 'db.php';
require_login_json();
$uid = current_user_id();
 
$chat_id = (int)($_POST['chat_id'] ?? 0);
$after_id = (int)($_POST['after_id'] ?? 0);
 
if(!$chat_id){ json_response(['ok'=>false,'messages'=>[]]); }
 
// ensure member
$st = $conn->prepare("SELECT 1 FROM chat_members WHERE chat_id=? AND user_id=?");
$st->bind_param('ii',$chat_id,$uid); $st->execute();
if(!$st->get_result()->fetch_row()){ json_response(['ok'=>false,'messages'=>[]]); }
 
$sql = "
SELECT m.id, m.sender_id, m.body, DATE_FORMAT(m.created_at,'%Y-%m-%d %H:%i:%s') AS created_at,
       CASE 
         WHEN (SELECT COUNT(*) FROM chat_members cm WHERE cm.chat_id=m.chat_id)=
              (SELECT COUNT(DISTINCT rr.user_id) FROM read_receipts rr WHERE rr.message_id=m.id)
         THEN 1 ELSE 0 END AS read_by_all
FROM messages m 
WHERE m.chat_id=? AND m.id>? 
ORDER BY m.id ASC
LIMIT 100";
$st = $conn->prepare($sql);
$st->bind_param('ii',$chat_id,$after_id);
$st->execute();
$rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
 
json_response(['ok'=>true,'messages'=>$rows]);
 
