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
            echo '<a href="cluster.php?a=start' . $sid . '">Cluster</a>';
            echo '<a href="research.php?sid=' . $usr['sid'] . '">Forschung</a>';
            echo '<a href="ranking.php?m=ranking' . $sid . '">Rangliste</a>';
            echo '<a href="user.php?a=config' . $sid . '">Optionen</a>';
            echo '<a href="login.php?a=logout' . $sid . '">Abmelden</a>';
        } else {
            echo '<a href="pub.php?a=register" id="registerLink">Registrieren</a>';
            echo '<a href="pub.php" id="loginLink">Anmelden</a>';
        }
        echo '</nav>';
    }

    echo '</div></header>';
    echo '<main class="container">';
}

function createlayout_bottom()
{
    echo "</main>\n";
    echo '<footer><div class="container foot">';
    echo '<div>© <span id="year"></span> ZeroDayEmpire</div>';
    echo '<div class="links"><a href="impressum.php">Impressum</a><a href="legal.php">Legal</a><a href="rules.php">Spielregeln</a></div>';
    echo '</div></footer>';
    echo '<script>(function(){const nav=document.getElementById("nav");const btn=document.getElementById("menuBtn");if(btn){btn.addEventListener("click",()=>{const open=nav.classList.toggle("open");btn.setAttribute("aria-expanded",String(open));});}})();';
    echo 'window.addEventListener("pointermove",e=>{const x=e.clientX/window.innerWidth*100;const y=e.clientY/window.innerHeight*100;document.documentElement.style.setProperty("--mx",x+"%");document.documentElement.style.setProperty("--my",y+"%");},{passive:true});';
    echo 'document.getElementById("year").textContent=new Date().getFullYear();</script>';
    basicfooter();
}

