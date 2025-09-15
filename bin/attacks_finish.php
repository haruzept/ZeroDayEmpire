<?php
// CLI helper to finish due attack runs. This is intentionally lightweight and
// mainly demonstrates how the cronjob would call into the library.

define('IN_ZDE', 1);
require_once __DIR__.'/../includes/attacks_lib.php';

// Placeholder: in a full implementation finish_due_runs() would check DB for
// expired entries and payout coins. Here we just provide the function stub so
// that the cron script can run without fatal errors.

if (function_exists('finish_due_runs')) {
    finish_due_runs();
} else {
    // simple stub
    echo "finish_due_runs() not implemented\n";
}
