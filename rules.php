<?php
define('IN_ZDE', 1);
$starttime = microtime();
// Load player data when a SID is supplied so the navigation reflects the
// logged-in state. Without a SID the page is shown with the public layout.
if (isset($_GET['sid'])) {
    $FILE_REQUIRES_PC = false;
    include 'ingame.php';
} else {
    include 'gres.php';
    include 'layout.php';
}

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

showdoc('rules', 'Regeln');
?>
