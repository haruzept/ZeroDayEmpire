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

$inv = db_fetch_hardware_all($pc);
$research = db_fetch_research_all($pc);
$lan = (int)($inv['lan'] ?? 0);
$running = count_running($pc);
$maxSlots = current_parallel_slots($lan);

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

echo '<section class="card table-card" id="attackTable" style="overflow:visible"><h2>Verfügbare Angriffe</h2><table id="attackTableInner" style="width:100%"><thead><tr><th scope="col" style="text-align:center">Angriff</th><th scope="col" style="text-align:center">Angriffsdauer</th><th scope="col" style="text-align:center">Kosten</th><th scope="col" style="text-align:center">Verdienst</th><th scope="col" style="text-align:center">Risiko</th><th scope="col" style="text-align:center">Status</th><th scope="col" style="text-align:center">Aktion</th></tr></thead><tbody>';
foreach ($defs as $d) {
    $code = $d['code'];
    $state = get_attack_state($pc, $code);
    $params = calc_effective_params($d, $state, $inv, $research);
    $deps = check_dependencies($pc, $code, $state['level']);
    $dep_ok = $deps['ok'];
    $depTooltip = '';
    if (!$dep_ok) {
        $depTooltip = 'Benötigt: '.implode(', ', $deps['missing']);
        $depTooltip = htmlspecialchars($depTooltip, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    $cooldown = is_on_cooldown($pc, $code);
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
    echo '<tr><td class="tooltip" data-tooltip="'.$descrTooltip.'"><strong>'.htmlspecialchars($d['name']).'</strong></td><td>'.format_duration($params['duration_min']*60).'</td><td>'.format_cc($params['cost']).'</td><td>'.format_cc($params['payout_expected']).'</td><td>'.$params['risk_pct'].'%</td><td'.($depTooltip ? ' class="tooltip" data-tooltip="'.$depTooltip.'"' : '').'>'.dependency_badge($dep_ok).'</td><td>'.$button.'</td></tr>';
}
echo '</tbody></table></section>';

createlayout_bottom();
