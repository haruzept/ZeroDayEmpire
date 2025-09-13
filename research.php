<?php
define('IN_ZDE', 1);
$FILE_REQUIRES_PC = true;
require_once __DIR__.'/ingame.php';

$action = $_REQUEST['a'] ?? $_REQUEST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'start') {
        $track = $_POST['id'] ?? $_POST['track'] ?? '';
        $res = research_start($pcid, $track);
        header('Content-Type: application/json');
        if (isset($res['error'])) {
            $reason = 'deps';
            if ($res['error'] === 'Nicht genügend Credits') {
                $reason = 'credits';
            } elseif ($res['error'] === 'Keine freien Slots') {
                $reason = 'slots';
            }
            echo json_encode(['ok' => false, 'reason' => $reason]);
        } else {
            echo json_encode(['ok' => true]);
        }
        exit;
    }
    if ($action === 'cancel') {
        $id = (int)($_POST['id'] ?? 0);
        $ok = research_cancel($pcid, $id);
        header('Content-Type: application/json');
        echo json_encode(['ok' => $ok]);
        exit;
    }
}

processupgrades($pc);
if ($pc['blocked'] > time()) { exit; }

function format_duration($seconds) {
    $seconds = (int)$seconds;
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    if ($h > 0) {
        return $m > 0 ? $h.' h '.$m.' min' : $h.' h';
    }
    return $m.' min';
}

$now = time();
$runningRows = [];
$r = db_query(
    'SELECT r.*, t.name FROM research r '
    .'JOIN research_tracks t ON t.track=r.track '
    .'WHERE r.pc=\''.mysql_escape_string($pcid).'\' AND r.`end`>\''.mysql_escape_string($now).'\' ORDER BY r.`end` ASC'
);
while ($row = mysql_fetch_assoc($r)) { $runningRows[] = $row; }
$running = count($runningRows);
$maxSlots = isset($pc['research_slots']) ? (int)$pc['research_slots'] : 1;
$credits = (int)$pc['credits'];
$nextEtaTs = $running ? (int)$runningRows[0]['end'] : 0;

$activeResearch = [];
foreach ($runningRows as $row) {
    $activeResearch[] = [
        'id' => (int)$row['id'],
        'name' => $row['name'],
        'level' => (int)$row['target_level'],
        'eta_text' => format_duration($row['end'] - $now)
    ];
}

require_once __DIR__.'/includes/research.php';
$tracks = research_get_tracks();
$research = [];
foreach ($tracks as $track => $info) {
    if ($info['level'] >= $info['max_level']) { continue; }
    $research[] = [
        'id' => $track,
        'name' => $info['name'],
        'level_cur' => (int)$info['level'],
        'level_max' => (int)$info['max_level'],
        'duration_sec' => (int)$info['next_time'],
        'duration_text' => format_duration($info['next_time']),
        'cost' => (int)$info['next_cost'],
        'deps_ok' => research_check_deps($pcid, $track, $info['level'] + 1) === true
    ];
}

createlayout_top('ZeroDayEmpire - Forschung');
?>

<div class="page-head container">
  <h1>Forschung</h1>
  <a href="index.php" class="btn ghost sm">Zur Übersicht</a>
</div>

<div class="container strip">
  <!-- Slots -->
  <div class="kpi kpi-icon">
    <div class="icon" aria-hidden="true">
      <!-- kleines Plus-Icon -->
      <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6z"/></svg>
    </div>
    <div class="stat">
      <div class="value"><?=(int)$running?> / <?=(int)$maxSlots?> <span class="unit">Slots</span></div>
      <div class="progress" aria-label="Forschungsslot-Auslastung">
        <div class="bar" style="width: <?=max(0,min(100, ($maxSlots>0? ($running/$maxSlots)*100 : 0)))?>%"></div>
      </div>
      <div class="label">Laufend / Gesamt</div>
    </div>
  </div>

  <!-- Credits -->
  <div class="kpi kpi-icon">
    <div class="icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M3 5h18v14H3zM5 9h14v2H5z"/></svg>
    </div>
    <div class="stat">
      <div class="value"><?=number_format($credits, 0, ',', '.') ?> <span class="unit">Credits</span></div>
      <div class="label">Verfügbar</div>
    </div>
  </div>

  <!-- Warteschlange / Status -->
  <div class="kpi kpi-icon">
    <div class="icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M12 7v5l4 2-.7 1.3L11 13V7zM12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>
    </div>
    <div class="stat">
      <?php if($running > 0): ?>
        <div class="value"><span id="eta">Berechnung…</span></div>
        <div class="label">Nächste Fertigstellung</div>
      <?php else: ?>
        <div class="value">Keine Forschung aktiv</div>
        <div class="label">Warteschlange</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if($running > 0): ?>
<div class="container card list" style="margin-top:8px">
  <h2>Laufende Forschung</h2>
  <ul class="list">
    <?php foreach($activeResearch as $r): ?>
      <li class="list-row">
        <span class="name"><?=htmlspecialchars($r['name'])?> (Level <?= (int)$r['level']?>)</span>
        <span class="muted" id="eta-<?= (int)$r['id']?>"><?=htmlspecialchars($r['eta_text'])?></span>
        <button class="button cancel-research" data-id="<?= (int)$r['id']?>">Abbrechen</button>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<div class="container card table-card">
  <h2>Verfügbare Forschung</h2>
  <table>
    <thead>
      <tr>
        <th>Zweig</th>
        <th>Level</th>
        <th>Dauer</th>
        <th>Kosten</th>
        <th>Status</th>
        <th class="number">Aktion</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($research as $row):
        $depsOk = !empty($row['deps_ok']);
        $enoughCredits = ($credits >= $row['cost']);
        $slotFree = ($running < $maxSlots);
        $canStart = $depsOk && $enoughCredits && $slotFree;
      ?>
      <tr>
        <td class="name"><?=htmlspecialchars($row['name'])?></td>
        <td><?= (int)$row['level_cur']?>/<?= (int)$row['level_max']?></td>
        <td><?=htmlspecialchars($row['duration_text'])?></td>
        <td class="number"><?=number_format($row['cost'], 0, ',', '.') ?> Credits</td>

        <td>
          <?php if($depsOk): ?>
            <span class="badge ok">Erfüllte Abhängigkeit</span>
          <?php else: ?>
            <span class="badge fail">Abhängigkeit fehlt</span>
          <?php endif; ?>
        </td>

        <td class="number">
          <button
            class="button start-research"
            <?= $canStart ? '' : 'disabled aria-disabled="true" data-tooltip="'.(
                  !$depsOk ? 'Abhängigkeit fehlt' :
                  (!$slotFree ? 'Alle Forsch-Slots belegt' : 'Zu wenig Credits')
                ).'"'; ?>
            data-id="<?=htmlspecialchars($row['id'])?>"
            data-cost="<?= (int)$row['cost'] ?>"
            data-duration="<?= (int)$row['duration_sec'] ?>"
          >Erforschen</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
  // Beispiel: Countdown für KPI-„eta“
  <?php if(!empty($nextEtaTs)): ?>
  (function(){
    const el = document.getElementById('eta');
    const end = <?= (int)$nextEtaTs ?> * 1000;
    function tick(){
      const t = Math.max(0, end - Date.now());
      const h = Math.floor(t/3600000), m = Math.floor((t%3600000)/60000);
      el.textContent = (h? h+' h ' : '') + m + ' min';
      if(t>0) requestAnimationFrame(tick);
    }
    tick();
  })();
  <?php endif; ?>

  // „Erforschen“ mit Inline-Spinner und Kontrast sicher
  document.querySelectorAll('.start-research').forEach(btn=>{
    btn.addEventListener('click', async ()=>{
      if(btn.disabled) return;
      const label = btn.textContent;
      btn.innerHTML = '<span class="spinner" aria-hidden="true"></span>';
      try{
        const res = await fetch('research.php?a=start', {
          method:'POST',
          headers:{'Content-Type':'application/x-www-form-urlencoded'},
          body: new URLSearchParams({ track: btn.dataset.id })
        }).then(r=>r.json());
        if(res.ok){ location.reload(); }
        else{
          btn.textContent = label;
          btn.setAttribute('data-tooltip',
            res.reason==='credits' ? 'Zu wenig Credits' :
            res.reason==='slots'   ? 'Alle Forsch-Slots belegt' :
            'Abhängigkeit fehlt'
          );
        }
      }catch(e){ btn.textContent = label; }
    });
  });

  document.querySelectorAll('.cancel-research').forEach(btn=>{
    btn.addEventListener('click', async ()=>{
      const id = btn.dataset.id;
      btn.disabled = true;
      try{
        const res = await fetch('research.php?a=cancel', {
          method:'POST',
          headers:{'Content-Type':'application/x-www-form-urlencoded'},
          body: new URLSearchParams({ id })
        }).then(r=>r.json());
        if(res.ok){ location.reload(); }
        else{ btn.disabled = false; }
      }catch(e){ btn.disabled=false; }
    });
  });
</script>

<?php
createlayout_bottom();
?>
