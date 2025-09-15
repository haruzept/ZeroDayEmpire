<?php
if (!defined('IN_ZDE')) { die('Hacking attempt'); }

function research_get_tracks()
{
    global $pc;
    $pcid = (int)$pc['id'];
    $tracks = array();
    $r = db_query(
        'SELECT t.*, COALESCE(s.level,0) AS level FROM research_tracks t '
        .'LEFT JOIN research_state s ON s.track=t.track AND s.pc=\''.mysql_escape_string($pcid).'\''
        .' ORDER BY t.track'
    );
    while ($row = mysql_fetch_assoc($r)) {
        $cur = (int)$row['level'];
        $row['level'] = $cur;
        if ($cur < $row['max_level']) {
            $calc = research_calculate($row['base_cost'],$row['cost_mult'],$row['base_time_min'],$row['time_mult'],$cur+1);
            $row['next_cost'] = $calc['cost'];
            $row['next_time'] = $calc['time'];
        } else {
            $row['next_cost'] = 0;
            $row['next_time'] = 0;
        }
        $tracks[$row['track']] = $row;
    }
    return $tracks;
}

function research_calculate($base_cost,$cost_mult,$base_time_min,$time_mult,$target_level)
{
    global $pc;
    $cost = (int)round($base_cost * pow($cost_mult, $target_level-1));
    $time_min = (int)ceil($base_time_min * pow($time_mult, $target_level-1));
    if (function_exists('duration_faktor')) {
        $df = duration_faktor($pc['cpu'],$pc['ram']);
    } else {
        $df = 1;
    }
    $time = (int)ceil($time_min * $df * 60);
    return array('cost'=>$cost,'time'=>$time);
}

function research_check_deps($pcid,$track,$target_level)
{
    $pcid = (int)$pcid;
    $track = mysql_escape_string($track);
    $target_level = (int)$target_level;
    $r = db_query(
        'SELECT type,gate_level,req_track,req_level FROM research_deps WHERE track=\''.$track.'\' '
        .'AND (type=\'unlock\' OR (type=\'level_gate\' AND gate_level=\''.$target_level.'\'))'
    );
    while ($dep = mysql_fetch_assoc($r)) {
        $req = db_query(
            'SELECT level FROM research_state WHERE pc=\''.mysql_escape_string($pcid).'\' AND track=\''.mysql_escape_string($dep['req_track']).'\' LIMIT 1'
        );
        $level = ($tmp = mysql_fetch_assoc($req)) ? (int)$tmp['level'] : 0;
        if ($level < (int)$dep['req_level']) {
            $tn = db_query(
                'SELECT name FROM research_tracks WHERE track=\''.mysql_escape_string($dep['req_track']).'\' LIMIT 1'
            );
            $reqName = (mysql_num_rows($tn) > 0) ? mysql_result($tn,0,'name') : $dep['req_track'];
            return 'Benötigt '.$reqName.' Stufe '.$dep['req_level'];
        }
    }
    return true;
}

function research_slots_available($pcid)
{
    global $pc;
    $pcid = (int)$pcid;
    $r = db_query('SELECT COUNT(*) AS c FROM research WHERE pc=\''.mysql_escape_string($pcid).'\' AND `end`>\''.time().'\'');
    $cnt = (int)mysql_result($r,0,'c');
    $max = isset($pc['research_slots']) ? (int)$pc['research_slots'] : 1;
    if ($cnt >= $max) {
        return false;
    }
    return $max - $cnt;
}

function research_start($pcid,$track)
{
    global $pc;
    $pcid = (int)$pcid;
    $track = mysql_escape_string($track);
    $slot = research_slots_available($pcid);
    if ($slot === false) {
        return array('error'=>'Keine freien Slots');
    }
    $r = db_query(
        'SELECT t.*, COALESCE(s.level,0) AS level FROM research_tracks t '
        .'LEFT JOIN research_state s ON s.track=t.track AND s.pc=\''.mysql_escape_string($pcid).'\''
        .' WHERE t.track=\''.$track.'\' LIMIT 1'
    );
    if (!$row = mysql_fetch_assoc($r)) {
        return array('error'=>'Unbekannter Zweig');
    }
    $cur = (int)$row['level'];
    if ($cur >= $row['max_level']) {
        return array('error'=>'Maximale Stufe erreicht');
    }
    $target = $cur + 1;
    $dep = research_check_deps($pcid,$track,$target);
    if ($dep !== true) {
        return array('error'=>$dep);
    }
    $calc = research_calculate($row['base_cost'],$row['cost_mult'],$row['base_time_min'],$row['time_mult'],$target);
    if ($pc['cryptocoins'] < $calc['cost']) {
        return array('error'=>'Nicht genügend CryptoCoins');
    }
    db_query('UPDATE pcs SET cryptocoins=cryptocoins-'.mysql_escape_string($calc['cost']).' WHERE id=\''.mysql_escape_string($pcid).'\' AND cryptocoins>='.mysql_escape_string($calc['cost']).'');
    if (mysql_affected_rows() < 1) {
        return array('error'=>'Nicht genügend CryptoCoins');
    }
    $pc['cryptocoins'] -= $calc['cost'];
    $start = time();
    $end = $start + $calc['time'];
    db_query('INSERT INTO research SET pc=\''.mysql_escape_string($pcid).'\', `start`=\''.mysql_escape_string($start).'\', `end`=\''.mysql_escape_string($end).'\', track=\''.$track.'\', target_level=\''.mysql_escape_string($target).'\'');
    return array('id'=>mysql_insert_id(),'end'=>$end);
}

function research_process($now = null)
{
    if ($now === null) { $now = time(); }
    $r = db_query('SELECT * FROM research WHERE `end`<=\''.mysql_escape_string($now).'\' ORDER BY `start` ASC');
    while ($row = mysql_fetch_assoc($r)) {
        db_query('INSERT INTO research_state SET pc=\''.mysql_escape_string($row['pc']).'\', track=\''.mysql_escape_string($row['track']).'\', level=\''.mysql_escape_string($row['target_level']).'\' ON DUPLICATE KEY UPDATE level=VALUES(level)');
        db_query('DELETE FROM research WHERE id=\''.mysql_escape_string($row['id']).'\'');
    }
}

function research_cancel($pcid,$id)
{
    $pcid = (int)$pcid;
    $id = (int)$id;
    db_query('DELETE FROM research WHERE pc=\''.mysql_escape_string($pcid).'\' AND id=\''.mysql_escape_string($id).'\'');
    return mysql_affected_rows() > 0;
}
?>
