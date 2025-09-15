<?php
// Basic library for attack actions. This is a lightweight implementation
// that follows the specification in the task description. It is not a full
// game ready system but provides reusable calculation helpers and basic
// dependency checks so that the UI can be built around it.

require_once __DIR__.'/attacks_queries.php';

// ---------------------------------------------------------------------------
// Constants used for balancing. These can be tweaked later without touching
// the database content.
// ---------------------------------------------------------------------------

const ATTACK_MAX_LEVEL_DEFAULT = 5;
const RISK_REDUCTION_PER_RAM = 0.01;   // 1%
const DURATION_REDUCTION_PER_RAM = 0.01;
const DURATION_REDUCTION_PER_CPU = 0.02;
const DURATION_REDUCTION_PER_LAN = 0.01;
const RISK_REDUCTION_PER_R_VEIL = 0.01;
const PARALLEL_SLOTS_PER_LAN = 3; // 1 + floor(lan/3)

// Mapping of external research/hardware keys used in the SQL seed data to
// the real codes in the game. In this repository the codes already match, so
// the mapping is mostly identity but keeping the structure allows future
// adjustments with little effort.
const RMAP = [
  'r_se'   => 'r_se',
  'r_veil' => 'r_veil',
  'r_pers' => 'r_pers',
  'r_c2'   => 'r_c2',
  'r_data' => 'r_data',
  'r_lab'  => 'r_lab',
  'r_bauk' => 'r_bauk',
  'r_rans' => 'r_rans',
];
const HMAP = [
  'cpu' => 'cpu', 'ram' => 'ram', 'lan' => 'lan', 'mm' => 'mm', 'bb' => 'bb',
  'sdk' => 'sdk', 'mk' => 'mk', 'trojan' => 'trojan',
];

// ---------------------------------------------------------------------------
// Helper functions
// ---------------------------------------------------------------------------

/**
 * Return definition of an attack action.
 */
function get_attack_def(string $code): ?array {
    return db_fetch_attack_def($code);
}

/**
 * Load the player's state for an attack. If none exists default level 1/xp 0
 * is returned.
 */
function get_attack_state(int $pc, string $code): array {
    $state = db_fetch_attack_state($pc, $code);
    if (!$state) {
        return ['level' => 1, 'xp' => 0];
    }
    return $state;
}

/**
 * Calculate effective parameters for an action based on definition, state,
 * inventory and research levels.
 */
function calc_effective_params(array $def, array $state, array $inventar, array $research): array {
    $level = (int)($state['level'] ?? 1);
    $cost = (int)round($def['base_cost'] * pow($def['cost_mult'], $level - 1));
    $payout = (int)round($def['base_payout'] * pow($def['payout_mult'], $level - 1));
    $duration = (float)($def['base_time_min'] * pow($def['time_mult'], $level - 1));
    $risk = (float)($def['base_risk_pct'] * pow(0.90, $level - 1));

    // hardware / research modifiers
    $ram = (int)($inventar['ram'] ?? 0);
    $cpu = (int)($inventar['cpu'] ?? 0);
    $lan = (int)($inventar['lan'] ?? 0);
    $rveil = (int)($research['r_veil'] ?? 0);

    $risk -= $ram * RISK_REDUCTION_PER_RAM * 100;
    $risk -= $rveil * RISK_REDUCTION_PER_R_VEIL * 100;
    if ($risk < 0) $risk = 0;

    $duration *= (1 - $ram * DURATION_REDUCTION_PER_RAM);
    $duration *= (1 - $cpu * DURATION_REDUCTION_PER_CPU);
    $duration *= (1 - $lan * DURATION_REDUCTION_PER_LAN);

    $slots = 1 + floor($lan / PARALLEL_SLOTS_PER_LAN);

    return [
        'cost' => $cost,
        'payout_expected' => $payout,
        'duration_min' => (int)round($duration),
        'risk_pct' => (int)round($risk),
        'parallel_slots' => (int)$slots,
    ];
}

/**
 * Very small dependency check. The function returns an array with keys
 * `ok` and `missing`. Missing contains human readable labels.
 * This implementation does not query research/hardware and therefore only
 * reports dependencies based on the arrays provided.
 */
function check_dependencies(int $pc, string $code, int $targetLevel): array {
    $deps = db_fetch_deps($code);
    $missing = [];
    foreach ($deps as $dep) {
        if ($dep['dep_type'] === 'unlock' && $targetLevel === 1) {
            $ok = check_single_dep($pc, $dep);
            if (!$ok) $missing[] = $dep['req_key'];
        } elseif ($dep['dep_type'] === 'level_gate' && $targetLevel >= $dep['gate_level']) {
            $ok = check_single_dep($pc, $dep);
            if (!$ok) $missing[] = $dep['req_key'];
        }
    }
    return ['ok' => empty($missing), 'missing' => $missing];
}

function check_single_dep(int $pc, array $dep): bool {
    if ($dep['req_kind'] === 'research') {
        $level = db_fetch_research_level($pc, $dep['req_key']);
        return $level >= $dep['req_level'];
    }
    // hardware
    $level = db_fetch_hardware_level($pc, $dep['req_key']);
    return $level >= $dep['req_level'];
}

/**
 * Determine how many runs are currently active for a given player.
 */
function count_running(int $pc): int {
    return db_count_running($pc);
}

/**
 * Calculate available parallel slots from LAN level.
 */
function current_parallel_slots(int $lan): int {
    return 1 + floor($lan / PARALLEL_SLOTS_PER_LAN);
}

/**
 * Simple cooldown checker using attack_runs table.
 */
function is_on_cooldown(int $pc, string $code): bool {
    $ts = db_cooldown_until($pc, $code);
    return $ts !== null && $ts > time();
}

/**
 * Wrapper for starting a run. This only covers the basic DB insert and cost
 * deduction. The heavy lifting like risk or payout calculation is omitted in
 * this simplified reference implementation.
 */
function start_run(int $pc, string $code): int {
    $def = get_attack_def($code);
    if (!$def) { throw new Exception('Unknown attack'); }
    $state = get_attack_state($pc, $code);
    $inv = db_fetch_hardware_all($pc);
    $research = db_fetch_research_all($pc);
    $params = calc_effective_params($def, $state, $inv, $research);
    return db_start_run($pc, $code, $state['level'], $params);
}

// ----------------------------------------------------------------------------
// Controller helpers used by attacks.php
// ----------------------------------------------------------------------------

function attacks_start_controller(int $pc, string $code): array {
    $deps = check_dependencies($pc, $code, get_attack_state($pc, $code)['level']);
    if (!$deps['ok']) return ['ok'=>false,'message'=>'Voraussetzungen fehlen: '.implode(', ',$deps['missing'])];
    $lan = db_fetch_hardware_level($pc, 'lan');
    $slots = current_parallel_slots($lan);
    if (count_running($pc) >= $slots) return ['ok'=>false,'message'=>'Alle Angriffsslots belegt.'];
    if (is_on_cooldown($pc,$code)) return ['ok'=>false,'message'=>'Cooldown aktiv.'];
    $runId = start_run($pc,$code);
    return ['ok'=>true,'message'=>'Angriff gestartet (#'.$runId.').'];
}

// expose fetch helpers for UI use ------------------------------------------------
function attacks_list_all(): array {
    return db_list_attacks_with_state();
}

function attacks_recent_runs(int $pc, int $limit = 20): array {
    return db_recent_runs($pc, $limit);
}

