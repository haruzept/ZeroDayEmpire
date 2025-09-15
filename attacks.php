<?php
// Basic attack menu page.
define('IN_ZDE', 1);
$FILE_REQUIRES_PC = true;
require_once __DIR__.'/ingame.php';
require_once __DIR__.'/includes/attacks_lib.php';

$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_code'])) {
    $code = trim($_POST['start_code']);
    $res = attacks_start_controller($pcid, $code);
    $messages[] = $res['message'];
}

$defs = attacks_list_all();
$defMap = [];
foreach ($defs as $def) { $defMap[$def['code']] = $def; }

$inv = db_fetch_hardware_all($pcid);
$research = db_fetch_research_all($pcid);
$lan = (int)($inv['lan'] ?? 0);
$runningRows = [];
$r = db_query('SELECT * FROM attack_runs WHERE pc=\''.mysql_escape_string($pcid).'\' AND status=\'running\' ORDER BY started_at ASC');
while ($row = mysql_fetch_assoc($r)) { $runningRows[] = $row; }
$running = count($runningRows);
$maxSlots = current_parallel_slots($lan);
$cc = (int)$pc['cc'];

$queueLabel = 'Kein Angriff aktiv';
if ($running) {
    $first = $runningRows[0];
    $info = $defMap[$first['code']] ?? null;
    $name = htmlspecialchars($info['name'] ?? $first['code']);
    $queueTime = strtotime($first['ends_at']) - time();
    $queueLabel = $name.' <span class="cd" data-end="'.strtotime($first['ends_at']).'">'.sprintf('%02d:%02d', floor($queueTime/3600), floor(($queueTime%3600)/60)).'</span> min';
}
$riskTooltip = htmlspecialchars('Chance, dass der Angriff scheitert oder entdeckt wird.', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

function format_duration($seconds) {
    $seconds = (int)$seconds;
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    if ($h > 0) {
        return $m > 0 ? $h.' h '.$m.' min' : $h.' h';
    }
    return $m.' min';
}

function format_cc($n) {
    return number_format((int)$n, 0, ',', '.').' CryptoCoins';
}

function dependency_badge($ok) {
    return '<span class="badge muted">'.($ok ? 'Erfüllte Abhängigkeit' : 'Abhängigkeit fehlt').'</span>';
}

createlayout_top('ZeroDayEmpire - Angriffe');

echo '<header class="page-head"><h1>Angriffe</h1></header>';

foreach ($messages as $m) {
    echo '<p class="msg">'.htmlspecialchars($m).'</p>';
}

echo '<div class="strip">';
echo '<div class="kpi kpi-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" width="50" height="50"><path d="M3 12h18M12 3v18" stroke="rgb(var(--accent))" stroke-width="2" fill="none"/></svg><div class="stat"><h3 class="value small">Verfügbare Slots: '.$running.' / '.$maxSlots.'</h3></div></div>';
echo '<div class="kpi kpi-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" width="50" height="50"><path d="M4 4h16v12H4z" stroke="rgb(var(--accent))" stroke-width="2" fill="none"/><path d="M2 18h20" stroke="rgb(var(--accent))"/></svg><div class="stat"><h3 class="value small" id="kpiCryptoCoins" data-value="'.$cc.'">'.format_cc($cc).'</h3></div></div>';
echo '<div class="kpi kpi-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" width="50" height="50"><circle cx="12" cy="12" r="9" stroke="rgb(var(--accent))" stroke-width="2" fill="none"/><path d="M12 7v5l3 2" stroke="rgb(var(--accent))" stroke-width="2" fill="none"/></svg><div class="stat"><h3 class="value small">'.$queueLabel.'</h3></div></div>';
echo '</div>';

echo '<section class="card table-card" id="attackTable" style="overflow:visible"><h2>Verfügbare Angriffe</h2><table id="attackTableInner" style="width:100%"><thead><tr><th scope="col" style="text-align:center">Angriff</th><th scope="col" style="text-align:center">Angriffsdauer</th><th scope="col" style="text-align:center">Kosten</th><th scope="col" style="text-align:center">Verdienst</th><th scope="col" style="text-align:center" class="tooltip" data-tooltip="'.$riskTooltip.'">Risiko</th><th scope="col" style="text-align:center">Status</th><th scope="col" style="text-align:center">Aktion</th></tr></thead><tbody>';
foreach ($defs as $d) {
    $code = $d['code'];
    $state = get_attack_state($pcid, $code);
    $params = calc_effective_params($d, $state, $inv, $research);
    $deps = check_dependencies($pcid, $code, $state['level']);
    $dep_ok = $deps['ok'];
    $depTooltip = '';
    if (!$dep_ok) {
        $depTooltip = 'Benötigt: '.implode(', ', $deps['missing']);
        $depTooltip = htmlspecialchars($depTooltip, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    $cooldown = is_on_cooldown($pcid, $code);
    $slotFree = ($running < $maxSlots);
    $btnAttr = 'class="btn sm"';
    $tooltip = '';
    if (!$slotFree) { $tooltip = 'Alle Angriffsslots belegt'; }
    elseif ($cooldown) { $tooltip = 'Cooldown aktiv'; }
    elseif (!$dep_ok) { $tooltip = 'Voraussetzungen fehlen'; }
    if ($tooltip || !$dep_ok || !$slotFree || $cooldown) { $btnAttr .= ' disabled aria-disabled="true"'; }
    $button = '<form method="post" style="margin:0"><input type="hidden" name="start_code" value="'.htmlspecialchars($code).'"><button '.$btnAttr.'>Starten</button></form>';
    if ($tooltip) {
        $button = '<span class="tooltip" data-tooltip="'.htmlspecialchars($tooltip, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'">'.$button.'</span>';
    }
    $descrTooltip = htmlspecialchars($d['descr'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo '<tr><td class="tooltip" data-tooltip="'.$descrTooltip.'"><strong>'.htmlspecialchars($d['name']).'</strong></td><td>'.format_duration($params['duration_min']*60).'</td><td>'.format_cc($params['cost']).'</td><td>'.format_cc($params['payout_expected']).'</td><td class="tooltip" data-tooltip="'.$riskTooltip.'">'.$params['risk_pct'].'%</td><td'.($depTooltip ? ' class="tooltip" data-tooltip="'.$depTooltip.'"' : '').'>'.dependency_badge($dep_ok).'</td><td>'.$button.'</td></tr>';
}
echo '</tbody></table></section>';
echo '<script>function updTimers(){document.querySelectorAll(".cd").forEach(function(el){var end=parseInt(el.dataset.end,10);var s=end-Math.floor(Date.now()/1000);if(s<0)s=0;var h=Math.floor(s/3600),m=Math.floor((s%3600)/60),sec=s%60;el.textContent=(h>0?String(h).padStart(2,"0")+":"+String(m).padStart(2,"0"):String(m).padStart(2,"0"))+":"+String(sec).padStart(2,"0");});}updTimers();setInterval(updTimers,1000);</script>';

createlayout_bottom();
