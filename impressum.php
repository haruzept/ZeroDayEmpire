<?php
// Display the Impressum page. If a valid SID is provided, keep the in-game menu.
define('IN_ZDE', 1);
if (isset($_GET['sid'])) {
    // Load user context but do not require a PC to display this page.
    $FILE_REQUIRES_PC = false;
    include 'ingame.php';
} else {
    // No session id – still display the page using the public layout.
    include 'gres.php';
    include 'layout.php';
}

createlayout_top('Impressum - ZeroDayEmpire');
?>
<h1>Impressum</h1>
<p>Max Mustermann</p>
<p>Musterstraße 1</p>
<p>12345 Musterstadt</p>
<p>E-Mail: info@example.com</p>
<?php
createlayout_bottom();
?>
