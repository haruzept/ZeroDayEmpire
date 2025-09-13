<?php
// Legal notice page with optional in-game menu when SID is present.
define('IN_ZDE', 1);
if (isset($_GET['sid'])) {
    $FILE_REQUIRES_PC = false;
    include 'ingame.php';
} else {
    include 'gres.php';
    include 'layout.php';
}

createlayout_top('ZeroDayEmpire - Legal');
?>
<h1>Legal</h1>
<p>Dieses Projekt verwendet verschiedene Open​-Source​-Technologien:</p>
<ul>
  <li>PHP</li>
  <li>Nginx</li>
  <li>MariaDB</li>
</ul>
<p>Das Originalspiel inklusive Spielmechaniken basiert auf <a href="https://github.com/dergriewatz/htn-original">HackTheNet.org</a>.</p>
<?php
createlayout_bottom();
?>
