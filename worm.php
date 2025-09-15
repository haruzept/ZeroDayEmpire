<?php

if (!defined('IN_ZDE')) {
    die('Hacking attempt');
}

global $dbcon;

$action = mt_rand(1, 3);

switch ($action) {

    case 1: // Geld aus Syndikatkasse eines Syndikate mit mehr als einer Million Credits klauen
        $victim = db_query('SELECT * FROM syndikate WHERE money>1000000 ORDER BY RAND() LIMIT 1;');
        if (!$victim) {
            break;
        }
        $victim = mysqli_fetch_assoc($victim);
        if ($victim['code'] == '') {
            break;
        }
        $creds = (int)$victim['money'];
        $creds = floor($creds / 1.5);
        $stolen = ($victim['money'] - $creds);
        $ev = nicetime4(
            ).' Ein gef&auml;hrlicher Internet-Wurm hat '.$stolen.' Credits aus der Syndikatkasse geklaut!'."\n";
        $victim['events'] = $ev.$victim['events'];
        db_query(
            'UPDATE `syndikate` SET `money`=' . mysqli_real_escape_string($dbcon, $creds) .
            ", `events`='" . mysqli_real_escape_string($dbcon, $victim['events']) .
            "' WHERE `id`='" . mysqli_real_escape_string($dbcon, $victim['id']) . "';"
        );
        echo mysqli_error($dbcon);
        db_query(
            'INSERT INTO logs SET type=\'worm_clmoney\', usr_id=\'' .
            mysqli_real_escape_string($dbcon, $victim['id']) .
            '\', payload=\'stole ' . mysqli_real_escape_string($dbcon, $stolen) .
            ' credits from syndikat ' . mysqli_real_escape_string($dbcon, $victim['id']) . '\';'
        );
        break;

    case 2: // PC von User aus dem oberen Teil der Rangliste blockieren
        $victim = db_query('SELECT * FROM users WHERE rank<=50 ORDER BY RAND() LIMIT 1;');
        if (!$victim) {
            break;
        }
        $victim = mysqli_fetch_assoc($victim);
        if ((int)$victim['id'] == 0) {
            break;
        }
        #echo '<br>id='.$victim['id'];
        $vpc = @mysqli_fetch_assoc(
            db_query('SELECT id,ip,name FROM servers WHERE owner=' . $victim['id'] . ' ORDER BY RAND() LIMIT 1;')
        );
        $blocked = time() + 6 * 60 * 60;
        db_query('UPDATE servers SET blocked=\'' . mysqli_real_escape_string($dbcon, $blocked) . '\' WHERE id=' . $vpc['id'] . ';');
        addsysmsg(
            $victim['id'],
            'Dein PC 10.47.'.$vpc['ip'].' ('.$vpc['name'].') wurde durch einen b&ouml;sartigen Wurm, der im Moment im Netz kursiert,
  bis '.nicetime($blocked).' blockiert!'
        );
        db_query(
            'INSERT INTO logs SET type=\'worm_blockserver\', usr_id=\'' .
            mysqli_real_escape_string($dbcon, $victim['id']) .
            '\', payload=\'blocked server ' . $vpc['id'] . '\';'
        );
        break;

    case 3: // PC von aktivem User aus dem Mittelfeld der Rangliste Credits schenken
        $ts = time() - 24 * 60 * 60;
        $victim = db_query(
            'SELECT * FROM users WHERE (rank>50 AND login_time>' . mysqli_real_escape_string($dbcon, $ts) . ') ORDER BY RAND() LIMIT 1;'
        );
        echo mysqli_error($dbcon);
        if (!$victim) {
            break;
        }
        $victim = mysqli_fetch_assoc($victim);
        if ((int)$victim['id'] == 0) {
            break;
        }
        #echo '<br>id='.$victim['id'];
        $vpc = @mysqli_fetch_assoc(
            db_query('SELECT id,ip,name,credits FROM servers WHERE owner=' . $victim['id'] . ' ORDER BY RAND() LIMIT 1;')
        );
        $plus = mt_rand(2000, 10000);
        $creds = $vpc['credits'] + $plus;
        db_query(
            'UPDATE servers SET credits=\'' . mysqli_real_escape_string($dbcon, $creds) . '\' WHERE id=' . mysqli_real_escape_string($dbcon, $vpc['id']) . ';'
        );
        addsysmsg(
            $victim['id'],
            'Auf deinen PC 10.47.'.$vpc['ip'].' ('.$vpc['name'].') wurde durch einen Wurm, der im Moment im Netz kursiert,
  die Summe von '.$plus.' Credits &uuml;berwiesen!'
        );
        db_query(
            'INSERT INTO logs SET type=\'worm_serversendmoney\', usr_id=\'' .
            mysqli_real_escape_string($dbcon, $victim['id']) .
            '\', payload=\'gave ' . mysqli_real_escape_string($dbcon, $plus) .
            ' credits to server ' . mysqli_real_escape_string($dbcon, $vpc['id']) . '\';'
        );
        break;

}


?>
