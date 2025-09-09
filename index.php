<?php
// Fetch champion name for KPI display
define('IN_ZDE', 1);
require_once __DIR__ . '/gres.php';
$champion = '';
$r = db_query('SELECT name FROM rank_users ORDER BY platz ASC LIMIT 1;');
if ($row = mysql_fetch_assoc($r)) {
    $champion = $row['name'];
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ZeroDayEmpire</title>
  <meta name="description" content="ZeroDayEmpire – Baue dein Syndikat, hacke Rivalen, dominiere den Markt." />
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
        <a href="dashboard.html" id="dashboardLink" style="display:none">Dashboard</a>
        <a href="#" id="logoutLink" style="display:none" onclick="logout()">Abmelden</a>
        <a href="config.html" id="configLink" style="display:none">Config</a>
        <a class="btn play-link" href="pub.php">Jetzt spielen</a>
      </nav>
    </div>
  </header>

  <main>
    <section class="hero">
      <div class="hero-grid" aria-hidden="true"></div>
      <div class="container">
        <h1>Baue. Hacke. Dominiere.</h1>
        <p>Errichte dein Syndikat, durchkreuze feindliche Operationen und kontrolliere die Schwarzmärkte. <strong>ZeroDayEmpire</strong> kombiniert Basisbau, Forschung und PvP-Infiltration in einer stilvollen Cyberpunk-Welt.</p>
        <div class="cta">
          <a class="btn play-link" href="pub.php" aria-label="Jetzt spielen">▶ Jetzt spielen</a>
          <a class="btn ghost" href="pub.php?a=register" aria-label="Account erstellen">+ Registrieren</a>
        </div>

        <div class="features" aria-label="Spiel-Features">
          <article class="card span-6">
            <div class="icon" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M3 12h18M12 3v18" stroke="rgb(var(--accent))" stroke-width="2"/></svg>
            </div>
            <h3>Syndikat aufbauen</h3>
            <p>Skaliere deine Infrastruktur von der Hinterhof-Node bis zum globalen Botnetz. Automatisiere Produktion und Handel über modulare Gebäude.</p>
          </article>
          <article class="card">
            <div class="icon" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M4 4h16v12H4z" stroke="rgb(var(--accent))" stroke-width="2"/><path d="M2 18h20" stroke="rgb(var(--accent))"/></svg>
            </div>
            <h3>Zero‑Day Angriffe</h3>
            <p>Nutze Schwachstellen, injiziere Exploits und exfiltriere Ressourcen – in Echtzeit gegen echte Gegner.</p>
          </article>
          <article class="card">
            <div class="icon" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="rgb(var(--accent))" stroke-width="2"/><path d="M12 6v6l4 2" stroke="rgb(var(--accent))" stroke-width="2"/></svg>
            </div>
            <h3>Forschung & Tech-Tree</h3>
            <p>Entsperre neue Tools, Tarnmodule und Verteidigungen. Finde Synergien für deinen Spielstil.</p>
          </article>
          <article class="card">
            <div class="icon" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z" stroke="rgb(var(--accent))" stroke-width="2"/></svg>
            </div>
            <h3>Allianzen & Diplomatie</h3>
            <p>Schließe Pakte, teile Aufklärung und orchestriere koordinierte Übernahmen gegnerischer Fraktionen.</p>
          </article>
          <article class="card span-6">
            <div class="icon" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M3 12c0-5 4-9 9-9s9 4 9 9-4 9-9 9S3 17 3 12z" stroke="rgb(var(--accent))" stroke-width="2"/><path d="M7 14l3 3 7-7" stroke="rgb(var(--accent))" stroke-width="2"/></svg>
            </div>
            <h3>Taktische Aufträge</h3>
            <p>Kurze, zielbasierte Missionen für zwischendurch – ideal für Mobile & Desktop. Sammle seltene Module für deinen Loadout.</p>
          </article>
        </div>

        <div class="strip" aria-label="Kurzinfo">
          <div class="kpi"><div class="label">Spieler online</div><div class="value" id="kpiOnline">—</div></div>
          <div class="kpi"><div class="label">Platz 1 der Rangliste</div><div class="value" id="kpiChampion"><?php echo htmlspecialchars($champion ?: '—'); ?></div></div>
          <div class="kpi"><div class="label">Aktuelle Version</div><div class="value" id="kpiVersion">v0.0.1</div></div>
        </div>
      </div>
    </section>
  </main>

  <footer>
    <div class="container foot">
      <div>© <span id="year"></span> ZeroDayEmpire</div>
      <div class="links">
        <a href="impressum.php">Impressum</a>
        <a href="legal.php">Legal</a>
        <a href="pub.php?d=rules">Spielregeln</a>
      </div>
    </div>
  </footer>

  <script>
    // Anzeige basierend auf Login-Status und Rolle
      (function(){
        let token = localStorage.getItem('token') || sessionStorage.getItem('token') || (document.cookie.match(/token=([^;]+)/)||[])[1];
        if(token && !localStorage.getItem('token') && !sessionStorage.getItem('token')){
          localStorage.setItem('token', token);
        }
        const role = localStorage.getItem('role') || sessionStorage.getItem('role');
        const reg = document.getElementById('registerLink');
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
          if(dash) dash.style.display='inline-flex';
          if(logout) logout.style.display='inline-flex';
          playLinks.forEach(a => a.href='dashboard.html');
        }else{
          playLinks.forEach(a => a.href='pub.php');
        }
        if(role === 'admin' && cfg){ cfg.style.display = 'inline-flex'; }
      })();

    // Mobile menu toggle
    (function(){
      const nav = document.getElementById('nav');
      const btn = document.getElementById('menuBtn');
      if(!btn) return;
      btn.addEventListener('click', () => {
        const open = nav.classList.toggle('open');
        btn.setAttribute('aria-expanded', String(open));
      });
    })();

    // Fancy pointer glow in the background
    window.addEventListener('pointermove', (e) => {
      const x = e.clientX / window.innerWidth * 100;
      const y = e.clientY / window.innerHeight * 100;
      document.documentElement.style.setProperty('--mx', x + '%');
      document.documentElement.style.setProperty('--my', y + '%');
    }, {passive:true});

    // Spieler online aus Statistik laden
    (function(){
      const el = document.getElementById('kpiOnline');
      if(!el) return;
      const update = () => {
        fetch('pub.php?d=stats')
          .then(r => r.text())
          .then(html => {
            const m = html.match(/Spieler online:<\/th>\s*<td>(\d+)/i);
            if(m) el.textContent = Number(m[1]).toLocaleString('de-DE');
          });
      };
      update();
      setInterval(update, 60000);
    })();

    // Year in footer
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
</body>
</html>
