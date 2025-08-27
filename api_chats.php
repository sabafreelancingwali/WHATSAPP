<?php
require_once 'db.php';
require_login_json();
$uid = current_user_id();
$q = trim($_POST['q'] ?? '');
 
if($q !== ''){
  // search users not yourself
  $stmt = $conn->prepare("SELECT id,name FROM users WHERE id<>? AND (name LIKE CONCAT('%',?,'%') OR email LIKE CONCAT('%',?,'%')) ORDER BY name LIMIT 10");
  $stmt->bind_param('iss',$uid,$q,$q); $stmt->execute();
  $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  // ensure chat exists for each user (one-to-one)
  $chats = [];
  foreach($users as $u){
    // Find or create a 1:1 chat
    $sql = "
      SELECT c.id,c.is_group,c.title FROM chats c
      JOIN chat_members m1 ON m1.chat_id=c.id AND m1.user_id=?
      JOIN chat_members m2 ON m2.chat_id=c.id AND m2.user_id=?
      WHERE c.is_group=0
      LIMIT 1
    ";
    $st = $conn->prepare($sql);
    $st->bind_param('ii',$uid,$u['id']); $st->execute();
    $found = $st->get_result()->fetch_assoc();
    if(!$found){
      // create
      $conn->begin_transaction();
      $conn->query("INSERT INTO chats (title,is_group) VALUES (NULL,0)");
      $chat_id = $conn->insert_id;
      $stm = $conn->prepare("INSERT INTO chat_members (chat_id,user_id) VALUES (?,?),(?,?)");
      $stm->bind_param('iiii',$chat_id,$uid,$chat_id,$u['id']);
      $stm->execute();
      $conn->commit();
      $found = ['id'=>$chat_id,'is_group'=>0,'title'=>null];
    }
    // Title is the other user's name
    $found['title'] = $u['name'];
    $chats[] = $found;
  }
} else {
  // list existing chats for user
  $sql = "
  SELECT c.id, c.is_group, 
         COALESCE(c.title,
           (SELECT u2.name FROM chat_members cm2 
            JOIN users u2 ON u2.id=cm2.user_id 
            WHERE cm2.chat_id=c.id AND cm2.user_id<>? LIMIT 1)
         ) AS title,
         cl.body AS last_message, cl.created_at
  FROM chats c
  JOIN chat_members cm ON cm.chat_id=c.id AND cm.user_id=?
  LEFT JOIN chat_latest cl ON cl.chat_id=c.id
  ORDER BY COALESCE(cl.created_at, c.created_at) DESC
  LIMIT 50";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('ii',$uid,$uid); $stmt->execute();
  $chats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
 
$out = array_map(function($c){
  return [
    'id'=>(int)$c['id'],
    'title'=>$c['title'] ?: 'Chat',
    'last_message'=>$c['last_message'] ?? '',
    'time'=> isset($c['created_at']) ? date('H:i', strtotime($c['created_at'])) : ''
  ];
}, $chats);
 
json_response(['ok'=>true,'chats'=>$out]);
