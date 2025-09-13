<?php
define('IN_ZDE', 1);
$FILE_REQUIRES_PC = true;
include('ingame.php');

$bucks = number_format($pc['credits'], 0, ',', '.');

if (isset($_REQUEST['xpc'])) {
    $pci = $_REQUEST['xpc'];
    $a = explode(',', $usr['pcs']);
    $found = false;
    for ($i = 0; $i < count($a); $i++) {
        if ($a[$i] == $pci) {
            $found = true;
            break;
        }
    }
    if ($found == true) {
        $pcid = $pci;
        $pc = getpc($pcid);
        write_session_data();
    }
}

processupgrades($pc);
if ($pc['blocked'] > time()) {
    exit;
}

function format_duration($seconds)
{
    $seconds = (int)$seconds;
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    if ($h > 0) {
        return $m > 0 ? $h.' h '.$m.' min' : $h.' h';
    }
    return $m.' min';
}
function format_credits($n)
{
    return number_format((int)$n, 0, ',', '.').' Credits';
}
function dependency_badge($ok)
{
    return '<span class="badge muted">'.($ok ? 'Erf&uuml;llte Abh&auml;ngigkeit' : 'Abh&auml;ngigkeit fehlt').'</span>';
}

createlayout_top('ZeroDayEmpire - Dein Computer');

echo '<header class="page-head"><h1>Dein Computer</h1><a href="game.php?m=start&amp;sid='.$sid.'" class="btn ghost sm">Zur &Uuml;bersicht</a></header>';

$now = time();
$runningRows = [];
$r = db_query('SELECT * FROM `upgrades` WHERE `pc`=\''.mysql_escape_string($pcid).'\' AND `end`>\''.mysql_escape_string($now).'\' ORDER BY `start` ASC');
while ($row = mysql_fetch_assoc($r)) { $runningRows[] = $row; }
$running = count($runningRows);
$credits = (int)$pc['credits'];

$queueLabel = 'Kein Upgrade aktiv';
if ($running) {
    $tmppc = $pc;
    $first = $runningRows[0];
    $item = $first['item'];
    $newlv = itemnextlevel($item, $tmppc[$item]);
    $s1 = formatitemlevel($item, $tmppc[$item]);
    $s2 = formatitemlevel($item, $newlv);
    $timeLeft = $first['end'] - $now;
    $queueLabel = idtoname($item).' '.$s1.' &raquo; '.$s2.' <span class="cd" data-end="'.$first['end'].'">'.sprintf('%02d:%02d', floor($timeLeft/3600), floor(($timeLeft%3600)/60)).'</span> min';
}

echo '<div class="strip">';
echo '<div class="kpi kpi-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" width="50" height="50"><path d="M3 12h18M12 3v18" stroke="rgb(var(--accent))" stroke-width="2" fill="none"/></svg><div class="stat"><h3 class="value small">Verf&uuml;gbare Slots: '.$running.' / '.UPGRADE_QUEUE_LENGTH.'</h3></div></div>';
echo '<div class="kpi kpi-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" width="50" height="50"><path d="M4 4h16v12H4z" stroke="rgb(var(--accent))" stroke-width="2" fill="none"/><path d="M2 18h20" stroke="rgb(var(--accent))"/></svg><div class="stat"><h3 class="value small" id="kpiCredits" data-value="'.$credits.'">'.format_credits($credits).'</h3></div></div>';
echo '<div class="kpi kpi-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" width="50" height="50"><circle cx="12" cy="12" r="9" stroke="rgb(var(--accent))" stroke-width="2" fill="none"/><path d="M12 7v5l3 2" stroke="rgb(var(--accent))" stroke-width="2" fill="none"/></svg><div class="stat"><h3 class="value small">'.$queueLabel.'</h3></div></div>';
echo '</div>';

if ($running) {
    echo '<section class="card table-card" style="overflow:visible"><h2>Upgrade-Queue</h2><table style="width:100%"><thead><tr><th>Item</th><th>Level</th><th>Fertig in</th><th>Aktion</th></tr></thead><tbody>';
    $tmppc = $pc;
    foreach ($runningRows as $row) {
        $item = $row['item'];
        $newlv = itemnextlevel($item, $tmppc[$item]);
        $s1 = formatitemlevel($item, $tmppc[$item]);
        $s2 = formatitemlevel($item, $newlv);
        echo '<tr><td>'.idtoname($item).'</td><td>'.$s1.' &raquo; '.$s2.'</td><td><span class="cd" data-end="'.$row['end'].'"></span></td><td><a class="btn sm" href="game.php?page=cancelupgrade&amp;upgrade='.$row['id'].'&amp;sid='.$sid.'">Abbrechen</a></td></tr>';
        $tmppc[$item] = $newlv;
    }
    echo '</tbody></table><p>Wichtig: Das Geld von einem abgebrochenen Upgrade wird NICHT zur&uuml;ckerstattet, sondern ist verloren!</p></section>';
}

$SALT = file_get('data/upgr_SALT.dat');
$idparam = preg_replace('([./])', '', crypt('ZDEiTeM', $SALT));

echo '<section class="card table-card" id="upgradeTable" style="overflow:visible"><h2>Verf&uuml;gbare Upgrades</h2><table style="width:100%"><thead><tr><th scope="col" style="text-align:center">Item</th><th scope="col" style="text-align:center">Level</th><th scope="col" style="text-align:center">Dauer</th><th scope="col" style="text-align:center">Kosten</th><th scope="col" style="text-align:center">Status</th><th scope="col" style="text-align:center">Aktion</th></tr></thead><tbody>';
foreach ($items as $item) {
    $cur = $pc[$item];
    $max = itemmaxval($item);
    $curStr = formatitemlevel($item, $cur);
    $maxStr = formatitemlevel($item, $max);
    echo '<tr><td><strong>'.idtoname($item).'</strong></td><td>'.$curStr.' / '.$maxStr.'</td>';
    if ($cur >= $max) {
        echo '<td colspan="3">Max</td><td></td></tr>';
        continue;
    }
    $inf = getiteminfo($item, $cur);
    $timeStr = format_duration($inf['d'] * 60);
    $dep_ok = isavailb($item, $pc);
    $slotFree = ($running < UPGRADE_QUEUE_LENGTH);
    $creditOK = ($credits >= $inf['c']);
    echo '<td>'.$timeStr.'</td><td>'.format_credits($inf['c']).'</td>';
    echo '<td>'.dependency_badge($dep_ok).'</td>';
    $can = $dep_ok && $slotFree && $creditOK;
    $encrid = crypt($item, $SALT);
    if ($can) {
        echo '<td><a class="btn sm" href="game.php?m=upgrade&amp;'.$idparam.'='.$encrid.'&amp;sid='.$sid.'">Upgrade</a></td></tr>';
    } else {
        $tooltip = '';
        if (!$slotFree) { $tooltip = 'Alle Upgrade-Slots belegt'; }
        elseif (!$creditOK) { $tooltip = 'Zu wenig Credits'; }
        $btnHtml = '<span class="btn sm" style="background-color:#888;color:#ccc;" aria-disabled="true">Upgrade</span>';
        if ($tooltip) { $btnHtml = '<span class="tooltip" data-tooltip="'.$tooltip.'">'.$btnHtml.'</span>'; }
        echo '<td>'.$btnHtml.'</td></tr>';
    }
}
echo '</tbody></table></section>';

echo '<script>function updTimers(){document.querySelectorAll(".cd").forEach(function(el){var end=parseInt(el.dataset.end,10);if(!end)return;var s=end-Math.floor(Date.now()/1000);if(s<0)s=0;var h=Math.floor(s/3600),m=Math.floor((s%3600)/60),sec=s%60;el.textContent=(h>0?String(h).padStart(2,"0")+":"+String(m).padStart(2,"0"):String(m).padStart(2,"0"))+":"+String(sec).padStart(2,"0");});}updTimers();setInterval(updTimers,1000);</script>';

createlayout_bottom();
