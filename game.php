<?php

define('IN_ZDE', 1);
$FILE_REQUIRES_PC = true;
include('ingame.php');

// Determine requested action while avoiding undefined index warnings
$action = $_REQUEST['page'] ?? '';
if ($action === '') {
    $action = $_REQUEST['mode'] ?? '';
}
if ($action === '') {
    $action = $_REQUEST['action'] ?? '';
}
if ($action === '') {
    $action = $_REQUEST['a'] ?? '';
}
if ($action === '') {
    $action = $_REQUEST['m'] ?? '';
}

$bucks = number_format($pc['credits'], 0, ',', '.');

switch ($action) {

    case 'start': // -------------------- START -----------------------

        /*if($usrid==1) {
            $r=db_query('SELECT id,buildstat FROM pcs WHERE buildstat!='';');
            while($data=mysql_fetch_assoc($r)):
              $a=explode('/', $data['buildstat']);
              db_query('INSERT INTO upgrades SET pc=\''.mysql_escape_string($data['id']).'\', end=\''.mysql_escape_string($a[0]).'\', item=\''.mysql_escape_string($a[1]).'\';');
            endwhile;
        }*/

        function infobox($titel, $class, $text, $param = 'class')
        {
            return '<div '.$param.'="'.$class.'">'.LF.'<h3>'.$titel.'</h3>'.LF.'<p>'.$text.'</p>'.LF.'</div>'."\n";
        }

        $info = "\n";

        if (($_GET['nlo'] ?? 0) == 1) {
            $info .= infobox(
                'ACHTUNG!!',
                'error',
                'Du hast dich bei deinem letzten Besuch nicht ausgeloggt! Das k&ouml;nnte zur Folge haben, dass dein Account in fremde H&auml;nde f&auml;llt. Au&szlig;erdem verf&auml;lscht es die Online/Offline-Anzeige! Benutz also bitte <em>immer</em> den Log Out-Button!'
            );
        }

        if ($server == 1) {
            #$info.=infobox('Hinweis','important','BLA BLA BLA');
        } else {

        }


#$info.=infobox('<b>ACHTUNG:</b> Multis keine Chance! Wer mehrere Accounts besitzt, wird gnadenlos gel&ouml;scht.');

        $SALT = file_get('data/upgr_SALT.dat');
        $idparam = preg_replace('([./])', '', crypt('ZDEiTeM', $SALT));
        function overview_upgrade_link($id)
        {
            global $pc, $sid, $SALT, $idparam;
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

                $finish = nicetime2(time() + ($xm * 60), false, ' um ', ' Uhr');

                $encrid = crypt($id, $SALT);
                $title = 'Kosten: '.$inf['c'].' Credits | Dauer: '.$m.' | Fertig: '.$finish;
                return ' <a href="game.php?m=upgrade&amp;'.$idparam.'='.$encrid.'&amp;sid='.$sid.'" title="'.htmlspecialchars($title).'">(Upgrade kaufen)</a>';
            }

            return '';
        }

        createlayout_top('ZeroDayEmpire - &Uuml;bersicht');
?>
<!-- ZDE theme inject -->
<style>@import url("style.css");
#computers li{position:relative}
#computers li .tip{display:none;position:absolute;top:100%;left:0;background:#222;color:#fff;padding:4px;border-radius:3px;max-width:240px;font-size:12px;z-index:10}
#computers li:hover .tip{display:block}
#computers li a:hover + .tip{display:none}
</style>
<div class="container">
<?php // /ZDE theme inject start



# Cluster-Mitgliedsbeitrag bezahlen:
        $cluster = getcluster($usr['cluster']);
        if ($cluster !== false && $usr['cm'] != strftime('%d.%m.')) {
            if ($cluster['tax'] > 0) {
                $pc['credits'] -= $cluster['tax'];
                if ($pc['credits'] > 0) {
                    db_query(
                        'UPDATE pcs SET credits='.mysql_escape_string(
                            $pc['credits']
                        ).' WHERE id=\''.mysql_escape_string($pcid).'\';'
                    );
                    $cluster['money'] += $cluster['tax'];
                    db_query(
                        'UPDATE clusters SET money='.mysql_escape_string(
                            $cluster['money']
                        ).' WHERE id=\''.mysql_escape_string($usr['cluster']).'\';'
                    );
                    $bucks = number_format($pc['credits'], 0, ',', '.');
                } else {
                    $info .= infobox(
                        'Fehler',
                        'important',
                        'Du hast auf deinem ersten PC 10.47.'.$pc['ip'].' ('.$pc['name'].') nicht mehr gen&uuml;gend Credits um den Cluster-Mitgliedsbeitrag von '.$cluster['tax'].' Credits zu bezahlen.'
                    );
                    # hmmm doppelte ID 'important'
                }
            }
            $usr['cm'] = strftime('%d.%m.');
            db_query(
                'UPDATE users SET cm=\''.mysql_escape_string($usr['cm']).'\' WHERE id=\''.mysql_escape_string(
                    $usrid
                ).'\''
            );
        }

# Anzahl neuer Mails festellen + updaten
        $newtotal = 0;
        if ($usr['newmail'] > 0) {
            $newmail = @mysql_num_rows(
                db_query(
                    'SELECT * FROM mails WHERE user=\''.mysql_escape_string($usrid).'\' AND box=\'in\' AND xread=\'no\''
                )
            );
            $newsys = @mysql_num_rows(
                db_query('SELECT * FROM sysmsgs WHERE user=\''.mysql_escape_string($usrid).'\' AND xread=\'no\'')
            );
            $newtotal = $newmail + $newsys;
            if ($newtotal != $usr['newmail']) {
                $usr['newmail'] = $newtotal;
                db_query(
                    'UPDATE users SET newmail=\''.mysql_escape_string($newtotal).'\' WHERE id=\''.mysql_escape_string(
                        $usrid
                    ).'\';'
                );
            }
        }

        $r = db_query('SELECT * FROM mails WHERE user='.mysql_escape_string($usrid).' AND box=\'in\'');
        $cnt = mysql_num_rows($r);
        if ($cnt >= getmaxmails('in')) {
            $info .= infobox(
                'WARNUNG',
                'error',
                'Dein Posteingang ist voll! Solange du keine Mails l&ouml;schst oder verschiebst, k&ouml;nnen dir keine anderen Nutzer mehr Ingame-Mails schicken!'
            );
        }

        $da = false;
        $sql = db_query('SELECT * FROM pcs WHERE owner='.mysql_escape_string($usr['id']).';');
        while ($x = mysql_fetch_assoc($sql)) {
            if ($x['points'] < 1024) {
                processupgrades($x);
            }
            if ($da !== true) {
                $tmp = isavailh('da', $x);
                if ($tmp) {
                    $da = true;
                }
            }
        }
        setuserval('da_avail', ($da == true ? 'yes' : 'no'));
        echo $notif.$info;
        ?>

<section class="features" aria-label="Dashboard">
  <article class="card span-6">
    <h3 class="tight">Willkommen, <?php echo safeentities($usr['name']); ?></h3>
    <p class="muted">Dein Syndikat wartet auf Befehle.</p>
  </article>

  <article class="card span-6">
    <h3>&Uuml;bersicht</h3>
    <div class="strip" style="margin-top:10px; grid-template-columns:repeat(auto-fit,minmax(140px,1fr))">
      <div class="kpi"><div class="label"><strong>Guthaben:</strong></div><div><span><?php echo $bucks; ?></span> <span class="unit">CR</span></div></div>
      <div class="kpi"><div class="label"><strong>Punkte:</strong></div><div><span><?php echo $usr['points']; ?></span> <span class="unit">Punkte</span></div></div>
    </div>
  </article>

  <article class="card span-6" id="computers">
      <h3>Computer</h3>
      <ul class="muted" style="list-style:none; padding-left:0; margin:10px 0 0 0">
        <li><a href="game.php?m=pc&amp;sid=<?php echo $sid; ?>"><strong><?php echo safeentities($pc['name']); ?></strong> (10.47.<?php echo $pc['ip']; ?>)</a></li>
        <li data-item="cpu"><?php echo idtoname('cpu'); ?>: <?php echo formatitemlevel('cpu',$pc['cpu']); ?><?php echo overview_upgrade_link('cpu'); ?><div class="tip"></div></li>
        <li data-item="ram"><?php echo idtoname('ram'); ?>: <?php echo formatitemlevel('ram',$pc['ram']); ?><?php echo overview_upgrade_link('ram'); ?><div class="tip"></div></li>
        <li data-item="lan"><?php echo idtoname('lan'); ?>: Level <?php echo $pc['lan']; ?><?php echo overview_upgrade_link('lan'); ?><div class="tip"></div></li>
      </ul>
    </article>

  <article class="card span-6" id="software">
      <h3>Software</h3>
      <ul class="muted" style="list-style:none; padding-left:0; margin:10px 0 0 0">
        <li data-item="mm"><?php echo idtoname('mm'); ?>: Version <?php echo $pc['mm']; ?><?php echo overview_upgrade_link('mm'); ?><div class="tip"></div></li>
        <li data-item="bb"><?php echo idtoname('bb'); ?>: Version <?php echo $pc['bb']; ?><?php echo overview_upgrade_link('bb'); ?><div class="tip"></div></li>
        <li><?php echo idtoname('fw'); ?>: Version <?php echo $pc['fw']; ?></li>
        <li><?php echo idtoname('av'); ?>: Version <?php echo $pc['av']; ?></li>
        <li><?php echo idtoname('ids'); ?>: Level <?php echo $pc['ids']; ?></li>
      </ul>
    </article>

  <article class="card span-6" id="upgradequeue">
    <h3>Upgrade-Queue</h3>
    <?php
      $r = db_query('SELECT * FROM `upgrades` WHERE `pc`=\''.mysql_escape_string($pcid).'\' AND `end`>\''.time().'\' ORDER BY `start` ASC;');
      $full = @mysql_num_rows($r);
      echo '<p><strong>Es sind '.$full.' von '.UPGRADE_QUEUE_LENGTH.' Slots belegt</strong></p>';
      if ($full > 0) {
          $tmppc = $pc;
          echo '<ul class="muted" style="list-style:none; padding-left:0; margin:10px 0 0 0">';
          while ($data = mysql_fetch_assoc($r)) {
              $item = $data['item'];
              $newlv = itemnextlevel($item, $tmppc[$item]);
              $s1 = formatitemlevel($item, $tmppc[$item]);
              $s2 = formatitemlevel($item, $newlv);
              echo '<li>'.idtoname($item).' '.$s1.' &raquo; '.$s2.' '.nicetime($data['end']).'</li>';
              $tmppc[$item] = $newlv;
          }
          echo '</ul>';
      } else {
          echo '<p class="muted">Keine Upgrades geplant.</p>';
      }
    ?>
  </article>

  <article class="card span-6" id="researchqueue">
    <h3>Forschungs-Queue</h3>
    <?php
      $r = db_query('SELECT * FROM `research` WHERE `pc`=\''.mysql_escape_string($pcid).'\' AND `end`>\''.time().'\' ORDER BY `start` ASC;');
      $running = @mysql_num_rows($r);
      $maxSlots = isset($pc['research_slots']) ? (int)$pc['research_slots'] : 1;
      echo '<p><strong>Es sind '.$running.' von '.$maxSlots.' Slots belegt</strong></p>';
      if ($running > 0) {
          $tracks = research_get_tracks();
          echo '<ul class="muted" style="list-style:none; padding-left:0; margin:10px 0 0 0">';
          while ($data = mysql_fetch_assoc($r)) {
              $track = $data['track'];
              $info = $tracks[$track] ?? null;
              $name = safeentities($info['name'] ?? $track);
              $curLvl = $data['target_level'] - 1;
              $maxLvl = $info['max_level'] ?? $data['target_level'];
              $s1 = $curLvl.'/'.$maxLvl;
              $s2 = $data['target_level'].'/'.$maxLvl;
              echo '<li>'.$name.' '.$s1.' &raquo; '.$s2.' '.nicetime($data['end']).'</li>';
          }
          echo '</ul>';
      } else {
          echo '<p class="muted">Keine Forschung in Arbeit.</p>';
      }
    ?>
  </article>

  <article class="card span-6" id="cluster">
    <h3>Cluster</h3>
    <?php if ($cluster !== false) { ?>
      <ul class="muted" style="list-style:none; padding-left:0; margin:10px 0 0 0">
        <li><strong><?php echo safeentities($cluster['name']); ?></strong></li>
        <li>Punkte: <?php echo number_format($cluster['points'],0,',','.'); ?></li>
        <li>Mitglieder: <?php echo $cluster['members'] ?? 0; ?></li>
        <li>Geld: <?php echo number_format($cluster['money'],0,',','.'); ?> CR</li>
      </ul>
    <?php } else { ?>
      <p class="muted">Du bist in keinem Cluster.</p>
    <?php } ?>
  </article>
</section>

<script>
document.querySelectorAll('#computers li[data-item], #software li[data-item]').forEach(li => {
  li.addEventListener('mouseenter', async () => {
    if (li.dataset.loaded) return;
    const item = li.getAttribute('data-item');
    const tip = li.querySelector('.tip');
    try {
      const res = await fetch(`game.php?m=item&item=${item}&sid=<?php echo $sid; ?>`);
      const html = await res.text();
      const div = document.createElement('div');
      div.innerHTML = html;
      const content = div.querySelector('#computer-item');
      let text = '';
      if (content) {
        const ps = content.querySelectorAll('p');
        text = ps.length >= 2 ? ps[1].textContent.trim() : ps.length === 1 ? ps[0].textContent.trim() : '';
      }
      tip.textContent = text;
    } catch (e) {
      tip.textContent = '';
    }
    li.dataset.loaded = '1';
  });
});
</script>

<?php
        if ($newtotal > 0) {
            echo '<div id="overview-messages">'."\n";
            echo '<h3>Messages</h3>'."\n";
            echo '<p>Du hast <strong>'.$newtotal.' ungelesene Nachricht'.($newtotal == 1 ? '' : 'en').'</strong>.</p>'."\n";
            echo '<p><a href="mail.php?m=start&amp;sid='.$sid.'">Gehe zu den Nachrichten</a></p>'."\n";
            echo '</div>';
        }
        ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();

        break;

    case 'pc': // ---------------------------- PC -------------------------------

        processupgrades($pc);

        createlayout_top('ZeroDayEmpire - Deine Computer');
        echo '<header class="page-head"><h1>Dein Computer</h1></header>';

        if ($pc['blocked'] > time()) {
            echo '<section class="card"><h2>Fehler</h2><p>Dieser PC ist blockiert bis '.nicetime2($pc['blocked'], true).'!</p></section>';
            createlayout_bottom();
            exit;
        }

        $attackable_bool = is_pc_attackable($pc) && is_noranKINGuser($usrid) == false;
        $attackable = $attackable_bool ? 'ja' : 'nein';
        $attack_title = $attackable_bool ? 'Dieser PC kann angegriffen werden' : 'Dieser PC kann nicht angegriffen werden';
        $attack_icon = '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true" width="50" height="50"><path d="M12 2l8 4v5c0 5-3 9-8 10-5-1-8-5-8-10V6l8-4z" fill="none" stroke="rgb(var(--accent))" stroke-width="2"/><path d="M14 3l7 7-1.5 1.5-2-2-4.5 4.5V20l-2 2-2-2 2-2v-4.5l4.5-4.5-2-2z" fill="none" stroke="rgb(var(--accent))" stroke-width="2"/></svg>';
        echo '<div class="strip">';
        echo '<div class="kpi kpi-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" width="50" height="50"><path d="M3 12h18M12 3v18" stroke="rgb(var(--accent))" stroke-width="2" fill="none"/></svg><div class="stat"><h3 class="value small">Punkte: '.$pc['points'].'</h3></div></div>';
        echo '<div class="kpi kpi-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" width="50" height="50"><path d="M4 4h16v12H4z" stroke="rgb(var(--accent))" stroke-width="2" fill="none"/><path d="M2 18h20" stroke="rgb(var(--accent))"/></svg><div class="stat"><h3 class="value small">'.$bucks.' Credits</h3></div></div>';
        echo '<div class="kpi kpi-icon" title="'.$attack_title.'">'.$attack_icon.'<div class="stat"><h3 class="value small">Angreifbar: '.$attackable.'</h3></div></div>';
        echo '</div>';

        function showinfo($id, $txt, $val = -1)
        {
            global $pc, $sid;
            if ($val == -1) {
                $val = $pc[$id];
            }
            $name = idtoname($id);
            $disp = formatitemlevel($id, $val);
            if ($disp && $disp != '0.0') {
                $info_text = htmlspecialchars(strip_tags(file_get('data/info/'.$id.'.txt')));
                $upgrade = isavailb($id, $pc) === true ? ' (Upgrade verf&uuml;gbar)' : '';
                echo '<tr><th><a href="game.php?m=item&amp;item='.$id.'&amp;sid='.$sid.'" title="'.$info_text.'">'.$name.'</a>:</th><td>';
                if ($txt != '') {
                    echo str_replace('%v', $disp, $txt).$upgrade;
                }
                echo '</td></tr>';
            }
        }

        $rhinfo = '';
        if ($pc['mk'] > 0 && $pc['rh'] > 0) {
            $rhinfo = '<tr><th>Remote Hijack</th><td>';
            $lastRemote = (int)$pc['lrh'] + REMOTE_HIJACK_DELAY;
            if ($lastRemote <= time()) {
                $rhinfo .= '<span style="color:green;">sofort verf&uuml;gbar</span>';
            } else {
                $rhinfo .= nicetime($lastRemote);
            }
            $rhinfo .= '</td></tr>';
        }

        $pc['name'] = safeentities($pc['name']);

        if (isset($notif)) {
            echo $notif;
        }

        echo '<div class="strip" style="grid-template-columns:repeat(2,1fr);text-align:left">';

        echo '<section class="card" id="computer-properties">';
        echo '<h2>Eigenschaften</h2>';
        echo '<p><a href="game.php?a=renamepclist&amp;sid='.$sid.'">Computer umbenennen</a></p>';
        echo '<table>';
        echo '<tr><th>Name:</th><td>'.$pc['name'].'</td></tr>';
        echo '<tr><th>IP:</th><td>10.47.'.$pc['ip'].'</td></tr>';
        echo '<tr><th>Punkte:</th><td>'.$pc['points'].'</td></tr>';
        echo '<tr><th>Geld:</th><td>'.$bucks.' Credits</td></tr>';
        $attackable = is_pc_attackable($pc) && is_noranKINGuser($usrid) == false;
        echo '<tr><th>Angreifbar:</th><td>'.($attackable ? 'ja' : 'nein').'</td></tr>';
        echo $rhinfo;
        echo '</table>';
        echo '</section>';

        echo '<section class="card" id="computer-essentials"><h2>Essentials</h2><table>';
        showinfo('cpu', '%v');
        showinfo('ram', '%v MB RAM');
        showinfo('lan', 'Level %v');
        showinfo('mm', 'Version %v');
        showinfo('bb', 'Version %v');
        echo '</table></section>';

        echo '<section class="card" id="computer-software"><h2>Software</h2><table>';
        showinfo('sdk', 'Version %v');
        showinfo('mk', 'Version %v');
        showinfo('ips', 'Level %v');
        echo '</table></section>';

        echo '<section class="card" id="computer-security"><h2>Sicherheit</h2><table>';
        showinfo('fw', 'Version %v');
        showinfo('av', 'Version %v');
        showinfo('ids', 'Level %v');
        echo '</table></section>';

        echo '<section class="card" id="computer-attack"><h2>Angriff</h2><table>';
        showinfo('trojan', 'Level %v');
        showinfo('rh', 'Level %v');
        if (isavailh('da', $pc) === true) {
            showinfo('da', 'Level %v', 1);
        }
        echo '</table></section>';

        echo '</div>';

        createlayout_bottom();
        break;

    case 'item': // ----------------------------------- ITEM --------------------------------
        if ($pc['blocked'] > time()) {
            exit;
        }

        $item = $_REQUEST['item'];
        $pcItemValue = $pc[$item] ?? null;
        if ($pcItemValue === null || (isavailh($item, $pc) != true && $pcItemValue < 1)) {
            http_response_code(404);
            createlayout_top('ZeroDayEmpire - Deine Computer');
?>
<!-- ZDE theme inject -->
<style>@import url("style.css");</style>
<div class="container">
<?php // /ZDE theme inject start


            echo '<div class="content" id="computer">'.LF.'<h2>Deine Computer</h2>'.LF.'<div class="error">'.LF.'<h3>Fehler</h3>'.LF.'<p>Dieses Item wurde nicht gefunden.</p>'.LF.'</div>'.LF.'</div>'."\n";
            ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
            break;
        }
        createlayout_top('ZeroDayEmpire - Deine Computer');
?>
<!-- ZDE theme inject -->
<style>@import url("style.css");</style>
<div class="container">
<?php // /ZDE theme inject start


        $val = formatitemlevel($item, $pcItemValue);

        $cssid = 'essential';
        if ($item == 'sdk' || $item == 'mk' || $item == 'ips') {
            $cssid = 'software';
        } elseif ($item == 'fw' || $item == 'av' || $item == 'ids') {
            $cssid = 'security';
        } elseif ($item == 'trojan' || $item == 'da' || $item == 'rh') {
            $cssid = 'attack';
        }

        echo '<div class="content" id="computer"><h2>Deine Computer</h2><div id="computer-item">';
        echo '<h3 id="computer-item-'.$cssid.'">'.idtoname($item).' '.$val.'</h3>';
        echo '<p><strong>Geld: '.$bucks.' Credits</strong></p><br />';
        echo '<p>'.file_get('data/info/'.$item.'.txt').'</p>'."\n";

        switch ($item) {
            case 'mm':
                if (isset($_REQUEST['purchased']) && $_REQUEST['purchased'] == 1) {
                    echo '<div id="ok"><h3>Update</h3><p>Update wurde angewendet!</p></div><br /><br />';
                }
                echo '<table>'."\n";
                echo '<tr>'.LF.'<th>Item</th>'.LF.'<th>Status</th>'.LF.'<th>Ertrag</th>'.LF.'<th>Update</th>'.LF.'</tr>'."\n";
                function mmitem($name, $id, $av, $f)
                {
                    global $STYLESHEET, $REMOTE_FILES_DIR, $DATADIR, $pc, $bucks, $pcid, $usrid, $sid;
                    $v = (float)$pc['mm'];
                    if ($v >= $av) {
                        echo '<tr class="name">'.LF.'<td>'.$name.'</td>'.LF.'<td class="level">Level '.$pc[$id].'</td>'.LF.'<td class="profit">'.calc_mph(
                                $pc[$id],
                                $f
                            ).' Credits/h</td>'.LF.'<td>';
                        if ($pc[$id] < 5) {
                            $c = (((int)$pc[$id] + 1) * 15 * $f);
                            if ($pc['credits'] - $c >= 0) {
                                echo '<a href="game.php?mode=update&item='.$id.'&sid='.$sid.'">Update</a>';
                            } else {
                                echo 'Update';
                            }
                            echo ' kostet '.$c.' Credits';
                        } else {
                            echo 'Kein Update mehr m&ouml;glich!';
                        }
                        echo '</td>'.LF.'</tr>'."\n";
                    }
                }

                mmitem('Online-Werbung', 'ads', 1, DPH_ADS);
                mmitem('0900-Dialer', 'dialer', 4, DPH_DIALER);
                mmitem('Auktionsbetrug', 'auctions', 8, DPH_AUCTIONS);
                mmitem('Online-Banking-Hack', 'bankhack', 10, DPH_BANKHACK);
                echo '</table>'."\n";
                echo '</div>'."\n";
                echo '<div id="computer-profit">'."\n";
                echo '<h3>Einkommen</h3>'."\n";
                echo '<p>'.get_gdph().' Credits/Stunde<br />'.number_format(
                        (get_gdph() / 60),
                        1,
                        ',',
                        '.'
                    ).' Credits/Minute<br />'.number_format((get_gdph() * 24), 0, ',', '.').' Credits/Tag</p>';
                break;

            case 'bb':
                echo '<p>Lagerkapazit&auml;t:</b> '.number_format(getmaxbb(), 0, ',', '.').' Credits</p>'."\n";
                break;

            case 'mk':

                $v = $pc['mk'];
                echo '</div>
<div id="computer-weapons">
<h3>Zur Verf&uuml;gung stehende Waffen:</h3>
<table>
';
                if (isavailh('scan', $pc)) {
                    echo '<tr>'.LF.'<th>Remote Scan:</th>'.LF.'<td>Spioniert fremde Rechner aus.</td>'.LF.'</tr>'."\n";
                }
                if (isavailh('trojan', $pc)) {
                    echo '<tr>'.LF.'<th>Trojaner:</th>'.LF.'<td>Sabotiert fremde Computer.</td>'.LF.'</tr>'."\n";
                }
                if (isavailh('smash', $pc)) {
                    echo '<tr>'.LF.'<th>Remote Smash:</th>'.LF.'<td>Zerst&ouml;rt Prozessor, Firewall oder SDK von fremden Rechnern.</td>'.LF.'</tr>'."\n";
                }
                if (isavailh('block', $pc)) {
                    echo '<tr>'.LF.'<th>Remote Block:</th>'.LF.'<td>Blockiert Computer f&uuml;r dessen Besitzer.</td>'.LF.'</tr>'."\n";
                }
                if (isavailh('rh', $pc)) {
                    echo '<tr>'.LF.'<th>Remote Hijack:</th>'.LF.'<td>Versucht, den feinlichen Rechner zu klauen.</td>'.LF.'</tr>'."\n";
                }

                echo '</table>'.LF.'<p>Die Waffen werden vom <a href="battle.php?m=opc&amp;sid='.$sid.'">Operation Center</a> aus eingesetzt.</p>';
                break;

            case 'trojan':

                $v = $pc['mk'];
                echo '<p><strong>Zur Verf&uuml;gung stehende Angriffs-M&ouml;glichkeiten:</strong></p><br /><dl>';

                if (tisavail('defacement', $pc)) {
                    echo '<dt>Defacement</dt><dd>&Auml;ndert die Beschreibung des Gegners.</dd>';
                }
                if (tisavail('transfer', $pc)) {
                    echo '<dt>Transfer</dt><dd>Klaut Geld.</dd>';
                }
                if (tisavail('deactivate', $pc)) {
                    echo '<dt>Deactivate</dt><dd>Deaktiviert Firewall, Antivirus oder IDS auf gegner. PC.</dd>';
                }

                echo '</dl><br /><p>Der Trojaner wird vom <a href="battle.php?m=opc&sid='.$sid.'">Operation Center</a> aus eingesetzt.</p>';
                break;
        }
        echo '</div>'.LF.'</div>'."\n";
        ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
        break;

    case 'update': // -------------------------------- UPDATE --------------------------------
        if ($pc['blocked'] > time()) {
            exit;
        }

        $id = $_REQUEST['item'];

        function updredir($x = '')
        {
            global $STYLESHEET, $REMOTE_FILES_DIR, $DATADIR, $pcid, $usrid, $sid;
            header('Location: game.php?mode=item&item=mm&sid='.$sid.$x);
        }

        switch ($id) {
            case 'ads':
                $f = DPH_ADS;
                break;
            case 'auctions':
                $f = DPH_AUCTIONS;
                if ($pc['mm'] < 8) {
                    exit;
                }
                break;
            case 'dialer':
                $f = DPH_DIALER;
                if ($pc['mm'] < 4) {
                    exit;
                }
                break;
            case 'bankhack':
                $f = DPH_BANKHACK;
                if ($pc['mm'] < 10) {
                    exit;
                }
                break;
            default:
                simple_message('Dieser Bug ist weg!');
                exit;
        }

        if ($pc[$id] < 5) {
            $c = (((int)$pc[$id] + 1) * 15 * $f);
            if ($pc['credits'] - $c >= 0) {
                $pc[$id] += 1;
                $pc['credits'] -= $c;
                db_query(
                    'UPDATE pcs SET '.mysql_escape_string($id).'='.mysql_escape_string(
                        $pc[$id]
                    ).', credits='.mysql_escape_string($pc['credits']).' WHERE id=\''.mysql_escape_string($pcid).'\''
                );
                updredir('&purchased=1');
            } else {
                updredir();
            }
        } else {
            updredir();
        }

        break;

    case 'upgrade': // -------------------------------- UPGRADE --------------------------------
        processupgrades($pc);
        if ($pc['blocked'] > time()) {
            exit;
        }

        $SALT = file_get('data/upgr_SALT.dat');
        $idparam = preg_replace('([./])', '', crypt('ZDEiTeM', $SALT));

        $id = $_REQUEST[$idparam];
        foreach ($items as $xx) {
            if (crypt($xx, $SALT) == $id) {
                $id = $xx;
                break;
            }
        }

// Eine Lockfile sicher erzeugen, sodass das Anlegen nicht parallel geschieht.
// Sollte die Lockfile nicht angelegt werden können, wird das selbe bis zu zehn
// Mal weiter probiert, sollte es dann noch fehlschlagen, wird das Script
// beendet
        $uniqueFileName = $sidfile.getmypid().microtime();
        $lockfp = fopen($uniqueFileName, 'w+');
        if (!fclose($lockfp)) {
            die('Konnte Upgradeanfragen nicht serialisieren: Sperrdatei konnte nicht vorbereitet werden.');
        }
        $i = 0;
        while (!@rename($uniqueFileName, $sidfile.'.upgradelock')) {
            usleep(mt_rand(100, 2000));
            if ($i++ > 100) {
                die('Konnte Upgradeanfragen nicht serialisieren: Zeitueberschreitung beim Anlegen der Sperrdatei.');
            }
        }

        $tmppc = $pc;
        $r1 = db_query(
            'SELECT * FROM `upgrades` WHERE `pc`=\''.mysql_escape_string($pcid).'\' AND `end`>\''.time(
            ).'\' ORDER BY start ASC;'
        );
        $cnt1 = mysql_num_rows($r1);
        while ($data = mysql_fetch_assoc($r1)) {
            $item = $data['item'];
            $tmppc[$item] = itemnextlevel($item, $tmppc[$item]);
        }

        if (isavailb($id, $tmppc) === true) {
            $inf = getiteminfo($id, $tmppc[$id]);


            $r2 = db_query(
                'SELECT * FROM `upgrades` WHERE `pc`=\''.mysql_escape_string(
                    $pcid
                ).'\' AND `item`=\''.mysql_escape_string($id).'\' AND `end`>\''.time().'\';'
            );
            $itemcnt = mysql_num_rows($r2) / 2;
            if ($cnt1 < UPGRADE_QUEUE_LENGTH && ($pc[$id] + $itemcnt) < itemmaxval($id)) {
                if ($pc['credits'] >= $inf['c']) {
                    $pc['credits'] -= $inf['c'];

                    $lastend = ($cnt1 < 1 ? time() : mysql_result($r1, $cnt1 - 1, 'end'));
                    $ftime = $lastend + (int)($inf['d'] * 60);
                    db_query(
                        'UPDATE `pcs` SET `credits`=`credits`-'.mysql_escape_string(
                            $inf['c']
                        ).' WHERE `id`=\''.mysql_escape_string($pcid).'\''
                    );
                    db_query(
                        'INSERT INTO `upgrades` SET `pc`=\''.mysql_escape_string($pcid).'\', `start`=\''.time(
                        ).'\', `end`=\''.mysql_escape_string($ftime).'\', `item`=\''.mysql_escape_string($id).'\';'
                    );

                    header(
                        'Location: upgradelist.php?ok='.urlencode(
                            'Upgrade f&uuml;r '.idtoname($id).' l&auml;uft bis '.nicetime($ftime)
                        ).'&sid='.$sid
                    );

                    /*createlayout_top('ZeroDayEmpire - Dein Computer - Upgrade');
?>
<!-- ZDE theme inject -->
<style>@import url("style.css");</style>
<div class="container">
<?php // /ZDE theme inject start


                    echo '<div id="computer" class="content">'."\n";
                    echo '<h2>Dein Computer</h2>'."\n";
                    echo '<div id="computer-upgrade-done">';
                    echo '<h3>Upgrade l&auml;uft!</h3>';
                    echo '<p><img src="images/pbar.gif"><br /><br /><strong>Fertigstellung:</strong>&nbsp;';
                    echo nicetime($ftime);
                    echo '</p><br /><p><a href="upgradelist.php?sid='.$sid.'">Zur&uuml;ck zum Upgrademen&uuml;</a></p>';
                    echo '</div></div>';
                    ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();*/

                } else {
                    header(
                        'Location: upgradelist.php?error='.urlencode('Nicht gen&uuml;gend Geld').'&sid='.$sid
                    );
                }
            } else {
                header('Location: upgradelist.php?sid='.$sid);
            }
        } else {
            header('Location: upgradelist.php?sid='.$sid);
        }

// Die oben angelegt Lockfile wieder löschen und damit anderen Upgrade-Anfragen
// Platz machen
        if (!unlink($sidfile.'.upgradelock')) {
            die('Konnte Upgradeanfragen nicht serialisieren: Sperrdatei konnte nicht gelöscht werden. Die Anfrage wurde jedoch bearbeitet.');
        }

        break;

    case 'cancelupgrade':  // -------------------------------- CANCEL UPGRADE --------------------------------

        $u = (int)$_REQUEST['upgrade'];
        $r = db_query(
            'SELECT id FROM upgrades WHERE pc=\''.mysql_escape_string($pcid).'\' AND id=\''.mysql_escape_string(
                $u
            ).'\' LIMIT 1;'
        );
        if (mysql_num_rows($r) == 1) {
            db_query(
                'DELETE FROM upgrades WHERE pc=\''.mysql_escape_string($pcid).'\' AND id=\''.mysql_escape_string(
                    $u
                ).'\' LIMIT 1;'
            );
            header('Location: upgradelist.php?sid='.$sid);
        }

        break;

    case 'selpc': // -------------------------------- Select PC --------------------------------
        $id = (int)$_REQUEST['pcid'];

        $pc = getpc($id);
        if ($pc['owner'] == $usrid) {
            $pcid = $id;
            write_session_data();
            header('Location: game.php?m=pc&sid='.$sid);
        }
        break;

    case 'pcs': // -------------------------------- PCs --------------------------------

        if ($usr['pcview_ext'] == 'yes') {
            $ext = true;
        } else {
            $ext = false;
        }
        if (isset($_REQUEST['extended'])) {
            $ext = ((int)$_REQUEST['extended'] == 1);
        }
        setuserval('pcview_ext', ($ext ? 'yes' : 'no'));

        $extv = (int)(!$ext);
        $extt = ($ext ? 'kompakte Ansicht' : 'erweiterte Ansicht');

        createlayout_top('Deine Computer');
?>
<!-- ZDE theme inject -->
<style>@import url("style.css");</style>
<div class="container">
<?php // /ZDE theme inject start



        echo '<div class="content" id="computer">
<h2>Deine Computer</h2>
<div class="submenu">
<p><a href="game.php?a=renamepclist&amp;sid='.$sid.'">Computer umbenennen</a></p>
</div>
<div id="computer-list">
<h3>Liste aller Computer</h3>
<form action="game.php?page=pcs&amp;sid='.$sid.'" method="post">
<p>Ordnen nach: <select name="sorttype" onchange="this.form.submit()"><option value="">Nicht ordnen</option>
<option value="name ASC">Name</option>
<option value="points ASC">Punkte</option>
<option value="country ASC">Land</option>
<option value="lrh ASC">Hijack (fehlerhaft)</option>
</select>
<a href="game.php?page=pcs&amp;sid='.$sid.'&amp;extended='.$extv.'">'.$extt.'</a>
</p>
<table>
<tr>
<th class="number">Nummer</th>
<th class="name">Computername</th>
<th class="ip">IP-Adresse</th>
<th class="points">Punkte</th>
<th class="credits">Geld</th>';
        if ($ext) {
            echo '<th class="upgrade">Upgrade-Status</th>
<th class="attack">Angriff</th>';
        }

        echo '<th class="hijack">Hijack?</th>'.LF.'</tr>'."\n";


         $st = $_POST['sorttype'] ?? '';
        switch ($st) {
            case 'name ASC':
                break;
            case 'points ASC':
                break;
            case 'country ASC':
                break;
            case 'lrh ASC':
                break;
            default:
                $st = '';
        }
        $ord = '';
        if ($st != '') {
            $ord = ' ORDER BY '.$st;
        }

#$list='';
        $tcreds = 0;
        if ($ext) {
            $sql = db_query('SELECT * FROM pcs WHERE owner='.mysql_escape_string($usr['id']).$ord.';');
        } else {
            $sql = db_query(
                'SELECT id,name,ip,country,points,credits,rh,lrh FROM pcs WHERE owner='.mysql_escape_string(
                    $usr['id']
                ).$ord.';'
            );
        }
         $number = 0;
         while ($x = mysql_fetch_assoc($sql)) {
             #$list.=$x['id'].',';
             $number++;
            $country = GetCountry('id', $x['country']);
            $x['points'] = (int)$x['points'];
            if ($x['points'] < 1024 && $ext) {
                processupgrades($x);
                $r = db_query(
                    'SELECT end,item FROM `upgrades` WHERE pc=\''.mysql_escape_string(
                        $x['id']
                    ).'\' ORDER BY `start` ASC;'
                );
                $cnt = (int)@mysql_num_rows($r);
                if ($cnt == 0) {
                    $stat = '<span style="color:red;">Kein Upgrade am Laufen</span>';
                } else {
                    $stat = '<span style="color:green;">'.$cnt.' Upgrade'.($cnt > 1 ? 's laufen' : ' l&auml;uft').' bis '.nicetime2(
                            mysql_result($r, $cnt - 1, 'end'),
                            true
                        ).'</span>';
                }
            } else {
                $stat = '-';
            }
            $tcreds += $x['credits'];
            $bucks = number_format($x['credits'], 0, ',', '.');
            $x['name'] = safeentities($x['name']);

            if (isavailh('rh', $x) === true) {
                if ($x['lrh'] + REMOTE_HIJACK_DELAY <= time()) {
                    $hijack = '<span style="color:green">verfügbar</span>';
                } else {
                    $hijack = '<span style="color:red">'.nicetime($x['lrh'] + REMOTE_HIJACK_DELAY).'</span>';
                }
            } else {
                $hijack = '<span style="color:black">nicht ausgebaut</span>';
            }

            $mmstat = '';
            if ($ext) {
                if (($x['mm'] >= 1 && $x['ads'] < 5) ||
                    ($x['mm'] >= 4 && $x['dialer'] < 5) ||
                    ($x['mm'] >= 8 && $x['auctions'] < 5) ||
                    ($x['mm'] >= 10 && $x['bankhack'] < 5)
                ) {
                    $mmstat = '<br /><span style="color:red">MoneyMarket-Update verf&uuml;gbar!</span>';
                }
            }

            {
                $pc = $x;
                $avail = isavailh('scan', $x);
                // initialise variables before passing by reference
                $next = 0;
                $last = 0;
                if (!isattackallowed($next, $last) && $avail) {
                    $attack = 'nein, erst wieder '.nicetime3($next);
                } elseif ($avail) {
                    $attack = '<span style="color:green">m&ouml;glich</span>';
                } else {
                    $attack = '-';
                }
            }


            echo '<tr>
<td class="number">'.$number.'</td>
<td class="name"><a href="game.php?m=selpc&amp;sid='.$sid.'&amp;pcid='.$x['id'].'">'.$x['name'].'</a>'.$mmstat.'</td>
<td class="ip">10.47.'.$x['ip'].' ('.$country['name'].')</td>
<td class="points">'.$x['points'].'</td>
<td class="credits">'.$bucks.' Credits</td>';
            if ($ext) {
                echo '<td class="upgrade"><a href="upgradelist.php?sid='.$sid.'&amp;xpc='.$x['id'].'">'.$stat.'</a></td>
<td class="attack">'.$attack.'</td>';
            }
            echo '<td class="hijack">'.$hijack.'<br>'.'Level '.$x['rh'].'</td></tr>';

        }

#$list=trim($list,',');
#setuserval('pcs', $list);
#echo $list;

        $tcreds = number_format($tcreds, 0, ',', '.');

        echo '
</table>
<p><strong>Insgesamt '.$tcreds.' Credits!</strong></p>
</div>
</div>
';
        ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
        break;

    case 'renamepclist': // ------------------------- Rename PC List ------------------------
        createlayout_top('ZeroDayEmpire - Deine Computer');
?>
<!-- ZDE theme inject -->
<style>@import url("style.css");</style>
<div class="container">
<?php // /ZDE theme inject start


        echo '<div class="content" id="computer">
<h2>Deine Computer</h2>
'.$notif.'<div id="computer-rename">
<h3>Computer umbenennen</h3>
<form action="game.php?a=renamepcs&amp;sid='.$sid.'" method="post">
<table>
<tr>
<th>IP</th>
<th>Alter Name</th>
<th>Neuer Name</th>
</tr>
';
        $a = explode(',', $usr['pcs']);
        rem_emptys($a);
        for ($i = 0; $i < count($a); $i++) {
            $x = GetPC($a[$i]);
            $x['name'] = htmlspecialchars($x['name']);
            echo '<tr>
<td>10.47.'.$x['ip'].'</td>
<td>'.$x['name'].'</td>
<td><input maxlength="30" name="pc'.$a[$i].'" value="'.$x['name'].'" /></td>
</tr>
';
        }
        echo '<tr id="computer-rename-confirm">
<td colspan="3"><input type="submit" value="Speichern" /></td>
</tr>
</table>
</form>
</div>
</div>
';
        ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
        break;

    case 'renamepcs': // ------------------------- Rename PCs ------------------------
        $a = explode(',', $usr['pcs']);
        for ($i = 0; $i < count($a); $i++) {
            if (trim($_POST['pc'.$a[$i]]) != '') {
                $n = trim($_POST['pc'.$a[$i]]);
                if (strlen($n) > 1 && strlen($n) <= 30) {
                    $xpc = GetPC($a[$i]);
                    $xpc['name'] = $n;
                    SavePC($a[$i], $xpc);
                }
            }
        }
        header(
            'Location: game.php?a=renamepclist&sid='.$sid.'&ok='.urlencode('Die &Auml;nderungen wurden gespeichert.')
        );
        break;

    case 'transfer': // ------------------------- TRANSFER ------------------------
        if ($pc['blocked'] > time()) {
            exit;
        }
        if ($pc['bb'] < 2 || $pc['mm'] < 2) {
            simple_message('Neeee so einfach nicht!');
            exit;
        }
        $type = $_POST['reciptype'];
        $credits = (int)$_POST['credits'];

        $e = '';
        if ($credits > $pc['credits']) {
            $e = 'Nicht gen&uuml;gend Credits f&uuml;r &Uuml;berweisung vorhanden!';
        }
        switch ($type) {
            case 'user':
                $recip = GetPC($_POST['pcip'], 'ip');
                if ($recip === false) {
                    $e = 'Ein Computer mit dieser IP existiert nicht!';
                }
                break;
            case 'cluster':
                $recip = $_POST['clustercode'];
                $recip = GetCluster($recip, 'code');
                if ($recip === false) {
                    $e = 'Ein Cluster mit diesem Code existiert nicht!';
                }
                break;
            default:
                $e = 'Ung&uuml;ltiger Empf&auml;nger-Typ!';
                break;
        }

        if ($credits < 100) {
            $e = 'Der Mindestbetrag f&uuml;r eine &Uuml;berweisung sind 100 Credits!';
        }

        if ($e == '') {
            $tcode = randomx(10);
            $fin = 0;
            createlayout_top('ZeroDayEmpire - Geld &uuml;berweisen');
?>
<!-- ZDE theme inject -->
<style>@import url("style.css");</style>
<div class="container">
<?php // /ZDE theme inject start


            echo '<div class="content">
<h2>&Uuml;berweisung</h2>
<div id="transfer-step2">
<h3>&Uuml;berweisung best&auml;tigen</h3>
<form action="game.php?a=transfer2&sid='.$sid.'"  method="post">
<input type="hidden" name="tcode" value="'.$tcode.'">
<p>';
            $text = '';
            switch ($type) {
                case 'user':
                    $recip_usr = getuser($recip['owner']);
                    if ($recip_user['id'] == $usrid) {
                        $ownerinfo = 'dir selber';
                    } else {
                        $ownerinfo = '<a class=il href="user.php?m=info&user='.$recip['owner'].'&sid='.$sid.'" target="_blank">'.$recip_usr['name'].'</a>';
                    }
                    $text .= '<b>Hiermit werden '.$credits.' Credits an den Rechner 10.47.'.$recip['ip'].', der '.$ownerinfo.' geh&ouml;rt, &uuml;berwiesen.</b><br /><br />';
                    if ($pc['country'] == $recip['country']) {
                        $rest = $credits;
                        $fin = $credits;
                        $text .= 'Da dein Rechner im selben Land steht, wie der Ziel-Rechner, fallen keine Geb&uuml;hren an. Der User erh&auml;lt <b>'.$rest.' Credits</b>.';
                    } else {
                        $c = GetCountry('id', $pc['country']);
                        $country = $c['name'];
                        $out = $c['out'];
                        $c = GetCountry('id', $recip['country']);
                        $country2 = $c['name'];
                        $in = $c['in'];
                        $rest = $credits - ($in + $out);
                        if ($rest > 0) {
                            $fin = $rest;
                            $text .= 'Von diesem Betrag werden noch '.$out.' Credits Geb&uuml;hren als Ausfuhr aus '.$country.' und '.$in.' Credits Geb&uuml;hren als Einfuhr nach '.$country2.', dem Standort von 10.47.'.$recip['ip'].' abgezogen. '.$recip_usr['name'].' erh&auml;lt also noch <b>'.$rest.' Credits</b>.';
                        } else {
                            $text .= 'Da der Betrag sehr gering ist, werden keine Geb&uuml;hren erhoben. '.$recip_usr['name'].' erh&auml;lt <b>'.$credits.' Credits</b>.';
                            $fin = $credits;
                        }

                    }
                    $max = getmaxbb($recip);
                    if ($recip['credits'] + $fin > $max) {
                        $rest = $max - $recip['credits'];
                        $fin = $rest;
                        $credits = $rest;
                        $text .= '<br /><br />Da '.$recip_usr['name'].' seinen BucksBunker nicht weit genug ausgebaut hat, um das Geld zu Empfangen, werden nur <b>'.$rest.' Credits</b> (inklusive Geb&uuml;hren) &uuml;berwiesen!';
                        if ($rest < 1) {
                            echo '<div class="error"><h3>BucksBunker voll</h3><p>Der BucksBunker von '.$recip_usr['name'].' ist voll! &Uuml;berweisung wird abgebrochen!</p></div>';
                            ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
                            exit;
                        }
                    }
                    echo $text;
                    break;

                case 'cluster':
                    echo '<b>Hiermit werden '.$credits.' Credits an den Cluster '.$recip['code'].' ('.$recip['name'].') &uuml;berwiesen.</b><br />';
                    $c = GetCountry('id', $pc['country']);
                    $country = $c['name'];
                    $out = $c['out'];
                    $rest = $credits - $out;
                    if ($rest > 0) {
                        $fin = $rest;
                        echo 'Davon werden noch '.$out.' Credits als Ausfuhr-Geb&uuml;hr f&uuml;r '.$country.' abgezogen. Der Cluster '.$recip['code'].' erh&auml;lt also noch <b>'.$rest.' Credits</b>';
                    } else {
                        echo 'Da der Betrag sehr gering ist, werden keine Geb&uuml;hren erhoben. Der Cluster '.$recip['code'].' erh&auml;lt <b>'.$credits.' Credits</b>.';
                        $fin = $credits;
                    }
                    break;
            }
            echo '<br /><br />
<input type="button" value="Abbrechen" onclick="location.replace(\'transferform.php?sid='.$sid.'\');" />
<input type="submit" value=" Ausf&uuml;hren " /></p></form>';
            echo '</div>'.LF.'</div>';
            ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
            file_put($DATADIR.'/tmp/transfer_'.$tcode.'.txt', $type.'|'.$recip['id'].'|'.$credits.'|'.$fin);
            db_query(
                'UPDATE users SET tcode=\''.mysql_escape_string($tcode).'\' WHERE id=\''.mysql_escape_string(
                    $usrid
                ).'\' LIMIT 1;'
            );

        } else {
            header('Location: transferform.php?sid='.$sid.'&error='.urlencode($e));
        }

        break;

    case 'transfer2':  // ------------------------- TRANSFER 2 ------------------------
        $code = $_REQUEST['tcode'];
        $fn = $DATADIR.'/tmp/transfer_'.$code.'.txt';
        if ($usr['tcode'] != $code || file_exists($fn) != true) {
            simple_message('&Uuml;berweisung ung&uuml;ltig! Bitte neu erstellen!');
            break;
        }
        $dat = explode('|', file_get($fn));
        @unlink($fn);
        if (count($dat) == 4) {
            $pc['credits'] -= $dat[2];
#print_r($dat);
            db_query(
                'UPDATE pcs SET credits=\''.mysql_escape_string($pc['credits']).'\' WHERE id='.mysql_escape_string(
                    $pcid
                )
            );
            if ($dat[0] == 'user') {
                $recip = getpc($dat[1]);
                $recip['credits'] += $dat[3];
                db_query(
                    'UPDATE pcs SET credits=credits+'.mysql_escape_string($dat[3]).' WHERE id=\''.mysql_escape_string(
                        $recip['id']
                    ).'\';'
                );
                $s = '[usr='.$usrid.']'.$usr['name'].'[/usr] hat dir '.$dat[2].' Credits auf deinen PC 10.47.'.$recip['ip'].' ('.$recip['name'].') &uuml;berwiesen.';
                if ($dat[2] != $dat[3]) {
                    $s .= ' Abz&uuml;glich der Geb&uuml;hren hast du '.$dat[3].' Credits erhalten!';
                }
                if ($recip['owner'] != $usrid) {
                    addsysmsg($recip['owner'], $s);
                }
                $msg = '&Uuml;berweisung an 10.47.'.$recip['ip'].' ('.$recip['name'].') ausgef&uuml;hrt!';
            } elseif ($dat[0] == 'cluster') {
                $c = getcluster($dat[1]);
                $c['money'] += $dat[3];
                $c['events'] = nicetime4(
                    ).' [usr='.$usrid.']'.$usr['name'].'[/usr] spendet dem Cluster '.$dat[3].' Credits.'.LF.$c['events'];
                db_query(
                    'UPDATE clusters SET money=\''.mysql_escape_string($c['money']).'\',events=\''.mysql_escape_string(
                        $c['events']
                    ).'\' WHERE id='.mysql_escape_string($c['id'])
                );
                $msg = 'Dem Cluster '.$c['code'].' wurden '.$dat['2'].' Credits &uuml;berwiesen!';
            }
            db_query(
                'INSERT INTO transfers VALUES(\''.mysql_escape_string($pcid).'\', \'user\', \''.mysql_escape_string(
                    $usrid
                ).'\', \''.mysql_escape_string($dat[1]).'\', \''.mysql_escape_string(
                    $dat[0]
                ).'\', \''.mysql_escape_string($recip['owner']).'\', \''.mysql_escape_string($dat[3]).'\', \''.time(
                ).'\');'
            );
            header('Location: transferform.php?sid='.$sid.'&ok='.urlencode($msg));
        }
        break;

    case 'subnet': // ------------------------- SUBNET ------------------------

        $subnet = $_REQUEST['subnet'] ?? '';
        if ($subnet == '') {
            $subnet = subnetfromip($pc['ip']);
        }
        if ((int)$subnet == 0) {
            $tmp = getcountry('id', $subnet);
            $subnet = $tmp['subnet'];
        }

        if ($subnet == '') {
            no_('gs_1');
            exit;
        }
        $c = GetCountry('subnet', $subnet);
        $info = '<div id="subnet-properties">'."\n";
        $info .= '<h3>Aktuelles Subnet</h3>'."\n";
        $info .= '<form action="game.php?mode=subnet&amp;sid='.$sid.'" method="post">';
        $info .= '<table>'."\n";
        $info .= '<tr>'."\n";
        $info .= '<th>Subnet:</th>'."\n";
        $info .= '<td>10.47.'.$subnet.'.0/24</td>'."\n";
        $info .= '</tr>'."\n";
        $info .= '<tr>'."\n";
        $info .= '<th>Land:</th>'."\n";
        $info .= '<td>'.$c['name'].'</td>'."\n";
        $info .= '</tr>'."\n";
        $info .= '<tr>'."\n";
        $info .= '<th>Einfuhr-Geb&uuml;hr:</th>'."\n";
        $info .= '<td>'.$c['in'].'</td>'."\n";
        $info .= '</tr>'."\n";
        $info .= '<tr>'."\n";
        $info .= '<th>Ausfuhr-Geb&uuml;hr:</th>'."\n";
        $info .= '<td>'.$c['out'].'</td>'."\n";
        $info .= '</tr>'."\n";

        include('data/static/country_data.inc.php');
        $options = '';
        foreach ($countrys as $ctry) {
            $options .= '<option value="'.$ctry['subnet'].'">10.47.'.$ctry['subnet'].'.x - '.$ctry['name'].'</option>';
        }

        $listpage = isset($_REQUEST['listpage']) ? (int)$_REQUEST['listpage'] : 1;
        if ($listpage < 1 || $listpage > 4) {
            $listpage = 1;
        }
        $r = db_query(
            'SELECT pcs.id AS pcs_id, pcs.ip AS pcs_ip, pcs.name AS pcs_name, pcs.points AS pcs_points, users.id AS users_id, users.name AS users_name, users.points AS users_points, clusters.id AS clusters_id, clusters.name AS clusters_name FROM (clusters RIGHT JOIN users ON clusters.id = users.cluster) RIGHT JOIN pcs ON users.id = pcs.owner WHERE country LIKE \''.mysql_escape_string(
                $c['id']
            ).'\' ORDER BY pcs.id ASC;'
        );
        $anz = mysql_num_rows($r);
        $pages = ceil($anz * (4 / 256));
        $plist = '';
        for ($i = 1; $i <= $pages; $i++) {
            if ($listpage != $i) {
                $plist .= '<a href="game.php?a=subnet&amp;sid='.$sid.'&amp;subnet='.$subnet.'&amp;listpage='.$i.'#subnet-content">'.$i.'</a> | ';
            } else {
                $plist .= $i.' | ';
            }
        }
        $plist = '<tr>'.LF.'<td class="navigation" colspan="5">Seite: | '.$plist.'</td>'.LF.'</tr>'."\n";

        $javascript = '<script type="text/javascript">
function showcountrysel() {
var newwin;
newwin=window.open(\'static/selcountry.php\',\'selcountry\',\'width=650,height=450,toolbar=0,menubar=0,location=0,status=1,resizable=1,scrollbars=1\');
}
function subnetgo(s) {
location.href=\'../game.php?mode=subnet&sid='.$sid.'&subnet=\'+s;
}
</script>
';

        createlayout_top('ZeroDayEmpire - Subnet');
?>
<!-- ZDE theme inject -->
<style>@import url("style.css");</style>
<div class="container">
<?php // /ZDE theme inject start


        echo '<div class="content" id="subnet">
<h2>Subnet</h2>
'.$info.'
<tr>
<td class="options" colspan="2">Anderes Subnet: <select name="subnet">
<option selected="selected" value="">[Eigenes Subnet]</option>';
#readfile('data/static/subnets.txt');
        echo $options;
        echo '</select> <input type="submit" value="OK" /></td>
</tr>
<tr>
<!--<td class="map" colspan="2"><a href="javascript:showcountrysel()">Von Karte ausw&auml;hlen...</a></td>//-->
</tr>
</table>
</form>
</div>
<div id="subnet-content">
<h3>Subnet-Liste</h3>
<table>
'.$plist.'
<tr>
<th class="ip">IP</th>
<th class="name">Name</th>
<th class="points">Punkte</th>
<th class="owner">Besitzer</th>
<th class="cluster">Cluster</th>
</tr>';

        switch ($listpage) {
            case 2:
                $start = 65;
                break;
            case 3:
                $start = 129;
                break;
            case 4:
                $start = 193;
                break;
            default:
                $start = 0;
        }

        if ($start > 0) {
            mysql_data_seek($r, $start - 1);
        }
        $i = $start;
        while ($data = mysql_fetch_assoc($r)) {
            $i++;
            if ($i > $start + 64) {
                break;
            }
            $ix = $data['pcs_id'];
            $data['pcs_name'] = safeentities($data['pcs_name']);
            if (is_noranKINGuser($data['users_id']) === true && is_noranKINGuser($usrid) === false) {
                continue;
            }

            if ($data['users_name'] != '') {
                $userinfo = '<a href="user.php?a=info&amp;user='.$data['users_id'].'&amp;sid='.$sid.'">'.$data['users_name'].'</a> ('.$data['users_points'].' P)';
                $userclass = 'owner';
            } else {
                $userclass = 'no-owner';
                $userinfo = '';
            }

            if ($data['clusters_name'] != '') {
                $clusterclass = 'cluster';
                $clusterinfo = '<a href="cluster.php?a=info&amp;cluster='.$data['clusters_id'].'&amp;sid='.$sid.'">'.$data['clusters_name'].'</a>';
            } else {
                $clusterclass = 'no-cluster';
                $clusterinfo = '';
            }

            echo '<tr>
<td class="ip">10.47.'.$data['pcs_ip'].'</td>
<td class="name">'.$data['pcs_name'].'</td>
<td class="points">'.$data['pcs_points'].'</td>
<td class="'.$userclass.'">'.$userinfo.'</td>
<td class="'.$clusterclass.'">'.$clusterinfo.'</td>
</tr>
';

        }

        echo $plist."\n".'</table>'.LF.'</div>'.LF.'</div>'."\n";
        ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();

        break;

    case 'kb':
        createlayout_top('ZeroDayEmpire - Hilfe');
?>
<!-- ZDE theme inject -->
<style>@import url("style.css");</style>
<div class="container">
<?php // /ZDE theme inject start


        readfile('data/static/kb.html');
        ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
        break;

}

?>
