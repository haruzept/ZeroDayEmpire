<?php
define('IN_ZDE', 1);
$FILE_REQUIRES_PC = true;
// Load the common game bootstrap. Using an absolute path via __DIR__
// avoids include_path issues that previously resulted in the script
// terminating with "Hacking attempt" when the file could not be found.
require_once __DIR__.'/ingame.php';

$action = $_REQUEST['a'] ?? $_REQUEST['action'] ?? $_REQUEST['m'] ?? $_REQUEST['page'] ?? '';

if ($action === 'start') {
    $track = $_GET['track'] ?? '';
    $res = research_start($pcid, $track);
    if (isset($res['error'])) {
        header('Location: research.php?sid='.$sid.'&error='.urlencode($res['error']));
    } else {
        header('Location: research.php?sid='.$sid.'&ok='.urlencode('Forschung gestartet'));
    }
    exit;
}
if ($action === 'cancel') {
    $id = (int)($_GET['id'] ?? 0);
    if (research_cancel($pcid, $id)) {
        header('Location: research.php?sid='.$sid.'&ok='.urlencode('Forschung abgebrochen'));
    } else {
        header('Location: research.php?sid='.$sid.'&error='.urlencode('Abbruch nicht m&ouml;glich'));
    }
    exit;
}

processupgrades($pc);
if ($pc['blocked'] > time()) { exit; }

createlayout_top('ZeroDayEmpire - Forschung');
?>
<!-- ZDE theme inject -->
<style>@import url("style.css");</style>
<div class="container">
<?php // /ZDE theme inject start

echo '<div class="content" id="computer">'."\n";
echo '<h2>Forschung</h2>'."\n";
echo '<div class="submenu"><p><a href="game.php?m=start&amp;sid='.$sid.'">Zur &Uuml;bersicht</a></p></div>'."\n";

include 'includes/codex_infobox_research.php';

$notif = '';
if (!empty($_GET['ok'])) {
    $notif .= '<div class="ok"><h3>OK</h3><p>'.htmlspecialchars($_GET['ok']).'</p></div>';
}
if (!empty($_GET['error'])) {
    $notif .= '<div class="error"><h3>Fehler</h3><p>'.htmlspecialchars($_GET['error']).'</p></div>';
}
echo $notif;

$r = db_query('SELECT * FROM research WHERE pc=\''.mysql_escape_string($pcid).'\' AND `end`>\''.time().'\' ORDER BY `start` ASC;');
$full = mysql_num_rows($r);
$maxSlots = isset($pc['research_slots']) ? (int)$pc['research_slots'] : 1;
if ($full > 0) {
    $states = array();
    $rs = db_query('SELECT track, level FROM research_state WHERE pc=\''.mysql_escape_string($pcid).'\';');
    while ($s = mysql_fetch_assoc($rs)) { $states[$s['track']] = (int)$s['level']; }

    echo '<h3>Laufende Forschungen</h3><p><strong>Es sind '.$full.' von '.$maxSlots.' Slots belegt</strong></p>'."\n";
    echo '<table>'."\n";
    while ($row = mysql_fetch_assoc($r)) {
        $ti = db_query('SELECT name FROM research_tracks WHERE track=\''.mysql_escape_string($row['track']).'\' LIMIT 1;');
        $name = mysql_result($ti,0,'name');
        $cur = $states[$row['track']] ?? ($row['target_level']-1);
        $next = $row['target_level'];
        echo '<tr><th>'.htmlspecialchars($name).'</th><td>L'.$cur.' &raquo; L'.$next.'</td>';
        echo '<td>'.nicetime($row['end']).'</td>';
        echo '<td><a href="research.php?a=cancel&amp;id='.$row['id'].'&amp;sid='.$sid.'">Abbrechen</a></td></tr>'."\n";
        $states[$row['track']] = $next;
    }
    echo '</table>'."\n";
    echo '<p>Abbruch erstattet keine Credits.</p>';
} else {
    echo '<h3>Laufende Forschungen</h3><p><strong>Es sind 0 von '.$maxSlots.' Slots belegt</strong></p>';
}

if ($full < $maxSlots) {
    $tracks = research_get_tracks();
    echo '<h3>Verf&uuml;gbare Forschung</h3>';
    echo '<p><strong>Geld: '.number_format($pc['credits'],0,',','.').' Credits</strong></p>'."\n";
    echo '<table>'."\n";
    echo '<tr><th>Zweig</th><th>Level</th><th>Dauer</th><th>Kosten</th><th>Start</th></tr>'."\n";
    foreach ($tracks as $track => $info) {
        $cur = $info['level'];
        $max = $info['max_level'];
        echo '<tr><td>'.htmlspecialchars($info['name']).'</td><td>'.$cur.'/'.$max.'</td>';
        if ($cur >= $max) {
            echo '<td colspan="3">Max</td></tr>'."\n";
            continue;
        }
        $timeStr = floor($info['next_time']/60).' min';
        if ($info['next_time'] >= 3600) {
            $h = floor($info['next_time']/3600);
            $m = floor(($info['next_time']/60)%60);
            $timeStr = $h.' h'.($m>0?' '.$m.' min':'');
        }
        $dep = research_check_deps($pcid,$track,$cur+1);
        $can = ($dep === true);
        echo '<td>'.$timeStr.'</td><td>'.$info['next_cost'].' Credits</td><td>';
        if ($can && $pc['credits'] >= $info['next_cost']) {
            echo '<a href="research.php?a=start&amp;track='.$track.'&amp;sid='.$sid.'">Start</a>';
        } else {
            $msg = $can ? 'Nicht gen&uuml;gend Credits' : $dep;
            echo '<span title="'.htmlspecialchars($msg).'">Start</span>';
        }
        echo '</td></tr>'."\n";
    }
    echo '</table>';
} else {
    echo '<h3>Verf&uuml;gbare Forschung</h3><p>Alle Slots belegt.</p>';
}

echo "\n".'</div>'."\n";
?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
