<?php

#if(substr_count($_SERVER['HTTP_HOST'],'.')>1) { header('Location: http://zdesrv.org/pub.php'); exit; }

define('IN_ZDE', 1);
$starttime = microtime();
include('gres.php');
include('layout.php');

define('REG_CODE_LEN', 24, false);

$action = $_REQUEST['page']
    ?? $_REQUEST['mode']
    ?? $_REQUEST['action']
    ?? $_REQUEST['a']
    ?? $_REQUEST['m']
    ?? $_REQUEST['d']
    ?? '';

function showdoc($fn, $te = '')
{
    if ($te != '') {
        $x = ' - '.$te;
    }
    createlayout_top('ZeroDayEmpire'.$x);
?>
<!-- ZDE theme inject -->
<style>
#register-step1 input[type="text"],
#register-step1 input[type="email"],
#register-step1 input[type="password"]{width:250px;}
.pwd-wrapper{position:relative;display:inline-block;}
#pwd-guidelines{display:none;position:absolute;left:0;top:calc(100% + 4px);background:var(--bg-2);border:1px solid var(--border);padding:10px;border-radius:8px;max-width:250px;z-index:10;}
</style>
<div class="container">
<?php // /ZDE theme inject start


    $x = 'data/pubtxt/'.$fn;
    if (file_exists($x.'.txt')) {
        readfile($x.'.txt');
    } else {
        @include($x.'.php');
    }
    ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
}

switch ($action) {

    case 'faq':
        showdoc('faq', 'FAQ');
        break;
    case 'credits':
        showdoc('credits', 'Team');
        break;
    case 'newpwd':
        showdoc('newpwd', 'Neues Passwort anfordern');
        break;
    case 'chat':
        showdoc('chat', 'Chat');
        break;
    case 'impressum':
        showdoc('impressum', 'Impressum');
        break;
    case 'refinfo':
        showdoc('refinfo', 'Werben von neuen Benutzern');
        break;
    case 'regelverstoss':
        showdoc('regelverstoss', 'Regelversto&szlig; melden');
        break;
        break;

case 'nickcheck':
        $nick = trim($_GET['nick'] ?? '');
        mysql_select_db(dbname(1));
        header('Content-Type: application/json');
        $exists = false;
        if ($nick !== '') {
            $exists = getuser($nick, 'name') !== false;
        }
        echo json_encode(['exists' => $exists]);
        exit;

    case 'emailcheck':
        $email = trim($_GET['email'] ?? '');
        mysql_select_db(dbname(1));
        header('Content-Type: application/json');
        $exists = false;
        if ($email !== '') {
            $exists = getuser($email, 'email') !== false;
        }
        echo json_encode(['exists' => $exists]);
        exit;

    case 'register':

        $nickVal = htmlspecialchars($_GET['nick'] ?? '', ENT_QUOTES);
        $emailVal = htmlspecialchars($_GET['email'] ?? '', ENT_QUOTES);
        $email2Val = htmlspecialchars($_GET['email2'] ?? '', ENT_QUOTES);

        createlayout_top('ZeroDayEmpire - Account anlegen');
?>
<!-- ZDE theme inject -->
<style>
#register-step1 input[type="text"],
#register-step1 input[type="email"],
#register-step1 input[type="password"]{width:250px;}
.pwd-wrapper{position:relative;display:inline-block;}
#pwd-guidelines{display:none;position:absolute;left:0;top:calc(100% + 4px);background:var(--bg-2);border:1px solid var(--border);padding:10px;border-radius:8px;max-width:250px;z-index:10;}
</style>
<div class="container">
<?php // /ZDE theme inject start


        echo '<div class="content" id="register">
<h2>Registrieren</h2>';
        echo $notif.'<div id="register-step1">
<h3>Schritt 1: Zugangsdaten</h3>
<form action="pub.php?a=regsubmit" method="post">
<table>
<tr>
<th>Nickname:</th>
<td><input name="nick" id="_nick" maxlength="20" required value="'.$nickVal.'" /><span id="nick-status"></span></td>
</tr>
<tr>
<th>E-Mail-Adresse:</th>
<td><input name="email" id="_email" type="email" maxlength="50" required value="'.$emailVal.'" /><span id="email-status"></span></td>
</tr>
<tr>
<th>E-Mail-Adresse (Wiederholung):</th>
<td><input name="email2" id="_email2" type="email" maxlength="50" required value="'.$email2Val.'" /><span id="email2-status"></span></td>
</tr>
<tr>
<th id="pwd-label">Passwort:</th>
<td><div class="pwd-wrapper"><input type="password" name="pwd" id="_pwd" required /><span id="pwd-status"></span><div id="pwd-guidelines">Passwort muss mindestens 8 Zeichen lang sein und Buchstaben sowie mindestens eine Zahl oder ein Sonderzeichen enthalten.</div></div></td>
</tr>
<tr>
<th>Passwort (Wiederholung):</th>
<td><input type="password" name="pwd2" id="_pwd2" required /><span id="pwd2-status"></span></td>
</tr>
<tr>
<td colspan="2"><input type="hidden" name="server" value="1" />
<label><input type="checkbox" name="rules" required> Ich akzeptiere die <a href="rules.php" target="_blank"><u>Spielregeln</u></a>.</label></td>
</tr>
<tr>
<td colspan="2"><input type="submit" value="Registrieren" /></td>
</tr>
</table>
</form>
</div>
</div>
';
echo <<<'SCRIPT'
<script>
(function(){
    const nickInput=document.getElementById("_nick");
    const nickStatus=document.getElementById("nick-status");
    nickInput.addEventListener("input",()=>{
        const value=nickInput.value.trim();
        if(value.length<3){nickStatus.textContent="";return;}
        fetch("pub.php?a=nickcheck&nick="+encodeURIComponent(value)).then(r=>r.json()).then(d=>{
            if(d.exists){nickStatus.textContent="Nickname bereits vergeben";nickStatus.style.color="red";}
            else{nickStatus.textContent="Nickname verfügbar";nickStatus.style.color="green";}
        });
    });

    const emailInput=document.getElementById("_email");
    const emailStatus=document.getElementById("email-status");
    const email2Input=document.getElementById("_email2");
    const email2Status=document.getElementById("email2-status");
    function checkEmail(){
        const value=emailInput.value.trim();
        if(value===""){emailStatus.textContent="";return;}
        if(!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(value)){
            emailStatus.textContent="Ungültige Email";
            emailStatus.style.color="red";
            return;
        }
        fetch("pub.php?a=emailcheck&email="+encodeURIComponent(value)).then(r=>r.json()).then(d=>{
            if(d.exists){
                emailStatus.textContent="Email bereits registriert";
                emailStatus.style.color="red";
            }else{
                emailStatus.textContent="";
            }
        });
        checkEmailMatch();
    }
    function checkEmailMatch(){
        const v1=emailInput.value.trim();
        const v2=email2Input.value.trim();
        if(v2===""){email2Status.textContent="";return;}
        if(v1===v2){
            email2Status.textContent="E-Mail-Adressen stimmen überein";
            email2Status.style.color="green";
        }else{
            email2Status.textContent="E-Mail-Adressen stimmen nicht überein";
            email2Status.style.color="red";
        }
    }
    emailInput.addEventListener("input",checkEmail);
    email2Input.addEventListener("input",checkEmailMatch);

    const pwdInput=document.getElementById("_pwd");
    const pwdStatus=document.getElementById("pwd-status");
    const pwd2Input=document.getElementById("_pwd2");
    const pwd2Status=document.getElementById("pwd2-status");
    const guidelines=document.getElementById("pwd-guidelines");
    const pwdLabel=document.getElementById("pwd-label");
    function checkPwd(){
        const v=pwdInput.value;
        const ok=v.length>=8 && /[A-Za-z]/.test(v) && (/[0-9]/.test(v) || /[^A-Za-z0-9]/.test(v));
        if(v===""){pwdStatus.textContent="";}
        else if(ok){pwdStatus.textContent="Passwort erfüllt Richtlinien";pwdStatus.style.color="green";}
        else{pwdStatus.textContent="Passwort zu schwach";pwdStatus.style.color="red";}
        checkPwdMatch();
    }
    function checkPwdMatch(){
        const v1=pwdInput.value;
        const v2=pwd2Input.value;
        if(v2===""){pwd2Status.textContent="";return;}
        if(v1===v2){
            pwd2Status.textContent="Passwörter stimmen überein";
            pwd2Status.style.color="green";
        }else{
            pwd2Status.textContent="Passwörter stimmen nicht überein";
            pwd2Status.style.color="red";
        }
    }
    pwdInput.addEventListener("input",checkPwd);
    pwd2Input.addEventListener("input",checkPwdMatch);
    [pwdInput,pwdLabel].forEach(el=>{
        el.addEventListener("mouseover",()=>{guidelines.style.display="block";});
        el.addEventListener("mouseout",()=>{guidelines.style.display="none";});
    });
    pwdInput.addEventListener("focus",()=>{guidelines.style.display="block";});
    pwdInput.addEventListener("blur",()=>{guidelines.style.display="none";});
    if(emailInput.value!="") checkEmail();
    if(email2Input.value!="") checkEmailMatch();
})();
</script>
SCRIPT;
        ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();

        break;

    case 'baduser':
        $nick1 = $_POST['nick1'];
        $nick2 = trim($_POST['nick2']);
        $text = trim($_POST['text']);
        createlayout_top('ZeroDayEmpire - Regelversto&szlig;');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


        echo '<div class="content" id="rules">
<h2>Regelversto&szlig; gemeldet</h2>
';
        if ($nick2 != '' & $text != '') {
            @mail(
                'regelverstoss@local.host',
                'Regelversto&szlig; gemeldet von '.$nick1,
                $nick2.' hat angeblich folgendes getan:'.LF."\n".$text,
                'From: ZeroDayEmpire <robot@ZeroDayEmpire.org>'
            );
            echo '<div class="ok"><h3>Gemeldet.</h3><p>Danke f&uuml;r deine Hilfe!</p></div>';
        } else {
            echo '<div class="error"><h3>Fehler</h3><p>Du musst schon den User angeben, der gegen die Regeln versto&szlig;en hat!<br />Auch was er getan hat, ist wichtig!</p></div>';
        }
        echo '</div>';
        ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();

        break;


    case 'regsubmit': // ----------------------- RegSubmit --------------------------

        $email = trim($_POST['email']);
        $email2 = trim($_POST['email2']);
        $pwd = (string)($_POST['pwd'] ?? '');
        $pwd2 = (string)($_POST['pwd2'] ?? '');
        $nick = trim($_POST['nick']);
        $server = (int)$_POST['server'];
        if ($server < 1 || $server > 2) {
            $server = 2;
        }
        mysql_select_db(dbname($server));
        $info = gettableinfo('users', dbname($server));
        if ($info['Rows'] >= MAX_USERS_PER_SERVER) {
            exit;
        }
        $e = false;

        $badwords = 'king|fuck|fick|sex|porn|penis|vagina|arsch|hitler|himmler|goebbels|göbbels|hure|nutte|fotze|bitch|schlampe';
# nein, king ist kein böses, sondern ein reserviertes wort ^^
        $nickzeichen = 'abcdefghijklmnopqrstuvwxyzäüöABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÜß0123456789_-:@.!=?\$%/&';
        function checknick($nick)
        {
            global $REMOTE_FILES_DIR, $DATADIR, $nickzeichen;
            $b = true;
            $len = strlen($nick);
            for ($i = 0; $i < $len; $i++) {
                $zz = substr($nick, $i, 1);
                if (strstr($nickzeichen, (string)$zz) == false) {
                    $b = false;
                    break;
                }
            }
            $x = preg_replace('#[-_:@.!=?\$%&/0-9]#i', '', $nick);
            if (trim($x) == '') {
                $b = false;
            }

            return $b;
        }

        if ($nick != '') {
            if (getuser($email, 'email') !== false) {
                $e = true;
                $msg .= 'Ein Benutzer mit dieser Emailadresse existiert bereits!<br />';
            }
            if (getuser($nick, 'name') !== false) {
                $e = true;
                $msg .= 'Ein Benutzer mit diesem Nicknamen existiert bereits!<br />';
            }
            if (checknick($nick) == false) {
                $e = true;
                $msg .= 'Der Nickname darf NUR die Zeichen <i>'.$nickzeichen.'</i> enthalten. Au&szlig;erdem darf er nicht nur aus Sonderzeichen bestehen.<br />';
            }
            if (strlen($nick) < 3 | strlen($nick) > 20) {
                $e = true;
                $msg .= 'Der Nickname muss zwischen 3 und 20 Zeichen lang sein.<br />';
            }
            $x = preg_replace('#[-_:@.!=?\$%&/0-9]#i', '', $nick);
            if (preg_match('#(' . $badwords . ')#i', $x) != false) {
                $e = true;
                $msg .= 'Der Nickname darf bestimmte W&ouml;rter nicht enthalten.<br />';
            }
        } else {
            $e = true;
            $msg .= 'Bitte Nickname eingeben.<br />';
        }
        if (!check_email($email)) {
            $e = true;
            $msg .= 'Bitte eine g&uuml;ltige Email-Adresse im Format x@y.z angeben.<br />';
        }
        if ($email !== $email2) {
            $e = true;
            $msg .= 'Die E-Mail-Adressen m&uuml;ssen &uuml;bereinstimmen.<br />';
        }
        if ($pwd === '') {
            $e = true;
            $msg .= 'Bitte ein Passwort eingeben.<br />';
        } elseif ($pwd !== $pwd2) {
            $e = true;
            $msg .= 'Die Passw&ouml;rter m&uuml;ssen &uuml;bereinstimmen.<br />';
        } elseif (strlen($pwd) < 8 || !preg_match('/[A-Za-z]/', $pwd) || !preg_match('/[0-9\\W]/', $pwd)) {
            $e = true;
            $msg .= 'Das Passwort muss mindestens 8 Zeichen lang sein und Buchstaben sowie mindestens eine Zahl oder ein Sonderzeichen enthalten.<br />';
        }
        if (!isset($_POST['rules'])) {
            $e = true;
            $msg .= 'Bitte die Regeln akzeptieren.<br />';
        }

        $javascript = file_get('data/pubtxt/selcountry_head.txt');

        if ($e == false) {

            createlayout_top('ZeroDayEmpire - Account anlegen');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start



            $tmpfnx = randomx(REG_CODE_LEN);
            $tmpdir = 'data/regtmp';
            if (!is_dir($tmpdir) && !mkdir($tmpdir, 0777, true)) {
                die('FUCK OFF!');
            }
            $tmpfn = $tmpdir.'/'.$tmpfnx.'.txt';
            if (!file_put($tmpfn, $nick.'|'.$email.'|'.$pwd.'|'.$server)) {
                die('FUCK OFF!');
            }

            $selcode = str_replace('%path%', 'images/maps', file_get('data/pubtxt/selcountry_body.txt'));
            echo '<div class="content" id="register">
<h2>Registrierung</h2>
<div id="register-step2">
<h3>Schritt 2: Land auswählen</h3>
<p>Bitte w&auml;hle jetzt, in welchem Land der Erde dein Computer stehen soll. Nat&uuml;rlich nur im Spiel und nicht in echt...</p>
<form action="pub.php?a=regsubmit2" method="post" name="coolform">
<input type="hidden" name="code" value="'.$tmpfnx.'" />
<input type="hidden" name="country" value="" />
'.$selcode.'
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
            header('Location:pub.php?a=register&error='.urlencode($msg).'&nick='.urlencode($nick).'&email='.urlencode($email).'&email2='.urlencode($email2));
        }
        break;

    case 'regsubmit2':  // ----------------------- RegSubmit 2 --------------------------

        $tmpfnx = $_POST['code'];
        if (preg_match('/^[a-z0-9]+$/i', $tmpfnx) !== 1) {
            die('FUCK OFF!');
        }
        $fn = 'data/regtmp/'.$tmpfnx.'.txt';
        $regtmp = file_get($fn);
        if ($regtmp === false) {
            die('FUCK OFF!');
        }
        $parts = explode('|', $regtmp);
        if (count($parts) < 4) {
            die('FUCK OFF!');
        }
        list($nick, $email, $pwd, $server) = $parts;
        mysql_select_db(dbname($server));

        $country = $_POST['country'];

# IST DAS LAND VOLL ? START
        $c = GetCountry('id', $country);
        $subnet = $c['subnet'];

        $r = db_query('SELECT `id` FROM `pcs` WHERE `ip` LIKE \''.mysql_escape_string($subnet).'.%\';');
        $cnt = mysql_num_rows($r);
        $xip = $cnt + 1;

        if ($xip > 254) {
            @unlink('data/regtmp/'.$tmpfnx.'.txt');
            simple_message('Das gew&auml;hlte Land ist schon &quot;voll&quot;! Bitte such dir ein anderes Land aus!');
            exit;
        }


# IST DAS LAND VOLL ? X_END

        file_put($fn, $nick.'|'.$email.'|'.$pwd.'|'.$country.'|'.$server);
        if ($nick == '' || $email == '' || $pwd == '' || $country == '' || $server == '') {
            simple_message('FEHLER AUFGETRETEN!', 'error');
            exit;
        }

        header('Location: pub.php?a=regactivate&code='.$tmpfnx);
        exit;

        break;

    case 'regactivate': // ----------------------- RegActivate --------------------------

        if (strlen($_GET['code']) <> REG_CODE_LEN) {
            simple_message('Keine Hackversuche bitte!');
            exit;
        }

        $fn = 'data/regtmp/'.$_GET['code'].'.txt';
        if (file_exists($fn) == false) {
            simple_message('Ung&uuml;ltiger Registrierungscode!');
        } else {

            $a = explode('|', file_get($fn));
            list($nick, $email, $pwd, $country, $server) = explode('|', file_get($fn));
            unlink($fn);

            mysql_select_db(dbname($server));

            if (getuser($nick, 'name') !== false) {
                simple_message('Ein Benutzer mit diesem Nicknamen existiert bereits!');
            }

            $tableinfo = GetTableInfo('users', dbname($server));
            $autoindex = $tableinfo['Auto_increment'];
            $r = addpc($country, $autoindex);
            if ($r != false) {

                $ts = time();
                db_query(
                    'INSERT INTO users(id, name, email,   password, pcs, liu, lic,  syndikatetat, login_time)'
                    .'          VALUES(0, \''.mysql_escape_string($nick).'\',\''.mysql_escape_string(
                        $email
                    ).'\',\''.md5($pwd).'\', \''.mysql_escape_string($r).'\', \''.mysql_escape_string(
                        $ts
                    ).'\', \''.mysql_escape_string($ts).'\', 0,        \''.mysql_escape_string($ts).'\');'
                );

                $ownerid = mysql_insert_id();
                db_query(
                    'UPDATE pcs SET owner=\''.mysql_escape_string($ownerid).'\', owner_name=\''.mysql_escape_string(
                        $nick
                    ).'\', owner_points=0, owner_syndikat=0, owner_syndikat_code=\'\' WHERE id='.mysql_escape_string($r)
                );

                db_query(
                    'INSERT INTO rank_users VALUES(0, '.mysql_escape_string($ownerid).', \''.mysql_escape_string(
                        $nick
                    ).'\', 0, 0);'
                );
                $rank = mysql_insert_id();
                db_query(
                    'UPDATE users SET rank='.mysql_escape_string($rank).' WHERE id='.mysql_escape_string($ownerid).';'
                );

                /*setcookie('ref_user');
                setcookie('regc1','yes',time()+24*60*60);
                $dummy=reloadsperre_CheckIP(true); # IP speichern
                */
                createlayout_top('ZeroDayEmpire - Registrierung erfolgreich');

?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


                echo '<div class="content" id="register">
<h2>Registrierung</h2>
<div id="register-activate">
';
                echo '<div class="ok"><h3>Registrierung erfolgreich!</h3>';
                echo '<p>Herzlichen Gl&uuml;ckwunsch!<br />Dein Account wurde aktiviert.<br /><button type="button" class="btn play-link" onclick="location.href=\'pub.php\'">Jetzt spielen!</button></p></div>';

            } else {
                createlayout_top('ZeroDayEmpire - Registrierung');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


                echo '<div class="content" id="register">
<h2>Registrierung</h2>
<div id="register-activate">
';
                echo '<div class="error"><h3>Sorry</h3>

<p>Das gew&auml;hlte Land ist schon "voll"! Bitte such dir ein anderes Land aus!</p></div>
<form action="pub.php?a=regsubmit" method="post">
<input type=hidden name="server" value="'.$server.'">
<input type=hidden name="nick" value="'.$nick.'">
<input type=hidden name="email" value="'.$email.'">
<p><input type=submit value=" Weiter "></p>
</form>';
            }

            echo '</div>'.LF.'</div>';
            ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();

        }
        break;

    case 'newpwdsubmit': // ----------------------- NEW PWD SUBMIT --------------------------

        $usrname = strtolower(trim($_REQUEST['nick']));
        $email = strtolower(trim($_REQUEST['email']));
        $server = (int)$_POST['server'];
        if ($server < 1 || $server > 2) {
            $server = 1;
        }

        mysql_select_db(dbname($server));

        if (check_email($email) === true) {

            $usr = getuser($usrname, 'name');

            if ($usr !== false) {
                if ($email == strtolower($usr['email'])) {
                    $pwd = bin2hex(random_bytes(4));

                    db_query(
                        'UPDATE users SET password=\''.md5($pwd).'\' WHERE id=\''.mysql_escape_string($usr['id']).'\';'
                    );

                    if (@mail(
                        $email,
                        'Zugangsdaten für ZeroDayEmpire',
                        "\n".'http://www.ZeroDayEmpire.org/'.LF."\n".'Server: Server '.$server."\n".'Benutzername: '.$usr['name'].LF.'Passwort: '.$pwd."\n",
                        'From: ZeroDayEmpire <robot@ZeroDayEmpire.org>'
                    )
                    ) {
                        db_query('UPDATE users SET sid=\'\' WHERE id=\''.mysql_escape_string($usr['id']).'\' LIMIT 1;');
                        unset($usr);
                        simple_message('Das neue Passwort wurde an Deine Email-Adresse geschickt!');
                    } else {
                        simple_message('Beim Verschicken der Email trat ein Fehler auf!');
                        /*if($_SERVER['HTTP_HOST']==localhost)*/
                        echo '<br />Neues Passwort: '.$pwd;
                    }

                } else {
                    unset($usr);
                    simple_message('Falsche Email-Adresse!');
                }
            } else {
                unset($usr);
                simple_message('Benutzername unbekannt!');
            }
        } else {
            unset($usr);
            simple_message('Email-Adresse ung&uuml;ltig!');
        }

        break;

    case 'stats': // ----------------------- STATS --------------------------
        createlayout_top('ZeroDayEmpire - Statistik');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start



        function stats($server)
        {
            if (mysql_select_db(dbname($server))) {

                $uinfo = gettableinfo('users', dbname($server));
                $pcinfo = gettableinfo('pcs', dbname($server));

                $cnt1 = $uinfo['Rows'];
                $cnt2 = $pcinfo['Rows'];
                $cnt = $cnt2 - $cnt1;
                $cnt3 = (int)@file_get('data/_server'.$server.'/logins_'.strftime('%x').'.txt');

                $cnt4 = GetOnlineUserCnt($server);

                echo '<h3>Server '.$server.'</h3>
<table>
<tr>
<th>Registrierte User:</th>
<td>'.$cnt1.'</td>
</tr>
<tr>
<th>Computer:</th>
<td>'.$cnt2.'</td>
</tr>
<tr>
<th>Spieler online:</th>
<td>'.$cnt4.'</td>
</tr>
<tr>
<th>Logins heute:</th>
<td>'.$cnt3.'</td>
</tr>
';

                $fn = 'data/_server'.$server.'/logins_'.strftime('%x', time() - 86400).'.txt';
                if (file_exists($fn)) {
                    $cnt = (int)file_get($fn);
                    echo '<tr>'.LF.'<th>Logins gestern:</th>'.LF.'<td>'.$cnt.'</td>'.LF.'</tr>'."\n";
                }
                echo '</table>'."\n";
            }
        }

        echo '<div class="content" id="server-statistic">'."\n";
        echo '<h2>Statistik</h2>'."\n";
        stats(1);


        echo "\n".'</div>';
        ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
        break;

    case 'deleteaccount':  // ----------------------- DELETE ACCOUNT --------------------------
        $code = $_GET['code'];
        $x = @file_get('data/regtmp/del_account_'.$code.'.txt');
        if ($x) {
            $x = explode('|', $x);

            mysql_select_db(dbname($x[1]));
            if ($usr = @delete_account($x[0])) {

                db_query(
                    'INSERT INTO logs SET type=\'deluser\', usr_id=\''.mysql_escape_string(
                        $usr['id']
                    ).'\', payload=\''.mysql_escape_string($usr['name']).' '.mysql_escape_string(
                        $usr['email']
                    ).' self-deleted\';'
                );

                simple_message('Account '.$usr['name'].' ('.$usrid.') gel&ouml;scht!');

            } else {
                simple_message('Account '.$usr['name'].' existiert nicht!');
            }

        } else {
            simple_message('Ung&uuml;ltiger Account-L&ouml;sch-Code!');
        }
        break;


    default: // ----------------------- STARTSEITE --------------------------

        createlayout_top('ZeroDayEmpire - browserbasiertes Online-Spiel');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start


        include('data/pubtxt/startseite.php');
        ?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
}

/*
function listnews($file) {
$f = fopen($file,'r');
$blub = fread($f,65535);
fclose($f);

$p = xml_parser_create();
xml_parse_into_struct($p,$blub,$values,$index);
xml_parser_free($p);

$pointer = 0;

for($i=0;$i<=sizeof($values);$i++) {
  if($values[$i]['tag']=='TITLE') {
  $linktitle[$pointer] = $values[$i]['value'];
}

if($values[$i]['tag']=='LINK') {
  $linkurl[$pointer] = $values[$i]['value'];
  $pointer++;
}
}

echo '<table>';
for($i=1;$i<=sizeof($linktitle);$i++) {
  if($linkurl[$i]!='' && $linktitle[$i]!='')
    echo '<tr><td><a href="'.$linkurl[$i].'">'.$linktitle[$i].'</a></td></tr>';
}
echo "</table>";

}*/


?>
