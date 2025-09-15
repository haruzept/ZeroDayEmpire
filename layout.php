<?php

if (!defined('IN_ZDE')) {
    die('Hacking attempt');
}

$javascript = '';
$bodytag = '';

include_once('config.php');

$server_tz = @file_get_contents('/etc/timezone');
if ($server_tz !== false) {
    $server_tz = trim($server_tz);
}
if ($server_tz === '' || $server_tz === false) {
    $server_tz = date_default_timezone_get();
}
date_default_timezone_set($server_tz);

function basicheader($title)
{
    global $javascript;
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html>\n";
    echo "<html lang=\"de\">\n";
    echo "<head>\n";
    echo "<meta charset=\"utf-8\">\n";
    echo "<title>$title</title>\n";
    echo '<link rel="stylesheet" href="style.css">' . "\n";
    echo '<script src="global.js" defer></script>' . "\n";
    echo '<link rel="icon" href="favicon.ico">' . "\n";
    echo $javascript;
    echo "</head>\n";
}

function basicfooter()
{
    echo "</body>\n</html>";
}

function createlayout_top($title = 'ZeroDayEmpire', $nomenu = false)
{
    global $usr, $bodytag;
    $sid = '';
    if (isset($usr['sid']) && $usr['sid'] !== '') {
        $sid = '&amp;sid=' . $usr['sid'];
    }

    basicheader($title);

    echo "<body$bodytag>\n";
    echo '<header class="site-header">';
    echo '<div class="container nav" id="nav">';
    $home = $sid === '' ? 'index.php' : 'game.php?m=start' . $sid;
    echo '<a class="brand" href="' . $home . '" aria-label="ZeroDayEmpire Startseite">';
    echo '<svg viewBox="0 0 100 100" aria-hidden="true" role="img">';
    echo '<path d="M50 5L95 50 50 95 5 50z" fill="rgb(var(--accent))"/>';
    echo '<path d="M50 20 80 50 50 80 20 50z" fill="#0b0f14"/>';
    echo '</svg>';
    echo '<div class="title">ZeroDayEmpire</div>';
    echo '<span class="badge">Cyber-Strategie</span>';
    echo '</a>';
    echo '<button class="btn ghost menu-toggle" id="menuBtn" aria-expanded="false" aria-controls="navList">Menü</button>';

    if ($nomenu == false) {
        echo '<nav class="nav-links" id="navList" aria-label="Hauptnavigation">';
        if ($sid !== '') {
            echo '<a href="game.php?m=start' . $sid . '">Übersicht</a>';
            echo '<a href="game.php?m=pc' . $sid . '">Computer</a>';
            echo '<a href="cluster.php?a=start' . $sid . '">Cluster</a>';
            echo '<a href="battle.php?m=opc&sid=' . $usr['sid'] . '">Operation Center</a>';
            echo '<a href="upgradelist.php?sid=' . $usr['sid'] . '">Upgrade</a>';
            echo '<a href="research.php?sid=' . $usr['sid'] . '">Forschung</a>';
            echo '<a href="ranking.php?m=ranking' . $sid . '">Rangliste</a>';
            echo '<a href="user.php?a=config' . $sid . '">Optionen</a>';
            echo '<a href="login.php?a=logout' . $sid . '">Abmelden</a>';
        } else {
            echo '<a href="pub.php?a=register" id="registerLink">Registrieren</a>';
            echo '<a href="dashboard.html" id="dashboardLink" style="display:none">Dashboard</a>';
            echo '<a href="#" id="logoutLink" style="display:none" onclick="logout()">Abmelden</a>';
            echo '<a href="config.html" id="configLink" style="display:none">Config</a>';
            echo '<a class="btn play-link" href="pub.php">Jetzt spielen</a>';
        }
        echo '</nav>';
    }

    echo '</div></header>';
    echo '<main class="container">';
}

function createlayout_bottom()
{
    global $usr;
    $sid = '';
    if (isset($usr['sid']) && $usr['sid'] !== '') {
        $sid = '?sid=' . $usr['sid'];
    }
    echo "</main>\n";
    echo '<footer><div class="container foot">';
    echo '<div>© <span id="year"></span> ZeroDayEmpire</div>';
    echo '<div class="links"><a href="impressum.php' . $sid . '">Impressum</a><a href="legal.php' . $sid . '">Legal</a><a href="rules.php' . $sid . '">Spielregeln</a></div>';
    echo '</div></footer>';
    echo '<script>';
    echo '(function(){';
    echo 'let token=localStorage.getItem("token")||sessionStorage.getItem("token")||(document.cookie.match(/token=([^;]+)/)||[])[1];';
    echo 'if(token && !localStorage.getItem("token") && !sessionStorage.getItem("token")){localStorage.setItem("token",token);}';
    echo 'const role=localStorage.getItem("role")||sessionStorage.getItem("role");';
    echo 'const reg=document.getElementById("registerLink");';
    echo 'const dash=document.getElementById("dashboardLink");';
    echo 'const logout=document.getElementById("logoutLink");';
    echo 'const cfg=document.getElementById("configLink");';
    echo 'const playLinks=document.querySelectorAll(".play-link");';
    echo 'window.logout=function(){localStorage.removeItem("token");localStorage.removeItem("role");sessionStorage.removeItem("token");sessionStorage.removeItem("role");document.cookie="token=; Max-Age=0; path=/";fetch("/logout",{method:"POST",headers:{"Authorization":token}});window.location.href="index.php";};';
    echo 'if(token){if(reg) reg.style.display="none";if(dash) dash.style.display="inline-flex";if(logout) logout.style.display="inline-flex";playLinks.forEach(a=>a.href="dashboard.html");}else{playLinks.forEach(a=>a.href="pub.php");}';
    echo 'if(role==="admin"&&cfg){cfg.style.display="inline-flex";}';
    echo '})();';
    echo '(function(){const nav=document.getElementById("nav");const btn=document.getElementById("menuBtn");if(btn){btn.addEventListener("click",()=>{const open=nav.classList.toggle("open");btn.setAttribute("aria-expanded",String(open));});}})();';
    echo 'window.addEventListener("pointermove",e=>{const x=e.clientX/window.innerWidth*100;const y=e.clientY/window.innerHeight*100;document.documentElement.style.setProperty("--mx",x+"%");document.documentElement.style.setProperty("--my",y+"%");},{passive:true});';
    echo 'document.getElementById("year").textContent=new Date().getFullYear();';
    echo '</script>';
    basicfooter();
}

