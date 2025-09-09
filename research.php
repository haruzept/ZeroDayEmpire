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

if (!function_exists('infobox')) {
    function infobox($titel, $class, $text, $param = 'class')
    {
        $formatted = nl2br($text);
        return "<div {$param}=\"{$class}\">\n"
            . "<h3>{$titel}</h3>\n"
            . "<p>{$formatted}</p>\n"
            . "</div>\n";
    }
}

    echo infobox(
        'Forschung & Entwicklung – Schadsoftware (sim.)',
        'info',
        'Was ist das?\n'
        . 'F&E erweitert das Upgrade-System um Forschungsslots. Forschung läuft zeitbasiert und schaltet stufenweise Effekte frei.\n\n'
        . 'Grundprinzip:\n'
        . '• 10 Forschungszweige mit je 5 Stufen (rein simulativ).\n'
        . '• Kosten/Zeit analog Upgrade: Basis je Zweig (L1) + Multiplikatoren.\n'
        . '• Dauer wird – wie bei Upgrades – zusätzlich mit der bestehenden CPU/RAM-Dauerfunktion skaliert.\n'
        . '• Max. parallele Forschungen = Forschungsslots.\n\n'
        . 'Zweige (Kurz):\n'
        . 'T1 r_ana Öffentliche Skriptanalyse → Einstieg.\n'
        . 'T2 r_poc PoC-Verständnis & Doku → Verständnis vertiefen.\n'
        . 'T3 r_bauk Modularer Code-Baukasten → Module.\n'
        . 'T4 r_lab Simuliertes Schwachstellen-Labor → Testumgebungen.\n'
        . 'T5 r_pers Persistenz-Forschung (sim.) → Haltbarkeit.\n'
        . 'T6 r_veil Verschleierungs-Methoden (sim.) → Tarnung.\n'
        . 'T7 r_c2 Steuerkanal-Emulation (sim.) → Kontrolle & Kommunikation.\n'
        . 'T8 r_data Datenzugriffs-Strategien (sim.) → Zugriff & Pfade.\n'
        . 'T9 r_se Social-Engineering-Simulation (sim.) → Menschlicher Faktor.\n'
        . 'T10 r_rans Ransomware-Architektur (sim.) → Endarchitektur.\n\n'
        . 'Freischaltung (Kurz):\n'
        . '• T2 ab T1≥2 | T3 ab T2≥2 | T4 ab T3≥2.\n'
        . '• T5 ab T3≥3 & T4≥2 | T6 ab T3≥3.\n'
        . '• T7 ab T5≥2 & T6≥2 | T8 ab T4≥3 & T5≥2.\n'
        . '• T9 ab T2≥3 & T8≥2 | T10 ab T5≥4, T6≥3, T7≥3, T8≥3, T9≥2.\n'
        . '• Zusätzliche Level-Gates sind in der Datenbank gepflegt.\n\n'
        . 'Kosten/Zeit (L1, pro Stufe ×1.60 / ×1.45):\n'
        . 'r_ana 100/5 · r_poc 200/8 · r_bauk 350/12 · r_lab 600/18 · r_pers 900/25 · r_veil 1400/35 · r_c2 2200/50 · r_data 3400/70 · r_se 5200/95 · r_rans 8000/130.\n\n'
        . 'Hinweise:\n'
        . '• Rein spielmechanisch (simuliert). Keine realen Anleitungen.\n'
        . '• Forschung kann pausiert/abgebrochen werden (kein Refund).\n'
        . '• Admin-Events können Zeit/Kosten global beeinflussen.'
    );

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
        echo '<tr><th>'.htmlspecialchars($name).'</th><td>L'.$cur.' » L'.$next.'</td>';
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
    echo '<h3>Verfügbare Forschung</h3>';
    echo '<p><strong>Geld: '.number_format($pc['credits'],0,',','.').' Credits</strong></p>'."\n";
    echo '<table>'."\n";
    echo '<tr><th>Zweig</th><th>Level</th><th>Dauer</th><th>Kosten</th><th>Erforschen</th></tr>'."\n";
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
            echo '<a href="research.php?a=start&amp;track='.$track.'&amp;sid='.$sid.'">Erforschen</a>';
        } else {
            $msg = $can ? 'Nicht genügend Credits' : $dep;
            echo '<span title="'.htmlspecialchars($msg).'">Erforschen</span>';
        }
        echo '</td></tr>'."\n";
    }
    echo '</table>';
} else {
    echo '<h3>Verfügbare Forschung</h3><p>Alle Slots belegt.</p>';
}

echo "\n".'</div>'."\n";

createlayout_bottom();
