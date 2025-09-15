<?php
// Basic attack menu page.
define('IN_ZDE', 1);
$FILE_REQUIRES_PC = true;
require_once __DIR__.'/ingame.php';
require_once __DIR__.'/includes/attacks_lib.php';

$pc = $pcid; // from ingame.php
$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_code'])) {
    $code = trim($_POST['start_code']);
    $res = attacks_start_controller($pc, $code);
    $messages[] = $res['message'];
}

$defs = attacks_list_all();
$runs = attacks_recent_runs($pc, 20);

createlayout_top('ZeroDayEmpire - Angriffe');

echo '<header class="page-head"><h1>Angriffe</h1></header>';

foreach ($messages as $m) {
    echo '<p class="msg">'.htmlspecialchars($m).'</p>';
}

include __DIR__.'/templates/attacks_list.tpl.php';

createlayout_bottom();
