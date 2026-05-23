<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>AgriTrack – Sign In</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Mono:wght@400;500&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0d1117;--sur:#161b22;--sur2:#1c2333;--bdr:rgba(255,255,255,0.07);--bdr2:rgba(255,255,255,0.13);
  --txt:#e6edf3;--txt2:#adbac7;--mut:#6e7681;
  --acc:#3fb950;--adim:rgba(63,185,80,0.1);--abdr:rgba(63,185,80,0.22);
  --red:#f85149;--rdim:rgba(248,81,73,0.09);
  --fb:'Nunito',sans-serif;--fd:'Syne',sans-serif;--fm:'DM Mono',monospace
}
html,body{height:100%;background:var(--bg);font-family:var(--fb);color:var(--txt)}
.wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
  background:var(--bg);
  background-image:
    radial-gradient(ellipse 80% 50% at 50% -10%, rgba(63,185,80,0.07) 0%, transparent 60%),
    radial-gradient(ellipse 40% 30% at 80% 80%, rgba(88,166,255,0.05) 0%, transparent 50%);
}
.card{background:var(--sur);border:1px solid var(--bdr2);border-radius:16px;width:100%;max-width:400px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,0.5)}
.brand{padding:32px 32px 24px;text-align:center;border-bottom:1px solid var(--bdr)}
.logo{width:52px;height:52px;background:var(--acc);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px;margin:0 auto 14px}
.brand-name{font-family:var(--fd);font-size:24px;font-weight:800;color:var(--txt);letter-spacing:-.4px}
.brand-name span{color:var(--acc)}
.brand-sub{font-size:11px;color:var(--mut);font-family:var(--fm);letter-spacing:.8px;text-transform:uppercase;margin-top:3px}
.body{padding:28px 32px}
.fgrp{margin-bottom:18px}
.flbl{font-size:10px;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.8px;font-family:var(--fm);margin-bottom:6px}
.finp{width:100%;padding:10px 13px;background:var(--sur2);border:1px solid var(--bdr2);border-radius:9px;color:var(--txt);font-family:var(--fb);font-size:14px;outline:none;transition:border-color .15s;appearance:none}
.finp:focus{border-color:var(--acc)}
.finp::placeholder{color:var(--mut)}
.ferr{font-size:11px;color:var(--red);margin-top:5px;font-family:var(--fm)}
.remember{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--txt2);cursor:pointer;margin-bottom:22px}
.remember input{accent-color:var(--acc);width:15px;height:15px;cursor:pointer}
.sbtn{width:100%;padding:12px;background:var(--acc);color:#fff;border:none;border-radius:9px;font-family:var(--fd);font-size:15px;font-weight:700;cursor:pointer;letter-spacing:.3px;transition:opacity .2s,transform .15s}
.sbtn:hover{opacity:.88}
.sbtn:active{transform:scale(.98)}
.foot{padding:16px 32px 24px;text-align:center;border-top:1px solid var(--bdr)}
.foot-txt{font-size:11px;color:var(--mut);font-family:var(--fm)}
.alert{background:var(--rdim);border:1px solid rgba(248,81,73,.25);border-radius:8px;padding:10px 13px;font-size:12px;color:var(--red);margin-bottom:18px;font-family:var(--fm)}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <div class="brand">
      <div class="logo">🌿</div>
      <div class="brand-name">Agri<span>Track</span></div>
      <div class="brand-sub">HQ Dashboard · Sign In</div>
    </div>
    <form method="POST" action="/login" class="body">
      @csrf

      @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
      @endif

      @if(session('status'))
        <div class="alert" style="background:var(--adim);border-color:var(--abdr);color:var(--acc)">
          {{ session('status') }}
        </div>
      @endif

      <div class="fgrp">
        <div class="flbl">Email Address</div>
        <input class="finp" type="email" name="email" value="{{ old('email') }}"
               placeholder="you@agritrack.ug" required autofocus autocomplete="email">
      </div>

      <div class="fgrp">
        <div class="flbl">Password</div>
        <input class="finp" type="password" name="password"
               placeholder="••••••••" required autocomplete="current-password">
      </div>

      <label class="remember">
        <input type="checkbox" name="remember"> Remember me for 30 days
      </label>

      <button type="submit" class="sbtn">Sign In</button>
    </form>
    <div class="foot">
      <div class="foot-txt">AgriTrack HQ · Uganda Agricultural Logistics</div>
    </div>
  </div>
</div>
</body>
</html>
