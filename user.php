<?php

define('IN_ZDE', 1);
$FILE_REQUIRES_PC = false;
include('ingame.php');

$action = $_REQUEST['page']
    ?? $_REQUEST['mode']
    ?? $_REQUEST['action']
    ?? $_REQUEST['a']
    ?? $_REQUEST['m']
    ?? '';

switch ($action) {
    case 'config': //------------------------- CONFIG -------------------------------

        createlayout_top('ZeroDayEmpire - Optionen');
?>
<!-- ZDE theme inject -->
<style>@import url("style.css");</style>
<div class="container">
<?php // /ZDE theme inject start



        echo '<div class="content" id="settings">
<h2>Optionen</h2>';

        echo '<br /><br />';

        foreach ($usr as $bez => $val) {
            $usr[$bez] = safeentities(html_entity_decode($val));
        }

        $m = '';
        $w = '';
        $x = '';
        if ($usr['gender'] == 'x') {
            $x = ' checked="checked"';
        } elseif ($usr['gender'] == 'm') {
            $m = ' checked="checked"';
        } elseif ($usr['gender'] == 'w') {
            $w = ' checked="checked"';
        }

        $dd = explode('.', $usr['birthday']);
        $dayVal = sprintf('%02d', (int)$dd[0]);
        $monthVal = sprintf('%02d', (int)$dd[1]);
        $yearVal = sprintf('%04d', (int)$dd[2]);

        $statx = '';
        if ($usr['stat'] > 1) {
            $statx = '<tr>'.LF.'<th>Dein Status:</th>'.LF.'<td>privilegiert<br />F&uuml;r die Sonderfunktionen rufe die Info-Seite eines Users auf!</td>'.LF.'</tr>'."\n";
        }
        if ($usr['stat'] == 1000) {
            $statx = '<tr>'.LF.'<th>Dein Status:</th>'.LF.'<td>King</td>'.LF.'</tr>'."\n";
        }

        if ($usr['bigacc'] == 'yes') {
            $account = 'Extended Account (werbefrei und Adressbuch)';
        } elseif ($usr['ads'] == 'no') {
            $account = 'werbefrei';
        } else {
            $account = 'normal';
        }

        $avatar = '';
        if (preg_match('#^https?://.*/.+#i', $usr['avatar'])) {
            $avatar = '<br />'.LF.'<img src="'.$usr['avatar'].'" alt="Avatar" />';
        }
        /*
        if($usr['bigacc']=='yes') {
          #$usessl=($usr['usessl']=='yes' ? 'checked="checked" ' : 'no');
          #$usessl='<input type="checkbox" value="yes" name="usessl" '.$usessl.'/>';
          $usessl='<em>Diese Funktion steht in K&uuml;rze f&uuml;r alle Extended Account-User zur Verf&uuml;gung</em>';
        } else {
          $usessl='<em>Diese Funktion steht nur in Extended Accounts zur Verf&uuml;gung</em>';
        }
        $usessl.="\n";*/

        echo '<div class="submenu">
<p>';

        if ($usr['bigacc'] == 'yes') {
            echo '<a href="abook.php?mode=admin&amp;sid='.$sid.'">Adressbuch verwalten</a>';
        }
#else echo '<a href="pub.php?d=extacc">Extended Account bestellen</a>'; # la la la

        if ($usr['bigacc'] == 'yes') {
            $dirname = dirname($_SERVER['PHP_SELF']);
            $dirname = str_replace($dirname, "\\", '/');
            $dirname = (strlen($dirname) > 0 && substr(
                $dirname,
                strlen($dirname) - 1,
                1
            ) != '/' ? $dirname.'/' : $dirname);
            $url = 'http://zdesrv.org/usrimg.php/'.$server.'-'.$usrid.'.png';
            $usrimg = ($usr['enable_usrimg'] != 'yes' ? '' : 'checked="checked" ');
            $usrimg = '<input type="checkbox" value="yes" name="enable_usrimg" '.$usrimg.'/>
 URL des Bildes: <a href="'.$url.'">'.$url.'</a>';
        } else {
            $usrimg = '<em>Diese Funktion steht nur in Extended Accounts zur Verf&uuml;gung!</em>';
        }

        echo '</p>
</div>
'.$notif.'<div id="settings-settings">
<h3>'.$usr['name'].'</h3>
<form action="user.php?a=saveconfig&amp;sid='.$sid.'" method="post">
<table>
<tr id="settings-settings-account">
<th>Account-Typ:</th>
<td>'.$account.'</td>
</tr>
<tr id="settings-settings-gender">
<th>Geschlecht:</th>
<td><input type="radio" name="sex" value="m" id="sm"'.$m.' />M&auml;nnlich <input type="radio" name="sex" value="w" id="sw"'.$w.' />Weiblich <input type="radio" name="sex" value="x" id="sx"'.$x.' />Keine Angabe</td>
</tr>
<tr id="settings-settings-date-of-birth">
<th>Geburtsdatum:</th>
<td><input type="text" name="bday" value="'.$dayVal.'" size="2" maxlength="2" />.<input type="text" name="bmonth" value="'.$monthVal.'" size="2" maxlength="2" />.<input type="text" name="byear" value="'.$yearVal.'" size="4" maxlength="4" /></td>
</tr>
<tr id="settings-settings-homepage">
<th>Deine Homepage:</th>
<td><input type="text" name="homepage" value="'.$usr['homepage'].'" maxlength="100" /></td>
</tr>
<tr id="settings-settings-city">
<th>Wohnort:</th>
<td><input type="text" name="ort" value="'.$usr['wohnort'].'" /></td>
</tr>
<tr id="settings-settings-description">
<th>Beschreibung (max. 2048 Zeichen):</th>
<td><textarea name="aboutme" rows="5" cols="50">'.$usr['infotext'].'</textarea></td>
</tr>
<tr id="settings-settings-avatar">
<th>Avatar-Bild (http://&nbsp;...):</th>
<td><input type="text" name="avatar" value="'.$usr['avatar'].'" />'.$avatar.'</td>
</tr>
<tr id="settings-settings-mail-signature">
<th>Signatur f&uuml;r Mails (max. 255 Zeichen):</th>
<td><textarea name="sig_mails" rows="4" cols="30">'.$usr['sig_mails'].'</textarea></td>
</tr>
<tr id="settings-settings-board-signature">
<th>Signatur f&uuml;r Cluster-Board (max. 255 Zeichen):</th>
<td><textarea name="sig_board" rows="4" cols="30">'.$usr['sig_board'].'</textarea></td>
</tr>
<tr id="settings-settings-mail-maximum">
<th>&raquo;Posteingang voll&laquo;-Nachricht:</th>
<td><input type="text" value="'.$usr['inbox_full'].'" name="inbox_full" maxlength="250" /><br />
Wenn dein Posteingang voll ist, erh&auml;lt ein User, der dir eine Nachricht schicken will, diese Meldung</td>
</tr>
<tr id="settings-settings-usrimg">
<th>Benutzerinfo-Bild aktivieren:</th>
<td>'.$usrimg.'</td>
</tr>';

        /*<!--<tr id="settings-settings-usessl">
        <th>SSL-Verschl&uuml;sselte Verbindung:</th>
        <td>'.$usessl.'</td>
        </tr>-->*/

        $usrimg_fmt = '';
        $fmts = array(
            'points',
            'ranking',
            'points ranking',
            'cluster points',
            'cluster ranking',
            'cluster points ranking',
        );
        $fmtnms = array(
            'Punkte',
            'Ranglisten-Platz',
            'Punkte + Platz',
            'Cluster + Punkte',
            'Cluster + Platz',
            'Cluster + Platz + Punkte',
        );
        for ($i = 0; $i < count($fmts); $i++) {
            $usrimg_fmt .= '<option value="'.$fmts[$i].'"';
            if ($usr['usrimg_fmt'] == $fmts[$i]) {
                $usrimg_fmt .= ' selected="selected"';
            }
            $usrimg_fmt .= '>'.$fmtnms[$i].'</option>'."\n";
        }

        if ($usr['bigacc'] == 'yes') {
            echo '<tr id="settings-settings-usrimg">
<th>Format des Benutzerinfo-Bildes:</th>
<td><select name="usrimg_fmt">
'.$usrimg_fmt.'
</select></td>
</tr>';
        }
        echo $statx.'<tr id="settings-settings-confirm">
<td colspan="2"><input type="submit" value="Speichern" /><button type="submit" name="delete_account" value="yes" style="background-color:red;color:white;float:right;" onclick="return confirm(\'Bist du sicher, dass du deinen Account l&ouml;schen m&ouml;chtest?\');">Account l&ouml;schen</button></td>
</tr>
</table>
</form>
</div>

<div id="settings-mail">
<h3>Email-Adresse &auml;ndern</h3>
<form action="user.php?a=setmailaddy&amp;sid='.$sid.'" method="post">
<table>
<tr id="settings-mail-address">
<th>Deine Email-Adresse:</th>
<td><input type="text" name="email" value="'.$usr['email'].'" /><br />
Die Email-Adresse ist f&uuml;r andere Benutzer nicht sichtbar</td>
</tr>
<tr id="settings-mail-password">
<th>Dein Account-Passwort:</th>
<td><input name="pwd" type="password" /><br />
Bitte zur Best&auml;tigung eingeben.</td>
</tr>
<tr id="settings-mail-confirm">
<td colspan="2"><input type="submit" value="Speichern" /></td>
</tr>
</table>
</form>
</div>';

        if ($usr['stat'] > 10) {
            echo '<div id="settings-password">
<form action="user.php?a=newpwd&amp;sid='.$sid.'" method="post">
<h3>Passwort &auml;ndern (Sonder-Funktion)</h3>
<table>
<tr id="settings-password-password">
<th>Neues Passwort:</th>
<td><input name="pwd" type="password" maxlength="16" /></td>
</tr>
<tr id="settings-password-confirm">
<td colspan="2"><input type="submit" value="Speichern" /></td>
</tr>
</table>
</form>
</div>
';
        }
        echo '</div>'."\n";
        ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
        break;

    case 'saveconfig': //------------------------- SAVE CONFIG -------------------------------

        if (isset($_POST['delete_account']) && $_POST['delete_account'] == 'yes') {
            $delusr = @delete_account($usrid);
            if ($delusr !== false) {
                db_query('INSERT INTO logs SET type=\'deluser\', usr_id=\''.mysql_escape_string($delusr['id']).'\', payload=\''.mysql_escape_string($delusr['name']).' '.mysql_escape_string($delusr['email']).' self-deleted\';');
                @unlink('data/login/'.$sid.'.txt');
                header('Refresh: 5; url=index.php');
                $msg = 'Account '.$delusr['name'].' ('.$usrid.') gel&ouml;scht!';
                $msg .= '<br />Du wirst in <span id="countdown">5</span> Sekunden automatisch zur <a href="index.php">Startseite</a> weitergeleitet.';
                $msg .= '<script>var seconds=5;var c=document.getElementById("countdown");var i=setInterval(function(){seconds--;if(seconds<=0){clearInterval(i);window.location.href="index.php";}c.textContent=seconds;},1000);</script>';
                simple_message($msg);
            } else {
                simple_message('Account '.$usrid.' existiert nicht!');
            }
        } else { # Nicht Account löschen sondern Settings speichern

            $g = $_POST['sex'];
            if ($g == '') {
                $g = 'x';
            }
            $bday = (int)$_POST['bday'];
            $bmonth = (int)$_POST['bmonth'];
            $byear = (int)$_POST['byear'];
            $birthday = sprintf('%02d.%02d.%04d', $bday, $bmonth, $byear);
            $hp = trim($_POST['homepage']);
            $ort = trim($_POST['ort']);
            $text = trim($_POST['aboutme']);
            $sig_mails = trim($_POST['sig_mails']);
            $sig_board = trim($_POST['sig_board']);
            $inbox_full = trim($_POST['inbox_full']);
            $avatar = trim($_POST['avatar']);

            $usessl = (isset($_POST['usessl']) && $_POST['usessl'] == 'yes' ? 'yes' : 'no');
            if ($usr['bigacc'] != 'yes') {
                $usessl = 'no';
            }

            $enable_usrimg = (isset($_POST['enable_usrimg']) && $_POST['enable_usrimg'] == 'yes' ? 'yes' : 'no');
            if ($usr['bigacc'] != 'yes') {
                $enable_usrimg = 'no';
            }

            $usrimg_fmt = $_POST['usrimg_fmt'] ?? $usr['usrimg_fmt'];

            $pcs = explode(',', $usr['pcs']);

            $e = false;
            $error = '';

            if (!preg_match('#^https?://#i', $hp)) {
                $hp = '';
            }
            if (!preg_match('#^https?://.*/.+#i', $avatar)) {
                $avatar = '';
            }
            if (strlen($ort) < 3) {
                $ort = '';
            }
            if (strlen($text) > 2048) {
                $e = true;
                $error .= 'Die Beschreibung darf maximal 2048 Zeichen haben!';
            }
            if (strlen($sig_mails) > 255) {
                $e = true;
                $error .= 'Die Signatur f&uuml;r Mails darf maximal 255 Zeichen haben!';
            }
            if (strlen($sig_board) > 255) {
                $e = true;
                $error .= 'Die Signatur f&uuml;rs Cluster-Board darf maximal 255 Zeichen haben!';
            }
            if (strlen($inbox_full) > 255) {
                $e = true;
                $error .= 'Die Nachricht bei vollem Posteingang darf maximal 255 Zeichen haben!';
            }

            if ($e == false) {

                foreach ($_POST as $bez => $val) {
                    $_POST[$bez] = html_entity_decode($val);
                }

                $usr['gender'] = $g;
                $usr['birthday'] = $birthday;
                $usr['homepage'] = safeentities($hp);
                $usr['infotext'] = safeentities($text);
                $usr['wohnort'] = safeentities($ort);
                $usr['sig_mails'] = safeentities($sig_mails);
                $usr['sig_board'] = safeentities($sig_board);
                $usr['inbox_full'] = safeentities($inbox_full);
                $usr['avatar'] = safeentities($avatar);
                $usr['usessl'] = $usessl;
                if ($usr['usrimg_fmt'] != $usrimg_fmt || $usr['enable_usrimg'] != $enable_usrimg) {
                    @unlink('data/_server'.$server.'/usrimgs/'.$usrid.'.png');
                }
                $usr['enable_usrimg'] = $enable_usrimg;
                $usr['usrimg_fmt'] = $usrimg_fmt;
                saveuserdata();
                header(
                    'Location: user.php?a=config&sid='.$sid.'&ok='.urlencode('Die &Auml;nderungen wurden gespeichert.')
                );
            } else {
                site_header('Optionen');
                body_start();
                echo '<h2>Optionen</h2>';
                echo '<div class="error">FEHLER:<br />'.$msg.'<br /><br />';
                echo 'Aufgrund dieser Fehler wurden die &Auml;nderungen <i>nicht</i> &uuml;bernommen!</div>';
                echo '</div>';
                site_footer();
            }

        }

        break;

    case 'setmailaddy': //------------------------- SET MAIL ADDY -------------------------------
        $email = trim($_POST['email']);
        if (!check_email($email)) {
            simple_message('Bitte eine g&uuml;ltige Email-Adresse im Format xxx@yyy.zz angeben!');
        } else {
            $pwd = trim($_POST['pwd']);
            $real_pwd = $usr['password'];

            if ($pwd == $real_pwd || md5($pwd) == $real_pwd) {
                db_query(
                    'UPDATE users SET email=\''.mysql_escape_string($email).'\' WHERE id=\''.mysql_escape_string(
                        $usrid
                    ).'\''
                );
                echo mysql_error();
                header('Location: user.php?a=config&sid='.$sid.'&saved=1');
            } else {
                simple_message('Falsches Passwort!');
            }
        }
        break;

    case 'info': //------------------------- INFO -------------------------------
        $index = $_REQUEST['user'];
        $a = getuser($index);
        if ($a != false) {

            $geschl = '';
            $gb = '';
            $ort = '';
            $hp = '';
            $locked = '';
            $bigacc = '';
            $pchw = '';

            $u_points = $a['points'];
            createlayout_top('ZeroDayEmpire - Benutzerprofil');
?>
<!-- ZDE theme inject -->
<style>@import url("style.css");</style>
<div class="container">
<?php // /ZDE theme inject start


            if ($a['gender'] == 'x') {
                $geschl = '';
            } elseif ($a['gender'] == 'm') {
                $geschl = 'M&auml;nnlich';
            } elseif ($a['gender'] == 'w') {
                $geschl = 'Weiblich';
            }
            if ($geschl != '') {
                $geschl = '<tr>'.LF.'<th>Geschlecht:</th>'.LF.'<td>'.$geschl.'</td>'.LF.'</tr>'."\n";
            }
            if ($a['wohnort'] != '') {
                $ort = '<tr>'.LF.'<th>Wohnort:</th><td>'.$a['wohnort'].'</td>'.LF.'</tr>'."\n";
            }

            if ($a['locked'] == 'yes') {
                $locked = '<tr id="account-locked">'.LF.'<th>Besonderheiten:</th>'.LF.'<td>Account gesperrt</td>'.LF.'</tr>'."\n";
            }

            if ($a['birthday'] != '0.0.0') {
                list($bday, $bmonth, $byear) = explode('.', $a['birthday']);
                $years = date('Y') - $byear;
                if ($bmonth > date('m')) {
                    $years--;
                }
                if ($bmonth == date('m') AND $bday > date('d')) {
                    $years--;
                }
                if ($years <= 104) {
                    $alter = $years.' Jahre';
                    $gb = '<tr>'.LF.'<th>Alter</th>'.LF.'<td>'.$alter.'</td>'.LF.'</tr>'."\n";
                }
            }
            if (preg_match('#^https?://#i', $a['homepage'])) {
                $hp = dereferurl($a['homepage']);
                $hp = safeentities($hp);
                $hp = '<tr>'.LF.'<th>Homepage:</th><td><a href="'.$hp.'">'.safeentities(
                        $a['homepage']
                    ).'</a></td>'.LF.'</tr>'."\n";
            }
            $descr = nl2br($a['infotext']);
            $c = $a['cluster'];
            if ($c != false) {
                $c = getcluster($c);
                $scluster = '<a href="cluster.php?a=info&amp;cluster='.$a['cluster'].'&amp;sid='.$sid.'">'.$c['name'].'</a> '.$c['code'];
            } else {
                $scluster = 'keiner';
            }

            $spcs = '';
            $sql = db_query('SELECT * FROM pcs WHERE owner='.mysql_escape_string($a['id']).' ORDER BY name ASC;');
            $pccnt = mysql_num_rows($sql);
#$attackallowed=false;
            while ($xpc = mysql_fetch_assoc($sql)) {
                $country = GetCountry('id', $xpc['country']);
                $xpc['name'] = safeentities($xpc['name']);
                if ((int)$usr['stat'] >= 100) {
                    $extras = ' <a href="secret.php?sid='.$sid.'&amp;m=file&amp;type=pc&amp;id='.$xpc['id'].'">Extras</a>';
                } else {
                    $extras = '';
                }
                $spcs .= '<li>'.$xpc['name'].' (10.47.'.$xpc['ip'].', <a href="game.php?m=subnet&amp;sid='.$sid.'&amp;subnet='.subnetfromip(
                        $xpc['ip']
                    ).'">'.$country['name'].'</a>, '.$xpc['points'].' Punkte)'.$extras.'</li>';
                #$xdefence=$xpc['fw'] + $xpc['av'] + $xpc['ids']/2;
                #if($xdefence >= MIN_ATTACK_XDEFENCE OR isavailh('scan',$xpc)==true) $attackallowed=true;
            }
            if (file_exists('data/login/'.$a['sid'].'.txt') == true) {
                $online = '<span style="color:green;">Online</span>';
            } else {
                $online = '<span style="color:red;">Offline</span>';
            }

            if ($usr['stat'] >= 100) {
                $descr .= '</td>'.LF.'</tr>'.LF.'<tr>'.LF.'<th>Sonder-Funktionen:</th>'.LF.'<td><a href="secret.php?sid='.$sid.'&amp;m=file&amp;type=user&amp;id='.$a['id'].'">'.($usr['stat'] == 1000 ? 'Bearbeiten' : 'Daten ansehen').'</a>';
            }

            if ($usr['stat'] == 1000) {
                $descr .= '<br />'.LF.'<a href="secret.php?a=lockacc&amp;sid='.$sid.'&amp;user='.$a['id'].'">Account sperren</a> | <a href="secret.php?a=delacc1&amp;sid='.$sid.'&amp;user='.$a['id'].'">Account l&ouml;schen</a>';
            }

            if ($usr['bigacc'] == 'yes') {
                $bigacc = '| <a href="abook.php?sid='.$sid.'&amp;action=add&amp;user='.$index.'">User zum Adressbuch hinzuf&uuml;gen</a>';
            }

            /*
            $rhx=true;
            if( $a['points'] <= ($usr['points'] * (25/100)) ) {
              $r=db_query('SELECT * FROM `attacks` WHERE `from_usr`=\''.mysql_escape_string($a['id']).'\' AND `to_usr`=\''.mysql_escape_string($usrid).'\' AND `type`<>\'scan\';');
              if(mysql_num_rows($r)==0) {
                $rhx=false;
              }
            }

            if($attackallowed!==true && $a['login_time']+MIN_INACTIVE_TIME>time() )
              $attack='Dieser User kann nicht angegriffen werden, weil er noch zu schwach ist.';
            elseif(is_noranKINGuser($index))
              $attack='Dieser User kann nicht angegriffen werden, weil er ein Administrator ist.';
            elseif($rhx==false)
              $attack='M&ouml;glich, allerdings kein Remote Hijack.';
            elseif(isattackallowed($dummy,$dummy2)==false)
              $attack='Dieser User k&ouml;nnte angegriffen werden, aber du kannst momentan nicht angreifen.';
            else
              $attack='Sofort m&ouml;glich';
            */
            $attack = '(keine Info)';
            $attack = 'Letztes Login: <i>'.nicetime3($a['login_time']).'</i><br />Angriff: <i>'.$attack.'</i>';

            $avatar = '';
            if (preg_match('#^https?://.*/.+#i', $a['avatar'])) {
                if ($usr['sid_ip'] != 'noip') {
                    $avatar = $a['avatar'];
                    $avatar = '<tr><td colspan="2"><img src="'.$avatar.'" alt="'.$a['name'].'" /></td></tr>';
                }
            }

            echo '<div class="content" id="user-profile">
<h2>Benutzer-Profil</h2>
<div class="submenu">
<p><a href="mail.php?m=newmailform&amp;sid='.$sid.'&amp;recip='.$a['name'].'">Mail an User</a> |
<a href="ranking.php?m=ranking&amp;sid='.$sid.'&amp;type=user&amp;id='.$a['id'].'">User in Rangliste</a>
'.$bigacc.'</p>
</div>
<div id="user-profile-profile">
<h3>'.$a['name'].'</h3>
<table>
'.$avatar.'
<tr>
<th>Punkte</th><td>'.$a['points'].'</td>
</tr>
'.$geschl.$gb.$ort.$hp.$locked.'
<tr>
<th>Cluster</th><td>'.$scluster.'</td></tr>
<tr>
<th>Computer ('.$pccnt.')</th>
<td><ul>'.$spcs.'</ul>'.$pchw.'</td>
</tr>
<tr>
<th>Angriff?</th>
<td>'.$attack.'</td>
</tr>
<tr>
<th>Online?</th>
<td>'.$online.'</td>
</tr>
';
            if ($descr != '') {
                $descr = preg_replace('/script/i', '$cr!p7', $descr);
                echo '<tr>
<th>Beschreibung:</th>
<td>'.$descr.'</td>
</tr>
';
            }
            echo '</table>
</div>
</div>
';
            ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
        } else {
            simple_message('Diesen Benutzer gibt es nicht!');
        }
        break;

    case 'newpwd': //------------------------- NEW PWD -------------------------------
        if ($usr['stat'] < 10) {
            simple_message('No!');
            exit;
        }
        $pwd = $_POST['pwd'];
        $usrname = strtolower($usr['name']);
        if (substr_count($pwd, ';') == 0) {
            db_query('UPDATE users SET password=\''.md5($pwd).'\' WHERE id=\''.$usrid.'\';');
            simple_message('Passwort ge&auml;ndert auf <i>'.$pwd.'</i>');
        } else {
            simple_message('Passwort ung&uuml;ltig!');
        }
        break;

}


?>
