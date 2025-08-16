#!/bin/sh
# Cron wrapper to update ranking points without web trigger
# Usage: schedule via crontab, e.g. every 3 hours
# 0 */3 * * * /bin/sh /path/to/cron/run_calc_points.sh
php "$(dirname "$0")/../run_calc_points.php"
