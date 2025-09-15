<?php
// This file contains thin wrappers around SQL queries used by the attacks
// module. The real game would use a database abstraction layer; here we use
// the legacy mysql_* style helpers already present in the project.

if (!defined('IN_ZDE')) {
    define('IN_ZDE', 1);
}
require_once __DIR__.'/../config.php';

// Fetch a single attack definition
function db_fetch_attack_def(string $code): ?array {
    $code = mysql_escape_string($code);
    $r = mysql_query("SELECT * FROM attack_actions WHERE code='".$code."'");
    if ($r && $row = mysql_fetch_assoc($r)) {
        return $row;
    }
    return null;
}

// Fetch player state for an attack
function db_fetch_attack_state(int $pc, string $code): ?array {
    $code = mysql_escape_string($code);
    $pc = (int)$pc;
    $r = mysql_query("SELECT level,xp FROM attack_state WHERE pc=$pc AND code='".$code."'");
    if ($r && $row = mysql_fetch_assoc($r)) { return $row; }
    return null;
}

// Fetch all deps for an attack
function db_fetch_deps(string $code): array {
    $code = mysql_escape_string($code);
    $res = mysql_query("SELECT * FROM attack_deps WHERE code='".$code."'");
    $rows = [];
    while ($res && $row = mysql_fetch_assoc($res)) { $rows[] = $row; }
    return $rows;
}

// Simple hardware / research level queries (legacy tables assumed)
function db_fetch_research_level(int $pc, string $key): int {
    $pc = (int)$pc; $key = mysql_escape_string($key);
    $r = mysql_query("SELECT level FROM research_tracks WHERE pc=$pc AND track='".$key."'");
    if ($r && $row = mysql_fetch_assoc($r)) return (int)$row['level'];
    return 0;
}

function db_fetch_hardware_level(int $pc, string $key): int {
    $pc = (int)$pc; $key = mysql_escape_string($key);
    $r = mysql_query("SELECT qty FROM hardware WHERE pc=$pc AND code='".$key."'");
    if ($r && $row = mysql_fetch_assoc($r)) return (int)$row['qty'];
    return 0;
}

function db_fetch_hardware_all(int $pc): array {
    $pc = (int)$pc;
    $r = mysql_query("SELECT code,qty FROM hardware WHERE pc=$pc");
    $rows = [];
    while ($r && $row = mysql_fetch_assoc($r)) { $rows[$row['code']] = (int)$row['qty']; }
    return $rows;
}

function db_fetch_research_all(int $pc): array {
    $pc = (int)$pc;
    $r = mysql_query("SELECT track,level FROM research_tracks WHERE pc=$pc");
    $rows = [];
    while ($r && $row = mysql_fetch_assoc($r)) { $rows[$row['track']] = (int)$row['level']; }
    return $rows;
}

function db_count_running(int $pc): int {
    $pc = (int)$pc;
    $r = mysql_query("SELECT COUNT(*) AS c FROM attack_runs WHERE pc=$pc AND status='running'");
    if ($r && $row = mysql_fetch_assoc($r)) return (int)$row['c'];
    return 0;
}

function db_cooldown_until(int $pc, string $code): ?int {
    $pc = (int)$pc; $code = mysql_escape_string($code);
    $r = mysql_query("SELECT MAX(cooldown_until) AS c FROM attack_runs WHERE pc=$pc AND code='".$code."'");
    if ($r && $row = mysql_fetch_assoc($r) && $row['c']) return strtotime($row['c']);
    return null;
}

// Start a run: deduct cost and insert log entry
function db_start_run(int $pc, string $code, int $level, array $params): int {
    $pc = (int)$pc; $code = mysql_escape_string($code);
    $cost = (int)$params['cost'];
    $payout = (int)$params['payout_expected'];
    $dur = (int)$params['duration_min'];
    $start = time();
    $end = $start + $dur * 60;
    // cost deduction (legacy table cc field on pcs?)
    mysql_query("UPDATE pcs SET cc=cc-$cost WHERE id=$pc");
    mysql_query("INSERT INTO attack_runs(pc,code,level_snapshot,cost,payout_expected,status,started_at,ends_at) VALUES ($pc,'$code',$level,$cost,$payout,'running',FROM_UNIXTIME($start),FROM_UNIXTIME($end))");
    return mysql_insert_id();
}

// fetch list of attack defs joined with player state - placeholder simple query
function db_list_attacks_with_state(): array {
    $res = mysql_query("SELECT * FROM attack_actions");
    $rows = [];
    while ($res && $row = mysql_fetch_assoc($res)) { $rows[] = $row; }
    return $rows;
}

function db_recent_runs(int $pc, int $limit): array {
    $pc = (int)$pc; $limit = (int)$limit;
    $res = mysql_query("SELECT * FROM attack_runs WHERE pc=$pc ORDER BY id DESC LIMIT $limit");
    $rows = [];
    while ($res && $row = mysql_fetch_assoc($res)) { $rows[] = $row; }
    return $rows;
}

