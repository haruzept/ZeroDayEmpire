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

createlayout_top('ZeroDayEmpire - Dein Computer');
?>
<!-- ZDE theme inject -->
<style>@import url("style.css");</style>
<div class="container">
<?php // /ZDE theme inject start

echo '<div class="content" id="computer">' . "\n";
echo '<h2>Dein Computer</h2>' . "\n";
echo '<div class="submenu"><p><a href="game.php?m=start&amp;sid='.$sid.'">Zur &Uuml;bersicht</a></p></div>' . "\n";
echo '<div id="computer-upgrades">' . "\n";
echo $notif;

$r = db_query('SELECT * FROM `upgrades` WHERE `pc`=\''.mysql_escape_string($pcid).'\' AND `end`>\''.time().'\' ORDER BY `start` ASC;');
$full = @mysql_num_rows($r);
if ($full > 0) {
    $tmppc = $pc;
    echo '<h3>Upgrade-Queue</h3><p><strong>Es sind '.$full.' von '.UPGRADE_QUEUE_LENGTH.' Slots belegt</strong></p>' . "\n";
    echo '<table>' . "\n";
    while ($data = mysql_fetch_assoc($r)) {
        $item = $data['item'];
        $newlv = itemnextlevel($item, $tmppc[$item]);
        $s1 = formatitemlevel($item, $tmppc[$item]);
        $s2 = formatitemlevel($item, $newlv);
        echo '<tr><th>'.idtoname($item).'</th><td>'.$s1.' &raquo; '.$s2.'</td>';
        echo '<td>'.nicetime($data['end']).'</td>';
        echo '<td><a href="game.php?page=cancelupgrade&amp;upgrade='.$data['id'].'&amp;sid='.$sid.'">Abbrechen</a></td></tr>' . "\n";
        $tmppc[$item] = $newlv;
    }
    echo '</table>' . "\n";
    echo '<p>Wichtig: Das Geld von einem abgebrochenen Upgrade wird NICHT zur&uuml;ckerstattet, sondern ist verloren!</p>';
}

if ($full < UPGRADE_QUEUE_LENGTH) {
    if (isset($tmppc)) {
        $pc = $tmppc;
    }
    echo '<h3>Upgrade zur Queue hinzuf&uuml;gen</h3>';
    echo '<p><strong>Geld: '.$bucks.' Credits</strong></p>' . "\n";
    $SALT = file_get('data/upgr_SALT.dat');
    $idparam = preg_replace('([./])', '', crypt('ZDEiTeM', $SALT));
    function buildinfo($id)
    {
        global $STYLESHEET, $DATADIR, $pc, $bucks, $sid, $usrid, $pcid;
        global $r, $full, $SALT, $idparam;
        if (isavailb($id, $pc)) {
            $inf = getiteminfo($id, $pc[$id]);
            $m = intval($inf['d']);
            $xm = $m;
            if ($m >= 60) {
                $m = floor($m / 60).' h';
                if (floor($xm % 60) > 0) {
                    $m .= ' : '.floor($xm % 60).' min';
                }
            } else {
                $m .= ' min';
            }
            $xm *= 60;
            $lastend = ($full < 1 ? time() : mysql_result($r, $full - 1, 'end'));
            $xm += $lastend;
            $m .= '</td><td>'.nicetime2($xm, false, ' um ', ' Uhr');
            $name = idtoname($id);
            $val = $pc[$id];
            $sval = formatitemlevel($id, $val);
            $s = $name.' ('.$sval.')';
            echo '<tr>'.LF.'<td>';
            echo $s;
            echo '</td>' . "\n";
            echo '<td>'.$m.'</td><td>'.$inf['c'].' Credits</td>';
            echo '<td>';
            $encrid = crypt($id, $SALT);
            if ($pc['credits'] >= $inf['c']) {
                echo '<a href="game.php?m=upgrade&amp;'.$idparam.'='.$encrid.'&amp;sid='.$sid.'" class="buy">';
                if ($pc[$id] > 0 || $id == 'ram' || $id == 'cpu') {
                    $s = 'Upgrade kaufen';
                } else {
                    $s = 'Kaufen';
                }
                echo $s.'</a>';
            } else {
                echo 'Nicht gen&uuml;gend Geld';
            }
            echo '</td></tr>';
            return true;
        }
        return false;
    }
    echo '<table>' . "\n";
    echo '<tr>'.LF.'<th>Item</th>'.LF.'<th>Dauer</th>'.LF.'<th>Fertigstellung</th>'.LF.'<th>Kosten</th>'.LF.'<th>Upgrade</th>'.LF.'</tr>' . "\n";
    reset($items);
    $cnt = 0;
    foreach ($items as $dummy => $item) {
        if (buildinfo($item)) {
            $cnt++;
        }
    }
    echo '</table>';
}

echo "\n".'</div>'.LF.'</div>'."\n";
?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
