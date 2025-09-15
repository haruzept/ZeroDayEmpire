<?php
define('IN_ZDE', 1);
$FILE_REQUIRES_PC = true;
require_once __DIR__.'/ingame.php';

$action = $_REQUEST['a'] ?? $_REQUEST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'start') {
        $track = $_POST['track'] ?? '';
        $res = research_start($pcid, $track);
        header('Content-Type: application/json');
        if (isset($res['error'])) {
            $reason = 'deps';
            if ($res['error'] === 'Nicht genügend Credits') {
                $reason = 'credits';
            } elseif ($res['error'] === 'Keine freien Slots') {
                $reason = 'slots';
            }
            echo json_encode(['ok' => false, 'reason' => $reason]);
        } else {
            echo json_encode(['ok' => true]);
        }
        exit;
    }
    if ($action === 'cancel') {
        $id = (int)($_POST['id'] ?? 0);
        $ok = research_cancel($pcid, $id);
        header('Content-Type: application/json');
        echo json_encode(['ok' => $ok]);
        exit;
    }
}

processupgrades($pc);
if ($pc['blocked'] > time()) { exit; }

function format_duration($seconds) {
    $seconds = (int)$seconds;
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    if ($h > 0) {
        return $m > 0 ? $h.' h '.$m.' min' : $h.' h';
    }
    return $m.' min';
}
function format_credits($n) {
    return number_format((int)$n, 0, ',', '.').' Credits';
}
function dependency_badge($ok) {
    return '<span class="badge muted">'.($ok ? 'Erfüllte Abhängigkeit' : 'Abhängigkeit fehlt').'</span>';
}

createlayout_top('ZeroDayEmpire - Forschung');

echo '<header class="page-head"><h1>Forschung</h1></header>';

$now = time();
$runningRows = [];
$r = db_query('SELECT * FROM research WHERE pc=\''.mysql_escape_string($pcid).'\' AND `end`>\''.mysql_escape_string($now).'\' ORDER BY `start` ASC');
while ($row = mysql_fetch_assoc($r)) { $runningRows[] = $row; }
$running = count($runningRows);
$runningTracks = [];
foreach ($runningRows as $row) { $runningTracks[$row['track']] = true; }
$maxSlots = isset($pc['research_slots']) ? (int)$pc['research_slots'] : 1;
$credits = (int)$pc['credits'];
$tracks = research_get_tracks();

$queueLabel = 'Keine Forschung aktiv';
if ($running) {
    $first = $runningRows[0];
    $info = $tracks[$first['track']] ?? null;
    $curLvl = $info['level'] ?? ($first['target_level'] - 1);
    $maxLvl = $info['max_level'] ?? $first['target_level'];
    $progress = $curLvl.'/'.$maxLvl;
    $queueTime = $first['end'] - $now;
    $name = htmlspecialchars($info['name'] ?? $first['track']);
    $queueLabel = $name.' '.$progress.' <span class="cd" data-end="'.$first['end'].'">'.sprintf('%02d:%02d', floor($queueTime/3600), floor(($queueTime%3600)/60)).'</span> min';
}

echo '<div class="strip">';
echo '<div class="kpi kpi-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" width="50" height="50"><path d="M3 12h18M12 3v18" stroke="rgb(var(--accent))" stroke-width="2" fill="none"/></svg><div class="stat"><h3 class="value small">Verfügbare Slots: '.$running.' / '.$maxSlots.'</h3></div></div>';
echo '<div class="kpi kpi-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" width="50" height="50"><path d="M4 4h16v12H4z" stroke="rgb(var(--accent))" stroke-width="2" fill="none"/><path d="M2 18h20" stroke="rgb(var(--accent))"/></svg><div class="stat"><h3 class="value small" id="kpiCredits" data-value="'.$credits.'">'.format_credits($credits).'</h3></div></div>';
echo '<div class="kpi kpi-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" width="50" height="50"><circle cx="12" cy="12" r="9" stroke="rgb(var(--accent))" stroke-width="2" fill="none"/><path d="M12 7v5l3 2" stroke="rgb(var(--accent))" stroke-width="2" fill="none"/></svg><div class="stat"><h3 class="value small">'.$queueLabel.'</h3></div></div>';
echo '</div>';

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

echo '<section class="card table-card" id="researchTable" style="overflow:visible"><h2>Verfügbare Forschung</h2><table id="researchTableInner" style="width:100%"><thead><tr><th scope="col" style="text-align:center">Zweig</th><th scope="col" style="text-align:center">Level</th><th scope="col" style="text-align:center">Dauer</th><th scope="col" style="text-align:center">Kosten</th><th scope="col" style="text-align:center">Status</th><th scope="col" style="text-align:center">Aktion</th></tr></thead><tbody>';
foreach ($tracks as $track => $info) {
    $cur = $info['level'];
    $max = $info['max_level'];
    $tooltipText = str_replace("\n", '&#10;', htmlspecialchars($trackTooltips[$track] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    echo '<tr><td'.($tooltipText ? ' class="tooltip" data-tooltip="'.$tooltipText.'"' : '').'><strong>'.htmlspecialchars($info['name']).'</strong></td><td>'.$cur.'/'.$max.'</td>';
    if ($cur >= $max) {
        echo '<td colspan="3">Max</td><td></td></tr>';
        continue;
    }
    $timeStr = format_duration($info['next_time']);
    $dep = research_check_deps($pcid, $track, $cur + 1);
    $dep_ok = $dep === true;
    $slotFree = ($running < $maxSlots);
    $creditOK = $credits >= $info['next_cost'];
    $runningThis = isset($runningTracks[$track]);
    echo '<td>'.$timeStr.'</td><td>'.format_credits($info['next_cost']).'</td>';
    if ($runningThis) {
        echo '<td>Upgrade l&auml;uft</td>';
    } else {
        $depTooltip = '';
        if (!$dep_ok) {
            $depTooltip = str_replace("\n", '&#10;', htmlspecialchars($dep, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        }
        echo '<td'.($depTooltip ? ' class="tooltip" data-tooltip="'.$depTooltip.'"' : '').'>'.dependency_badge($dep_ok).'</td>';
    }
    $tooltip = '';
    if (!$slotFree) { $tooltip = 'Alle Forsch-Slots belegt'; }
    elseif (!$creditOK) { $tooltip = 'Zu wenig Credits'; }
    elseif ($runningThis) { $tooltip = 'Upgrade l&auml;uft'; }
    $can = $dep_ok && $slotFree && $creditOK && !$runningThis;
    $btnAttr = 'class="btn sm start-btn" data-track="'.$track.'" data-cost="'.$info['next_cost'].'" data-duration="'.$info['next_time'].'"';
    if (!$can) {
        $btnAttr .= ' disabled aria-disabled="true"';
        if (!$dep_ok || $runningThis) { $btnAttr .= ' style="background-color:#888;color:#ccc;"'; }
        $buttonHtml = '<button '.$btnAttr.'>Erforschen</button>';
        if ($tooltip) { $buttonHtml = '<span class="tooltip" data-tooltip="'.$tooltip.'">'.$buttonHtml.'</span>'; }
        echo '<td>'.$buttonHtml.'</td></tr>';
    } else {
        echo '<td><button '.$btnAttr.'>Erforschen</button></td></tr>';
    }
}
echo '</tbody></table></section>';

echo '<script>
function updTimers(){document.querySelectorAll(".cd").forEach(function(el){var end=parseInt(el.dataset.end,10);var s=end-Math.floor(Date.now()/1000);if(s<0)s=0;var h=Math.floor(s/3600),m=Math.floor((s%3600)/60),sec=s%60;el.textContent=(h>0?String(h).padStart(2,"0")+":"+String(m).padStart(2,"0"):String(m).padStart(2,"0"))+":"+String(sec).padStart(2,"0");});}
updTimers();setInterval(updTimers,1000);
document.querySelectorAll(".start-btn").forEach(function(btn){btn.addEventListener("click",function(){if(btn.disabled)return;btn.disabled=true;var track=btn.dataset.track;var old=btn.textContent;btn.innerHTML="<span class=\"spinner\"></span>";fetch("research.php?a=start&sid='.$sid.'",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:"track="+encodeURIComponent(track)}).then(function(r){return r.json();}).then(function(data){if(data.ok){location.reload();}else{btn.textContent=old;btn.disabled=false;}});});});
</script>';

createlayout_bottom();
?>
