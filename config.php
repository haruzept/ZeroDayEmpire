<?php

# DATENBANKEN:
$database_prefix = 'zde_server';
$database_suffix = '';

$db_use_this_values = false;
$db_host = '';
$db_username = 'root';
$db_password = '';


# KEINE MITSPIELER:
$no_ranking_users = '1,2';
$no_ranking_clusters = '2'; # Nur eine Angabe möglich

# KONSTANTEN
define('UPDATE_INTERVAL', 10800, false); # Interval für Punkte-Updates in Sekunden
define('MIN_ATTACK_XDEFENCE', 9, false);
define('MIN_INACTIVE_TIME', 259200, false); # Inaktive Zeit vor möglichem Angriff
define('REMOTE_HIJACK_DELAY', 172800, false); # Wartezeit zwischen zwei Remote Hijacks
define('MAX_USERS_PER_SERVER', 4444, false); # Maximale Anzahl von Spielern pro Server

# DIVERSES
$REMOTE_FILES_DIR = '.'; # dreck


?>
