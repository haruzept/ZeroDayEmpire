<?php

define('IN_ZDE', 1);
$FILE_REQUIRES_PC = false;
include('ingame.php');


$action = $_REQUEST['page'] ?? '';
# Die folgenden Variablen sollten nicht mehr verwendet werden
if ($action == '') {
    $action = $_REQUEST['mode'] ?? '';
}
if ($action == '') {
    $action = $_REQUEST['action'] ?? '';
}
if ($action == '') {
    $action = $_REQUEST['a'] ?? '';
}
if ($action == '') {
    $action = $_REQUEST['m'] ?? '';
}

# Konstanten für Syndikat-Verträge:
define('CV_WAR', 1, false);
define('CV_BEISTAND', 2, false);
define('CV_PEACE', 3, false);
define('CV_NAP', 5, false);
define('CV_WING', 6, false);

# Syndikat-Daten lesen:
$syndikatid = $usr['syndikat'];
$good_actions = 'start join found info listmembers request1 request2';
$syndikat = getsyndikat($syndikatid);
// eregi() was removed in PHP 7.0; use stripos() for a case-insensitive check instead
if ($syndikat == false && stripos($good_actions, $action) === false) {
    no_();
    exit;
}

function savemysyndikat()
{ # Eigenen Syndikat speichern
    global $syndikatid, $syndikat;
    $s = '';
    foreach ($syndikat as $bez => $val) {
        $s .= $bez.'=\''.mysql_escape_string($val).'\',';
    }
    $s = trim($s, ',');
    db_query('UPDATE syndikate SET '.$s.' WHERE id=\''.mysql_escape_string($syndikatid).'\'');
}

switch ($action) {
    case 'start': //------------------------- START -------------------------------

        if ($usr['da_avail'] == 'yes') {
            $pc = getpc($pcid);
        }

        createlayout_top('ZeroDayEmpire - Syndikat');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


        echo '<div class="content" id="syndikat">'."\n";
        echo '<h2>Syndikat</h2>'."\n";


        function nosyndikat()
        {
# ich bin keinem (existierenden) Syndikat
            global $REMOTE_FILES_DIR, $DATADIR, $sid, $usrid, $pcid;
            echo '<div id="syndikat-found">
<h3>Syndikat gr&uuml;nden</h3>
<form action="syndikat.php?page=found&amp;sid='.$sid.'" method="post">
<table>
<tr>
<th>Name:</th>
<td><input type="text" name="name" maxlength="48" /></td>
</tr>
<tr>
<th>Code:</th>
<td><input type="text" name="code" maxlength="12" /></td>
</tr>
<tr><td colspan="2"><input type="submit" value="Gr&uuml;nden" /></td>
</tr>
</table>
</form>
</div>
<div class="important"><h3>Hinweis</h3>
<p>Um einem existierenden Syndikat beizutreten, rufe die Info-Seite eines Syndikate auf.
Dort findest du einen "Mitgliedsantrag stellen"-Link.</p></div>
</div>';
        }

#  kein Syndikat
        if ($syndikat === false) {
            nosyndikat();
            ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
            exit;
        }

        if (eregi('http://.*/.*', $syndikat['logofile'])) {
            if ($usr['sid_ip'] != 'noip') {
                $img = $syndikat['logofile'];
                #$img=dereferurl($img);
                $img = '<tr>'.LF.'<td colspan="2"><img src="'.$img.'" alt="Syndikat-Logo" /></td>'.LF.'</tr>'."\n";
            }
            #$img='<tr>'.LF.'<td colspan="2">Das Syndikatlogo kann im Moment wegen einer noch nicht geschlossenen Sicherheitsl&uuml;cke nicht angezeigt werden.</td>'.LF.'</tr>'."\n";
        } else {
            $img = '';
        }

        $a = explode("\n", $syndikat['events']);
        $mod = false;
        if (count($a) > 21) {
            $syndikat['events'] = joinex(array_slice($a, 0, 20), "\n");
            $mod = true;
        }
        $list = str_replace("\n", '<br />', $syndikat['events']);
        gFormatText($list);

        if ($mod == true) {
            savemysyndikat();
        }

        $reqs = @mysql_num_rows(
            db_query('SELECT user FROM cl_reqs WHERE syndikat='.mysql_escape_string($syndikatid).' AND dealed=\'no\'')
        );
        $funcs = '';
        $stat = (int)$usr['syndikatetat'];
        $settings = '<a href="syndikat.php?page=config&amp;sid='.$sid.'">Einstellungen</a><br />';
        $members = '<a href="syndikat.php?page=members&amp;sid='.$sid.'">Mitglieder-Verwaltung</a><br />';
        $finances = '<a href="syndikat.php?page=finances&amp;sid='.$sid.'">Syndikat-Kasse</a><br />';
        $battles = '<a href="syndikat.php?page=battles&amp;sid='.$sid.'">Angriffs&uuml;bersicht</a><br />';
        $konvents = '<a href="syndikat.php?page=convents&amp;sid='.$sid.'">Vertr&auml;ge</a><br />';
        $req_verw = '<a href="syndikat.php?page=req_verw&amp;sid='.$sid.'">Mitgliedsantr&auml;ge</a> ('.$reqs.')<br />';
        if ($stat == CS_ADMIN) {
            $funcs = $settings.$members.$finances.$battles.$konvents.$req_verw;
            $jobs = 'Den Syndikat verwalten. Du kannst alles machen!';
        }
        if ($stat == CS_COADMIN) {
            $funcs = $settings.$finances.$battles.$konvents.$req_verw;
            $jobs = 'Den Syndikat verwalten. Du kannst alles machen au&szlig;er den Status von Mitgliedern &auml;ndern.';
        }
        if ($stat == CS_WAECHTER) {
            $funcs = $battles;
            $jobs = 'Schlachten im Auge behalten.';
        }
        if ($stat == CS_WARLORD) {
            $funcs = $battles.$konvents.$finances;
            $jobs = 'Wie ein General den Syndikat durch Kriege f&uuml;hren!';
        }
        if ($stat == CS_KONVENTIONIST) {
            $funcs = $konvents.$finances;
            $jobs = 'Durch Verhandlungen, Zahlungen und Vertr&auml;ge den politischen Status des Syndikate bestimmen.';
        }
        if ($stat == CS_SUPPORTER) {
            $funcs = $finances;
            $jobs = 'Schwache Syndikat-Mitglieder unterst&uuml;tzen.';
        }
        if ($stat == CS_MITGLIEDERMINISTER) {
            $funcs = $req_verw;
            $jobs = 'Aufname-Antr&auml;ge pr&uuml;fen.';
        }

        if ($stat > CS_MEMBER) {
            $jobs = '<tr>'.LF.'<th>Aufgaben:</th>'.LF.'<td>'.$jobs.'</td>'.LF.'</tr>'."\n";
        }

        if ($funcs != "") {
            $funcs = '<tr>'.LF.'<th>Funktionen:</th>'.LF.'<td>'.$funcs.'</td>'.LF.'</tr>'."\n";
        }

        $members = mysql_num_rows(
            db_query('SELECT id FROM users WHERE syndikat=\''.mysql_escape_string($syndikatid).'\'')
        );

        if ($members > 0 && $syndikat['points'] > 0) {
            $av = round($syndikat['points'] / $members, 2);
        } else {
            $av = 0;
        }

        $money = number_format((int)$syndikat['money'], 0, ',', '.');

        $syndikatetat = cscodetostring($usr['syndikatetat']);

        foreach ($syndikat as $bez => $val) {
            $syndikat[$bez] = safeentities($val);
        }

        echo '<div id="syndikat-overview">
<h3>'.$syndikat['name'].'</h3>
<table width="90%">
'.$img.'<tr id="syndikat-overview-board1">
<td colspan="2"><a href="cboard.php?page=board&amp;sid='.$sid.'">Zum Syndikat-Board</a></td>
</tr>
<tr>
<th>Name:</th>
<td>'.$syndikat['name'].'</td>
</tr>
<tr>
<th>Code:</th>
<td>'.$syndikat['code'].'</td>
</tr>
<tr>
<th>Mitglieder (<a href="syndikat.php?page=listmembers&amp;syndikat='.$usr['syndikat'].'&amp;sid='.$sid.'">anzeigen</a>):</th>
<td>'.$members.'
(<a href="syndikat.php?page=leave&amp;sid='.$sid.'">Austreten</a>)</td>
</tr>
<tr>
<th>Punkte</th>
<td>'.$syndikat['points'].'</td>
</tr>
<tr>
<th>Durchschnitt:</th>
<td>'.$av.' Punkte pro User</td>
</tr>
<tr>
<th>Dein Status:</th>
<td>'.$syndikatetat.'</td>
</tr>
'.$jobs.$funcs.'<tr>
<th>Verm&ouml;gen:</th>
<td>'.$money.' Credits</td>
</tr>
<tr>
<th>Mitgliedsbeitrag:</th>
<td>'.$syndikat['tax'].' Credits pro Tag pro User</td>
</tr>
<tr id="syndikat-overview-events">
<th>Ereignisse:</th>
<td><div>'.$list.'</div></td>
</tr>
<tr id="syndikat-overview-board2">
<td colspan="2"><a href="cboard.php?page=board&amp;sid='.$sid.'">Zum Syndikat-Board</a></td>
</tr>
</table>
</div>';

        if ($usr['syndikatetat'] == CS_ADMIN || $usr['syndikatetat'] == CS_COADMIN):
            $syndikat['notice'] = html_entity_decode($syndikat['notice']);
            echo '<div id="syndikat-notice-create">
<h3>Aktuelle Notiz</h3>
<form action="syndikat.php?sid='.$sid.'&amp;page=savenotice" method="post">
<table>
<tr><th>Text:</th><td><textarea name="notice" rows="4" cols="30">'.$syndikat['notice'].'</textarea></td></tr>
<tr><th>Aktionen:</th><td><input type="submit" value="Speichern" />
<input type="button" onclick="this.form.notice.value=\'\';this.form.submit();" value="L&ouml;schen" />
</td></tr>
</table>
</form>
</div>';
        endif;

#echo '<div class="important"><h3>Hinweis</h3><p>Heute Nachmittag k&ouml;nnen leider keine DAs erstellt / ausgef&uuml;hrt werden, da die
#interne Verwaltung selbiger umgestellt wird. Danke f&uuml;r euer Verst&auml;ndis!<br />KingIR</p></div>';

        echo '<div id="syndikat-distributed-attacks">
<h3>Distributed Attacks</h3><br />';
        if ($usr['da_avail'] == 'yes') {
            $pc = getpc($pcid);
            if (isavailh('da', $pc) == true) {
                echo '<p><a href="distrattack.php?sid='.$sid.'&amp;page=create">Neue Distributed Attack erstellen</a></p>'."\n";
            } else {
                echo '<p>Von diesem PC aus kannst du keine DA erstellen!</p>'."\n";
            }
        }
        echo '<p><a href="distrattack.php?sid='.$sid.'&amp;page=list">Vorhandene Distributed Attacks anzeigen</a></p>';

        echo '</div>'."\n";

        ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
        break;

    case 'delconvent': //----------------- DELETE CONVENT -------------------------
        if ($usr['syndikatetat'] == CS_ADMIN || $usr['syndikatetat'] == CS_WARLORD ||
            $usr['syndikatetat'] == CS_KONVENTIONIST || $usr['syndikatetat'] == CS_COADMIN
        ) {

            $c = explode('-', $_REQUEST['convent']);
            $c[0] = (int)$c[0];
            $c[1] = (int)$c[1];

            $sql = 'FROM cl_pacts WHERE syndikat='.mysql_escape_string($syndikatid).' AND partner='.mysql_escape_string(
                    $c[1]
                ).' AND convent='.mysql_escape_string($c[0]).' LIMIT 1';
            $r = db_query('SELECT * '.$sql.';');
            if (@mysql_num_rows($r) == 1) {
                db_query('DELETE '.$sql.';');

                $convent = cvcodetostring($c[0]);
                $dat = getsyndikat($c[1]);

                $dat['events'] = nicetime4(
                    ).' Der Syndikat [syndikat='.$syndikatid.']'.$syndikat['code'].'[/syndikat] hat <i>'.$convent.'</i> mit euch annulliert!'.LF.$dat['events'];
                db_query(
                    'UPDATE syndikate SET events=\''.mysql_escape_string(
                        $dat['events']
                    ).'\' WHERE id='.mysql_escape_string($dat['id'])
                );

                $syndikat['events'] = nicetime4(
                    ).' [usr='.$usrid.']'.$usr['name'].'[/usr] annulliert <i>'.$convent.'</i> mit dem Syndikat [syndikat='.$dat['id'].']'.$dat['code'].'[/syndikat]!'.LF.$syndikat['events'];

                $x = explode("\n", $syndikat['events']);
                if (count($x) > 21) {
                    $syndikat['events'] = joinex(array_slice($x, 0, 20), "\n");
                }

                db_query(
                    'UPDATE syndikate SET events=\''.mysql_escape_string(
                        $syndikat['events']
                    ).'\' WHERE id='.mysql_escape_string($syndikatid)
                );
            }

            header('Location: syndikat.php?sid='.$sid.'&page=convents&ok='.urlencode('Der Vertrag wurde annulliert.'));

        } else {
            no_();
        }
        break;


    case 'convents': //----------------- CONVENTS -------------------------
        if ($usr['syndikatetat'] == CS_ADMIN || $usr['syndikatetat'] == CS_WARLORD ||
            $usr['syndikatetat'] == CS_KONVENTIONIST || $usr['syndikatetat'] == CS_COADMIN
        ) {

#simple_message('Die Vertr&auml;ge-Verwaltung ist heute morgen nicht verf&uuml;gbar. Probier es heute nachmittag nochmal.');
#exit;

            createlayout_top('ZeroDayEmpire - Syndikat - Vertr&auml;ge');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


            echo '<div class="content" id="syndikat">
<h2>Syndikat</h2>'
                .$notif.'<div id="syndikat-create-convent">
<h3>Vertrag erstellen</h3>
<form action="syndikat.php?page=saveconvents&amp;sid='.$sid.'" method="post">
<table>
<tr>
<th>Vertrags-Partner (Code):</th>
<td><input type="text" name="partner" maxlength="12" /></td>
</tr>
<tr>
<th>Vertrags-Art:</th>
<td><select name="type">
<option value="1">Kriegserkl&auml;rung</option>
<option value="2">Beistandsvertrag</option>
<option value="3">Friedensvertrag</option>
<option value="5">Nicht-Angriffs-Pakt</option>
<option value="6">Wing-Treaty</option>
</select></td>
</tr>
<tr id="syndikat-create-convent-confirm">
<td colspan="2"><input type="submit" value="Erstellen" /></td>
</tr>
</table>
</form>
</div>';

            $r = db_query(
                'SELECT cl_pacts.convent,syndikate.code,syndikate.id,cl_pacts.partner FROM (cl_pacts RIGHT JOIN syndikate ON cl_pacts.partner=syndikate.id) WHERE cl_pacts.syndikat='.mysql_escape_string(
                    $syndikatid
                ).' ORDER BY syndikate.code ASC;'
            );
            if (mysql_num_rows($r) > 0) {
                echo '<div id="syndikat-convents">
<h3>Eigene bestehende Vertr&auml;ge</h3>
<table>
<tr>
<th>Syndikat</th>
<th>Vertrag</th>
<th>Löschen?</th>
</tr>
';
                while ($pact = mysql_fetch_assoc($r)):
                    $temp = cvcodetostring($pact['convent']);
                    echo '<tr>
<td><a href="syndikat.php?page=info&amp;sid='.$sid.'&amp;syndikat='.$pact['id'].'">'.$pact['code'].'</a></td>
<td>'.$temp.'</td>
<td><a href="syndikat.php?page=delconvent&amp;sid='.$sid.'&amp;convent='.$pact['convent'].'-'.$pact['partner'].'">L&ouml;schen</a></td>
</tr>
';
                endwhile;
                echo '</table>
</div>
';
            }

            $r = db_query(
                'SELECT cl_pacts.convent,syndikate.code,syndikate.id FROM (cl_pacts RIGHT JOIN syndikate ON cl_pacts.syndikat=syndikate.id) WHERE cl_pacts.partner='.mysql_escape_string(
                    $syndikatid
                ).' ORDER BY syndikate.code ASC;'
            );
            if (mysql_num_rows($r) > 0) {
                echo '<div id="syndikat-convents">
<h3>Bestehende Vertr&auml;ge anderer Syndikat mit uns</h3>
<table>
<tr>
<th>Syndikat</th>
<th>Vertrag</th>
</tr>
';
                while ($pact = mysql_fetch_assoc($r)):
                    $temp = cvcodetostring($pact['convent']);
                    echo '<tr>
<td><a href="syndikat.php?page=info&amp;sid='.$sid.'&amp;syndikat='.$pact['id'].'">'.$pact['code'].'</a></td>
<td>'.$temp.'</td>
</tr>
';
                endwhile;
                echo '</table>
</div>
';
            }

            echo '</div>'."\n";
            ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();

        } else {
            no_();
        }
        break;

    case 'saveconvents': //------------------------- SAVE CONVENTS -------------------------------
        if ($usr['syndikatetat'] == CS_ADMIN || $usr['syndikatetat'] == CS_WARLORD ||
            $usr['syndikatetat'] == CS_KONVENTIONIST || $usr['syndikatetat'] == CS_COADMIN
        ) {

            $dat = getsyndikat($_POST['partner'], 'code');
            if ($dat == false) {
                $error = 'Ein Syndikat mit dem Code '.$_POST['partner'].' existiert nicht!';
            } elseif ($dat['id'] == $syndikatid) {
                $error = 'Du kannst keinen Vertrag mit dem eigenen Syndikat abschlie&szlig;en!';
            } else {
                $type = (int)$_POST['type'];
                if ($type < 1 OR $type > 6) {
                    no_();
                    exit;
                }
                $convent = cvCodeToString($type);
                $cname = htmlspecialchars($dat['code']);
                $dat['events'] = nicetime4(
                    ).' Der Syndikat [syndikat='.$syndikatid.']'.$syndikat['code'].'[/syndikat] hat <i>'.$convent.'</i> mit euch eingetragen.'.LF.$dat['events'];
                db_query(
                    'UPDATE syndikate SET events=\''.mysql_escape_string(
                        $dat['events']
                    ).'\' WHERE id='.mysql_escape_string($dat['id'])
                );
                db_query(
                    'INSERT INTO cl_pacts VALUES ('.mysql_escape_string($syndikatid).', '.mysql_escape_string(
                        $type
                    ).', '.mysql_escape_string($dat['id']).');'
                );
                $syndikat['events'] = nicetime4(
                    ).' [usr='.$usrid.']'.$usr['name'].'[/usr] tr&auml;gt <i>'.$convent.'</i> mit dem Syndikat [syndikat='.$dat['id'].']'.$cname.'[/syndikat] ein.'.LF.$syndikat['events'];
                db_query(
                    'UPDATE syndikate SET events=\''.mysql_escape_string(
                        $syndikat['events']
                    ).'\' WHERE id='.mysql_escape_string($syndikatid)
                );
                $ok = 'Der Vertrag wurde abgeschlossen.';
            }
            header(
                'Location: syndikat.php?page=convents&sid='.$sid.'&'.($ok != '' ? 'ok='.urlencode(
                        $ok
                    ) : 'error='.urlencode($error))
            );

        } else {
            no_();
        }
        break;

    case 'savefincances': //------------------------- SAVE FINANCES -------------------------------
        if ($usr['syndikatetat'] == CS_ADMIN || $usr['syndikatetat'] == CS_COADMIN) {
            $tax = $_REQUEST['tax'];
            if (is_long((int)$tax)) {
                $syndikat['events'] = nicetime4(
                    ).' [usr='.$usrid.']'.$usr['name'].'[/usr] setzt Mitgliedsbeitrag auf '.$tax.' Credits pro Tag'.LF.$syndikat['events'];
                db_query(
                    'UPDATE syndikate SET events=\''.mysql_escape_string(
                        $syndikat['events']
                    ).'\',tax='.mysql_escape_string($tax).' WHERE id='.mysql_escape_string($syndikatid)
                );
                header(
                    'Location: syndikat.php?page=finances&sid='.$sid.'&ok='.urlencode(
                        'Die &Auml;nderungen wurden &uuml;bernommen.'
                    )
                );
            } else {
                header(
                    'Location: syndikat.php?page=finances&sid='.$sid.'&error='.urlencode('Bitte eine Zahl eingeben.')
                );
            }
        } else {
            no_();
        }
        break;

    case 'finances': //------------------------- FINANCES -------------------------------
        if ($usr['syndikatetat'] == CS_ADMIN || $usr['syndikatetat'] == CS_WARLORD ||
            $usr['syndikatetat'] == CS_KONVENTIONIST || $usr['syndikatetat'] == CS_SUPPORTER || $usr['syndikatetat'] == CS_COADMIN
        ) {

            $syndikat['money'] = (int)$syndikat['money'];
            $syndikat['tax'] = (int)$syndikat['tax'];

            $javascript = '<script type="text/javascript">'."\n";
            if ($usr['bigacc'] == 'yes') {
                $javascript .= 'function fill(s) { document.frm.pcip.value=s; }';
            }
            $javascript .= '
function autosel(obj) { var i = (obj.name==\'pcip\' ? 1 : 0);
  document.frm.reciptype[i].checked=true; }
</script>';

            createlayout_top('ZeroDayEmpire - Syndikat - Finanzen');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


            echo '<div class="content" id="syndikat">
<h2>Syndikat</h2>
'.$notif;
            if ($usr['syndikatetat'] == CS_ADMIN || $usr['syndikatetat'] == CS_COADMIN) {
                $fm = number_format($syndikat['money'], 0, ',', '.');
                echo '<div id="syndikat-money">
<h3>Vermögen</h3>
<p>Aktuelles Verm&ouml;gen des Syndikate: '.$fm.' Credits.</p>
</div>
<div id="syndikat-tax">
<h3>Mitgliedsbeitrag</h3>
<p>Mitgliedsbeitrag in Credits pro User pro Tag festlegen:</p>
<form action="syndikat.php?page=savefincances&amp;sid='.$sid.'" method="post">
<table>
<tr>
<th>Syndikat-Mitgliedsbeitrag:</th>
<td><input type="text" name="tax" maxlength="5" value="'.$syndikat['tax'].'" /></td>
</tr>
<tr>
<td colspan="2"><input type="submit" value="Speichern" /></td>
</tr>
</table>
</form>
</div>
';
            }

            if ($usr['bigacc'] == 'yes') {
                $bigacc = '&nbsp;<a href="javascript:show_abook(\'pc\')">Adressbuch</a>';
            }
            echo '
<div id="syndikat-transfers">
<h3>Überweisungen</h3>
<form action="syndikat.php?page=transfer&amp;sid='.$sid.'" method="post" name="frm">
<table>
<tr>
<th>Empf&auml;nger:</th>
<td><input type="radio" checked="checked" name="reciptype" value="syndikat" /> Syndikat &ndash; Code: <input type="text" name="syndikatcode" onchange="autosel(this)" maxlength="12" /><br />
<input type="radio" name="reciptype" value="user" /> Benutzer &ndash; IP: 10.47.<input type="text" name="pcip" onchange="autosel(this)" maxlength="7" />'.$bigacc.'</td>
</tr>
<tr>
<th>Betrag:</th>
<td><input type="text" name="credits" maxlength="5" value="0" /> Credits</td>
</tr>
<tr>
<td colspan="2"><input type="submit" value="Ausf&uuml;hren" /></td>
</tr>
</table>
</form>
</div>
<div id="syndikat-tax-paid">
<h3>Wer hat bezahlt?</h3>
<table>
<tr>
<th>Name</th>
<th>letzte Bezahlung</th>
</tr>
';


# Wer hat wann bezahlt...?
            $r = db_query(
                'SELECT id,name,cm FROM users WHERE syndikat=\''.mysql_escape_string($syndikatid).'\' ORDER BY name ASC'
            );
            while ($user = mysql_fetch_assoc($r)) {
                if ($user['cm'] == strftime('%d.%m.')) {
                    $user['cm'] = 'heute';
                } elseif ($user['cm'] == strftime('%d.%m.', time() - 86400)) {
                    $user['cm'] = 'gestern';
                }
                echo '<tr>'.LF.'<td><a href="user.php?page=info&amp;user='.$user['id'].'&amp;sid='.$sid.'">'.$user['name'].'</a></td><td>'.$user['cm'].'</td></tr>';
            }
            echo '</table>'.LF.'</div>'.LF.'</div>'.LF;

            ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();

        } else {
            no_();
        }
        break;

    case 'members': //------------------------- MEMBERS -------------------------------
        if ($usr['syndikatetat'] == CS_ADMIN) {

            createlayout_top('ZeroDayEmpire - Syndikat');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


            echo '<div class="content" id="syndikat">
<h2>Syndikat</h2>
'.$notif.'<div id="syndikat-member-administration">
<h3>Mitglieder-Verwaltung</h3>
<form action="syndikat.php?page=savemembers&amp;sid='.$sid.'" method="post">
<table>
<tr>
<th>Name</th>
<th>Punkte</th>
<th>Status</th>
<th>Letztes Log In</th>
<th>Ausschlie&szlig;en?</th>
</tr>
';

            function stat_list_item($id, $c)
            {
                echo '<option value="'.$id.'"'.($c == $id ? ' selected="selected">' : '>').cscodetostring(
                        $id
                    ).'</option>';
            }

            $r = db_query(
                'SELECT * FROM users WHERE syndikat=\''.mysql_escape_string($syndikatid).'\' ORDER BY name ASC'
            );

            while ($udat = mysql_fetch_assoc($r)) {
                $uix = $udat['id'];
                if ($uix == $usrid) {
                    continue;
                }
                echo '<tr>'.LF.'<td><a href="user.php?page=info&amp;user='.$uix.'&amp;sid='.$sid.'">'.$udat['name'].'</a></td>'.LF.'<td>'.$udat['points'].'</td>'.LF.'<td>';
                echo '<select name="stat'.$uix.'">';
                stat_list_item(CS_MEMBER, $udat['syndikatetat']);
                stat_list_item(CS_ADMIN, $udat['syndikatetat']);
                stat_list_item(CS_COADMIN, $udat['syndikatetat']);
                stat_list_item(CS_WAECHTER, $udat['syndikatetat']);
                stat_list_item(CS_JACKASS, $udat['syndikatetat']);
                stat_list_item(CS_WARLORD, $udat['syndikatetat']);
                stat_list_item(CS_KONVENTIONIST, $udat['syndikatetat']);
                stat_list_item(CS_SUPPORTER, $udat['syndikatetat']);
                stat_list_item(CS_MITGLIEDERMINISTER, $udat['syndikatetat']);
                echo '</select></td>'.LF.'<td>'.nicetime3(
                        $udat['login_time']
                    ).'</td>'.LF.'<td><input type="checkbox" value="yes" name="kick'.$uix.'" /></td></tr>';
            }

            echo '<tr>
<td colspan="5"><input type="submit" value="Speichern" /></td>
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

        } else {
            no_();
        }
        break;

    case 'savemembers': //-------------------- SAVE MEMBERS ------------------
        if ($usr['syndikatetat'] == CS_ADMIN) {

            $r = db_query(
                'SELECT id,name,syndikatetat FROM users WHERE syndikat=\''.mysql_escape_string(
                    $syndikatid
                ).'\' ORDER BY name ASC'
            );

            while ($udat = mysql_fetch_assoc($r)) {
                $uix = $udat['id'];
                if ($uix == $usrid) {
                    continue;
                }
                if ($_POST['kick'.$uix] == 'yes') { # User aus dem Syndikat schmei&szlig;en?
                    db_query('UPDATE users SET syndikat=\'\',cm=\'\',syndikatetat=0 WHERE id='.mysql_escape_string($uix));
                    $syndikat['events'] = nicetime4(
                        ).' [usr='.$udat['id'].']'.$udat['name'].'[/usr] wird durch [usr='.$usrid.']'.$usr['name'].'[/usr] aus dem Syndikat ausgeschlossen.'.LF.$syndikat['events'];
                    addsysmsg(
                        $udat['id'],
                        'Du wurdest durch [usr='.$usrid.']{'.$usr['name'].'[/usr] aus dem Syndikat [syndikat='.$syndikatid.']'.$syndikat['code'].'[/syndikat] ausgeschlossen!'
                    );
                } else {
                    $stat = (int)$_REQUEST['stat'.$uix];
                    if ($udat['syndikatetat'] != $stat) {
                        db_query(
                            'UPDATE users SET syndikatetat=\''.mysql_escape_string(
                                $stat
                            ).'\' WHERE id='.mysql_escape_string($uix)
                        );
                        $syndikat['events'] = nicetime4(
                            ).' [usr='.$udat['id'].']'.$udat['name'].'[/usr] erh&auml;lt durch [usr='.$usrid.']'.$usr['name'].'[/usr] den Status '.cscodetostring(
                                $stat
                            ).'.'.LF.$syndikat['events'];
                    }
                }
            }

            $x = explode("\n", $syndikat['events']);
            if (count($x) > 21) {
                $syndikat['events'] = joinex(array_slice($x, 0, 20), "\n");
            }
            db_query(
                'UPDATE syndikate SET events=\''.mysql_escape_string(
                    $syndikat['events']
                ).'\' WHERE id='.mysql_escape_string($syndikatid)
            );

            header(
                'Location: syndikat.php?page=members&sid='.$sid.'&ok='.urlencode(
                    'Die &Auml;nderungen wurden &uuml;bernommen!'
                )
            );
        } else {
            no_();
        }
        break;

    case 'config': //------------------------- CONFIG -------------------------------
        if ($usr['syndikatetat'] == CS_ADMIN || $usr['syndikatetat'] == CS_COADMIN) {

            foreach ($syndikat as $bez => $val) {
                $syndikat[$bez] = safeentities(html_entity_decode($val));
            }

            $anch = ($syndikat['acceptnew'] == 'yes' ? ' checked="checked"' : '');

            createlayout_top('ZeroDayEmpire - Syndikat');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


            echo '<div class="content" id="syndikat">
<h2>Syndikat</h2>
'.$notif.'<div id="syndikat-settings">
<h3>Syndikat-Einstellungen</h3>
<form action="syndikat.php?page=savecfg&amp;sid='.$sid.'" method="post">
<table>
<tr>
<th>Syndikat-Name:</th>
<td><input type="text" name="name" maxlength="48" value="'.$syndikat['name'].'" /></td>
</tr>
<tr>
<th>Syndikat-Code:</th>
<td><input type="text" name="code" maxlength="12" value="'.$syndikat['code'].'" /></td>
</tr>
<tr>
<th>Neue Mitglieder?</th>
<td><input name="acceptnew" value="yes" type="checkbox"'.$anch.' /> Sollen Spieler Mitgliedsantr&auml;ge stellen d&uuml;rfen, um dem Syndikat beizutreten?</td>
</tr>
<tr>
<th>Beschreibung:</th>
<td><textarea rows="10" cols="50" name="about">'.$syndikat['infotext'].'</textarea></td>
</tr>
<tr>
<th>Namen der Ordner im Syndikat-Board:</th>
<td>Ordner 1:<br />
<input type="text" name="box0" value="'.$syndikat['box1'].'" maxlength="30" /><br />
Ordner 2:<br />
<input type="text" name="box1" value="'.$syndikat['box2'].'" maxlength="30" /><br />
Ordner 3:<br />
<input type="text" name="box2" value="'.$syndikat['box3'].'" maxlength="30" /></td>
</tr>
<tr>
<th>Logo-Datei:</th>
<td><input type="text" name="logofile" value="'.$syndikat['logofile'].'" /><br />Eine Internet-Adresse mit http:// eingeben.</td>
</tr>
<tr>
<th>Homepage:</th>
<td><input type="text" name="homepage" value="'.$syndikat['homepage'].'" /><br />Eine Internet-Adresse mit http:// eingeben.</td>
</tr>
<tr>
<th>Syndikat l&ouml;schen:</th>
<td><input name="delete" value="yes" type="checkbox" /></td>
</tr>
<tr>
<td colspan="2"><input type="submit" value="Speichern" /></td>
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

        } else {
            no_();
        }
        break;

    case 'delsyndikat':
        if ($_POST['delete'] == 'yes') {
            $r = db_query('SELECT id FROM users WHERE syndikat=\''.mysql_escape_string($syndikatid).'\';');
            while ($data = mysql_fetch_assoc($r)) {
                addsysmsg(
                    $data['id'],
                    'Dein Syndikat '.$syndikat['code'].' wurde gel&ouml;scht! Das passierte durch [usr='.$usrid.']'.$usr['name'].'[/usr] ('.cscodetostring(
                        $usr['syndikatetat']
                    ).')'
                );
            }
            deletesyndikat($usr['syndikat']);
            db_query(
                'INSERT INTO logs SET type=\'delsyndikat\', usr_id=\''.mysql_escape_string(
                    $usrid
                ).'\', payload=\''.mysql_escape_string($usr['name']).' deletes '.mysql_escape_string(
                    $syndikat['code']
                ).'\';'
            );
        }
        break;

    case 'savecfg': //------------------------- SAVE CONFIG -------------------------------
        if ($usr['syndikatetat'] == CS_ADMIN || $usr['syndikatetat'] == CS_COADMIN) {

            if ($_POST['delete'] == 'yes') {

                createlayout_top();
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


                echo '<div class="content" id="syndikat">
<h2>Syndikat l&ouml;schen</h2>
<h3>Bitte best&auml;tigen!</h3>
<form action="syndikat.php?page=delsyndikat&amp;sid='.$sid.'" method="post">
<p><strong>Setz den Haken und klick auf "Weiter" um den Syndikat endg&uuml;ltig zu l&ouml;schen!</strong></p>
<p><input type="checkbox" value="yes" name="delete" /></p>
<p><input type="submit" value=" Weiter " /></p>
</form>
</div>';
                ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();

            } else {

                $name = $_POST['name'];
                $code = $_POST['code'];
                $text = $_POST['about'];
                $logo = str_replace('\\', '/', $_POST['logofile']);
                $hp = str_replace('\\', '/', $_POST['homepage']);
                $acceptnew = ($_POST['acceptnew'] == 'yes' ? 'yes' : 'no');

                $msg = '';
                $e = false;
                if (trim($code) == '') {
                    $e = true;
                    $msg .= 'Das Feld Code muss ein K&uuml;rzel f&uuml;r den Syndikat enthalten!<br />';
                }
                if (trim($name) == '') {
                    $e = true;
                    $msg .= 'Das Feld Name muss einen Namen f&uuml;r den Syndikat enthalten!<br />';
                }
                if (preg_match('/[;<>"]/', $name) != false) {
                    $e = true;
                    $msg .= 'Der Name darf nicht die Zeichen ; &lt; &gt; &quot; enthalten!<br />';
                }
                if (preg_match('/[;<>"]/', $code) != false) {
                    $e = true;
                    $msg .= 'Der Code darf nicht die Zeichen ; &lt; &gt; &quot; enthalten!<br />';
                }
                if (eregi('http://.*/.*', $logo) == false) {
                    $logo = '';
                }
                if (eregi('http://.*', $hp) == false) {
                    $hp = '';
                }
                if ($code != $syndikat['code']) {
                    $c = getsyndikat($code, 'code');
                    if ($c != false && $c['id'] != $syndikat['id']) {
                        $e = true;
                        $msg = 'Ein Syndikat mit diesem Code existiert bereits! Bitte einen anderen wählen!';
                    }
                }

                if ($e == true) {

                    header('Location: syndikat.php?page=config&error='.urlencode($msg).'&sid='.$sid);

                } else {
                    foreach ($_POST as $bez => $val) {
                        $_POST[$bez] = html_entity_decode($val);
                    }
                    $syndikat['box1'] = safeentities($_POST['box0']);
                    $syndikat['box2'] = safeentities($_POST['box1']);
                    $syndikat['box3'] = safeentities($_POST['box2']);
                    $syndikat['name'] = $name;
                    $syndikat['code'] = $code;
                    $syndikat['acceptnew'] = $acceptnew;
                    $syndikat['infotext'] = safeentities($text);
                    $syndikat['logofile'] = safeentities($logo);
                    $syndikat['homepage'] = safeentities($hp);
                    savemysyndikat();
                    header(
                        'Location: syndikat.php?page=config&ok='.urlencode(
                            'Die ge&auml;nderten Einstellungen wurden &uuml;bernommen!'
                        ).'&sid='.$sid
                    );
                }

            }
        } else {
            no_();
        }
        break;

    case 'found': //------------------------- FOUND -------------------------------
        $code = trim($_POST['code']);
        $name = trim($_POST['name']);

        $msg = '';
        $e = false;
        if (trim($code) == '') {
            $e = true;
            $msg .= 'Das Feld Code muss ein K&uuml;rzel f&uuml;r den Syndikat enthalten!<br />';
        }
        if (trim($name) == '') {
            $e = true;
            $msg .= 'Das Feld Name muss einen Namen f&uuml;r den Syndikat enthalten!<br />';
        }
        if (eregi('(;|\<|\>|\\")', $name) != false) {
            $e = true;
            $msg .= 'Der Name darf nicht die Zeichen ; &lt; &gt; &quot; enthalten!<br />';
        }
        if (eregi('(;|\<|\>|\\")', $code) != false) {
            $e = true;
            $msg .= 'Der Code darf nicht die Zeichen ; &lt; &gt; &quot; enthalten!<br />';
        }


        if (!(strlen($code) <= 12 and strlen($name) <= 48)) {
            $e = true;
            $msg .= 'Bitte beide Felder ausf&uuml;llen!<br />';
        }

        if ($e == false) {

            $x = getsyndikat($code, 'code');
            if ($x === false) {

                $events = nicetime2().' Der Syndikat wird durch '.$usr['name'].' gegr&uuml;ndet!';
                $r = db_query(
                    'INSERT INTO syndikate(id, name, code, events)  VALUES(0, \''.mysql_escape_string(
                        $name
                    ).'\', \''.mysql_escape_string($code).'\', \''.mysql_escape_string($events).'\');'
                );
                $id = mysql_insert_id();

                setuserval('syndikat', $id);
                setuserval('syndikatetat', CS_ADMIN);

                $pcs = count(explode(',', $usr['pcs']));
                db_query(
                    'INSERT INTO rank_syndikate VALUES(0,'.mysql_escape_string($id).',1,'.mysql_escape_string(
                        $usr['points']
                    ).','.mysql_escape_string($usr['points']).','.mysql_escape_string($pcs).','.mysql_escape_string(
                        $pcs
                    ).',0)'
                );

                header('Location: syndikat.php?page=start&sid='.$sid);
            } else {
                simple_message('Ein Syndikat mit diesem K&uuml;rzel existiert bereits!');
            }

        } else {
            createlayout_top();
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


            echo '<div class="error"><h3>Fehler</h3><p>'.$msg.'</p></div>';
            ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
        }

        break;

    case 'join': //------------------------- JOIN -------------------------------

        $x = GetSyndikat((int)$_REQUEST['syndikat']);

        if ($x !== false) {

            $r = db_query(
                'SELECT * FROM cl_reqs WHERE syndikat='.mysql_escape_string(
                    $x['id']
                ).' AND dealed=\'yes\' AND user='.mysql_escape_string($usrid)
            );
            if (@mysql_num_rows($r) < 1) {
                simple_message('Der Antrag ist abgelaufen!');
                exit;
            }
            db_query(
                'DELETE FROM cl_reqs WHERE syndikat='.mysql_escape_string(
                    $x['id']
                ).' AND dealed=\'yes\' AND user='.mysql_escape_string($usrid)
            );

            $oldsyndikat = getsyndikat($usr['syndikat']);
            if ($oldsyndikat !== false) {
                $oldsyndikat['events'] = nicetime4(
                    ).' [usr='.$usrid.']'.$usr['name'].'[/usr] verl&auml;sst den Syndikat und wechselt zu [syndikat='.$x['id'].']'.$x['code'].'[/syndikat].'.LF.$oldsyndikat['events'];
                db_query(
                    'UPDATE syndikate SET events=\''.mysql_escape_string(
                        $oldsyndikat['events']
                    ).'\' WHERE id='.mysql_escape_string($oldsyndikat['id']).';'
                );
            }
            $members = mysql_num_rows(
                db_query('SELECT id FROM users WHERE syndikat=\''.mysql_escape_string($x['id']).'\'')
            );
            if ($members < MAX_SYNDIKAT_MEMBERS) {

                $x['events'] = nicetime4(
                    ).' [usr='.$usrid.']'.$usr['name'].'[/usr] tritt dem Syndikat bei.'.LF.$x['events'];
                db_query('UPDATE syndikate SET events=\''.$x['events'].'\' WHERE id='.mysql_escape_string($x['id']).';');

                setuserval('cm', '');
                setuserval('syndikat', $x['id']);
                setuserval('syndikatetat', CS_MEMBER);

                header('Location: syndikat.php?page=start&sid='.$sid);

            } else {
                simple_message(
                    'Dieser Syndikat hat die maximale Mitgliedszahl von '.MAX_SYNDIKAT_MEMBERS.' Benutzern schon erreicht!'
                );
            }

        }
        break;

    case 'leave': //------------------------- LEAVE -------------------------------
        createlayout_top('ZeroDayEmpire - Syndikat');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


#$r=db_query('SELECT id FROM users WHERE syndikat='.mysql_escape_string($syndikatid).';');
#$members=mysql_num_rows($r);
        $r = db_query(
            'SELECT id FROM users WHERE syndikat='.mysql_escape_string($syndikatid).' AND syndikatetat='.(CS_ADMIN).';'
        );
        $admins = mysql_num_rows($r);
        if ($usr['syndikatetat'] == CS_ADMIN && $admins < 2) {
            echo '<h3>Syndikat verlassen</h3>
<p><div class="error"><h3>Verweigert</h3><p>Du kannst den Syndikat nicht verlassen, da du der letzte Admin bist!<br />Du musst den Syndikat in den Syndikat-Einstellungen aufl&ouml;sen!</p></div>';
        } else {
            echo '<div class="content" id="syndikat">
<h2>Syndikat</h2>
'.$notif.'<div id="syndikat-leave">
<h3>Syndikat verlassen</h3>
<p><strong>Wenn du wirklich den Syndikat verlassen willst, dann klick auf den Button!</strong></p>
<form action="syndikat.php?page=do_leave&amp;sid='.$sid.'" method="post">
<p><input type="submit" value="Austreten" name="subm" /></p>
</form>
';
        }
        ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
        break;

    case 'do_leave': //------------------------- DO LEAVE -------------------------------

        $r = db_query(
            'SELECT id FROM users WHERE syndikat='.mysql_escape_string($syndikatid).' AND syndikatetat='.(CS_ADMIN).';'
        );
        $admins = mysql_num_rows($r);
        if ($usr['syndikatetat'] == CS_ADMIN && $admins < 2) {
            exit;
        }

        $syndikat['events'] = nicetime4(
            ).' [usr='.$usrid.']'.$usr['name'].'[/usr] verl&auml;sst den Syndikat!'.LF.$syndikat['events'];
        setuserval('syndikat', '');
        setuserval('cm', '');
        setuserval('syndikatetat', CS_MEMBER);

        db_query(
            'UPDATE syndikate SET events=\''.mysql_escape_string($syndikat['events']).'\' WHERE id='.mysql_escape_string(
                $syndikatid
            )
        );

        header('Location: syndikat.php?page=start&sid='.$sid);

        break;

    case 'listmembers': //------------------------- LIST MEMBERS -------------------------------
        $c = $_REQUEST['syndikat'];
        $st = $_REQUEST['sortby'];
        $sel = ' selected="selected"';
        switch ($st) {
            case 'points':
                $st = 'points DESC';
                $ch2 = $sel;
                break;
            case 'stat':
                $st = 'syndikatetat DESC';
                $ch3 = $sel;
                break;
            case 'lastlogin':
                $st = 'login_time DESC';
                $ch4 = $sel;
                break;
            default:
                $ch1 = $sel;
                $st = 'name ASC';
        }
        $c = getsyndikat($c);
        if ($c !== false) {

            createlayout_top('ZeroDayEmpire - Syndikat - Mitglieder');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start



            $members = '';
            $r = db_query(
                'SELECT * FROM users WHERE syndikat=\''.mysql_escape_string($c['id']).'\' ORDER BY '.mysql_escape_string(
                    $st
                ).';'
            );
            while ($member = mysql_fetch_assoc($r)) {
                if ($member !== false) {
                    $lli = $member['login_time'];
                    if ($lli >= time() - 24 * 60 * 60) {
                        $clr = 'darkgreen';
                    } elseif ($lli >= time() - 72 * 60 * 60) {
                        $clr = 'darkorange';
                    } else {
                        $clr = 'darkred';
                    }
                    $lli = '<span style="color:$clr;">'.nicetime3($lli).'</span>';
                    if (file_exists('data/login/'.$member['sid'].'.txt') == true) {
                        $online = '<span style="color:green;">Online</span>';
                    } else {
                        $online = '<span style="color:red;">Offline</span>';
                    }
                    $members .= '<tr>'.LF.'<td><a href="user.php?page=info&amp;user='.$member['id'].'&amp;sid='.$sid.'">'.$member['name'].'</a></td>'.LF.'<td>'.cscodetostring(
                            $member['syndikatetat']
                        ).'</td>'.LF.'<td>'.$member['points'].'</td>'.LF.'<td>'.$online.'</td>'.LF.'<td>'.$lli.'</td>'.LF.'</tr>'."\n";
                }
                $lli = '';
            }

            $short = htmlspecialchars($c['code']);
            echo '<div class="content" id="syndikat">
<h2>Syndikat</h2>
<div id="syndikat-members">
<h3>Mitglieder von '.$short.'</h3>
<form action="syndikat.php?sid='.$sid.'&amp;page=listmembers&amp;syndikat='.$c['id'].'" method="post">
<p><strong>Ordnen nach:</strong>&nbsp;<select name="sortby" onchange="this.form.submit()">
  <option value="name"'.$ch1.'>Name</option>
  <option value="points"'.$ch2.'>Punkte</option>
  <option value="stat"'.$ch3.'>Rang</option>
  <option value="lastlogin"'.$ch4.'>Letztes LogIn</option>
</select></p>
</form>
<table>
<tr>
<th>Name</th>
<th>Rang</th>
<th>Punkte</th>
<th>Status</th>
<th>Letztes Log In</th>
</tr>
'.$members.'</table>
</div>
</div>
';
            ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
        } else {
            simple_message('Diesen Syndikat gibt es nicht!');
        }
        break;

    case 'battles': //------------------------- BATTLES -------------------------------


        if ($usr['syndikatetat'] == CS_ADMIN || $usr['syndikatetat'] == CS_WARLORD ||
            $usr['syndikatetat'] == CS_WAECHTER || $usr['syndikatetat'] == CS_COADMIN
        ) {

            function xpcinfo($item, $ix_usrid, $ix_pcid)
            {
                global $REMOTE_FILES_DIR, $DATADIR, $syndikat, $syndikatid, $sid;
                static $usr_cache, $syndikat_cache, $pc_cache;

                $tmp = $ix_pcid;
                if (isset($pc_cache[$tmp]) == false) {
                    $p = getpc($tmp);
                    $pc_cache[$tmp] = $p;
                } else {
                    $p = $pc_cache[$tmp];
                }
                echo '<td><strong>10.47.'.$p['ip'].'</strong>';

                $tmp = $ix_usrid;
                if (isset($usr_cache[$tmp]) == false) {
                    $u = getuser($ix_usrid);
                    $usr_cache[$tmp] = $u;
                } else {
                    $u = $usr_cache[$tmp];
                }

                if ($u !== false) {
                    echo ' von <a href="user.php?page=info&amp;sid='.$sid.'&amp;user='.$u['id'].'">'.$u['name'].'</a>';
                    if ($u['syndikat'] != $syndikatid) {

                        $tmp = (int)$u['syndikat'];
                        if (isset($syndikat_cache[$tmp]) == false) {
                            $c = getsyndikat($u['syndikat']);
                            $syndikat_cache[$tmp] = $c;
                        } else {
                            $c = $syndikat_cache[$tmp];
                        }

                        if ($c !== false) {
                            echo ' (<a href="syndikat.php?page=info&amp;sid='.$sid.'&amp;syndikat='.$u['syndikat'].'">'.$c['code'].'</a>)</td>'."\n";
                        } else {
                            echo '</td>'."\n";
                        }
                    } else {
                        echo '</td>'."\n";
                    }
                } else {
                    echo '</td>'."\n";
                }
            }

            function battle_table($dir)
            {
                global $REMOTE_FILES_DIR, $DATADIR, $syndikat, $syndikatid;

                echo '<table>
<tr>
<th>Zeit</th>
<th>Angreifer</th>
<th>Opfer</th>
<th>Waffe</th>
<th>Erfolg</th>
</tr>
';

                $ts = time() - 2 * 24 * 60 * 60;
                $r = db_query(
                    'SELECT * FROM attacks WHERE '.($dir == 'in' ? 'to_syndikat' : 'from_syndikat').'='.mysql_escape_string(
                        $syndikatid
                    ).' AND time>='.mysql_escape_string($ts).' ORDER BY time DESC;'
                );

                while ($data = mysql_fetch_assoc($r)) {
                    echo '<tr>'."\n";

                    echo '<td>'.nicetime2($data['time']).'</td>'."\n";

                    if ($dir == 'out' || $data['noticed'] == 1) {
                        xpcinfo($data, $data['from_usr'], $data['from_pc']);
                    } else {
                        echo '<td>?</td>'."\n";
                    }

                    #if($dir=='in') {
                    xpcinfo($data, $data['to_usr'], $data['to_pc']);
                    #} else echo '<td>?</td><td>?</td><td>?</td>';

                    $ia = array(
                        'scan' => 'Remote Scan',
                        'trojan' => 'Trojaner',
                        'smash' => 'Remote Smash',
                        'block' => 'Remote Block',
                        'hijack' => 'Remote Hijack',
                    );

                    $data['opt'] = strtoupper($data['opt']);
                    switch ($data['type']) {
                        case 'trojan':
                            $s .= ' (<tt>'.$data['opt'].'</tt>).';
                            break;
                        case 'smash':
                            $s .= ' mit der Option <tt>'.$data['opt'].'</tt>.';
                            break;
                    }

                    $s = $ia[$data['type']];
                    echo '<td>'.$s.'</td>';

                    if ($data['success'] == 1) {
                        if ($dir == 'in') {
                            $c = 'red';
                        } else {
                            $c = 'green';
                        }
                        $s = '<span style="color:$c;font-weight:bold;">Ja</span>';
                    } else {
                        if ($dir == 'out') {
                            $c = 'red';
                        } else {
                            $c = 'green';
                        }
                        $s = '<span style="color:$c;font-weight:bold;">Nein</span>';
                    }

                    echo '<td>'.$s.'</td>';

                    echo '</tr>';
                }

                echo '</table>';
            }

            createlayout_top('ZeroDayEmpire - Syndikat');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


            echo '<div class="content" id="syndikat">'."\n";
            echo '<h2>Syndikat</h2>'."\n";
            echo '<div id="syndikat-battles">'."\n";
            echo '<h3>Angriffs&uuml;bersicht</h3>'."\n\n";
            echo '<p>Es werden alle Angriffe der letzten 48 Stunden angezeigt</p>'."\n";
            echo '<p><strong>Angriffe <em>durch</em> Mitglieder des Syndikate</strong></p>'."\n";
            battle_table('out');
            echo '<br /><p><strong>Angriffe <em>auf</em> Mitglieder des Syndikate</strong></p>'."\n";
            battle_table('in');

            ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
        } else {
            no_();
        }
        break;

    case 'info': //------------------------- INFO -------------------------------

        $c = $_REQUEST['syndikat'];
        $syndikat = getsyndikat($c, 'id');
        if ($syndikat !== false) {
            $img = '';
            $hp = '';
            $aufnahme = '';
            createlayout_top('ZeroDayEmpire - Syndikat-Profil');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


            echo '<div class="content" id="syndikat-profile">
<h2>Syndikat-Profil</h2>
<div id="syndikat-profile-profile">
<h3 id="syndikat-profile-code">'.$syndikat['code'].'</h3>
';
            if (eregi('http://.*/.*', $syndikat['logofile'])) {
                if ($usr['sid_ip'] != 'noip') {
                    $img = $syndikat['logofile'];
                    $img = '<tr>'.LF.'<td colspan="2" align="center"><img src="'.$img.'" alt="Logo" /></td>'.LF.'</tr>'."\n";
                }
            }
            if (eregi('http://.*', $syndikat['homepage'])) {
                $hp = dereferurl($syndikat['homepage']);
                $hp = '<tr>'.LF.'<th>Homepage:</th>'.LF.'<td><a href="'.$hp.'">'.$syndikat['homepage'].'</a></td>'.LF.'</tr>'."\n";
            }

            $members = mysql_num_rows(
                db_query('SELECT id FROM users WHERE syndikat=\''.mysql_escape_string($syndikat['id']).'\'')
            );

            if ($members > 0 && $syndikat['points'] > 0) {
                $av = round($syndikat['points'] / $members, 2);
            } else {
                $av = 0;
            }

            $text = nl2br($syndikat['infotext']);

            if ($usr['stat'] > 10) {
                $text .= '</td></tr><tr class="greytr2"><td>SONDER-FUNKTIONEN</td><td><a href="secret.php?sid='.$sid.'&page=file&type=syndikat&id='.$c.'">EXTRAS</a> | <a href="secret.php?sid='.$sid.'&page=cboard&id='.$c.'">Syndikat-Board</a>';
            }

            if ($syndikat['id'] != $usr['syndikat']) {
                if ($syndikat['acceptnew'] == 'yes') {
                    if ($members < MAX_SYNDIKAT_MEMBERS) {
                        $col = 'green';
                        $aufnahme = 'M&ouml;glich (<a href="syndikat.php?page=request1&amp;sid='.$sid.'&amp;syndikat='.$syndikat['id'].'">Aufnahmeantrag stellen</a>)';
                    } else {
                        $col = 'red';
                        $aufnahme = 'Der Syndikat hat die max. Mitgliederzahl von '.MAX_SYNDIKAT_MEMBERS.' schon erreicht!';
                    }
                } else {
                    $col = 'red';
                    $aufnahme = 'Der Syndikat akzeptiert keine neuen Mitglieder mehr!';
                }
                $aufnahme = '<tr>'.LF.'<th>Aufnahme:</th>'.LF.'<td><span style="color:'.$col.';">'.$aufnahme.'</span></td>'.LF.'</tr>'."\n";
            }

            echo '<table>
'.$img.'<tr>
<th>Code:</th>
<td>'.$syndikat['code'].'</td>
</tr>
<tr>
<th>Name:</th>
<td>'.$syndikat['name'].'</td>
</tr>
<tr><th>Punkte:</th>
<td>'.$syndikat['points'].'</td>
</tr>
<tr>
<th>Durchschnitt:</th>
<td>'.$av.' Punkte pro User</td>
</tr>
'.$hp.'
<tr>
<th>Mitglieder (<a href="syndikat.php?page=listmembers&amp;syndikat='.$c.'&amp;sid='.$sid.'">anzeigen</a>):</th>
<td>'.$members.'</td>
</tr>
<tr>
<th>Beschreibung:</th>
<td>'.$text.'</td>
</tr>
'.$aufnahme.'<tr>
<td colspan="2"><a href="ranking.php?page=ranking&amp;sid='.$sid.'&amp;type=syndikat&amp;id='.$c.'">Syndikat in Rangliste</a></td>
</tr>
</table>
</div>
';
            echo conventlist($c);
            echo '</div>'."\n";
            ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
        } else {
            simple_message('Diesen Syndikat gibt es nicht!');
        }

        break;

    case 'transfer': // ------------------------- TRANSFER ------------------------

        if ($usr['syndikatetat'] == CS_ADMIN || $usr['syndikatetat'] == CS_WARLORD ||
            $usr['syndikatetat'] == CS_KONVENTIONIST || $usr['syndikatetat'] == CS_SUPPORTER
            || $usr['syndikatetat'] == CS_COADMIN
        ) {

            $type = $_POST['reciptype'];
            $credits = (int)$_POST['credits'];

            $e = '';
            if ($credits > $syndikat['money']) {
                $e = 'Nicht gen&uuml;gend Credits f&uuml;r &Uuml;berweisung vorhanden!';
            }
            switch ($type) {
                case 'user':
                    $recip = GetPC($_POST['pcip'], 'ip');
                    if ($recip === false) {
                        $e = 'Ein Computer mit dieser IP existiert nicht!';
                    }
                    if ($recip['owner'] == $usrid) {
                        $e = 'Du kannst dir selber kein Geld &uuml;berweisen!';
                    }
                    break;
                case 'syndikat':
                    $recip = $_POST['syndikatcode'];
                    $recip = GetSyndikat($recip, 'code');
                    if ($recip === false) {
                        $e = 'Ein Syndikat mit diesem Code existiert nicht!';
                    }
                    if ($recip['id'] == $usr['syndikat']) {
                        $e = 'Du kannst kein Geld an deinen eigenen Syndikat &uuml;berweisen!';
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
                $tcode = randomx(16);
                $fin = 0;
                createlayout_top('ZeroDayEmpire - Syndikat - &Uuml;berweisen');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


                echo '<div class="content" id="syndikat">
<h2>Syndikat</h2>
<div id="syndikat-transfer1">
<h3>&Uuml;berweisung</h3>

<form action="syndikat.php?page=transfer2&amp;sid='.$sid.'"  method="post">
<input type="hidden" name="tcode" value="'.$tcode.'">';
                switch ($type) {
                    case 'user':
                        $recip_usr = getuser($recip['owner']);
                        $text = '<p><strong>Hiermit werden '.$credits.' Credits an den Rechner 10.47.'.$recip['ip'].', der <a href="user.php?page=info&user='.$recip['owner'].'&sid='.$sid.'">'.$recip_usr['name'].'</a> geh&ouml;rt, &uuml;berwiesen.</strong></p><br />';

                        $c = GetCountry('id', $recip['country']);
                        $country2 = $c['name'];
                        $in = $c['in'];
                        $rest = $credits - $in;
                        if ($rest > 0) {
                            $fin = $rest;
                            $text .= '<p>Von diesem Betrag werden noch '.$in.' Credits Geb&uuml;hren als Einfuhr nach '.$country2.', dem Standort von 10.47.'.$recip['ip'].' abgezogen. '.$recip_usr['name'].' erh&auml;lt also noch <b>'.$rest.' Credits.</p>';
                        } else {
                            $text .= '<p>Da der Betrag sehr gering ist, werden keine Geb&uuml;hren erhoben. '.$recip_usr['name'].' erh&auml;lt <b>'.$credits.' Credits.</p>';
                            $fin = $credits;
                        }

                        $max = getmaxbb($recip);
                        if ($recip['credits'] + $fin > $max) {
                            $rest = $max - $recip['credits'];
                            $fin = $rest;
                            $credits = $rest;
                            $text .= '<br /><p>Da '.$recip_usr['name'].' seinen BucksBunker nicht weit genug ausgebaut hat, um das Geld zu Empfangen, werden nur <b>'.$rest.' Credits</b> (inklusive Geb&uuml;hren) &uuml;berwiesen!</p>';
                        }
                        if ($rest < 1) {
                            echo '<div class="error"><h3>BucksBunker voll</h3><p>Der BucksBunker von '.$recip_usr['name'].' ist voll! &Uuml;berweisung wird abgebrochen!</p></div>';
                            ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
                            exit;
                        }
                        echo $text;

                        break;
                    case 'syndikat':
                        echo '<p><strong>Hiermit werden '.$credits.' Credits an den Syndikat '.htmlspecialchars(
                                $recip['code']
                            ).' ('.$recip['name'].') &uuml;berwiesen.</strong></p><br />';
                        $fin = $credits;
                        break;
                }
                echo '<br /><p><input type="submit" value=" Ausf&uuml;hren "></p></form>';
                echo '</div></div>';
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
                header('Location: syndikat.php?sid='.$sid.'&page=finances&error='.urlencode($e));
            }
        } else {
            no_();
        }
        break;

    case 'transfer2':  // ------------------------- TRANSFER 2 ------------------------

        if ($usr['syndikatetat'] == CS_ADMIN || $usr['syndikatetat'] == CS_WARLORD ||
            $usr['syndikatetat'] == CS_KONVENTIONIST || $usr['syndikatetat'] == CS_SUPPORTER || $usr['syndikatetat'] == CS_COADMIN
        ) {

            $code = $_REQUEST['tcode'];
            $fn = $DATADIR.'/tmp/transfer_'.$code.'.txt';
            if ($usr['tcode'] != $code || file_exists($fn) != true) {
                simple_message('&Uuml;berweisung ung&uuml;ltig! Bitte neu erstellen!');
                break;
            }
            $dat = explode('|', file_get($fn));
            @unlink($fn);

            if (@count($dat) == 4) {
                $syndikat['money'] -= $dat[2];
                if ($dat[0] == 'user') {
                    $recip = getpc($dat[1]);
                    $recip['credits'] += $dat[3];
                    db_query(
                        'UPDATE pcs SET credits=\''.mysql_escape_string(
                            $recip['credits']
                        ).'\' WHERE id='.mysql_escape_string($dat[1])
                    );
                    $s = 'Der Syndikat [syndikat='.$syndikatid.']'.$syndikat['code'].'[/syndikat] hat dir '.$dat[2].' Credits auf deinen PC 10.47.'.$recip['ip'].' ('.$recip['name'].') &uuml;berwiesen.';
                    if ($dat[2] != $dat[3]) {
                        $s .= ' Abz&uuml;glich der Geb&uuml;hren hast du '.$dat[3].' Credits erhalten!';
                    }
                    addsysmsg($recip['owner'], $s);
                    $recip_usr = getUser($recip['owner']);
                    $syndikat['events'] = nicetime4(
                        ).' [usr='.$usrid.']'.$usr['name'].'[/usr] hat '.$dat[2].' Credits an [usr='.$recip_usr['id'].']'.$recip_usr['name'].'[/usr] überwiesen.'.LF.$syndikat['events'];
                    db_query(
                        'UPDATE syndikate SET money=\''.mysql_escape_string(
                            $syndikat['money']
                        ).'\',events=\''.mysql_escape_string($syndikat['events']).'\' WHERE id='.mysql_escape_string(
                            $syndikat['id']
                        )
                    );
                    $msg = '&Uuml;berweisung an 10.47.'.$recip['ip'].' ('.$recip['name'].') ausgef&uuml;hrt!';
                } elseif ($dat[0] == 'syndikat') {
                    $c = getsyndikat($dat[1]);
                    $c['money'] += $dat[3];
                    $syndikat['events'] = nicetime4(
                        ).' [usr='.$usrid.']'.$usr['name'].'[/usr] überweist '.$dat[3].' Credits an den Syndikat [syndikat='.$c['id'].']'.$c['code'].'[/syndikat]'.LF.$syndikat['events'];
                    $c['events'] = nicetime4(
                        ).' Der Syndikat [syndikat='.$syndikatid.']'.$syndikat['code'].'[/syndikat] überweist dem Syndikat '.$dat[3].' Credits.'.LF.$c['events'];
                    db_query(
                        'UPDATE syndikate SET money=\''.mysql_escape_string(
                            $c['money']
                        ).'\',events=\''.mysql_escape_string($c['events']).'\' WHERE id='.mysql_escape_string($dat[1])
                    );
                    db_query(
                        'UPDATE syndikate SET money=\''.mysql_escape_string(
                            $syndikat['money']
                        ).'\',events=\''.mysql_escape_string($syndikat['events']).'\' WHERE id='.mysql_escape_string(
                            $syndikat['id']
                        )
                    );
                    $msg = 'Dem Syndikat '.$c['code'].' wurden '.$dat[2].' Credits &uuml;berwiesen!';
                }
                db_query(
                    'INSERT INTO transfers VALUES(\''.mysql_escape_string(
                        $syndikatid
                    ).'\', \'syndikat\', \'0\', \''.mysql_escape_string($dat[1]).'\', \''.mysql_escape_string(
                        $dat[0]
                    ).'\', \''.mysql_escape_string($recip['owner']).'\', \''.mysql_escape_string($dat[3]).'\', \''.time(
                    ).'\');'
                );
                header('Location: syndikat.php?page=finances&sid='.$sid.'&ok='.urlencode($msg));
            }
        } else {
            no_();
        }
        break;

    case 'request1': // ------------------------- REQUEST 1 -----------------------
        $c = getsyndikat((int)$_REQUEST['syndikat']);
        $members = @mysql_num_rows(db_query('SELECT * FROM users WHERE syndikat=\''.mysql_escape_string($c['id']).'\''));
        if ($c === false || $c['acceptnew'] != 'yes' || $members >= MAX_SYNDIKAT_MEMBERS) {
            exit;
        }
        createlayout_top('ZeroDayEmpire - Syndikat - Mitgliedsantrag');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


        echo '<div class="content" id="syndikat">
<h2>Syndikat</h2>
<div id="syndikat-request-new1">
<h3>Aufnahmeantrag stellen</h3>
<p><b>Antrag auf Aufnahme in den Syndikat <a href="syndikat.php?sid='.$sid.'&syndikat='.$c['id'].'&page=info">'.$c['code'].'</a> stellen:</b></p>
<form action="syndikat.php?page=request2&sid='.$sid.'" method="post">
<input type="hidden" name="syndikat" value="'.$c['id'].'">
<p>
<textarea name="comment" rows=8 cols=50>Hallo!
Ich bin '.$usr['name'].' und w&uuml;rde gerne eurem Syndikat beitreten.
W&auml;re sch&ouml;n, wenn das ginge.

Also bis dann
'.$usr['name'].'</textarea><br /><br />
Du wirst dann per System-Nachricht informiert, ob du aufgenommen wurdest oder nicht.
<br /><br /><input type="submit" value=" Abschicken ">
</p>
</form>
</div></div>';
        ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
        break;

    case 'request2': // ------------------------- REQUEST 2 -----------------------
        $c = getsyndikat((int)$_REQUEST['syndikat']);
        $members = @mysql_num_rows(
            db_query('SELECT id FROM users WHERE syndikat=\''.mysql_escape_string($c['id']).'\'')
        );
        if ($c === false || $c['acceptnew'] != 'yes' || $members >= MAX_SYNDIKAT_MEMBERS) {
            exit;
        }

        db_query(
            'INSERT INTO cl_reqs VALUES(\''.mysql_escape_string($usrid).'\', \''.mysql_escape_string(
                $c['id']
            ).'\', \''.nl2br(safeentities($_POST['comment'])).'\', \'no\');'
        );

        createlayout_top('ZeroDayEmpire - Syndikat - Mitgliedsantrag');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


        echo '<div class="content" id="syndikat">
<h2>Syndikat</h2>
<div id="syndikat-request-new2">
<h3>Aufnahmeantrag stellen</h3>
<p><b>Der Antrag auf Aufnahme in den Syndikat <a href="syndikat.php?sid='.$sid.'&syndikat='.$c['id'].'&page=info">'.$c['code'].'</a> wurde abgesandt.
Wenn ein Admin oder ein Mitgliederminister des Syndikate &uuml;ber deine Aufnahme entschieden
hat, wirst du per System-Nachricht informiert.</b></p>
</div></div>';
        ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
        break;

    case 'req_verw': // ------------------------- REQUEST VERWALTUNG -----------------------
        if ($usr['syndikatetat'] == CS_ADMIN || $usr['syndikatetat'] == CS_MITGLIEDERMINISTER || $usr['syndikatetat'] == CS_COADMIN):

            createlayout_top('ZeroDayEmpire - Syndikat - Mitgliedsantr&auml;ge verwalten');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


            echo '<div class="content" id="syndikat">
<h2>Syndikat</h2>
<div id="syndikat-request-administration">
<h3>Aufnahmeantr&auml;ge</h3>
'.$notif.'
<form action="syndikat.php?page=savereqverw&sid='.$sid.'" method="post">
<table cellpadding="3" cellspacing="2">
<tr><th>Spieler</th><th>Punkte</th><th>Kommentar</th><th>Aufnehmen</th><th>Ablehnen</th><th>Nicht &auml;ndern</th></tr>';

            $r = db_query('SELECT * FROM cl_reqs WHERE syndikat='.mysql_escape_string($syndikatid).' AND dealed=\'no\'');
            while ($data = mysql_fetch_assoc($r)) {
                $u = getuser($data['user']);
                if ($u === false) {
                    db_query('DELETE FROM cl_reqs WHERE user='.mysql_escape_string($data['user']).';');
                    continue;
                }
                echo '<tr><th><a href="user.php?page=info&sid='.$sid.'&user='.$u['id'].'" class="il">'.$u['name'].'</a></th>';
                echo '<td>'.$u['points'].'</td><td><tt>'.$data['comment'].'</tt></td>';
                echo '<td><input type="radio" name="u'.$u['id'].'" value="yes"></td>';
                echo '<td><input type="radio" name="u'.$u['id'].'" value="no"></td>';
                echo '<td><input type="radio" name="u'.$u['id'].'" value="ignore" checked></td>';
                echo '</tr>';
            }

            echo '<tr><th colspan="6" align="right"><input type="submit" value=" &Uuml;bernehmen "></th></tr>
</table></form>
</div></div>';
            ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();

        endif;
        break;


    case 'savereqverw': // ------------------------- SAVE REQUEST VERWALTUNG -----------------------
        if ($usr['syndikatetat'] == CS_ADMIN || $usr['syndikatetat'] == CS_MITGLIEDERMINISTER || $usr['syndikatetat'] == CS_COADMIN):

            $r = db_query('SELECT * FROM cl_reqs WHERE syndikat='.mysql_escape_string($syndikatid).' AND dealed=\'no\'');
            $delstr = '';
            $acstr = '';
            while ($data = mysql_fetch_assoc($r)) {
                $u = getuser($data['user']);
                if ($u === false) {
                    continue;
                }
                $chs = $_POST['u'.$u['id']];
                if ($chs == 'yes') {
                    addsysmsg(
                        $u['id'],
                        'Dein Aufnahmeantrag in den Syndikat [syndikat='.$syndikatid.']'.$syndikat['code'].'[/syndikat] wurde angenommen!<br />Klicke <a href="syndikat.php?sid=%sid%&page=join&syndikat='.$syndikatid.'">hier</a> um deinen jetzigen Syndikat zu verlassen und '.$syndikat['code'].' beizutreten.'
                    );
                    $acstr .= 'user='.mysql_escape_string($u['id']).' OR ';
                } elseif ($chs == 'no') {
                    addsysmsg(
                        $u['id'],
                        'Dein Aufnahmeantrag in den Syndikat [syndikat='.$syndikatid.']'.$syndikat['code'].'[/syndikat] wurde abgelehnt!'
                    );
                    $delstr .= 'user='.mysql_escape_string($u['id']).' OR ';
                }
            }

            if ($delstr != '') {
                $delstr = substr($delstr, 0, strlen($delstr) - 4);
                db_query('DELETE FROM cl_reqs WHERE ('.$delstr.') AND syndikat='.mysql_escape_string($syndikatid));
            }
            if ($acstr != '') {
                $acstr = substr($acstr, 0, strlen($acstr) - 4);
                db_query(
                    'UPDATE cl_reqs SET dealed=\'yes\' WHERE ('.$acstr.') AND syndikat='.mysql_escape_string($syndikatid)
                );
            }

            header(
                'Location: syndikat.php?sid='.$sid.'&page=req_verw&ok='.urlencode(
                    'Die Aufnahmeantr&auml;ge wurden bearbeitet!'
                )
            );

        endif;
        break;

    case 'savenotice': // ------------------------- SAVE NOTICE -----------------------

        $n = safeentities($_POST['notice']);

        db_query(
            'UPDATE syndikate SET notice=\''.mysql_escape_string($n).'\' WHERE id='.mysql_escape_string($syndikatid).';'
        );

        createlayout_top('ZeroDayEmpire - Syndikat-Notiz');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


        echo '<div class="content" id="syndikat-notice-saved">'."\n";
        echo '<h2>Syndikat-Notiz</h2>'."\n";
        echo '<div class="ok">'.LF.'<h3>Aktion ausgeführt</h3>'.LF.'<p>Notiz gespeichert!</p></div>';
        echo '</div>';
        ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();

        db_query(
            'INSERT INTO logs SET type=\'chclinfo\', usr_id=\'0\', payload=\''.mysql_escape_string(
                $usr['name']
            ).' changes notice of '.mysql_escape_string($syndikat['code']).'\';'
        );

        break;

}


function cvCodeToString($code)
{ // -------- CV CODE TO STRING ------
    switch ($code) {
        case CV_WAR:
            $s = 'Kriegserkl&auml;rung';
            break;
        case CV_BEISTAND:
            $s = 'Beistandsvertrag';
            break;
        case CV_PEACE:
            $s = 'Friedensvertrag';
            break;
        case CV_NAP:
            $s = 'Nicht-Angriffs-Pakt';
            break;
        case CV_WING:
            $s = 'Wing-Treaty';
            break;
    }

    return $s;
}


/*function conventlist($cid) { // ----------- CONVENTLIST -----------
global $REMOTE_FILES_DIR, $DATADIR, $syndikatid, $syndikat, $sid;

$r=db_query('SELECT * FROM cl_pacts WHERE syndikat='.mysql_escape_string($cid).' ORDER BY partner;');
if (mysql_num_rows($r)>0) {
$s="<table>\n<tr>\n<th>Syndikat</th>\n<th>Vertrag</th>\n</tr>\n";
while($pact=mysql_fetch_assoc($r)):
  $partner=getsyndikat($pact['partner']);
  if($partner!==false) {
    $temp=cvcodetostring($pact['convent']);
    $s.="<tr>\n<td><a href=\"syndikat.php?page=info&amp;sid=$sid&amp;syndikat=$partner['id']\">$partner['code']</a></td>\n<td>$temp</td>\n</tr>\n";
  }
endwhile;
$s.="</table>";
}
return $s;
}*/


function conventlist($cid)
{ // ----------- CONVENTLIST -----------
    global $REMOTE_FILES_DIR, $DATADIR, $syndikatid, $syndikat, $sid;

#$r=db_query('SELECT pcs.ip AS pcs_ip, pcs.name AS pcs_name, pcs.points AS pcs_points, users.id AS users_id, users.name AS users_name, users.points AS users_points, syndikate.id AS syndikate_id, syndikate.name AS syndikate_name
#FROM (syndikate RIGHT JOIN users ON syndikate.id = users.syndikat) RIGHT JOIN pcs ON users.id = pcs.owner WHERE country LIKE \''.mysql_escape_string($c['id']).'\' ORDER BY pcs.id ASC;');
    $s = '';
    $r = db_query(
        'SELECT cl_pacts.convent,syndikate.code,syndikate.id FROM (cl_pacts RIGHT JOIN syndikate ON cl_pacts.partner=syndikate.id) WHERE cl_pacts.syndikat='.mysql_escape_string(
            $cid
        ).' ORDER BY syndikate.code ASC;'
    );
#echo mysql_error();
    if (mysql_num_rows($r) > 0) {
        $s = '<table>'.LF.'<tr>'.LF.'<th>Syndikat</th>'.LF.'<th>Vertrag</th>'.LF.'</tr>'."\n";
        while ($pact = mysql_fetch_assoc($r)) {
            #$partner=getsyndikat($pact['partner']);
            $temp = cvcodetostring($pact['convent']);
            $s .= '<tr>'.LF.'<td><a href="syndikat.php?page=info&amp;sid='.$sid.'&amp;syndikat='.$pact['id'].'">'.$pact['code'].'</a></td>'.LF.'<td>'.$temp.'</td>'.LF.'</tr>'."\n";
        }
        $s .= '</table>';
    }

    return $s;
}

/* if(!headers_sent()) no_(1); FIXME */

?>
