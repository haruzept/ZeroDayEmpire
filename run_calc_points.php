<?php
// Wrapper script to run point calculation from CLI.
// Defines IN_ZDE constant and includes calc_points.php
// to allow execution without direct web access.

define('IN_ZDE', 1);
require __DIR__ . '/calc_points.php';
