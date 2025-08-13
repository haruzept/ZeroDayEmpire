<?php
// Redirect visitors to the public entry point unless they already
// requested "pub.php" (which is rewritten to this file with ?page=pub).
if (!isset($_GET['page']) || $_GET['page'] !== 'pub') {
    header('Location: pub.php');
    exit;
}

// Further application logic would continue below once the correct page is
// requested.
?>
