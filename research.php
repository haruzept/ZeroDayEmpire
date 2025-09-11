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
        header('Location: research.php?sid='.$sid.'&error='.urlencode('Abbruch nicht möglich'));
    }
    exit;
}

processupgrades($pc);
if ($pc['blocked'] > time()) { exit; }

createlayout_top('ZeroDayEmpire - Forschung');
echo '<div class="content" id="computer">'."\n";
echo '<h2>Forschung</h2>'."\n";
echo '<div class="submenu"><p><a href="game.php?m=start&amp;sid='.$sid.'">Zur Übersicht</a></p></div>'."\n";


$notif = '';
if (!empty($_GET['ok'])) {
    $notif .= '<div class="ok"><h3>OK</h3><p>'.htmlspecialchars($_GET['ok']).'</p></div>';
}
if (!empty($_GET['error'])) {
    $notif .= '<div class="error"><h3>Fehler</h3><p>'.htmlspecialchars($_GET['error']).'</p></div>';
}
echo $notif;
 
// Tooltip descriptions for each research track
$trackTooltips = [
    'r_ana' => "Vermittelt die Grundlagen zum Einordnen öffentlich verfügbarer Skripte/PoCs in der Simulation. Dient als Voraussetzung für fortgeschrittene Zweige.",
    'r_bauk' => "Baut wiederverwendbare Module auf, damit Funktionen in Szenarien schneller kombiniert werden können. Erhöht die Effizienz nachfolgender Forschungsschritte.",
    'r_c2' => "Emuliert sichere Command-&-Control-Mechaniken zur Koordination in Szenarien. Verbessert Tests zu Steuerung und Abstimmung verteilter Komponenten.",
    'r_data' => "Analysiert und modelliert Zugriffspfade auf Daten in Systemen – rein als Szenario. Verbessert die Bewertung von Risiken/Ertrag in Simulationen.",
    'r_lab' => "Stellt eine sichere Testumgebung bereit, um Verhalten und Wechselwirkungen zu beobachten. Reduziert Fehlversuche und ist Gate für höhere Forschung.",
    'r_pers' => "Untersucht, wie Zustände über Szenario-Neustarts hinweg erhalten bleiben (nur simuliert). Erhöht die Beständigkeit von Effekten und öffnet Pfade zu C2/Daten.",
    'r_poc' => "Vertieft das Verständnis von Proof-of-Concepts und sorgt für saubere Dokumentation. Erhöht Nachvollziehbarkeit und schaltet den Baukasten frei.",
    'r_rans' => "Modelliert eine End-to-End-Architektur als reines Lern-/Balancing-Szenario ohne reale Ausführung. Gilt als komplexes Abschlussziel und bündelt Erkenntnisse aus Persistenz, Verschleierung, C2, Datenzugriff und Social Engineering.",
    'r_se' => "Trainiert menschliche Faktoren, Kommunikation und Täuschungsmuster ohne echte Interaktion. Verbessert das Zusammenspiel mit Datenzugriff und Steuerkanal in Szenarien.",
    'r_veil' => "Untersucht Tarnmechaniken abstrakt innerhalb der Simulation. Unterstützt höhere Zweige, indem es Erkennungsrisiken in Szenario-Bewertungen reduziert.",
];

$r = db_query('SELECT * FROM research WHERE pc=\''.mysql_escape_string($pcid).'\' AND `end`>\''.time().'\' ORDER BY `start` ASC;');
$full = mysql_num_rows($r);
$maxSlots = isset($pc['research_slots']) ? (int)$pc['research_slots'] : 1;

echo '<div class="strip"><div class="kpi"><div class="label">Laufende Forschungen</div><div class="value">'.$full.' / '.$maxSlots.'</div></div>';
echo '<div class="kpi"><div class="label">Credits</div><div class="value">'.number_format($pc['credits'],0,',','.').'</div></div></div>';
if ($full > 0) {
    $states = array();
    $rs = db_query('SELECT track, level FROM research_state WHERE pc=\''.mysql_escape_string($pcid).'\';');
    while ($s = mysql_fetch_assoc($rs)) { $states[$s['track']] = (int)$s['level']; }

    echo '<h3>Laufende Forschungen</h3>'."\n";
    echo '<table>'."\n";
    while ($row = mysql_fetch_assoc($r)) {
        $ti = db_query('SELECT name FROM research_tracks WHERE track=\''.mysql_escape_string($row['track']).'\' LIMIT 1;');
        $name = mysql_result($ti,0,'name');
        $cur = $states[$row['track']] ?? ($row['target_level']-1);
        $next = $row['target_level'];
        echo '<tr><th>'.htmlspecialchars($name).'</th><td>L'.$cur.' » L'.$next.'</td>';
        echo '<td>'.nicetime($row['end']).'</td>';
        echo '<td><a href="research.php?a=cancel&amp;id='.$row['id'].'&amp;sid='.$sid.'">Abbrechen</a></td></tr>'."\n";
        $states[$row['track']] = $next;
    }
    echo '</table>'."\n";
    echo '<p>Abbruch erstattet keine Credits.</p>';
} else {
    echo '<h3>Laufende Forschungen</h3><p>Keine Forschung aktiv.</p>';
}

$tracks = research_get_tracks();
echo '<h3>Verfügbare Forschung</h3>';
echo '<table>'."\n";
echo '<tr><th>Zweig</th><th>Level</th><th>Dauer</th><th>Kosten</th><th>Erforschen</th></tr>'."\n";
foreach ($tracks as $track => $info) {
    $cur = $info['level'];
    $max = $info['max_level'];
    $tooltipText = str_replace("\n", '&#10;', htmlspecialchars($trackTooltips[$track] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    echo '<tr><td'.($tooltipText ? ' title="'.$tooltipText.'"' : '').'>'.htmlspecialchars($info['name']).'</td><td>'.$cur.'/'.$max.'</td>';
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
    $slotFree = ($full < $maxSlots);
    echo '<td>'.$timeStr.'</td><td>'.$info['next_cost'].' Credits</td><td>';
    if ($dep === true && $slotFree && $pc['credits'] >= $info['next_cost']) {
        echo '<a href="research.php?a=start&amp;track='.$track.'&amp;sid='.$sid.'">Erforschen</a>';
    } else {
        if ($dep !== true) { $msg = $dep; }
        elseif (!$slotFree) { $msg = 'Alle Slots belegt'; }
        else { $msg = 'Nicht genügend Credits'; }
        echo '<span title="'.htmlspecialchars($msg).'">Erforschen</span>';
    }
    echo '</td></tr>'."\n";
}
echo '</table>';

echo "\n".'</div>'."\n";

createlayout_bottom();
