<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Impressum - ZeroDayEmpire</title>
  <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpath fill='%2300ffc3' d='M50 5L95 50 50 95 5 50z'/%3E%3C/svg%3E"/>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <header class="site-header">
    <div class="container nav" id="nav">
      <a class="brand" href="index.php" aria-label="ZeroDayEmpire Startseite">
        <svg viewBox="0 0 100 100" aria-hidden="true" role="img">
          <path d="M50 5L95 50 50 95 5 50z" fill="rgb(var(--accent))"/>
          <path d="M50 20 80 50 50 80 20 50z" fill="#0b0f14"/>
        </svg>
        <div class="title">ZeroDayEmpire</div>
        <span class="badge">Cyber-Strategie</span>
      </a>
      <button class="btn ghost menu-toggle" id="menuBtn" aria-expanded="false" aria-controls="navList">Menü</button>
      <nav class="nav-links" id="navList" aria-label="Hauptnavigation">
        <a href="pub.php?a=register" id="registerLink">Registrieren</a>
        <a href="pub.php" id="loginLink">Anmelden</a>
        <a href="dashboard.html" id="dashboardLink" style="display:none">Dashboard</a>
        <a href="#" id="logoutLink" style="display:none" onclick="logout()">Abmelden</a>
        <a href="config.html" id="configLink" style="display:none">Config</a>
        <a class="btn play-link" href="pub.php">Jetzt spielen</a>
      </nav>
    </div>
  </header>
  <main>
    <div class="container">
      <h1>Impressum</h1>
      <p>Max Mustermann</p>
      <p>Musterstraße 1</p>
      <p>12345 Musterstadt</p>
      <p>E-Mail: info@example.com</p>
    </div>
  </main>
  <footer>
    <div class="container foot">
      <div>© <span id="year"></span> ZeroDayEmpire</div>
      <div class="links">
        <a href="impressum.php">Impressum</a>
        <a href="legal.php">Legal</a>
      </div>
    </div>
  </footer>
  <script>
    (function(){
      const token = localStorage.getItem('token') || sessionStorage.getItem('token') || (document.cookie.match(/token=([^;]+)/)||[])[1];
      const role = localStorage.getItem('role') || sessionStorage.getItem('role');
      const reg = document.getElementById('registerLink');
      const login = document.getElementById('loginLink');
      const dash = document.getElementById('dashboardLink');
      const logout = document.getElementById('logoutLink');
      const cfg = document.getElementById('configLink');
      const playLinks = document.querySelectorAll('.play-link');
      window.logout = function(){
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        sessionStorage.removeItem('token');
        sessionStorage.removeItem('role');
        document.cookie = 'token=; Max-Age=0; path=/';
        fetch('/logout',{method:'POST',headers:{'Authorization':token}});
        window.location.href = 'index.php';
      };
      if(token){
        if(reg) reg.style.display='none';
        if(login) login.style.display='none';
        if(dash) dash.style.display='inline-flex';
        if(logout) logout.style.display='inline-flex';
        playLinks.forEach(a => a.href='dashboard.html');
      }else{
        playLinks.forEach(a => a.href='pub.php');
      }
      if(role === 'admin' && cfg){ cfg.style.display = 'inline-flex'; }
    })();
    (function(){
      const nav = document.getElementById('nav');
      const btn = document.getElementById('menuBtn');
      if(!btn) return;
      btn.addEventListener('click', () => {
        const open = nav.classList.toggle('open');
        btn.setAttribute('aria-expanded', String(open));
      });
    })();
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
</body>
</html>
