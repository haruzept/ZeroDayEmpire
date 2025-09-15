<?php
define('IN_ZDE', 1);
$FILE_REQUIRES_PC = true;
include('ingame.php');

$bucks = number_format($pc['cryptocoins'], 0, ',', '.');

if ($pc['blocked'] > time()) {
    exit;
}
if ($pc['bb'] < 2 || $pc['mm'] < 2) {
    simple_message('Neeee so einfach nicht!');
    exit;
}

$javascript = '<script type="text/javascript">' . "\n";
if ($usr['bigacc'] == 'yes') {
    $javascript .= 'function fill(s) { document.frm.pcip.value=s; }\n';
}
$javascript .= 'function autosel(obj) { var i = (obj.name==\'pcip\' ? 1 : 0);\n  document.frm.reciptype[i].checked=true; }\n</script>';
createlayout_top('ZeroDayEmpire - Geld &uuml;berweisen');
?>
<!-- ZDE theme inject -->
<div class="container">
<?php // /ZDE theme inject start

if ($usr['bigacc'] == 'yes') {
    $bigacc = '&nbsp;<a href="javascript:show_abook(\\\'pc\\\')">Adressbuch</a>';
}
echo '<div class="content" id="server">\n<h2>Dein Server</h2>\n<div id="server-transfer-start">\n<h3>Geld &uuml;berweisen</h3>\n'.$notif.'<br />\n<p><b>Geld: '.$bucks.' CryptoCoins</b></p>\n<form action="game.php?a=transfer&sid='.$sid.'" method="post" name="frm">\n<table>\n<tr><th colspan="3">&Uuml;berweisung</th></tr>\n<tr><th>Empf&auml;nger:</th><td>\n<table>\n<tr><td><input type="radio" name="reciptype" value="syndikat" id="_syndikat"><label for="_syndikat">Ein Syndikat</label></td>\n<td> - Code: <input onchange="autosel(this)" name="syndikatcode" size="12" maxlength="12"></td></tr>\n<tr><td><input type="radio" checked="checked" name="reciptype" value="user" id="_user"><label for="_user">Ein Benutzer</label></td>\n<td> - IP: 10.47.<input onchange="autosel(this)" name="pcip" size="7" maxlength="7">'.$bigacc.'</td></tr>\n</table>\n</td></tr>\n<tr><th>Betrag:</th><td><input name="cryptocoins" size="5" maxlength="6" value="0"> CryptoCoins</td></tr>\n<tr><th>&nbsp;</th><td><input type="submit" value=" Ausf&uuml;hren "></td></tr>\n</table></form>\n</div>\n</div>';
?>
</div>
<!-- /ZDE theme inject -->
<?php
createlayout_bottom();
