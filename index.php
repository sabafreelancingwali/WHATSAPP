<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>WhatsApp Clone — Login</title>
<style>
  :root{--bg:#0b141a;--card:#111b21;--soft:#202c33;--text:#e9edef;--accent:#25d366;--muted:#8696a0;--danger:#f87171;}
  *{box-sizing:border-box} html,body{height:100%}
  body{margin:0;background:linear-gradient(180deg,#0b141a,#111b21);font-family:Inter,system-ui,Segoe UI,Roboto,Arial;color:var(--text);display:grid;place-items:center}
  .wrap{width:min(980px,95vw);display:grid;grid-template-columns:1.2fr 1fr;gap:18px}
  .hero{background:url('https://images.unsplash.com/photo-1520975922117-44e1076a0aaa?q=80&w=1400&auto=format&fit=crop') center/cover;border-radius:24px;min-height:520px;box-shadow:0 10px 30px rgba(0,0,0,.35);position:relative;overflow:hidden}
  .hero::after{content:"Chat that feels instant.";position:absolute;left:24px;bottom:24px;background:rgba(0,0,0,.35);padding:12px 16px;border-radius:14px;font-weight:600;backdrop-filter:blur(6px)}
  .card{background:var(--card);border:1px solid rgba(255,255,255,.06);border-radius:24px;padding:22px;box-shadow:0 10px 30px rgba(0,0,0,.3)}
  h1{margin:8px 0 18px;font-size:28px}
  .tabs{display:flex;background:var(--soft);border-radius:14px;padding:6px;margin-bottom:18px}
  .tab{flex:1;text-align:center;padding:10px 12px;border-radius:10px;cursor:pointer;font-weight:600;color:var(--muted)}
  .tab.active{background:#182229;color:var(--text)}
  form{display:grid;gap:10px}
  label{font-size:13px;color:var(--muted)}
  input{width:100%;padding:12px 14px;border-radius:12px;border:1px solid #22303a;background:#0e1a20;color:var(--text);outline:none}
  input:focus{border-color:#2a9d66;box-shadow:0 0 0 3px rgba(37,211,102,.15)}
  .btn{padding:12px 14px;border:none;border-radius:12px;background:var(--accent);color:#052914;font-weight:800;cursor:pointer;transition:.2s}
  .btn:hover{transform:translateY(-1px);box-shadow:0 10px 20px rgba(37,211,102,.25)}
  .hint{color:var(--muted);font-size:12px}
  .err{color:var(--danger);font-size:13px}
  .success{color:#4ade80;font-size:13px}
  @media (max-width:900px){.wrap{grid-template-columns:1fr}.hero{min-height:200px}}
</style>
</head>
<body>
<div class="wrap">
  <div class="hero"></div>
 
  <div class="card">
    <h1>Welcome back 👋</h1>
    <div class="tabs">
      <div class="tab active" data-tab="login">Login</div>
      <div class="tab" data-tab="signup">Sign up</div>
    </div>
 
    <div id="login" class="panel">
      <form id="loginForm">
        <div>
          <label>Email</label>
          <input type="email" name="email" required />
        </div>
        <div>
          <label>Password</label>
          <input type="password" name="password" required minlength="6" />
        </div>
        <button class="btn" type="submit">Login</button>
        <div id="loginMsg" class="hint"></div>
      </form>
    </div>
 
    <div id="signup" class="panel" style="display:none">
      <form id="signupForm">
        <div>
          <label>Name</label>
          <input type="text" name="name" required />
        </div>
        <div>
          <label>Email</label>
          <input type="email" name="email" required />
        </div>
        <div>
          <label>Password</label>
          <input type="password" name="password" required minlength="6" />
        </div>
        <button class="btn" type="submit">Create account</button>
        <div id="signupMsg" class="hint"></div>
      </form>
    </div>
  </div>
</div>
 
<script>
  // Tab switch
  document.querySelectorAll('.tab').forEach(t=>{
    t.onclick=()=>{
      document.querySelectorAll('.tab').forEach(x=>x.classList.remove('active'));
      t.classList.add('active');
      document.querySelectorAll('.panel').forEach(p=>p.style.display='none');
      document.getElementById(t.dataset.tab).style.display='block';
    }
  });
 
  // Helpers
  async function post(url, data){
    const r = await fetch(url,{method:'POST',body:data});
    return r.json();
  }
 
  // Login
  document.getElementById('loginForm').onsubmit=async(e)=>{
    e.preventDefault();
    const msg = document.getElementById('loginMsg');
    msg.textContent = 'Signing in...';
    const data = new FormData(e.target);
    const res = await post('api_login.php', data);
    if(res.ok){
      msg.className='success'; msg.textContent='Success! Redirecting...';
      // JS redirection (no PHP header redirect)
      setTimeout(()=>{ window.location.href='chats.php'; }, 400);
    }else{
      msg.className='err'; msg.textContent=res.error||'Login failed';
    }
  };
 
  // Signup
  document.getElementById('signupForm').onsubmit=async(e)=>{
    e.preventDefault();
    const msg = document.getElementById('signupMsg');
    msg.textContent='Creating account...';
    const data = new FormData(e.target);
    const res = await post('api_signup.php', data);
    if(res.ok){
      msg.className='success'; msg.textContent='Account created! Redirecting...';
      setTimeout(()=>{ window.location.href='chats.php'; }, 500);
    }else{
      msg.className='err'; msg.textContent=res.error||'Signup failed';
    }
  };
</script>
</body>
</html>
