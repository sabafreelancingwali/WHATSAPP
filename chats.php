?php require_once 'db.php'; $uid = current_user_id(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>WhatsApp Clone — Chats</title>
<style>
  :root{--bg:#0b141a;--card:#111b21;--soft:#202c33;--soft2:#1f2c34;--text:#e9edef;--muted:#8696a0;--accent:#25d366;--me:#005c4b;--you:#202c33;}
  *{box-sizing:border-box} html,body{height:100%} body{margin:0;background:var(--bg);font-family:Inter,system-ui,Segoe UI,Roboto,Arial;color:var(--text)}
  .app{display:grid;grid-template-columns:360px 1fr;height:100vh}
  .sidebar{border-right:1px solid #25343d;background:#111b21;display:flex;flex-direction:column}
  .topbar{padding:14px 16px;display:flex;gap:10px;align-items:center;justify-content:space-between;border-bottom:1px solid #25343d}
  .topbar .me{display:flex;gap:10px;align-items:center}
  .avatar{width:36px;height:36px;border-radius:50%;background:#2a3942;display:grid;place-items:center;font-weight:700}
  .search{padding:10px}
  .search input{width:100%;padding:10px 12px;border-radius:12px;background:#0e1a20;border:1px solid #22303a;color:var(--text);outline:none}
  .list{overflow:auto}
  .chatItem{display:grid;grid-template-columns:48px 1fr auto;gap:10px;align-items:center;padding:12px 14px;border-bottom:1px solid #1a262e;cursor:pointer}
  .chatItem:hover{background:#0f1a20}
  .chatItem .title{font-weight:700}
  .chatItem .preview{color:var(--muted);font-size:12px}
  .chatItem .time{color:var(--muted);font-size:12px}
  .main{display:grid;grid-template-rows:auto 1fr auto}
  .chatHeader{border-bottom:1px solid #25343d;background:#111b21;padding:12px 16px;display:flex;align-items:center;gap:10px}
  .chatHeader .title{font-weight:700}
  .msgs{background:url('https://i.imgur.com/lJt8x4F.png') repeat;opacity:.98;overflow:auto;padding:18px;display:flex;flex-direction:column;gap:10px}
  .bubble{max-width:70%;padding:10px 12px;border-radius:14px;line-height:1.35;position:relative}
  .meB{align-self:flex-end;background:var(--me);border-top-right-radius:4px}
  .youB{align-self:flex-start;background:var(--you);border-top-left-radius:4px}
  .meta{display:flex;gap:6px;align-items:center;margin-top:4px;font-size:11px;color:#c9d6dd;opacity:.85}
  .ticks{font-size:14px}
  .composer{display:flex;gap:8px;padding:12px;background:#111b21;border-top:1px solid #25343d}
  .composer input{flex:1;padding:12px;border-radius:12px;background:#0e1a20;border:1px solid #22303a;color:var(--text);outline:none}
  .composer button{padding:12px 16px;border:none;border-radius:12px;background:var(--accent);color:#052914;font-weight:800;cursor:pointer}
  .empty{display:grid;place-items:center;color:var(--muted)}
  .logout{background:none;border:1px solid #2a3942;color:#cfe3ee;border-radius:10px;padding:8px 12px;cursor:pointer}
  @media (max-width:900px){.app{grid-template-columns:1fr}.sidebar{position:absolute;z-index:10;width:100%;max-width:420px;height:100vh;transform:translateX(-100%);transition:.25s}.sidebar.show{transform:translateX(0)}.toggle{display:inline}}
  .toggle{display:none;background:none;border:1px solid #2a3942;color:#cfe3ee;border-radius:10px;padding:6px 10px;cursor:pointer}
</style>
</head>
<body>
<div class="app">
  <aside class="sidebar" id="sidebar">
    <div class="topbar">
      <div class="me">
        <div class="avatar" id="meAvatar">U</div>
        <div>
          <div id="meName">You</div>
          <div class="muted" style="font-size:12px;color:#8aa4b0">Online</div>
        </div>
      </div>
      <button class="logout" id="logoutBtn">Logout</button>
    </div>
    <div class="search"><input id="searchInput" placeholder="Search or start new chat"/></div>
    <div class="list" id="chatList"></div>
  </aside>
 
  <main class="main">
    <div class="chatHeader">
      <button class="toggle" id="toggleBtn">Chats</button>
      <div class="avatar" id="chatAvatar">C</div>
      <div>
        <div class="title" id="chatTitle">Select a conversation</div>
        <div class="muted" style="color:#8aa4b0;font-size:12px" id="chatSubtitle">—</div>
      </div>
    </div>
 
    <div class="msgs" id="msgs">
      <div class="empty">👈 Pick a chat from the left or search a user to start messaging.</div>
    </div>
 
    <div class="composer">
      <input id="msgInput" placeholder="Type a message" disabled />
      <button id="sendBtn" disabled>Send</button>
    </div>
  </main>
</div>
 
<script>
  // If not logged in, bounce to login via JS (no PHP header redirect)
  const isLoggedIn = <?php echo $uid ? 'true' : 'false'; ?>;
  if(!isLoggedIn){ window.location.href = 'index.php'; }
 
  const sidebar = document.getElementById('sidebar');
  const toggleBtn = document.getElementById('toggleBtn');
  toggleBtn.onclick = ()=> sidebar.classList.toggle('show');
 
  const chatListEl = document.getElementById('chatList');
  const msgsEl = document.getElementById('msgs');
  const msgInput = document.getElementById('msgInput');
  const sendBtn = document.getElementById('sendBtn');
  const chatTitle = document.getElementById('chatTitle');
  const chatSubtitle = document.getElementById('chatSubtitle');
  const meName = document.getElementById('meName');
  const meAvatar = document.getElementById('meAvatar');
 
  let me = null;
  let activeChatId = null;
  let latestSeenId = 0;
 
  function esc(s){ return s.replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m])); }
 
  async function api(url, data=null){
    const opts = data ? {method:'POST', body:data} : {};
    const r = await fetch(url, opts);
    return r.json();
  }
 
  async function loadMe(){
    const meRes = await api('api_me.php');
    if(meRes.ok){ 
      me = meRes.user;
      meName.textContent = me.name;
      meAvatar.textContent = me.name?.[0]?.toUpperCase()||'U';
    }
  }
 
  async function loadChats(query=''){
    const fd = new FormData(); fd.append('q', query);
    const res = await api('api_chats.php', fd);
    chatListEl.innerHTML = '';
    (res.chats||[]).forEach(c=>{
      const el = document.createElement('div');
      el.className='chatItem';
      el.innerHTML = `
        <div class="avatar">${esc(c.title?.[0]?.toUpperCase()||'C')}</div>
        <div>
          <div class="title">${esc(c.title||'Chat')}</div>
          <div class="preview">${esc(c.last_message||'No messages yet')}</div>
        </div>
        <div class="time">${c.time||''}</div>
      `;
      el.onclick=()=>openChat(c.id, c.title);
      chatListEl.appendChild(el);
    });
  }
 
  async function openChat(id, title){
    activeChatId = id; latestSeenId = 0;
    chatTitle.textContent = title||'Chat';
    chatSubtitle.textContent = 'Messages are end-to-end among friends 😉';
    msgInput.disabled = false; sendBtn.disabled = false;
    msgsEl.innerHTML = '';
    await loadMessages(true);
    sidebar.classList.remove('show');
  }
 
  function bubbleHTML(m){
    const mine = m.sender_id == me.id;
    const cls = 'bubble '+(mine?'meB':'youB');
    const ticks = (m.read_by_all ? '✓✓' : '✓');
    const when = new Date(m.created_at.replace(' ','T')+'Z');
    return `
      <div class="${cls}">
        <div>${esc(m.body)}</div>
        <div class="meta">
          <span>${when.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</span>
          ${mine?`<span class="ticks" title="${m.read_by_all?'Seen':'Sent'}">${ticks}</span>`:''}
        </div>
      </div>
    `;
  }
 
  async function loadMessages(scrollBottom=false){
    if(!activeChatId) return;
    const fd = new FormData(); fd.append('chat_id', activeChatId); fd.append('after_id', latestSeenId);
    const res = await api('api_poll.php', fd);
    let appended = false;
    (res.messages||[]).forEach(m=>{
      msgsEl.insertAdjacentHTML('beforeend', bubbleHTML(m));
      latestSeenId = Math.max(latestSeenId, Number(m.id));
      appended = true;
    });
    if(appended || scrollBottom){
      msgsEl.scrollTop = msgsEl.scrollHeight;
    }
    // Mark read for newly fetched
    if(appended){ markRead(latestSeenId); }
  }
 
  async function markRead(maxId){
    if(!activeChatId || !maxId) return;
    const fd = new FormData();
    fd.append('chat_id', activeChatId);
    fd.append('max_id', maxId);
    await api('api_mark_read.php', fd);
  }
 
  sendBtn.onclick = async ()=>{
    if(!activeChatId) return;
    const text = msgInput.value.trim();
    if(!text) return;
    const fd = new FormData();
    fd.append('chat_id', activeChatId);
    fd.append('body', text);
    const res = await api('api_send.php', fd);
    if(res.ok){
      msgInput.value = '';
      await loadMessages(true);
      await loadChats(document.getElementById('searchInput').value.trim());
    }
  };
 
  document.getElementById('searchInput').oninput = (e)=> {
    loadChats(e.target.value.trim());
  };
 
  document.getElementById('logoutBtn').onclick = async ()=>{
    await api('api_logout.php');
    // JS redirect to login
    window.location.href='index.php';
  };
 
  // Polling loop (feels real-time)
  setInterval(loadMessages, 1500);
 
  // Bootstrap
  (async()=>{
    await loadMe();
    await loadChats();
    // Optional: open the first chat automatically
    // You can also create a new chat by searching a user (see api_start_chat.php below).
  })();
</script>
</body>
</html>
 
